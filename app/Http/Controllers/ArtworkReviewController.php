<?php

namespace App\Http\Controllers;

use App\Models\ArtworkAnnotation;
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
        $review = ArtworkReview::where('token', $token)
            ->with(['deliverable', 'annotations'])
            ->firstOrFail();

        if (!$review->isAccessible()) {
            return view('artwork.expired');
        }

        return view('artwork.review', compact('review'));
    }

    /**
     * Store annotations submitted by the client.
     * Route: POST /artwork-review/{token}/annotate
     */
    public function store(Request $request, string $token)
    {
        $review = ArtworkReview::where('token', $token)->firstOrFail();

        if (!$review->isAccessible()) {
            return response()->json(['error' => 'This review link has expired or been deactivated.'], 403);
        }

        $data = $request->validate([
            'client_name'   => ['nullable', 'string', 'max:120'],
            'annotations'   => ['required', 'array', 'min:1'],
            'annotations.*.type'      => ['required', 'in:pin,drawing,text'],
            'annotations.*.x_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'annotations.*.y_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'annotations.*.content'   => ['nullable', 'string'],
            'annotations.*.color'     => ['nullable', 'string', 'max:20'],
            'annotations.*.pin_number'=> ['nullable', 'integer'],
        ]);

        // Save client name if provided
        if (!empty($data['client_name'])) {
            $review->update(['client_name' => $data['client_name']]);
        }

        // Remove previous annotations for this review (replace with new submission)
        $review->annotations()->delete();

        foreach ($data['annotations'] as $ann) {
            $review->annotations()->create([
                'type'       => $ann['type'],
                'x_percent'  => $ann['x_percent'] ?? null,
                'y_percent'  => $ann['y_percent'] ?? null,
                'content'    => $ann['content'] ?? null,
                'color'      => $ann['color'] ?? '#ef4444',
                'pin_number' => $ann['pin_number'] ?? null,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Your feedback has been submitted successfully!']);
    }

    // =========================================================================
    //  AUTH-PROTECTED — Team-facing
    // =========================================================================

    /**
     * Generate a new review link for a deliverable.
     * Route: POST /deliverables/{deliverable}/send-artwork
     */
    public function create(Request $request, Deliverable $deliverable)
    {
        $request->validate([
            'expires_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $days    = $request->input('expires_days', 30);
        $review  = ArtworkReview::create([
            'deliverable_id' => $deliverable->id,
            'token'          => ArtworkReview::generateToken(),
            'expires_at'     => now()->addDays($days),
            'is_active'      => true,
            'created_by'     => auth()->id(),
        ]);

        $url = route('artwork.review.show', ['token' => $review->token]);

        return response()->json([
            'success' => true,
            'url'     => $url,
            'review'  => [
                'id'         => $review->id,
                'token'      => $review->token,
                'expires_at' => $review->expires_at?->toDateString(),
                'url'        => $url,
            ],
        ]);
    }

    /**
     * Show the team annotation dashboard for a deliverable.
     * Route: GET /deliverables/{deliverable}/artwork-review
     */
    public function dashboard(Deliverable $deliverable)
    {
        $reviews = ArtworkReview::where('deliverable_id', $deliverable->id)
            ->with(['annotations.resolvedBy', 'creator'])
            ->latest()
            ->get();

        return view('artwork.team_view', compact('deliverable', 'reviews'));
    }

    /**
     * Toggle resolved state of an annotation.
     * Route: POST /artwork-annotations/{annotation}/resolve
     */
    public function resolve(ArtworkAnnotation $annotation)
    {
        $annotation->update([
            'is_resolved' => !$annotation->is_resolved,
            'resolved_by' => $annotation->is_resolved ? null : auth()->id(),
            'resolved_at' => $annotation->is_resolved ? null : now(),
        ]);

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
