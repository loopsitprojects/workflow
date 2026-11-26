<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use App\Services\AiChatService;
use App\Services\FeatureManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    protected AiChatService $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    /**
     * Handle incoming AI Chat messages and action triggers.
     */
    public function sendMessage(Request $request, ?Deliverable $deliverable = null): JsonResponse
    {
        // 1. Verify global AI feature toggle
        if (!FeatureManager::isAiFeaturesEnabled()) {
            return response()->json([
                'success' => false,
                'reply'   => 'AI features are currently turned off in System Settings. An administrator can enable them under Admin > Settings.',
                'action'  => 'disabled',
            ], 403);
        }

        $validated = $request->validate([
            'message'              => 'nullable|string|max:4000',
            'action'               => 'nullable|string|in:chat,grammar_check,artwork_critique,brainstorm,rewrite_caption,analyze_revisions,brand_trends',
            'conversation_history' => 'nullable|array',
            'conversation_history.*.role'    => 'required_with:conversation_history|string|in:user,model,assistant',
            'conversation_history.*.content' => 'required_with:conversation_history|string',
            'live_context'         => 'nullable|array',
            'live_context.concept' => 'nullable|string|max:20000',
            'live_context.copy'    => 'nullable|string|max:20000',
            'live_context.caption' => 'nullable|string|max:20000',
            'live_context.deliverable_id' => 'nullable|integer',
        ]);

        $message = trim($validated['message'] ?? '');
        $action = $validated['action'] ?? 'chat';
        $history = $validated['conversation_history'] ?? [];
        $liveContext = $validated['live_context'] ?? [];

        // If deliverable not injected via route parameter, attempt resolution from live_context
        if (!$deliverable && !empty($liveContext['deliverable_id'])) {
            $deliverable = Deliverable::with(['project.brand'])->find($liveContext['deliverable_id']);
        }

        // If an empty message is provided on standard chat, default prompt
        if (empty($message) && $action === 'chat') {
            return response()->json([
                'success' => false,
                'reply'   => 'Please provide a prompt or question for the AI assistant.',
            ], 422);
        }

        $result = $this->aiChatService->handleChat(
            $message,
            $history,
            $deliverable,
            $action,
            $liveContext
        );

        return response()->json($result);
    }
}
