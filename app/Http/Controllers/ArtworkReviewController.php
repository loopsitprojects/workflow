<?php

namespace App\Http\Controllers;

use App\Models\ArtworkAnnotation;
use App\Models\ArtworkAnnotationComment;
use App\Models\ArtworkReview;
use App\Models\Deliverable;
use Illuminate\Http\Request;

class ArtworkReviewController extends Controller
{
    // =========================================================================
    //  PUBLIC (no auth) — Client-facing
    // =========================================================================

    /**
     * Show the public artwork review page for the client.
     * Route: GET /artwork-review/{token}
     */
    public function show(string $token)
    {
        if (!\App\Services\FeatureManager::isClientReviewEnabled()) {
            return response()->view('artwork.expired', ['message' => 'The client review portal is currently disabled.'], 404);
        }

        $review = ArtworkReview::where('token', $token)
            ->with(['deliverable.subtasks', 'annotations.comments.user', 'annotations.resolvedBy'])
            ->firstOrFail();

        if (!$review->isAccessible()) {
            return view('artwork.expired');
        }

        $artworks = $review->deliverable ? $review->deliverable->getAllArtworkFiles() : [];

        return view('artwork.review', compact('review', 'artworks'));
    }

    /**
     * Store annotations submitted by the client.
     * Route: POST /artwork-review/{token}/annotate
     */
    public function store(Request $request, string $token)
    {
        if (!\App\Services\FeatureManager::isClientReviewEnabled()) {
            return response()->json(['error' => 'The client review portal is currently disabled.'], 403);
        }

        $review = ArtworkReview::where('token', $token)->firstOrFail();

        if (!$review->isAccessible()) {
            return response()->json(['error' => 'This review link has expired or been deactivated.'], 403);
        }

        $data = $request->validate([
            'client_name'                => ['nullable', 'string', 'max:120'],
            'annotations'                => ['required', 'array', 'min:1'],
            'annotations.*.type'         => ['required', 'in:pin,drawing,text'],
            'annotations.*.artwork_index'=> ['nullable', 'integer', 'min:0'],
            'annotations.*.image_url'    => ['nullable', 'string'],
            'annotations.*.x_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'annotations.*.y_percent'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'annotations.*.content'      => ['nullable', 'string'],
            'annotations.*.color'        => ['nullable', 'string', 'max:20'],
            'annotations.*.pin_number'   => ['nullable', 'integer'],
        ]);

        // Save client name if provided
        if (!empty($data['client_name'])) {
            $review->update(['client_name' => $data['client_name']]);
        }

        foreach ($data['annotations'] as $ann) {
            $review->annotations()->create([
                'artwork_index' => $ann['artwork_index'] ?? 0,
                'image_url'     => $ann['image_url'] ?? null,
                'type'          => $ann['type'],
                'x_percent'     => $ann['x_percent'] ?? null,
                'y_percent'     => $ann['y_percent'] ?? null,
                'content'       => $ann['content'] ?? null,
                'color'         => $ann['color'] ?? '#ef4444',
                'pin_number'    => $ann['pin_number'] ?? null,
                'is_resolved'   => false,
            ]);
        }

        // Notify Brand Manager / Lead / link creator
        $deliverable = $review->deliverable;
        if ($deliverable) {
            $deliverable->update(['client_status' => 'Client Revisions']);
            if ($deliverable->parent_deliverable_id) {
                Deliverable::where('id', $deliverable->parent_deliverable_id)->update(['client_status' => 'Client Revisions']);
            }
        }

        $manager = null;
        if ($deliverable) {
            $manager = $deliverable->brandManager ?? $deliverable->project?->lead;
        }

        // Fallback to link creator
        if (!$manager) {
            $manager = $review->creator;
        }

        if ($manager) {
            try {
                $manager->notify(new \App\Notifications\ArtworkReviewSubmitted($review, $review->client_name ?? 'Client'));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send ArtworkReviewSubmitted notification: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => 'Your feedback has been submitted successfully!']);
    }

    /**
     * Client approves the artwork deliverable.
     * Route: POST /artwork-review/{token}/approve
     */
    public function approve(Request $request, string $token)
    {
        if (!\App\Services\FeatureManager::isClientReviewEnabled()) {
            return response()->json(['error' => 'The client review portal is currently disabled.'], 403);
        }

        $review = ArtworkReview::where('token', $token)->firstOrFail();

        if (!$review->isAccessible()) {
            return response()->json(['error' => 'This review link has expired or been deactivated.'], 403);
        }

        $deliverable = $review->deliverable;
        if ($deliverable) {
            $deliverable->update(['client_status' => 'Client Approved']);
            if ($deliverable->parent_deliverable_id) {
                Deliverable::where('id', $deliverable->parent_deliverable_id)->update(['client_status' => 'Client Approved']);
            }
        }

        return response()->json(['success' => true, 'message' => 'Artwork has been approved!']);
    }

    // =========================================================================
    //  AUTH-PROTECTED — Team-facing
    // =========================================================================

    /**
     * Generate or fetch the single permanent shareable review link for a deliverable.
     * Route: POST /deliverables/{deliverable}/send-artwork
     */
    public function create(Request $request, Deliverable $deliverable)
    {
        if (!\App\Services\FeatureManager::isClientReviewEnabled()) {
            return response()->json(['success' => false, 'message' => 'The Send to Client feature is disabled by administrator.'], 403);
        }

        $review = ArtworkReview::firstOrCreate(
            ['deliverable_id' => $deliverable->id],
            [
                'round_number'   => 1,
                'token'          => ArtworkReview::generateToken(),
                'expires_at'     => null,
                'is_active'      => true,
                'created_by'     => auth()->id(),
            ]
        );

        $review->update([
            'is_active'  => true,
            'expires_at' => null,
        ]);

        // Automatically update deliverable client_status to 'Sent to Client'
        $deliverable->update(['client_status' => 'Sent to Client']);
        if ($deliverable->parent_deliverable_id) {
            Deliverable::where('id', $deliverable->parent_deliverable_id)->update(['client_status' => 'Sent to Client']);
        }

        $url = route('artwork.review.show', ['token' => $review->token]);

        return response()->json([
            'success' => true,
            'url'     => $url,
            'review'  => [
                'id'           => $review->id,
                'token'        => $review->token,
                'url'          => $url,
            ],
        ]);
    }

    /**
     * Show the team annotation dashboard for a deliverable.
     * Route: GET /deliverables/{deliverable}/artwork-review
     */
    public function dashboard(Deliverable $deliverable)
    {
        $deliverable->load('subtasks');
        $artworks = $deliverable->getAllArtworkFiles();

        $review = ArtworkReview::firstOrCreate(
            ['deliverable_id' => $deliverable->id],
            [
                'round_number'   => 1,
                'token'          => ArtworkReview::generateToken(),
                'expires_at'     => null,
                'is_active'      => true,
                'created_by'     => auth()->id(),
            ]
        );

        $review->load(['annotations.resolvedBy', 'annotations.comments.user', 'creator']);

        return view('artwork.team_view', compact('deliverable', 'review', 'artworks'));
    }

    /**
     * Add a comment to an artwork annotation.
     * Route: POST /artwork-annotations/{annotation}/respond
     */
    public function respond(Request $request, ArtworkAnnotation $annotation)
    {
        $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
            'response_text' => ['nullable', 'string', 'max:2000'],
        ]);

        $commentText = trim($request->input('comment') ?? $request->input('response_text') ?? '');
        if (empty($commentText)) {
            return response()->json(['error' => 'Comment cannot be empty.'], 422);
        }

        $comment = $annotation->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $commentText,
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id'               => $comment->id,
                'comment'          => $comment->comment,
                'user_id'          => $comment->user_id,
                'user_name'        => auth()->user()?->name ?? 'Team Member',
                'user_initials'    => strtoupper(substr(auth()->user()?->name ?? 'T', 0, 2)),
                'created_at_human' => $comment->created_at->diffForHumans(),
                'can_delete'       => true,
            ],
        ]);
    }

    /**
     * Delete a comment from an artwork annotation.
     * Route: DELETE /artwork-annotation-comments/{comment}
     */
    public function deleteComment(ArtworkAnnotationComment $comment)
    {
        if ($comment->user_id && $comment->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            return response()->json(['error' => 'You can only delete your own comments.'], 403);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }

    public function resolve(ArtworkAnnotation $annotation)
    {
        $annotation->update([
            'is_resolved' => !$annotation->is_resolved,
            'resolved_by' => $annotation->is_resolved ? null : auth()->id(),
            'resolved_at' => $annotation->is_resolved ? null : now(),
        ]);

        $review = $annotation->review;
        if ($review && $review->deliverable) {
            $deliv = $review->deliverable;
            $unresolved = $review->annotations()->where('is_resolved', false)->exists();
            $newStatus = $unresolved ? 'Client Revisions' : 'Client Approved';
            $deliv->update(['client_status' => $newStatus]);
            if ($deliv->parent_deliverable_id) {
                Deliverable::where('id', $deliv->parent_deliverable_id)->update(['client_status' => $newStatus]);
            }
        }

        return response()->json([
            'success'     => true,
            'is_resolved' => $annotation->fresh()->is_resolved,
        ]);
    }

    /**
     * Deactivate a review link.
     * Route: DELETE /artwork-reviews/{review}
     */
    public function destroy(ArtworkReview $review)
    {
        $review->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Get reviews for a deliverable (JSON — used by the show modal).
     * Route: GET /deliverables/{deliverable}/artwork-reviews-json
     */
    public function reviewsJson(Deliverable $deliverable)
    {
        $reviews = ArtworkReview::where('deliverable_id', $deliverable->id)
            ->withCount(['annotations', 'annotations as unresolved_count' => function ($q) {
                $q->where('is_resolved', false);
            }])
            ->latest()
            ->get()
            ->map(function ($r) {
                return [
                    'id'               => $r->id,
                    'round_number'     => $r->round_number ?? 1,
                    'url'              => route('artwork.review.show', ['token' => $r->token]),
                    'is_active'        => $r->is_active,
                    'expires_at'       => $r->expires_at?->toDateString(),
                    'client_name'      => $r->client_name,
                    'annotations_count'=> $r->annotations_count,
                    'unresolved_count' => $r->unresolved_count,
                    'created_at'       => $r->created_at->diffForHumans(),
                    'is_accessible'    => $r->isAccessible(),
                ];
            });

        return response()->json($reviews);
    }
}
