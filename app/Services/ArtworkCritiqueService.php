<?php

namespace App\Services;

use App\Models\Deliverable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArtworkCritiqueService
{
    protected ?string $geminiApiKey;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.key', env('GEMINI_API_KEY'));
    }

    /**
     * Critique an uploaded artwork against deliverable brief and design standards.
     *
     * @param Deliverable $deliverable
     * @param string|null $artworkUrl Optional specific artwork URL/path. If null, uses latest uploaded artwork.
     * @return array
     */
    public function critiqueArtwork(Deliverable $deliverable, ?string $artworkUrl = null): array
    {
        if (empty($this->geminiApiKey)) {
            return [
                'success' => false,
                'message' => 'Gemini API key is required for AI Artwork Vision analysis. Please add GEMINI_API_KEY to your .env file.',
            ];
        }

        // 1. Resolve artwork file
        $imageData = $this->resolveImageData($deliverable, $artworkUrl);
        if (!$imageData) {
            return [
                'success' => false,
                'message' => 'No valid artwork image found to analyze. Please upload an image (PNG, JPG, WEBP) first.',
            ];
        }

        // 2. Prepare context from deliverable
        $title   = $deliverable->title ?? 'Deliverable';
        $concept = strip_tags($deliverable->concept ?: ($deliverable->notes ?: 'None provided'));
        $copy    = strip_tags($deliverable->post_copy ?: 'None provided');
        $caption = strip_tags($deliverable->caption ?: 'None provided');
        $brand   = $deliverable->project?->brand?->name ?? 'Brand';

        $prompt = <<<PROMPT
You are an award-winning Creative Director and Senior Art Director at a premier marketing and design agency.
Critique this uploaded artwork for a creative deliverable for client "{$brand}".

--- Deliverable Context ---
Title: {$title}
Creative Concept / Brief: {$concept}
Copy / Text: {$copy}
Caption: {$caption}

--- Analysis Guidelines ---
Analyze the provided visual image across 5 professional creative criteria:
1. Overall Aesthetic & First Impression (Score from 1 to 10 and 1-sentence verdict).
2. Visual Hierarchy & Composition (Layout balance, focal point, breathing room/whitespace).
3. Typography & Legibility (Font choice, text contrast, readability over background).
4. Brand & Brief Alignment (Does this visual effectively fulfill the creative concept and copy?).
5. Actionable Polish Tips (2 to 3 concrete, specific refinements the designer can make before final sign-off).

Output ONLY JSON matching this exact structure:
{
  "score": 8.5,
  "verdict": "Strong, eye-catching composition with excellent contrast, with a minor tweak needed on body font size.",
  "strengths": [
    "Compelling central focal point",
    "High contrast background ensures immediate attention"
  ],
  "hierarchy": "Analysis of layout flow and focal priority...",
  "typography": "Analysis of text styling and readability...",
  "alignment": "How well it reflects the brief and copy...",
  "suggestions": [
    "Increase padding around the bottom call-to-action button by 15%",
    "Ensure the headline text has enough contrast against the gradient"
  ]
}
PROMPT;

        try {
            $model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-flash-latest'));
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiApiKey}";

            $response = Http::timeout(25)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inlineData' => [
                                        'mimeType' => $imageData['mime'],
                                        'data'     => $imageData['base64'],
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.3,
                    ]
                ]);

            if (!$response->successful()) {
                Log::warning('Gemini Artwork Critique API error: ' . $response->status() . ' ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'AI Artwork review service error: ' . ($response->json('error.message') ?? 'Please try again later.'),
                ];
            }

            $body = $response->json();
            $rawJson = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $critique = json_decode($rawJson, true);

            if (!is_array($critique) || !isset($critique['score'])) {
                return [
                    'success' => false,
                    'message' => 'Unable to parse artwork critique. Please try again.',
                ];
            }

            return [
                'success'     => true,
                'artwork_url' => $imageData['url'] ?? $artworkUrl,
                'critique'    => $critique,
            ];
        } catch (\Throwable $e) {
            Log::error('ArtworkCritiqueService exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Service error while analyzing artwork: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve image binary and mime type from deliverable or direct URL.
     */
    protected function resolveImageData(Deliverable $deliverable, ?string $artworkUrl): ?array
    {
        $targetPath = $artworkUrl;

        // If no URL provided, find from deliverable's artwork files
        if (empty($targetPath)) {
            $files = $deliverable->getFinalDesignsArray();
            foreach ($files as $file) {
                if (preg_match('/\.(jpe?g|png|webp|gif)($|\?)/i', $file)) {
                    $targetPath = $file;
                    break;
                }
            }
        }

        if (empty($targetPath)) {
            return null;
        }

        // Clean relative path without storage prefix or domain
        $cleanRelPath = $targetPath;
        if (preg_match('#/storage/(.+?)(\?.*)?$#i', $targetPath, $m)) {
            $cleanRelPath = $m[1];
        } else {
            $cleanRelPath = ltrim(parse_url($targetPath, PHP_URL_PATH) ?? $targetPath, '/');
            if (str_starts_with($cleanRelPath, 'storage/')) {
                $cleanRelPath = substr($cleanRelPath, 8);
            }
        }

        $rawBytes = null;
        $mime = 'image/jpeg';

        // 1. Try local storage disks
        foreach (['public', 'local'] as $disk) {
            if (Storage::disk($disk)->exists($cleanRelPath)) {
                $rawBytes = Storage::disk($disk)->get($cleanRelPath);
                $mime = Storage::disk($disk)->mimeType($cleanRelPath) ?: 'image/jpeg';
                break;
            }
            if (Storage::disk($disk)->exists($targetPath)) {
                $rawBytes = Storage::disk($disk)->get($targetPath);
                $mime = Storage::disk($disk)->mimeType($targetPath) ?: 'image/jpeg';
                break;
            }
        }

        // 2. If path is relative to storage public
        if (!$rawBytes && file_exists(storage_path('app/public/' . $cleanRelPath))) {
            $fullPath = storage_path('app/public/' . $cleanRelPath);
            $rawBytes = file_get_contents($fullPath);
            $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        }

        // 3. If file exists in public path
        if (!$rawBytes && file_exists(public_path($cleanRelPath))) {
            $fullPath = public_path($cleanRelPath);
            $rawBytes = file_get_contents($fullPath);
            $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        }

        // 4. If path is a remote URL or S3 URL
        if (!$rawBytes && filter_var($targetPath, FILTER_VALIDATE_URL)) {
            try {
                $httpRes = Http::timeout(10)->get($targetPath);
                if ($httpRes->successful()) {
                    $rawBytes = $httpRes->body();
                    $mime = $httpRes->header('Content-Type') ?: 'image/jpeg';
                }
            } catch (\Throwable $e) {
                Log::warning('Could not fetch remote artwork: ' . $e->getMessage());
            }
        }

        if (!$rawBytes) {
            return null;
        }

        // Limit size for API if too large (>4MB)
        if (strlen($rawBytes) > 4 * 1024 * 1024 && extension_loaded('gd')) {
            $img = @imagecreatefromstring($rawBytes);
            if ($img) {
                ob_start();
                imagejpeg($img, null, 80);
                $rawBytes = ob_get_clean();
                $mime = 'image/jpeg';
                imagedestroy($img);
            }
        }

        return [
            'base64' => base64_encode($rawBytes),
            'mime'   => $mime,
            'url'    => $targetPath,
        ];
    }
}
