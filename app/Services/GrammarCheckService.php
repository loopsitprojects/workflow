<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrammarCheckService
{
    /**
     * Endpoint for LanguageTool API.
     * Can be overridden via LANGUAGETOOL_URL in .env if self-hosted.
     */
    protected string $apiUrl;

    /**
     * Optional Gemini API key.
     */
    protected ?string $geminiApiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.languagetool.url', env('LANGUAGETOOL_URL', 'https://api.languagetool.org/v2/check'));
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
    }

    /**
     * Check multiple content fields (e.g. concept, copy, caption) for spelling & grammar errors.
     *
     * @param array<string, string> $fields
     * @param string $language
     * @return array
     */
    public function checkFields(array $fields, string $language = 'en-US'): array
    {
        $results = [];
        $totalIssues = 0;
        $engine = !empty($this->geminiApiKey) ? 'gemini' : 'languagetool';

        foreach ($fields as $fieldName => $htmlContent) {
            $plainText = $this->prepareText($htmlContent);
            if (empty(trim($plainText))) {
                $results[$fieldName] = [];
                continue;
            }

            if (!empty($this->geminiApiKey)) {
                $issues = $this->checkWithGemini($plainText, $fieldName);
                if ($issues === null) {
                    // Gracefully fallback to LanguageTool if Gemini failed or timed out
                    $issues = $this->checkWithLanguageTool($plainText, $language);
                    $engine = 'languagetool (fallback)';
                }
            } else {
                $issues = $this->checkWithLanguageTool($plainText, $language);
            }

            $results[$fieldName] = $issues;
            $totalIssues += count($issues);
        }

        return [
            'success'      => true,
            'engine'       => $engine,
            'total_issues' => $totalIssues,
            'fields'       => $results,
        ];
    }

    /**
     * Call Google Gemini API for creative agency copy review, spelling, grammar & polish.
     */
    public function checkWithGemini(string $text, string $field): ?array
    {
        try {
            $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiApiKey}";

            $prompt = <<<PROMPT
You are an expert creative marketing copy editor and proofreader.
Analyze the following advertising/creative deliverable text ({$field}):
"{$text}"

Identify all:
1. Spelling mistakes / typos.
2. Grammar and punctuation errors.
3. Copywriting enhancements (e.g., active voice, clarity, punchy improvements).

Output ONLY JSON matching this exact structure:
{
  "issues": [
    {
      "category": "spelling|grammar|punctuation|style",
      "category_name": "Spelling|Grammar|Punctuation|Copy Polish",
      "message": "Clear explanation of the error or suggestion",
      "original": "exact word or phrase from text to replace",
      "replacements": ["suggested replacement 1", "suggested replacement 2"],
      "sentence": "the full sentence where the issue occurs"
    }
  ]
}
If no issues are found, return {"issues": []}.
PROMPT;

            $response = Http::timeout(25)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.2,
                    ]
                ]);

            if (!$response->successful()) {
                Log::warning('Gemini grammar check error: ' . $response->status() . ' ' . $response->body());
                return null;
            }

            $body = $response->json();
            $rawJson = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed = json_decode($rawJson, true);

            if (!isset($parsed['issues']) || !is_array($parsed['issues'])) {
                return null;
            }

            $formatted = [];
            foreach ($parsed['issues'] as $index => $issue) {
                if (empty($issue['original'])) continue;

                $formatted[] = [
                    'id'            => 'gemini_' . $index . '_' . crc32($issue['original']),
                    'category'      => strtolower($issue['category'] ?? 'grammar'),
                    'category_name' => $issue['category_name'] ?? 'Grammar',
                    'message'       => $issue['message'] ?? 'Suggestion found',
                    'original'      => $issue['original'],
                    'replacements'  => array_slice($issue['replacements'] ?? [], 0, 5),
                    'sentence'      => $issue['sentence'] ?? $issue['original'],
                ];
            }

            return $formatted;
        } catch (\Throwable $e) {
            Log::warning('Gemini check exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check using LanguageTool API.
     */
    public function checkWithLanguageTool(string $text, string $language = 'en-US'): array
    {
        try {
            $response = Http::asForm()
                ->timeout(8)
                ->post($this->apiUrl, [
                    'text'     => $text,
                    'language' => $language,
                ]);

            if (!$response->successful()) {
                Log::warning('LanguageTool grammar check API non-200 response: ' . $response->status());
                return [];
            }

            $data = $response->json();
            $matches = $data['matches'] ?? [];

            $formatted = [];
            foreach ($matches as $index => $match) {
                $catId = strtoupper($match['rule']['category']['id'] ?? 'OTHER');
                $issueType = strtolower($match['rule']['issueType'] ?? 'other');

                $category = 'grammar';
                if ($catId === 'TYPOS' || $issueType === 'misspelling') {
                    $category = 'spelling';
                } elseif (in_array($catId, ['PUNCTUATION', 'STYLE', 'TYPOGRAPHY'])) {
                    $category = strtolower($catId);
                }

                $replacements = array_slice(array_column($match['replacements'] ?? [], 'value'), 0, 5);
                $original = mb_substr($text, $match['offset'], $match['length']);

                $formatted[] = [
                    'id'            => 'issue_' . $index . '_' . $match['offset'],
                    'category'      => $category,
                    'category_name' => $match['rule']['category']['name'] ?? ucfirst($category),
                    'message'       => $match['message'] ?? 'Possible error found',
                    'short_message' => $match['shortMessage'] ?? '',
                    'original'      => $original,
                    'offset'        => $match['offset'],
                    'length'        => $match['length'],
                    'replacements'  => $replacements,
                    'sentence'      => $match['sentence'] ?? '',
                ];
            }

            return $formatted;
        } catch (\Throwable $e) {
            Log::error('GrammarCheckService exception: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Backward-compatible alias.
     */
    public function checkText(string $text, string $language = 'en-US'): array
    {
        return $this->checkWithLanguageTool($text, $language);
    }

    /**
     * Normalize and strip HTML tags from editor content while keeping text spacing intact.
     */
    protected function prepareText(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $clean = preg_replace('/<\/(p|div|h[1-6]|li)>/i', "\n", $html);
        $clean = strip_tags($clean);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = str_replace("\xc2\xa0", ' ', $clean);

        return trim($clean);
    }
}
