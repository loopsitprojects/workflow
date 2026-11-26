<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Deliverable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RevisionAnalyticsService
{
    protected ?string $geminiApiKey;
    protected string $geminiModel;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->geminiModel = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));
    }

    /**
     * Perform deep diagnostic analysis on why a specific deliverable accumulated revisions.
     */
    public function analyzeDeliverableRevisions(Deliverable $deliverable): array
    {
        $deliverable->loadMissing([
            'project.brand',
            'revisionsHistory.user',
            'revisionsHistory.fixedByUser',
            'artworkReviews.annotations'
        ]);

        $revisions = $deliverable->revisionsHistory->sortBy('created_at')->values();
        $totalRevisions = $revisions->count();

        // 1. If no revisions exist
        if ($totalRevisions === 0) {
            return [
                'success' => true,
                'total_revisions' => 0,
                'reply' => "### ✨ No Revisions Recorded\n\n**\"{$deliverable->title}\"** has **0 revision rounds** on record. The creative assets and copy have progressed cleanly through the approval pipeline without route-back requests.",
            ];
        }

        // 2. Compile revision rounds details
        $revisionTimeline = [];
        foreach ($revisions as $idx => $rev) {
            $roundNum = $idx + 1;
            $requester = $rev->user ? "{$rev->user->name} ({$rev->user->role})" : 'Reviewer';
            $stage = $rev->stage_at_revision ?: 'Review Stage';
            $target = ucfirst($rev->target ?: 'Creative Team');
            $instructions = trim($rev->instructions ?: 'No written feedback provided.');
            $fixedStatus = $rev->fixed_at ? "Resolved by " . ($rev->fixedByUser?->name ?? 'User') . " on " . $rev->fixed_at->format('M d, H:i') : "Pending resolution";

            $revisionTimeline[] = [
                'round' => "Round {$roundNum} (R{$roundNum})",
                'requested_by' => $requester,
                'stage_at_revision' => $stage,
                'assigned_to' => $target,
                'instructions' => $instructions,
                'status' => $fixedStatus,
                'created_at' => $rev->created_at?->format('M d, Y H:i') ?? 'N/A',
            ];
        }

        // 3. Compile client annotations if any
        $annotationsList = [];
        foreach ($deliverable->artworkReviews as $review) {
            foreach ($review->annotations as $anno) {
                if (!empty($anno->content)) {
                    $annotationsList[] = [
                        'type' => $anno->type ?? 'pin',
                        'comment' => $anno->content,
                        'resolved' => $anno->is_resolved ? 'Yes' : 'No',
                    ];
                }
            }
        }

        // Fallback if Gemini key is missing
        if (empty($this->geminiApiKey)) {
            $summaryLines = [];
            $summaryLines[] = "### 🔍 Deliverable Revision Diagnostic ({$totalRevisions} rounds)";
            $summaryLines[] = "**Deliverable:** {$deliverable->title} | **Current Stage:** " . ($deliverable->stage ?? $deliverable->approval_stage);
            $summaryLines[] = "";
            $summaryLines[] = "#### 📜 Revision History Log";
            foreach ($revisionTimeline as $item) {
                $summaryLines[] = "- **{$item['round']}** by *{$item['requested_by']}* at stage *{$item['stage_at_revision']}* (Target: {$item['assigned_to']}):\n  > \"{$item['instructions']}\"";
            }
            return [
                'success' => true,
                'total_revisions' => $totalRevisions,
                'reply' => implode("\n", $summaryLines),
            ];
        }

        // 4. Prompt Gemini with structured diagnostic framework
        $brandName = $deliverable->project?->brand?->name ?? 'Brand';
        $subtaskType = $deliverable->subtask_type ?: 'Social Post';
        $currentStage = $deliverable->stage ?? $deliverable->approval_stage;
        $concept = trim(strip_tags($deliverable->concept ?: ($deliverable->notes ?: 'N/A')));
        $copy = trim(strip_tags($deliverable->post_copy ?: 'N/A'));
        $caption = trim(strip_tags($deliverable->caption ?: 'N/A'));

        $timelineJson = json_encode($revisionTimeline, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $annotationsJson = json_encode($annotationsList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
You are an expert Creative Operations Director and Agency Workflow Auditor.
Analyze the following revision trail for deliverable "{$deliverable->title}" (Brand: {$brandName}, Type: {$subtaskType}, Current Stage: {$currentStage}).
Identify exactly why this deliverable has accumulated {$totalRevisions} revision round(s), find root causes, pinpoint bottlenecks, and give a clear pass-to-approval roadmap.

Deliverable Context:
- Brief / Concept: {$concept}
- Current Body Copy: {$copy}
- Current Caption: {$caption}

Revision Rounds Trail:
{$timelineJson}

Client Proofing Annotations (if any):
{$annotationsJson}

Structure your diagnostic report in clean GitHub-Flavored Markdown with these sections:
### 🔍 Revision Diagnostic Summary
(1-2 concise paragraphs stating the total rounds, key stages where work got stuck, and overall churn velocity.)

### ⚠️ Root Cause Breakdown
Categorize the primary causes into clear areas (e.g. 📝 Copy & Tone Nuance, 🎨 Visual Layout & Hierarchy, 📋 Brief & Scope Ambiguity, or 👥 Client Direction Shift). State specifically what changed or was missing in each round.

### 🛑 Friction Points & Contradictions
Highlight any conflicting instructions between review stages or recurring issues that weren't resolved on the first fix.

### ✅ Action Plan to Pass Approval
Provide 2 to 3 crystal-clear, non-negotiable action items for the creative team (Writer and/or Designer) to get this deliverable approved immediately in its next submission.

Tone: Objective, constructive, analytical, and agency-smart.
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";
            $response = Http::timeout(35)->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 1500,
                ]
            ]);

            if ($response->successful()) {
                $reply = $response->json('candidates.0.content.parts.0.text');
                if (!empty($reply)) {
                    return [
                        'success' => true,
                        'total_revisions' => $totalRevisions,
                        'reply' => trim($reply),
                    ];
                }
            } else {
                Log::warning("RevisionAnalyticsService Gemini API warning: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("RevisionAnalyticsService diagnostic error: " . $e->getMessage());
        }

        // Fallback response if API fails
        $fallback = "### 🔍 Deliverable Revision Diagnostic ({$totalRevisions} rounds)\n\n"
            . "**Deliverable:** {$deliverable->title} | **Current Stage:** {$currentStage}\n\n"
            . "#### ⚠️ Key Recorded Revisions:\n";
        foreach ($revisionTimeline as $item) {
            $fallback .= "- **{$item['round']}** by *{$item['requested_by']}* ({$item['stage_at_revision']}): \"{$item['instructions']}\"\n";
        }
        $fallback .= "\n*Recommendation: Review the latest feedback above and ensure alignment between the Writer and Designer before resubmission.*";

        return [
            'success' => true,
            'total_revisions' => $totalRevisions,
            'reply' => $fallback,
        ];
    }

    /**
     * Analyze brand-wide revision trends across all deliverables under a brand.
     */
    public function analyzeBrandRevisionTrends(Brand $brand): array
    {
        $deliverables = Deliverable::query()
            ->whereHas('project', fn($q) => $q->where('brand_id', $brand->id))
            ->whereHas('revisionsHistory')
            ->with(['revisionsHistory.user'])
            ->latest('id')
            ->limit(25)
            ->get();

        if ($deliverables->isEmpty()) {
            return [
                'success' => true,
                'has_data' => false,
                'reply' => "### ✨ Brand Revision Health: Excellent\n\nNo revision cycles were found for **{$brand->name}**. All recent deliverables are passing through workflow stages without significant review pushback.",
            ];
        }

        $totalRevised = $deliverables->count();
        $totalRevCount = 0;
        $stageCount = [];
        $typeCount = [];
        $feedbackSamples = [];

        foreach ($deliverables as $d) {
            $revs = $d->revisionsHistory;
            $count = $revs->count();
            $totalRevCount += $count;

            $type = $d->subtask_type ?: 'Standard Post';
            $typeCount[$type] = ($typeCount[$type] ?? 0) + $count;

            foreach ($revs as $r) {
                $stage = $r->stage_at_revision ?: 'Review';
                $stageCount[$stage] = ($stageCount[$stage] ?? 0) + 1;

                if (!empty($r->instructions) && count($feedbackSamples) < 15) {
                    $feedbackSamples[] = [
                        'deliverable' => $d->title,
                        'stage' => $stage,
                        'instructions' => mb_substr($r->instructions, 0, 180),
                    ];
                }
            }
        }

        $avgRevisions = round($totalRevCount / max($totalRevised, 1), 1);
        arsort($stageCount);
        arsort($typeCount);

        if (empty($this->geminiApiKey)) {
            return [
                'success' => true,
                'has_data' => true,
                'avg_revisions' => $avgRevisions,
                'reply' => "### 📊 Brand Revision Patterns ({$brand->name})\n\n"
                    . "- **Deliverables analyzed with revisions:** {$totalRevised}\n"
                    . "- **Average revisions per affected deliverable:** {$avgRevisions}\n"
                    . "- **Most frequent revision stage:** " . key($stageCount) . "\n"
                    . "- **Subtask format with highest churn:** " . key($typeCount),
            ];
        }

        $statsSummary = [
            'brand' => $brand->name,
            'deliverables_with_revisions' => $totalRevised,
            'average_revisions' => $avgRevisions,
            'revisions_by_stage' => $stageCount,
            'revisions_by_type' => $typeCount,
            'recent_feedback_samples' => $feedbackSamples,
        ];

        $statsJson = json_encode($statsSummary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
You are a Lead Creative Operations Director.
Analyze the following revision patterns across deliverables for brand "{$brand->name}".
Summarize the key trends, explain why deliverables for this brand tend to get revised, and provide 3 actionable workflow recommendations for the creative team.

Data Summary:
{$statsJson}

Format in clean GitHub-Flavored Markdown with:
### 📊 Brand Revision Trends ({$brand->name})
### 🔎 Recurring Patterns & Bottlenecks
### 💡 Strategic Recommendations to Reduce Churn
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";
            $response = Http::timeout(35)->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => 1200,
                ]
            ]);

            if ($response->successful()) {
                $reply = $response->json('candidates.0.content.parts.0.text');
                if (!empty($reply)) {
                    return [
                        'success' => true,
                        'has_data' => true,
                        'avg_revisions' => $avgRevisions,
                        'reply' => trim($reply),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::error("RevisionAnalyticsService brand trends error: " . $e->getMessage());
        }

        return [
            'success' => true,
            'has_data' => true,
            'avg_revisions' => $avgRevisions,
            'reply' => "### 📊 Brand Revision Summary ({$brand->name})\n\n"
                . "Analyzed {$totalRevised} deliverables with an average of {$avgRevisions} revisions each. Revisions occur most frequently at the **" . key($stageCount) . "** stage on **" . key($typeCount) . "** deliverables.",
        ];
    }
}
