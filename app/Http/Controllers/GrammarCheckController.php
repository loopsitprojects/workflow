<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use App\Services\GrammarCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrammarCheckController extends Controller
{
    /**
     * Check grammar and spelling for submitted creative content fields.
     */
    public function check(Request $request, GrammarCheckService $service, ?Deliverable $deliverable = null): JsonResponse
    {
        if (!\App\Services\FeatureManager::isAiFeaturesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AI features are currently disabled in system settings.',
            ], 403);
        }

        $validated = $request->validate([
            'fields'   => 'required|array',
            'fields.*' => 'nullable|string',
            'language' => 'nullable|string|max:10',
        ]);

        $fields = $validated['fields'];
        $language = $validated['language'] ?? 'en-US';

        $result = $service->checkFields($fields, $language);

        return response()->json($result);
    }
}
