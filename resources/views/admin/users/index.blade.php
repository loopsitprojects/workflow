<x-layout title="User Management">
    @php
        $roleOrder = ['Admin', 'Brand Manager', 'Approver', 'Traffic Coordinator', 'Coordinator', 'Designer', 'Writer'];
        $groupedUsers = $users->sortBy(function($user) use ($roleOrder) {
            $pos = array_search($user->role, $roleOrder);
            return $pos === false ? 999 : $pos;
        })->groupBy('role');

        $roleColors = [
            'Admin' => ['bg' => 'bg-emerald-900', 'text' => 'text-emerald-50', 'accent' => 'border-emerald-500', 'dot' => 'bg-emerald-500'],
            'Brand Manager' => ['bg' => 'bg-blue-900', 'text' => 'text-blue-50', 'accent' => 'border-blue-500', 'dot' => 'bg-blue-500'],
            'Approver' => ['bg' => 'bg-amber-600', 'text' => 'text-amber-50', 'accent' => 'border-amber-400', 'dot' => 'bg-amber-400'],
            'Coordinator' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-50', 'accent' => 'border-indigo-400', 'dot' => 'bg-indigo-400'],
            'Traffic Coordinator' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-50', 'accent' => 'border-indigo-400', 'dot' => 'bg-indigo-400'],
            'Designer' => ['bg' => 'bg-pink-600', 'text' => 'text-pink-50', 'accent' => 'border-pink-400', 'dot' => 'bg-pink-400'],
            'Writer' => ['bg' => 'bg-sky-500', 'text' => 'text-sky-50', 'accent' => 'border-sky-300', 'dot' => 'bg-sky-400'],
        ];
    @endphp

    <style>
        .mini-user-tile { transition: all 0.2s ease; }
        .mini-user-tile:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    </style>

    <div class="flex flex-col gap-10 pb-20"
         x-data="{
            search: '',
            users: {{ \Illuminate\Support\Js::from($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role, 'email' => $u->email ?? ''])) }},
            get filteredIds() {
                if (!this.search.trim()) return null;
                const s = this.search.toLowerCase();
                return this.users.filter(u => u.name.toLowerCase().includes(s) || u.role.toLowerCase().includes(s) || u.email.toLowerCase().includes(s)).map(u => u.id);
            },
            deleteTarget: null,
            deleteForm: null,
            confirmDelete(userId, name, formId) {
                this.deleteTarget = name;
                this.deleteForm = formId;
            },
            doDelete() {
                if (this.deleteForm) document.getElementById(this.deleteForm).submit();
            }
         }">
        <!-- Delete Confirmation Modal -->
        <div x-show="deleteTarget" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
            <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-100 dark:border-white/[0.08] shadow-2xl p-6 w-full max-w-sm">
                <div class="w-12 h-12 bg-red-50 dark:bg-red-500/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-[15px] font-bold text-gray-900 dark:text-white mb-1">Delete User</h3>
                <p class="text-[13px] text-gray-500 dark:text-slate-400 mb-5">Are you sure you want to permanently delete <strong x-text="deleteTarget" class="text-gray-800 dark:text-white"></strong>? This cannot be undone.</p>
                <div class="flex gap-2 justify-end">
                    <button @click="deleteTarget = null" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-white/[0.06] hover:bg-gray-200 dark:hover:bg-white/[0.10] transition-colors">Cancel</button>
                    <button @click="doDelete()" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">Delete</button>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-1 tracking-tight">Team Members</h1>
                <p class="text-[10px] text-gray-400 dark:text-slate-500 font-black uppercase tracking-[0.2em] opacity-80">Directory of all users by role</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white dark:bg-white/[0.04] border border-gray-200 dark:border-white/[0.08] shadow-sm w-56">
                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" placeholder="Search by name or role…"
                           class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700 dark:text-slate-300 placeholder-gray-400 dark:placeholder-slate-500">
                    <span x-show="search" @click="search=''" class="text-[10px] font-bold text-gray-400 cursor-pointer hover:text-gray-600 uppercase tracking-wide">Clear</span>
                </div>
                <a href="{{ route('users.create') }}" class="px-6 py-3 bg-[#0055D4] text-white rounded-xl text-[10px] font-black hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 uppercase tracking-widest flex items-center gap-2">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    Add User
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 px-5 py-3 rounded-xl text-xs font-bold shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800 text-red-700 dark:text-red-400 px-5 py-3 rounded-xl text-xs font-bold shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col gap-10">
            @foreach($groupedUsers as $role => $users)
                @php $colors = $roleColors[$role] ?? $roleColors['Admin']; @endphp
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $colors['dot'] }}"></div>
                        <h2 class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 dark:text-slate-500">{{ $role }}s</h2>
                        <span class="text-[10px] font-bold text-gray-300 dark:text-slate-700">({{ $users->count() }})</span>
                        <div class="h-px flex-1 bg-gray-50 dark:bg-slate-800/50"></div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                        @foreach($users as $user)
                        <div class="mini-user-tile group relative bg-white dark:bg-[#0F172A] rounded-2xl border border-gray-100 dark:border-slate-800 p-3 flex items-center gap-3"
                             x-show="!filteredIds || filteredIds.includes({{ $user->id }})">
                            <!-- Avatar initial -->
                            <div class="relative flex-shrink-0">
                                <div class="w-8 h-8 rounded-lg border {{ $colors['accent'] }} flex items-center justify-center text-xs font-bold uppercase" style="background: var(--color-bg-secondary); color: var(--color-text-secondary);">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                @if($user->id === auth()->id())
                                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 border-2 border-white dark:border-[#0F172A] rounded-full flex items-center justify-center">
                                        <svg class="w-1.5 h-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Name Only -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-[12px] font-bold text-gray-900 dark:text-white truncate tracking-tight capitalize">{{ preg_replace('/\s*\(.*?\)\s*/', '', $user->name) }}</h3>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-1 opacity-40 group-hover:opacity-100 transition-opacity duration-200">
                                <a href="{{ route('users.edit', $user) }}" class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-all" title="Edit User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </a>
                                @if($user->id !== auth()->id())
                                <form id="del-user-{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                                <button type="button"
                                        @click="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}', 'del-user-{{ $user->id }}')"
                                        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-all" title="Delete User">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layout>
