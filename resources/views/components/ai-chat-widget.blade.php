@props([
    'deliverable' => null,
])

@php
    $aiEnabled = \App\Services\FeatureManager::isAiEnabled();
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
             class="hidden sm:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900/90 dark:bg-slate-800/90 backdrop-blur-md text-white text-xs font-semibold shadow-lg border border-slate-700/50">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Loops AI Copilot</span>
            <button type="button" @click.stop="showPill = false" class="text-slate-400 hover:text-white ml-1">&times;</button>
        </div>

        <!-- Main Circular Trigger Button -->
        <button type="button"
                @click="toggleChat()"
                class="relative group w-14 h-14 rounded-full bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 text-white shadow-xl shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-105 active:scale-95 transition-all duration-200 flex items-center justify-center cursor-pointer focus:outline-none focus:ring-4 focus:ring-indigo-500/30">
            
            <!-- Sparkles Icon (When Closed) -->
            <div x-show="!isOpen" class="transition-transform duration-200 group-hover:rotate-12 flex items-center justify-center">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    <path d="M5 3v4"/>
                    <path d="M19 17v4"/>
                    <path d="M3 5h4"/>
                    <path d="M17 19h4"/>
                </svg>
            </div>

            <!-- Close Icon (When Open) -->
            <div x-show="isOpen" class="transition-transform duration-200 rotate-90 flex items-center justify-center">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"/>
                    <path d="m6 6 12 12"/>
                </svg>
            </div>

            <!-- Glow Ring -->
            <span class="absolute -inset-0.5 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 opacity-0 group-hover:opacity-40 blur transition-opacity"></span>
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. EXPANDABLE CHAT WINDOW                                                 -->
    <!-- ========================================================================= -->
    <div x-show="isOpen"
         x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95"
         class="fixed bottom-24 right-6 z-50 w-[410px] max-w-[calc(100vw-2rem)] h-[600px] max-h-[82vh] bg-white dark:bg-[#111927] border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl shadow-slate-900/20 dark:shadow-black/50 flex flex-col overflow-hidden backdrop-blur-xl">

        <!-- Top Header Bar -->
        <div class="px-5 py-3.5 bg-slate-50/90 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-purple-600 text-white flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-black text-slate-900 dark:text-white tracking-tight">Loops AI Copilot</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Ready
                        </span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium truncate max-w-[200px]" 
                         x-text="deliverableTitle ? (deliverableTitle + (brandName ? ' (' + brandName + ')' : '')) : 'Agency Assistant'">
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-1 text-slate-400">
                <!-- Clear Chat -->
                <button type="button"
                        @click="clearChat()"
                        title="Clear Conversation"
                        class="p-1.5 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-lg transition-colors cursor-pointer">
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
                        class="p-1.5 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/50 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"/>
                        <path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Deliverable Context Banner (if on deliverable) -->
        <template x-if="deliverableTitle">
            <div class="px-4 py-2 bg-indigo-50/60 dark:bg-indigo-950/20 border-b border-indigo-100/60 dark:border-indigo-900/30 flex items-center justify-between gap-2 text-[11px]">
                <div class="flex items-center gap-1.5 truncate text-indigo-700 dark:text-indigo-300 font-semibold">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span class="truncate" x-text="'Grounded in: ' + deliverableTitle"></span>
                </div>
                <span class="text-[10px] text-indigo-500 font-bold uppercase shrink-0" x-text="stage || 'Active'"></span>
            </div>
        </template>

        <!-- Quick Action Chips Carousel -->
        <div class="px-3 py-2 bg-slate-50/50 dark:bg-slate-900/40 border-b border-slate-100 dark:border-slate-800/80 overflow-x-auto no-scrollbar flex items-center gap-1.5 shrink-0">
            <!-- Chip 1: Copy & Grammar Check -->
            <button type="button"
                    @click="triggerAction('grammar_check')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 shadow-2xs whitespace-nowrap transition-colors cursor-pointer disabled:opacity-50">
                <span>⚡ Check Copy &amp; Grammar</span>
            </button>

            <!-- Chip 2: Artwork Critique -->
            <button type="button"
                    @click="triggerAction('artwork_critique')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-purple-500 hover:text-purple-600 dark:hover:text-purple-400 shadow-2xs whitespace-nowrap transition-colors cursor-pointer disabled:opacity-50">
                <span>🎨 Critique Artwork</span>
            </button>

            <!-- Chip 3: Rewrite Caption -->
            <button type="button"
                    @click="triggerAction('rewrite_caption')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 shadow-2xs whitespace-nowrap transition-colors cursor-pointer disabled:opacity-50">
                <span>✍️ Rewrite Caption</span>
            </button>

            <!-- Chip 4: Brainstorm Concepts -->
            <button type="button"
                    @click="triggerAction('brainstorm')"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 shadow-2xs whitespace-nowrap transition-colors cursor-pointer disabled:opacity-50">
                <span>💡 Brainstorm Ideas</span>
            </button>
        </div>

        <!-- Scrollable Messages History Area -->
        <div id="loopsAiChatScrollContainer"
             class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs text-slate-800 dark:text-slate-200">
            
            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    <!-- USER MESSAGE -->
                    <template x-if="msg.role === 'user'">
                        <div class="flex justify-end">
                            <div class="max-w-[85%] rounded-2xl rounded-tr-xs bg-blue-600 text-white px-3.5 py-2.5 text-xs font-medium shadow-sm leading-relaxed whitespace-pre-wrap"
                                 x-text="msg.content">
                            </div>
                        </div>
                    </template>

                    <!-- MODEL / ASSISTANT MESSAGE -->
                    <template x-if="msg.role === 'model'">
                        <div class="flex items-start gap-2.5 group">
                            <!-- Small Avatar -->
                            <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-blue-600 to-purple-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5 shadow-2xs">
                                <span>AI</span>
                            </div>

                            <div class="flex-1 space-y-1.5 min-w-0">
                                <div class="relative bg-slate-50 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-800 rounded-2xl rounded-tl-xs p-3.5 text-xs leading-relaxed text-slate-800 dark:text-slate-200 shadow-2xs">
                                    
                                    <!-- Formatted Markdown Body -->
                                    <div class="prose dark:prose-invert max-w-none text-xs space-y-2 leading-relaxed"
                                         x-html="renderMarkdown(msg.content)">
                                    </div>

                                    <!-- Quick Copy Button (Floating Top-Right) -->
                                    <button type="button"
                                            @click="copyMessageText(msg.content)"
                                            title="Copy message to clipboard"
                                            class="absolute top-2 right-2 p-1 rounded-md bg-white dark:bg-slate-700/80 border border-slate-200 dark:border-slate-600 text-slate-400 hover:text-slate-700 dark:hover:text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between text-[10px] text-slate-400 px-1">
                                    <span x-text="msg.time || ''"></span>
                                    <span x-show="msg.copied" class="text-emerald-500 font-bold">✓ Copied to clipboard</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Loading / Thinking Indicator -->
            <div x-show="isLoading" class="flex items-start gap-2.5">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-blue-600 to-purple-600 text-white flex items-center justify-center shrink-0 text-[10px] mt-0.5">
                    <span>AI</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2.5 text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 shadow-2xs">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 animate-bounce" style="animation-delay: 0ms;"></span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 animate-bounce" style="animation-delay: 150ms;"></span>
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-indigo-500 animate-bounce" style="animation-delay: 300ms;"></span>
                    <span class="text-[11px] font-semibold text-slate-400 ml-1">Analyzing...</span>
                </div>
            </div>
        </div>

        <!-- Bottom Input & Send Form -->
        <div class="p-3 bg-white dark:bg-[#111927] border-t border-slate-200/80 dark:border-slate-800 shrink-0">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                <div class="flex-1 relative">
                    <textarea x-model="userInput"
                              @keydown.enter.prevent="if (!$event.shiftKey) { sendMessage(); }"
                              rows="1"
                              placeholder="Ask anything about this brief or design..."
                              class="w-full text-xs bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-2xl px-3.5 py-2.5 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 resize-none max-h-24 min-h-[42px] leading-relaxed transition-all"></textarea>
                </div>

                <button type="submit"
                        :disabled="isLoading || !userInput.trim()"
                        class="h-[42px] px-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center shrink-0 hover:from-blue-700 hover:to-indigo-700 active:scale-95 transition-all shadow-md shadow-indigo-500/20 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m22 2-7 20-4-9-9-4Z"/>
                        <path d="M22 2 11 13"/>
                    </svg>
                </button>
            </form>
            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1 px-1">
                <span>Press <strong>Enter</strong> to send &bull; <strong>Shift+Enter</strong> for newline</span>
                <span>Powered by Gemini</span>
            </div>
        </div>
    </div>
</div>

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
            // Load greetings message
            let greeting = '👋 **Hi! I am your Loops AI Creative Copilot.**\n\nI can review copy & grammar, critique final artwork against your reference moodboards, rewrite social captions, and brainstorm creative angles.';
            if (this.deliverableTitle) {
                greeting = `👋 **Hi! I am your Loops AI Creative Copilot.**\n\nI am currently focused on **${this.deliverableTitle}**` + (this.brandName ? ` for **${this.brandName}**.` : '.') + `\n\nTap any quick action above or ask me anything!`;
            }

            this.messages.push({
                role: 'model',
                content: greeting,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                copied: false
            });
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

        async triggerAction(actionName) {
            if (this.isLoading) return;

            let promptLabel = '';
            if (actionName === 'grammar_check') promptLabel = '⚡ Run Copy & Grammar Review';
            else if (actionName === 'artwork_critique') promptLabel = '🎨 Critique Uploaded Artwork';
            else if (actionName === 'rewrite_caption') promptLabel = '✍️ Rewrite Caption';
            else if (actionName === 'brainstorm') promptLabel = '💡 Brainstorm Creative Angles';

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
            const endpoint = this.deliverableId 
                ? `/deliverables/${this.deliverableId}/ai-chat`
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
            escaped = escaped.replace(/^### (.*$)/gim, '<h4 class="font-bold text-slate-900 dark:text-white mt-2 mb-1 text-xs">$1</h4>');
            escaped = escaped.replace(/^## (.*$)/gim, '<h3 class="font-extrabold text-slate-900 dark:text-white mt-2.5 mb-1 text-xs">$1</h3>');

            // Bold & Italic
            escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900 dark:text-white">$1</strong>');
            escaped = escaped.replace(/\*(.*?)\*/g, '<em class="italic">$1</em>');
            escaped = escaped.replace(/_(.*?)_/g, '<em class="italic text-slate-500 dark:text-slate-400">$1</em>');

            // Inline Code
            escaped = escaped.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-mono text-[11px]">$1</code>');

            // Bullet Lists
            escaped = escaped.replace(/^\- (.*$)/gim, '<div class="flex items-start gap-1.5 pl-1 my-0.5"><span class="text-indigo-500 font-bold">•</span><span>$1</span></div>');

            // Paragraphs / Linebreaks
            escaped = escaped.replace(/\n\n/g, '<div class="h-2"></div>');
            escaped = escaped.replace(/\n/g, '<br/>');

            return escaped;
        }
    };
}
</script>
