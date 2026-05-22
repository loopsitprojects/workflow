<div x-data="{
    open: false,
    filter: 'all',
    notifications: @js(auth()->user() ? (auth()->user()->isAdmin()
        ? \Illuminate\Notifications\DatabaseNotification::with('notifiable')->latest()->take(30)->get()
        : auth()->user()->unreadNotifications()->latest()->take(30)->get()
    )->map(fn($n) => [
        'id'           => $n->id,
        'title'        => $n->data['task_title'] ?? 'Deliverable Update',
        'message'      => $n->data['message'] ?? '',
        'type'         => $n->data['type'] ?? 'info',
        'actor_name'   => $n->data['actor_name'] ?? 'System',
        'actor_avatar' => $n->data['actor_avatar'] ?? null,
        'url'          => $n->data['url'] ?? '#',
        'read'         => !is_null($n->read_at),
        'date'         => $n->created_at->diffForHumans(),
        'target_name'  => $n->notifiable->name ?? '',
        'is_global'    => auth()->user()->isAdmin() && $n->notifiable_id !== auth()->id(),
    ]) : []),
    init() {
        this.$nextTick(() => this.$dispatch('update-unread-count', this.unreadCount()));
        this.$watch('notifications', v => this.$dispatch('update-unread-count', v.filter(n => !n.read).length));
    },
    unreadCount() { return this.notifications.filter(n => !n.read).length; },
    filteredNotifications() {
        if (this.filter === 'unread')      return this.notifications.filter(n => !n.read);
        if (this.filter === 'deliverables') return this.notifications.filter(n => ['stage_update','revision_request'].includes(n.type));
        return this.notifications;
    },
    markAllAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(() => this.notifications.forEach(n => n.read = true));
    },
    archiveAll() {
        fetch('/notifications/archive-all', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(() => { this.notifications = []; this.open = false; });
    }
}"
@open-notifications.window="open = true"
@keydown.escape.window="open = false"
x-show="open"
class="fixed inset-0 z-[200]"
style="display:none;">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="absolute inset-0 bg-black/30 dark:bg-black/50 backdrop-blur-sm">
    </div>

    {{-- Slide-in Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         class="absolute inset-y-0 right-0 w-[400px] flex flex-col
                bg-white dark:bg-[#111827]
                border-l border-gray-200/60 dark:border-white/[0.06]
                shadow-2xl dark:shadow-black/60">

        {{-- Header --}}
        <div class="flex-shrink-0 px-6 pt-6 pb-4 border-b border-gray-100 dark:border-white/[0.06]">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <h2 class="text-[15px] font-extrabold text-gray-900 dark:text-white tracking-tight">Notifications</h2>
                    <template x-if="unreadCount() > 0">
                        <span class="px-1.5 py-0.5 rounded-md bg-blue-500 text-white text-[10px] font-black"
                              x-text="unreadCount()"></span>
                    </template>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="markAllAsRead"
                            class="text-[11px] font-bold text-blue-500 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                        Mark all read
                    </button>
                    <button @click="open = false"
                            class="p-1.5 rounded-lg text-gray-400 dark:text-slate-500
                                   hover:text-gray-700 dark:hover:text-white
                                   hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Filter tabs --}}
            <div class="flex gap-1">
                <button @click="filter = 'all'"
                        :class="filter === 'all'
                            ? 'bg-gray-100 dark:bg-white/[0.08] text-gray-900 dark:text-white font-semibold'
                            : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300'"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all">All</button>
                <button @click="filter = 'unread'"
                        :class="filter === 'unread'
                            ? 'bg-gray-100 dark:bg-white/[0.08] text-gray-900 dark:text-white font-semibold'
                            : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300'"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all">Unread</button>
                <button @click="filter = 'deliverables'"
                        :class="filter === 'deliverables'
                            ? 'bg-gray-100 dark:bg-white/[0.08] text-gray-900 dark:text-white font-semibold'
                            : 'text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300'"
                        class="px-3 py-1.5 rounded-lg text-[11px] font-semibold transition-all">Deliverables</button>
            </div>
        </div>

        {{-- List --}}
        <div class="flex-1 overflow-y-auto notif-scroll">
            <template x-if="filteredNotifications().length === 0">
                <div class="flex flex-col items-center justify-center h-64 gap-4 text-center px-8">
                    <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-white/[0.04] border border-gray-100 dark:border-white/[0.06] flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[13px] font-bold text-gray-700 dark:text-slate-300 mb-1">All caught up</p>
                        <p class="text-[12px] text-gray-400 dark:text-slate-500">No notifications here.</p>
                    </div>
                </div>
            </template>

            <div class="py-2">
                <template x-for="notif in filteredNotifications()" :key="notif.id">
                    <a :href="notif.url"
                       :class="!notif.read ? 'bg-blue-50/50 dark:bg-blue-500/[0.05]' : ''"
                       class="group flex items-start gap-3 px-5 py-3.5
                              hover:bg-gray-50 dark:hover:bg-white/[0.04]
                              border-b border-gray-50 dark:border-white/[0.03]
                              transition-colors duration-150 last:border-b-0">

                        {{-- Avatar with badge --}}
                        <div class="relative flex-shrink-0 mt-0.5">
                            <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 dark:bg-white/[0.06] ring-1 ring-gray-200 dark:ring-white/[0.08]">
                                <template x-if="notif.actor_avatar">
                                    <img :src="notif.actor_avatar" class="w-full h-full object-cover" alt="">
                                </template>
                                <template x-if="!notif.actor_avatar">
                                    <div class="w-full h-full flex items-center justify-center text-[11px] font-bold text-gray-500 dark:text-slate-400"
                                         x-text="notif.actor_name ? notif.actor_name.charAt(0).toUpperCase() : 'S'"></div>
                                </template>
                            </div>
                            {{-- Type badge --}}
                            <div :class="{
                                    'bg-blue-500':   notif.type === 'stage_update',
                                    'bg-red-500':    notif.type === 'revision_request',
                                    'bg-violet-500': notif.type === 'mention',
                                    'bg-gray-400':   !['stage_update','revision_request','mention'].includes(notif.type)
                                 }"
                                 class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-[#111827] flex items-center justify-center">
                                <template x-if="notif.type === 'stage_update'">
                                    <svg class="w-1.5 h-1.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </template>
                                <template x-if="notif.type === 'revision_request'">
                                    <svg class="w-1.5 h-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </template>
                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] leading-snug text-gray-500 dark:text-slate-400">
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="notif.actor_name"></span>
                                <span x-html="notif.message
                                    ? ' ' + notif.message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                                    : ' updated a deliverable'"></span>
                            </p>
                            <p class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 mt-0.5 truncate" x-text="notif.title"></p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="text-[10px] text-gray-400 dark:text-slate-500" x-text="notif.date"></span>
                                <template x-if="notif.is_global && notif.target_name">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold
                                                 bg-gray-100 dark:bg-white/[0.05]
                                                 text-gray-400 dark:text-slate-500
                                                 uppercase tracking-wide">
                                        → <span x-text="notif.target_name"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- Unread dot --}}
                        <div class="flex-shrink-0 mt-1.5">
                            <div x-show="!notif.read" class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 px-5 py-4 border-t border-gray-100 dark:border-white/[0.06] bg-gray-50/80 dark:bg-white/[0.02]">
            <button @click="archiveAll"
                    class="w-full py-2.5 rounded-xl text-[12px] font-bold
                           text-gray-500 dark:text-slate-400
                           bg-white dark:bg-white/[0.04]
                           border border-gray-200 dark:border-white/[0.08]
                           hover:bg-gray-100 dark:hover:bg-white/[0.08]
                           hover:text-gray-700 dark:hover:text-slate-200
                           transition-all duration-150 flex items-center justify-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8"/>
                </svg>
                Clear all notifications
            </button>
        </div>
    </div>
</div>

<style>
.notif-scroll::-webkit-scrollbar { width: 3px; }
.notif-scroll::-webkit-scrollbar-track { background: transparent; }
.notif-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.dark .notif-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); }
</style>
