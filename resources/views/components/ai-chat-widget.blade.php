@props([
    'deliverable' => null,
])

@php
    $aiEnabled = \App\Services\FeatureManager::isAiFeaturesEnabled();
    if (!$aiEnabled) {
        return;
    }

    $activeDeliverable = $deliverable;
    if (!$activeDeliverable && isset($GLOBALS['current_deliverable'])) {
        $activeDeliverable = $GLOBALS['current_deliverable'];
    }

    $deliverableId = $activeDeliverable?->id ?? null;
    $deliverableTitle = $activeDeliverable?->title ?? null;
    $brandName = $activeDeliverable?->project?->brand?->name ?? null;
    $stage = $activeDeliverable?->stage ?? null;
@endphp

<div x-data="loopsAiChatWidget({
    deliverableId: {{ $deliverableId ? (int)$deliverableId : 'null' }},
    deliverableTitle: '{{ $deliverableTitle ? addslashes($deliverableTitle) : '' }}',
    brandName: '{{ $brandName ? addslashes($brandName) : '' }}',
    stage: '{{ $stage ? addslashes($stage) : '' }}'
})"
@deliverable-selected.window="updateDeliverableContext($event.detail)"
x-cloak
class="relative z-50 font-sans">

    <!-- ========================================================================= -->
    <!-- 1. FLOATING LAUNCHER BUTTON (Bottom-Right)                                 -->
    <!-- ========================================================================= -->
    <div class="fixed bottom-6 right-6 z-50 flex items-center gap-3">
        <!-- Optional Tooltip / Pill -->
        <div x-show="!isOpen && showPill" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-4"
             class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/95 text-slate-700 border border-slate-200/90 shadow-lg dark:bg-[#0e1422]/95 dark:text-slate-200 dark:border-slate-800 dark:shadow-xl text-xs font-medium backdrop-blur-md">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
            <span>Loops AI Copilot</span>
            <button type="button" @click.stop="showPill = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 ml-1">&times;</button>
        </div>

        <!-- Main Trigger Button -->
        <button type="button"
                @click="toggleChat()"
                class="relative group w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-700 shadow-xl shadow-slate-200/60 hover:bg-slate-50 hover:border-slate-300 dark:bg-[#0e1422] dark:border-slate-700/80 dark:text-slate-200 dark:shadow-black/50 dark:hover:bg-slate-800 dark:hover:border-slate-600 active:scale-95 transition-all duration-200 flex items-center justify-center cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-300 dark:focus:ring-slate-700">
            
            <!-- Sparkles Icon (When Closed) -->
            <div x-show="!isOpen" class="transition-transform duration-200 group-hover:scale-110 flex items-center justify-center text-slate-600 dark:text-slate-300">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    <path d="M5 3v4"/>
                    <path d="M19 17v4"/>
                    <path d="M3 5h4"/>
                    <path d="M17 19h4"/>
                </svg>
            </div>

            <!-- Close Icon (When Open) -->
            <div x-show="isOpen" class="transition-transform duration-200 flex items-center justify-center text-slate-500 dark:text-slate-400">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"/>
                    <path d="m6 6 12 12"/>
                </svg>
            </div>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. EXPANDABLE CHAT WINDOW                                                 -->
    <!-- ========================================================================= -->
    <div x-show="isOpen"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-[0.98]"
         class="fixed bottom-22 right-6 z-50 w-[420px] max-w-[calc(100vw-2rem)] h-[620px] max-h-[85vh] bg-white border border-slate-200 shadow-2xl shadow-slate-400/20 dark:bg-[#0b0f19] dark:border-slate-800/80 dark:shadow-black/70 rounded-2xl flex flex-col overflow-hidden">

        <!-- Top Header Bar (No duplicate deliverable title) -->
        <div class="px-4 py-3 bg-slate-50/90 border-b border-slate-200 dark:bg-[#0e1422] dark:border-slate-800/80 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 dark:bg-slate-800/90 dark:border-slate-700/60 dark:text-slate-300 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[13px] font-semibold text-slate-800 dark:text-slate-100 tracking-tight">Loops AI Copilot</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                        <span>Ready</span>
                    </span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-1 text-slate-400">
                <!-- Clear Chat -->
                <button type="button"
                        @click="clearChat()"
                        title="Clear Conversation"
                        class="p-1.5 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 rounded-md transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"/>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                </button>

                <!-- Close Window -->
                <button type="button"
                        @click="isOpen = false"
                        title="Close AI Assistant"
                        class="p-1.5 hover:text-slate-700 hover:bg-slate-100 dark:hover:text-slate-200 dark:hover:bg-slate-800/60 rounded-md transition-colors cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Deliverable Context Bar (Appears once, clean & compact) -->
        <template x-if="deliverableTitle">
            <div class="px-3.5 py-2.5 bg-slate-50/80 border-b border-slate-200/90 dark:bg-[#0e1424] dark:border-slate-800/70 flex flex-col gap-2 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 truncate text-slate-700 dark:text-slate-200 text-xs font-medium">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <span class="truncate" x-text="deliverableTitle + (brandName ? ' &bull; ' + brandName : '')"></span>
                    </div>
                    <span class="text-[9.5px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800/80 dark:text-slate-300 dark:border-slate-700/60 shrink-0" 
                          x-text="stage || 'Active'"></span>
                </div>

                <!-- Subtle Checklist Pills -->
                <div class="flex items-center gap-1.5 text-[10px]">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200/90 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-700/50 font-medium shadow-2xs">
                        <svg class="w-2.5 h-2.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Concept</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200/90 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-700/50 font-medium shadow-2xs">
                        <svg class="w-2.5 h-2.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Copy</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200/90 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-700/50 font-medium shadow-2xs">
                        <svg class="w-2.5 h-2.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Caption</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white text-slate-600 border border-slate-200/90 dark:bg-slate-800/60 dark:text-slate-300 dark:border-slate-700/50 font-medium shadow-2xs">
                        <svg class="w-2.5 h-2.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span>Artwork</span>
                    </span>
                </div>
            </div>
        </template>

        <!-- Quick Action Chips Bar (No emojis, crisp stroke icons) -->
        <div class="px-3.5 py-2 bg-slate-50/40 border-b border-slate-200/70 dark:bg-[#0b0f19] dark:border-slate-800/60 flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
            <!-- Chip 1: Copy & Grammar Check -->
            <button type="button"
                    @click="triggerAction('grammar_check')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-2xs dark:bg-[#111827] dark:text-slate-300 dark:border-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-800/80 dark:hover:text-slate-100 whitespace-nowrap transition-colors cursor-pointer disabled:opacity-40">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 11 3 3L22 4"/>
                    <path d="m21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>Check Copy &amp; Grammar</span>
            </button>

            <!-- Chip 2: Critique Artwork -->
            <button type="button"
                    @click="triggerAction('artwork_critique')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-2xs dark:bg-[#111827] dark:text-slate-300 dark:border-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-800/80 dark:hover:text-slate-100 whitespace-nowrap transition-colors cursor-pointer disabled:opacity-40">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                    <circle cx="9" cy="9" r="2"/>
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                </svg>
                <span>Critique Artwork</span>
            </button>

            <!-- Chip 3: Rewrite Caption -->
            <button type="button"
                    @click="triggerAction('rewrite_caption')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-2xs dark:bg-[#111827] dark:text-slate-300 dark:border-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-800/80 dark:hover:text-slate-100 whitespace-nowrap transition-colors cursor-pointer disabled:opacity-40">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
                <span>Rewrite Caption</span>
            </button>

            <!-- Chip 4: Brainstorm Concepts -->
            <button type="button"
                    @click="triggerAction('brainstorm')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-white text-slate-700 border border-slate-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 shadow-2xs dark:bg-[#111827] dark:text-slate-300 dark:border-slate-800 dark:hover:border-slate-700 dark:hover:bg-slate-800/80 dark:hover:text-slate-100 whitespace-nowrap transition-colors cursor-pointer disabled:opacity-40">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
                    <path d="M9 18h6"/>
                    <path d="M10 22h4"/>
                </svg>
                <span>Brainstorm Ideas</span>
            </button>
        </div>

        <!-- Scrollable Messages Area -->
        <div id="loopsAiChatScrollContainer"
             class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs text-slate-700 dark:text-slate-200 bg-white dark:bg-[#0b0f19] loops-chat-scroll">
            
            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    <!-- USER MESSAGE (Subtle slate bubble) -->
                    <template x-if="msg.role === 'user'">
                        <div class="flex justify-end">
                            <div class="max-w-[85%] rounded-xl bg-slate-100 border border-slate-200 text-slate-800 dark:bg-slate-800/90 dark:border-slate-700/70 dark:text-slate-100 px-3.5 py-2 text-xs leading-relaxed whitespace-pre-wrap font-normal"
                                 x-text="msg.content">
                            </div>
                        </div>
                    </template>

                    <!-- MODEL / ASSISTANT MESSAGE -->
                    <template x-if="msg.role === 'model'">
                        <div class="flex items-start gap-2.5 group">
                            <!-- Small Avatar -->
                            <div class="w-6 h-6 rounded-md bg-slate-100 border border-slate-200 text-slate-600 dark:bg-slate-800/90 dark:border-slate-700/60 dark:text-slate-300 text-[10px] font-semibold flex items-center justify-center shrink-0 mt-0.5">
                                <span>AI</span>
                            </div>

                            <div class="flex-1 space-y-1.5 min-w-0">
                                <div class="relative bg-slate-50 border border-slate-200/90 dark:bg-[#111827] dark:border-slate-800/80 rounded-xl p-3 text-xs leading-relaxed text-slate-800 dark:text-slate-200">
                                    
                                    <!-- Formatted Markdown Body -->
                                    <div class="prose dark:prose-invert max-w-none text-xs space-y-1.5 leading-relaxed text-slate-800 dark:text-slate-200"
                                         x-html="renderMarkdown(msg.content)">
                                    </div>

                                    <!-- Quick Copy Button (Floating Top-Right) -->
                                    <button type="button"
                                            @click="copyMessageText(msg.content)"
                                            title="Copy message to clipboard"
                                            class="absolute top-2 right-2 p-1 rounded bg-white border border-slate-200 text-slate-400 hover:text-slate-700 dark:bg-slate-800/90 dark:border-slate-700/70 dark:text-slate-400 dark:hover:text-slate-200 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500 px-1">
                                    <span x-text="msg.time || ''"></span>
                                    <span x-show="msg.copied" class="text-slate-700 dark:text-slate-300 font-medium">Copied to clipboard</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Loading Indicator -->
            <div x-show="isLoading" class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-md bg-slate-100 border border-slate-200 text-slate-600 dark:bg-slate-800/90 dark:border-slate-700/60 dark:text-slate-300 text-[10px] font-semibold flex items-center justify-center shrink-0 mt-0.5">
                    <span>AI</span>
                </div>
                <div class="bg-slate-50 border border-slate-200 dark:bg-[#111827] dark:border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce" style="animation-delay: 0ms;"></span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce" style="animation-delay: 150ms;"></span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-slate-400 dark:bg-slate-500 animate-bounce" style="animation-delay: 300ms;"></span>
                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 ml-1">Analyzing...</span>
                </div>
            </div>
        </div>

        <!-- Bottom Input & Send Form -->
        <div class="p-3 bg-slate-50/90 border-t border-slate-200 dark:bg-[#0e1422] dark:border-slate-800/80 shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                <div class="flex-1 relative">
                    <textarea x-model="userInput"
                              @keydown.enter.prevent="if (!$event.shiftKey) { sendMessage(); }"
                              rows="1"
                              placeholder="Ask anything about this brief or design..."
                              class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-300 focus:border-slate-300 dark:bg-[#111827] dark:border-slate-700/80 dark:text-slate-100 dark:placeholder-slate-500 dark:focus:ring-slate-600 dark:focus:border-slate-600 resize-none max-h-24 min-h-[38px] leading-relaxed transition-all"></textarea>
                </div>

                <button type="submit"
                        :disabled="isLoading || !userInput.trim()"
                        class="h-[38px] w-[38px] rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700/90 dark:border-slate-700/70 dark:text-slate-200 dark:hover:text-white flex items-center justify-center shrink-0 transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m22 2-7 20-4-9-9-4Z"/>
                        <path d="M22 2 11 13"/>
                    </svg>
                </button>
            </form>
            <div class="flex items-center justify-between text-[10.5px] text-slate-400 dark:text-slate-500 mt-1.5 px-0.5">
                <span>Enter to send · Shift+Enter for new line</span>
                <span>Gemini</span>
            </div>
        </div>
    </div>
</div>

<style>
.loops-chat-scroll::-webkit-scrollbar {
    width: 6px;
}
.loops-chat-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.loops-chat-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.5);
    border-radius: 9999px;
}
.dark .loops-chat-scroll::-webkit-scrollbar-thumb,
html.dark .loops-chat-scroll::-webkit-scrollbar-thumb {
    background: rgba(51, 65, 85, 0.6);
}
.loops-chat-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.8);
}
.dark .loops-chat-scroll::-webkit-scrollbar-thumb:hover,
html.dark .loops-chat-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(71, 85, 105, 0.8);
}
</style>

<script>
function loopsAiChatWidget(config) {
    return {
        isOpen: false,
        showPill: true,
        isLoading: false,
        userInput: '',
        deliverableId: config.deliverableId || null,
        deliverableTitle: config.deliverableTitle || '',
        brandName: config.brandName || '',
        stage: config.stage || '',
        messages: [],

        init() {
            // Check if page already has an active subtask/deliverable in the modal
            if (!this.deliverableId) {
                const taskIdAttr = document.getElementById('modalTaskTitle')?.getAttribute('data-task-id');
                if (taskIdAttr) {
                    const parsed = parseInt(taskIdAttr);
                    if (!isNaN(parsed) && parsed > 0) {
                        this.deliverableId = parsed;
                    }
                }
            }

            // Load greetings message
            let greeting = '**Loops AI Creative Copilot**\n\nAsk anything about creative briefs, copy, captions, or artwork critiques.';
            if (this.deliverableTitle) {
                greeting = `Focused on **${this.deliverableTitle}**` + (this.brandName ? ` (${this.brandName})` : '') + `.\n\nCreative concept, copy, caption, and artwork are loaded.`;
            }

            this.messages.push({
                role: 'model',
                content: greeting,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                copied: false
            });
        },

        updateDeliverableContext(detail) {
            if (!detail) return;
            const isDifferent = this.deliverableId && detail.id && (this.deliverableId !== detail.id);
            this.deliverableId = detail.id || this.deliverableId;
            this.deliverableTitle = detail.title || this.deliverableTitle;
            if (detail.brandName) this.brandName = detail.brandName;
            if (detail.stage) this.stage = detail.stage;

            // Notify user of dynamic context switch when navigating subtasks
            if (isDifferent && this.messages.length > 0) {
                this.messages.push({
                    role: 'model',
                    content: `Active deliverable updated to **${this.deliverableTitle}**` + (this.brandName ? ` (${this.brandName})` : '') + `.\n\nConcept, copy, caption, and artwork are synchronized.`,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    copied: false
                });
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.showPill = false;
                this.$nextTick(() => {
                    this.scrollToBottom();
                });
            }
        },

        clearChat() {
            this.messages = [];
            this.init();
        },

        scrollToBottom() {
            const container = document.getElementById('loopsAiChatScrollContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        getLiveDeliverableContext() {
            let concept = '';
            let copy = '';
            let caption = '';

            // 1. Concept: check window.quillConcept, textarea modalConcept, or DOM editor
            if (window.quillConcept?.root?.innerHTML && window.quillConcept.root.innerHTML !== '<p><br></p>') {
                concept = window.quillConcept.root.innerHTML;
            } else if (document.getElementById('modalConcept')?.value) {
                concept = document.getElementById('modalConcept').value;
            } else if (document.querySelector('#quillConcept .ql-editor')?.innerHTML) {
                concept = document.querySelector('#quillConcept .ql-editor').innerHTML;
            }

            // 2. Body Copy: check window.quillCopy, textarea modalSubtaskCopy, or DOM editor
            if (window.quillCopy?.root?.innerHTML && window.quillCopy.root.innerHTML !== '<p><br></p>') {
                copy = window.quillCopy.root.innerHTML;
            } else if (document.getElementById('modalSubtaskCopy')?.value) {
                copy = document.getElementById('modalSubtaskCopy').value;
            } else if (document.querySelector('#quillCopy .ql-editor')?.innerHTML) {
                copy = document.querySelector('#quillCopy .ql-editor').innerHTML;
            }

            // 3. Caption: check window.quillCaption, textarea modalCaption, or DOM editor
            if (window.quillCaption?.root?.innerHTML && window.quillCaption.root.innerHTML !== '<p><br></p>') {
                caption = window.quillCaption.root.innerHTML;
            } else if (document.getElementById('modalCaption')?.value) {
                caption = document.getElementById('modalCaption').value;
            } else if (document.querySelector('#quillCaption .ql-editor')?.innerHTML) {
                caption = document.querySelector('#quillCaption .ql-editor').innerHTML;
            }

            // 4. Resolve active task ID if modal is open
            let activeId = this.deliverableId;
            const taskIdAttr = document.getElementById('modalTaskTitle')?.getAttribute('data-task-id');
            if (taskIdAttr) {
                const parsed = parseInt(taskIdAttr);
                if (!isNaN(parsed) && parsed > 0) {
                    activeId = parsed;
                }
            }

            return {
                deliverable_id: activeId,
                concept: (concept || '').trim(),
                copy: (copy || '').trim(),
                caption: (caption || '').trim(),
            };
        },

        async triggerAction(actionName) {
            if (this.isLoading) return;

            let promptLabel = '';
            if (actionName === 'grammar_check') promptLabel = 'Run Copy & Grammar Review';
            else if (actionName === 'artwork_critique') promptLabel = 'Critique Uploaded Artwork';
            else if (actionName === 'rewrite_caption') promptLabel = 'Rewrite Caption';
            else if (actionName === 'brainstorm') promptLabel = 'Brainstorm Creative Angles';

            this.messages.push({
                role: 'user',
                content: promptLabel,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });

            this.isLoading = true;
            this.$nextTick(() => this.scrollToBottom());

            await this.callAiEndpoint({ action: actionName });
        },

        async sendMessage() {
            const text = this.userInput.trim();
            if (!text || this.isLoading) return;

            this.messages.push({
                role: 'user',
                content: text,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });

            this.userInput = '';
            this.isLoading = true;
            this.$nextTick(() => this.scrollToBottom());

            await this.callAiEndpoint({ message: text, action: 'chat' });
        },

        async callAiEndpoint(payload) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Extract live on-screen text from Quill editors & active modal
            const liveCtx = this.getLiveDeliverableContext();
            payload.live_context = liveCtx;

            const targetId = liveCtx.deliverable_id || this.deliverableId;
            const endpoint = targetId 
                ? `/deliverables/${targetId}/ai-chat`
                : '/ai/chat';

            // Prepare history turns for conversation context
            const history = this.messages
                .filter(m => m.role === 'user' || m.role === 'model')
                .slice(-8)
                .map(m => ({ role: m.role, content: m.content }));

            payload.conversation_history = history;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (data.reply) {
                    this.messages.push({
                        role: 'model',
                        content: data.reply,
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        copied: false
                    });
                } else {
                    this.messages.push({
                        role: 'model',
                        content: '⚠️ No response received from the assistant. Please try again.',
                        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                        copied: false
                    });
                }
            } catch (err) {
                console.error('AI Chat Error:', err);
                this.messages.push({
                    role: 'model',
                    content: '⚠️ Network error communicating with the AI service. Please verify your connection and try again.',
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    copied: false
                });
            } finally {
                this.isLoading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        copyMessageText(content) {
            if (!content) return;
            navigator.clipboard.writeText(content).then(() => {
                const msg = this.messages.find(m => m.content === content);
                if (msg) {
                    msg.copied = true;
                    setTimeout(() => { msg.copied = false; }, 2000);
                }
            }).catch(err => {
                console.error('Clipboard copy error:', err);
            });
        },

        renderMarkdown(rawText) {
            if (!rawText) return '';
            
            // Safe HTML escaping
            let escaped = rawText
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Headers
            escaped = escaped.replace(/^### (.*$)/gim, '<h4 class="font-bold text-slate-800 dark:text-slate-100 mt-2 mb-1 text-xs">$1</h4>');
            escaped = escaped.replace(/^## (.*$)/gim, '<h3 class="font-extrabold text-slate-800 dark:text-slate-100 mt-2.5 mb-1 text-xs">$1</h3>');

            // Bold & Italic
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900 dark:text-slate-100">$1</strong>');
            escaped = escaped.replace(/\*(.*?)\*/g, '<em class="italic">$1</em>');
            escaped = escaped.replace(/_(.*?)_/g, '<em class="italic text-slate-500 dark:text-slate-400">$1</em>');

            // Inline Code
            escaped = escaped.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700/60 font-mono text-[11px]">$1</code>');

            // Bullet Lists
            escaped = escaped.replace(/^\- (.*$)/gim, '<div class="flex items-start gap-1.5 pl-1 my-0.5"><span class="text-slate-400 dark:text-slate-500 font-bold">•</span><span>$1</span></div>');

            // Paragraphs / Linebreaks
            escaped = escaped.replace(/\n\n/g, '<div class="h-2"></div>');
            escaped = escaped.replace(/\n/g, '<br/>');

            return escaped;
        }
    };
}
</script>
