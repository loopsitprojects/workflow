<?php

namespace App\Services;

use App\Models\Deliverable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected ?string $geminiApiKey;
    protected GrammarCheckService $grammarService;
    protected ArtworkCritiqueService $critiqueService;

    public function __construct(
        GrammarCheckService $grammarService,
        ArtworkCritiqueService $critiqueService
    ) {
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->grammarService = $grammarService;
        $this->critiqueService = $critiqueService;
    }

    /**
     * Process an incoming AI chat message or specialized action.
     *
     * @param string $message
     * @param array $history
     * @param Deliverable|null $deliverable
     * @param string $action 'chat'|'grammar_check'|'artwork_critique'|'brainstorm'|'rewrite_caption'
     * @return array
     */
    public function handleChat(string $message, array $history = [], ?Deliverable $deliverable = null, string $action = 'chat'): array
    {
        // 1. Direct Specialized Action: Grammar Check
        if ($action === 'grammar_check' && $deliverable) {
            return $this->handleGrammarCheckAction($deliverable);
        }

        // 2. Direct Specialized Action: Artwork Critique
        if ($action === 'artwork_critique' && $deliverable) {
            return $this->handleArtworkCritiqueAction($deliverable);
        }

        // 3. Conversational Chat & Creative Generation
        return $this->handleConversationalChat($message, $history, $deliverable, $action);
    }

    /**
     * Run full grammar and copy review for deliverable and return structured chat response.
     */
    protected function handleGrammarCheckAction(Deliverable $deliverable): array
    {
        $fields = [
            'concept' => $deliverable->concept ?: ($deliverable->notes ?: ''),
            'copy'    => $deliverable->post_copy ?: '',
            'caption' => $deliverable->caption ?: '',
        ];

        $results = $this->grammarService->checkFields($fields);

        $totalIssues = $results['total_issues'] ?? 0;
        $engine = $results['engine'] ?? 'gemini';

        if ($totalIssues === 0) {
            $reply = "✅ **Copy & Grammar Review Complete!**\n\nAll content fields (**Concept**, **Copy**, and **Caption**) look clean, polished, and ready for review. No grammatical errors or typos were detected.";
        } else {
            $reply = "🔍 **Copy & Grammar Review Findings ({$totalIssues} issue" . ($totalIssues === 1 ? '' : 's') . " found):**\n\n";

            foreach (['concept' => 'Creative Concept', 'copy' => 'Body Copy', 'caption' => 'Caption'] as $fKey => $fLabel) {
                $fieldIssues = $results['fields'][$fKey] ?? [];
                if (!empty($fieldIssues)) {
                    $reply .= "### **{$fLabel}** (" . count($fieldIssues) . " issue" . (count($fieldIssues) === 1 ? '' : 's') . "):\n";
                    foreach ($fieldIssues as $idx => $iss) {
                        $original = $iss['original'] ?? '';
                        $suggestion = $iss['suggestion'] ?? '';
                        $msg = $iss['message'] ?? '';
                        $num = $idx + 1;
                        $reply .= "- **{$num}. \"{$original}\"** → `{$suggestion}`\n  _{$msg}_\n";
                    }
                    $reply .= "\n";
                }
            }
            $reply .= "💡 _Would you like me to rewrite any of these sections to be more punchy or engaging?_";
        }

        return [
            'success'   => true,
            'reply'     => $reply,
            'action'    => 'grammar_check',
            'data'      => $results,
        ];
    }

    /**
     * Run multimodal visual artwork critique for deliverable and return chat response.
     */
    protected function handleArtworkCritiqueAction(Deliverable $deliverable): array
    {
        $critique = $this->critiqueService->critiqueArtwork($deliverable);

        if (!$critique['success']) {
            return [
                'success' => false,
                'reply'   => "⚠️ " . ($critique['message'] ?? 'Unable to analyze artwork image.'),
                'action'  => 'artwork_critique',
                'data'    => $critique,
            ];
        }

        $data = $critique['critique'] ?? [];
        $score = $data['score'] ?? '—';
        $verdict = $data['verdict'] ?? 'Review completed.';
        $strengths = $data['strengths'] ?? [];
        $suggestions = $data['suggestions'] ?? [];
        $hierarchy = $data['hierarchy'] ?? null;
        $typography = $data['typography'] ?? null;
        $alignment = $data['alignment'] ?? null;
        $refFidelity = $data['reference_fidelity'] ?? null;

        $reply = "🎨 **AI Artwork Review & Reference Critique**\n\n";
        $reply .= "⭐ **Creative Director Score:** **{$score} / 10**\n";
        $reply .= "💬 **Verdict:** {$verdict}\n\n";

        if (!empty($strengths)) {
            $reply .= "### **Strengths:**\n";
            foreach ($strengths as $st) {
                $reply .= "- ✅ {$st}\n";
            }
            $reply .= "\n";
        }

        if ($typography) {
            $reply .= "### **Typography & Readability:**\n{$typography}\n\n";
        }

        if ($alignment) {
            $reply .= "### **Brief & Copy Alignment:**\n{$alignment}\n\n";
        }

        if ($refFidelity) {
            $reply .= "### **Moodboard & Reference Fidelity:**\n{$refFidelity}\n\n";
        }

        if (!empty($suggestions)) {
            $reply .= "### **Actionable Polish Tips:**\n";
            foreach ($suggestions as $sg) {
                $reply .= "- 💡 {$sg}\n";
            }
        }

        return [
            'success'  => true,
            'reply'    => $reply,
            'action'   => 'artwork_critique',
            'data'     => $critique,
        ];
    }

    /**
     * Perform conversational dialogue or specialized prompt generation using Gemini API.
     */
    protected function handleConversationalChat(string $message, array $history, ?Deliverable $deliverable, string $action): array
    {
        if (empty($this->geminiApiKey)) {
            return [
                'success' => false,
                'reply'   => '⚠️ **Gemini API key is missing.** Please configure `GEMINI_API_KEY` in your `.env` file to use the AI Assistant.',
                'action'  => $action,
            ];
        }

        $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiApiKey}";

        // Build context block
        $context = $this->buildDeliverableContext($deliverable);

        $systemInstruction = <<<INSTRUCTION
You are "Loops AI Assistant", an elite Creative Agency Director, Senior Copywriter, and Visual Strategist at a top-tier marketing and creative agency.
You assist marketing specialists, copywriters, art directors, and brand managers with their daily creative workflows.

Guidelines:
1. Always be professional, insightful, inspiring, and concise.
2. If deliverable context is provided, ground your answers in the active project brief, brand identity, concept, copy, caption, and reference assets.
3. When providing copy suggestions or revisions, format them clearly with bullet points or blockquotes so the user can easily copy and use them.
4. If asked to brainstorm or rewrite, provide 2 to 3 distinct high-quality options (e.g. Option A: High Energy, Option B: Minimal & Modern, Option C: Story-Driven).
5. Format your output in clean GitHub-flavored Markdown.

{$context}
INSTRUCTION;

        // Build contents payload
        $contents = [];

        // Specialized action prompt overrides
        $userPrompt = $message;
        if ($action === 'brainstorm') {
            $userPrompt = "Please brainstorm 3 distinct creative concept angles and visual directions for this deliverable based on the brief and target brand.";
        } elseif ($action === 'rewrite_caption') {
            $userPrompt = "Please rewrite the social media caption for this deliverable into 3 distinct variations (1: Punchy/Catchy with CTA, 2: Storytelling/Emotional, 3: Short & Minimal) including relevant hashtags.";
        }

        // Add history turns (limit to last 10 turns for speed and context window)
        $trimmedHistory = array_slice($history, -10);
        foreach ($trimmedHistory as $item) {
            $role = ($item['role'] ?? 'user') === 'user' ? 'user' : 'model';
            $text = trim($item['content'] ?? '');
            if (!empty($text)) {
                $contents[] = [
                    'role'  => $role,
                    'parts' => [['text' => $text]],
                ];
            }
        }

        // Append current turn
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userPrompt]],
        ];

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [
                            ['text' => $systemInstruction]
                        ]
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1500,
                    ]
                ]);

            if (!$response->successful()) {
                Log::error('AiChatService Gemini API error: ' . $response->body());
                return [
                    'success' => false,
                    'reply'   => '⚠️ I could not process your request at this moment. (Error code: ' . $response->status() . '). Please try again in a moment.',
                    'action'  => $action,
                ];
            }

            $json = $response->json();
            $replyText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (empty($replyText)) {
                return [
                    'success' => false,
                    'reply'   => '⚠️ Received an empty response from AI model. Please try asking again.',
                    'action'  => $action,
                ];
            }

            return [
                'success' => true,
                'reply'   => trim($replyText),
                'action'  => $action,
            ];

        } catch (\Throwable $e) {
            Log::error('AiChatService exception: ' . $e->getMessage());
            return [
                'success' => false,
                'reply'   => '⚠️ An unexpected error occurred while communicating with the AI service: ' . $e->getMessage(),
                'action'  => $action,
            ];
        }
    }

    /**
     * Build rich deliverable context string to ground AI responses.
     */
    protected function buildDeliverableContext(?Deliverable $deliverable): string
    {
        if (!$deliverable) {
            return "Current Context: General agency hub. No specific deliverable is currently selected.";
        }

        $brandName = $deliverable->project?->brand?->name ?? 'Agency Client';
        $projectName = $deliverable->project?->name ?? 'Campaign';
        $title = $deliverable->title ?? 'Deliverable';
        $stage = $deliverable->stage ?? 'Writer';
        $concept = strip_tags($deliverable->concept ?: ($deliverable->notes ?: 'None provided'));
        $copy = strip_tags($deliverable->post_copy ?: 'None provided');
        $caption = strip_tags($deliverable->caption ?: 'None provided');
        
        $refUrls = $deliverable->getReferenceUrlsArray();
        $refUrlsStr = !empty($refUrls) ? implode(", ", $refUrls) : 'None';

        $hasArtwork = !empty($deliverable->artwork_url);
        $artworkCount = is_array($deliverable->artwork_url) ? count($deliverable->artwork_url) : ($hasArtwork ? 1 : 0);

        return <<<CONTEXT
=== CURRENT ACTIVE DELIVERABLE CONTEXT ===
- Brand: {$brandName}
- Project: {$projectName}
- Deliverable: {$title}
- Workflow Stage: {$stage}
- Creative Concept: {$concept}
- Copy / Text: {$copy}
- Social Caption: {$caption}
- Reference Links: {$refUrlsStr}
- Uploaded Artwork Count: {$artworkCount}
==========================================
CONTEXT;
    }
}
