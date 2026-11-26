<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Deliverable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrandIntelligenceService
{
    protected ?string $geminiApiKey;
    protected string $geminiModel;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->geminiModel = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));
    }

    /**
     * Get or build the Brand Creative DNA profile.
     * Cached for 7 days per brand. Can be force-refreshed.
     */
    public function getBrandDna(Brand $brand, bool $forceRefresh = false): array
    {
        $cacheKey = "brand_intelligence_dna_{$brand->id}";

        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $dna = $this->synthesizeBrandDna($brand);
        Cache::put($cacheKey, $dna, now()->addDays(7));

        return $dna;
    }

    /**
     * Invalidate the cached DNA for a brand.
     */
    public function invalidateBrandDna(int $brandId): void
    {
        Cache::forget("brand_intelligence_dna_{$brandId}");
    }

    /**
     * Synthesize brand DNA using Gemini by analyzing historical deliverables.
     */
    public function synthesizeBrandDna(Brand $brand): array
    {
        // 1. Fetch recent deliverables under this brand (up to 30)
        $deliverables = Deliverable::query()
            ->whereHas('project', fn($q) => $q->where('brand_id', $brand->id))
            ->where(function ($q) {
                $q->whereNotNull('concept')
                  ->orWhereNotNull('post_copy')
                  ->orWhereNotNull('caption')
                  ->orWhereNotNull('notes');
            })
            ->latest('id')
            ->limit(30)
            ->get();

        if ($deliverables->isEmpty()) {
            return [
                'has_data' => false,
                'brand_name' => $brand->name,
                'tone_of_voice' => 'Professional, engaging, and creative.',
                'target_audience' => 'General target audience.',
                'content_pillars' => ['Product showcases', 'Brand awareness', 'Audience engagement'],
                'visual_style' => 'Modern, high quality agency aesthetics.',
                'signature_patterns' => 'Clear call-to-actions and clean formatting.',
                'sample_deliverables_count' => 0,
                'brand_essence_summary' => "New or upcoming brand with limited historical deliverables recorded in the system yet.",
            ];
        }

        // 2. Prepare digest of deliverables
        $digest = [];
        foreach ($deliverables as $d) {
            $concept = trim(strip_tags($d->concept ?: ($d->notes ?: '')));
            $copy = trim(strip_tags($d->post_copy ?: ''));
            $caption = trim(strip_tags($d->caption ?: ''));
            
            if (empty($concept) && empty($copy) && empty($caption)) {
                continue;
            }

            $digest[] = [
                'title' => $d->title,
                'subtask_type' => $d->subtask_type,
                'stage' => $d->stage ?? $d->approval_stage,
                'concept' => mb_substr($concept, 0, 300),
                'copy' => mb_substr($copy, 0, 300),
                'caption' => mb_substr($caption, 0, 300),
            ];
        }

        if (empty($digest) || empty($this->geminiApiKey)) {
            return [
                'has_data' => true,
                'brand_name' => $brand->name,
                'tone_of_voice' => 'Engaging, creative, and audience-focused.',
                'target_audience' => 'Consumers and social media community.',
                'content_pillars' => ['Social media campaigns', 'Creative storytelling', 'Promotional offers'],
                'visual_style' => 'Clean typography and vibrant visuals.',
                'signature_patterns' => 'Engaging hooks and bilingual expressions where applicable.',
                'sample_deliverables_count' => count($deliverables),
                'brand_essence_summary' => "Profile inferred from " . count($deliverables) . " deliverables in the system.",
            ];
        }

        // 3. Prompt Gemini to extract Brand Creative DNA
        $sampleJson = json_encode($digest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $prompt = <<<PROMPT
You are an expert Brand Strategist and Creative Director.
Analyze the following sample of recent deliverables, concepts, body copy, and captions created for the brand "{$brand->name}".
Extract and synthesize the brand's unique Creative DNA in JSON format.

Deliverables Sample:
{$sampleJson}

Requirements for JSON output:
Respond with ONLY valid JSON with exactly these keys:
{
  "tone_of_voice": "A vivid description of their tone (e.g. youthful, energetic, bilingual Singlish/Sinhala hybrid, witty puns, friendly warmth)",
  "target_audience": "Clear description of target audience, demographic, and lifestyle",
  "content_pillars": ["Pillar 1", "Pillar 2", "Pillar 3"],
  "visual_style": "Key aesthetic direction (colors, mood, typography style, visual hierarchy)",
  "signature_patterns": "Recurring linguistic habits, bilingual nuances, common CTAs, or hashtag styles",
  "brand_essence_summary": "A 2-sentence executive summary of the brand's identity and creative persona"
}
PROMPT;

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}";
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'responseMimeType' => 'application/json'
                ]
            ]);

            if ($response->successful()) {
                $raw = $response->json('candidates.0.content.parts.0.text');
                $decoded = json_decode($raw, true);

                if (is_array($decoded) && !empty($decoded['tone_of_voice'])) {
                    return array_merge($decoded, [
                        'has_data' => true,
                        'brand_name' => $brand->name,
                        'sample_deliverables_count' => count($deliverables),
                        'last_analyzed_at' => now()->toIso8601String(),
                    ]);
                }
            } else {
                Log::warning("BrandIntelligenceService Gemini API warning: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("BrandIntelligenceService synthesis error: " . $e->getMessage());
        }

        return [
            'has_data' => true,
            'brand_name' => $brand->name,
            'tone_of_voice' => 'Engaging, modern, and audience-resonant.',
            'target_audience' => 'Brand loyalists and active social audience.',
            'content_pillars' => ['Seasonal campaigns', 'Product highlights', 'Community engagement'],
            'visual_style' => 'Vibrant, polished agency aesthetic.',
            'signature_patterns' => 'Clear CTAs, conversational tone, bilingual nuances.',
            'sample_deliverables_count' => count($deliverables),
            'brand_essence_summary' => "Creative identity built across " . count($deliverables) . " recorded deliverables.",
        ];
    }

    /**
     * Build a formatted markdown block to inject into AI Copilot system prompt.
     */
    public function formatBrandDnaForAi(Brand $brand): string
    {
        $dna = $this->getBrandDna($brand);

        $tone = $dna['tone_of_voice'] ?? 'Creative and engaging';
        $audience = $dna['target_audience'] ?? 'Target consumer base';
        $pillars = is_array($dna['content_pillars'] ?? null) ? implode(', ', $dna['content_pillars']) : ($dna['content_pillars'] ?? 'Brand campaigns');
        $style = $dna['visual_style'] ?? 'Modern agency creative';
        $patterns = $dna['signature_patterns'] ?? 'Conversational copywriting';
        $summary = $dna['brand_essence_summary'] ?? ($dna['summary'] ?? '');

        return <<<BLOCK
=== BRAND CREATIVE DNA ({$brand->name}) ===
• Brand Essence: {$summary}
• Tone of Voice: {$tone}
• Target Audience: {$audience}
• Content Pillars: {$pillars}
• Visual & Aesthetic Style: {$style}
• Signature Linguistic Patterns: {$patterns}
Ensure all brainstormed concepts, copy rewrites, and suggestions adhere strictly to this established Brand DNA!
============================================
BLOCK;
    }
}
