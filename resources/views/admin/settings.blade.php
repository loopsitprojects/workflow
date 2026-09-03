<x-layout title="Admin Settings">
    <div class="flex flex-col gap-10">
        <!-- Header Section -->
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-1">Admin Settings</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 font-medium tracking-tight">Configure system settings, manage users, subtask types, and system maintenance mode.</p>
        </div>

        @if(session('success'))
            <div style="background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:700;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:700;">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- User Management Card -->
            <a href="{{ route('users.index') }}" class="group bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border border-gray-100 dark:border-slate-800 hover:border-blue-200 dark:hover:border-blue-500/50 transition-all hover:translate-y-[-4px]">
                <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">User Management</h3>
                <p class="text-sm text-gray-400 dark:text-slate-500 font-medium leading-relaxed mb-6">Add, edit, or remove team members and assign system roles.</p>
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 text-[10px] font-bold uppercase tracking-widest">
                    Manage Users
                    <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </div>
            </a>

            <!-- Subtask Type Management Card -->
            <a href="{{ route('subtask-types.index') }}" class="group bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border border-gray-100 dark:border-slate-800 hover:border-amber-200 dark:hover:border-amber-500/50 transition-all hover:translate-y-[-4px]">
                <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-500 transition-colors">
                    <svg class="w-7 h-7 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Subtask Types</h3>
                <p class="text-sm text-gray-400 dark:text-slate-500 font-medium leading-relaxed mb-6">Define custom categories for deliverables like Carousel, Reels, or KV.</p>
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-[10px] font-bold uppercase tracking-widest">
                    Manage Types
                    <svg class="w-3 h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </div>
            </a>
        </div>

        <!-- Client Review / Send to Client Section -->
        <div class="bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border {{ ($clientReviewEnabled ?? true) ? 'border-blue-500/30 dark:border-blue-500/30' : 'border-gray-100 dark:border-slate-800' }}">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 {{ ($clientReviewEnabled ?? true) ? 'bg-blue-500/10 text-blue-500' : 'bg-slate-500/10 text-slate-400' }} rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Send to Client (Client Review Portal)</h3>
                            @if($clientReviewEnabled ?? true)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    FEATURE ENABLED
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    FEATURE DISABLED
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-400 dark:text-slate-500 font-medium mt-1">
                            Enable or disable tokenized proof links, client annotations, and the "Send to Client" button.
                        </p>
                    </div>
                </div>

                <!-- Sleek Toggle Switch -->
                <form action="{{ route('admin.features.toggle-client-review') }}" method="POST" id="clientReviewToggleForm" class="flex-shrink-0 flex items-center gap-3">
                    @csrf
                    <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                        {{ ($clientReviewEnabled ?? true) ? 'ON' : 'OFF' }}
                    </span>
                    <button type="submit" onclick="return confirmAction(event, '{{ ($clientReviewEnabled ?? true) ? 'Disable Send to Client Feature?' : 'Enable Send to Client Feature?' }}', '{{ ($clientReviewEnabled ?? true) ? 'Hide Send to Client actions and pause external client review sessions?' : 'Allow teams to generate review links and send proofs to clients?' }}', false, '{{ ($clientReviewEnabled ?? true) ? 'Disable Feature' : 'Enable Feature' }}');"
                        class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($clientReviewEnabled ?? true) ? 'bg-blue-600' : 'bg-gray-300 dark:bg-slate-700' }}"
                        role="switch" aria-checked="{{ ($clientReviewEnabled ?? true) ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($clientReviewEnabled ?? true) ? 'translate-x-6' : 'translate-x-0' }}"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Maintenance Mode Section -->
        <div class="bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border {{ $maintenance['enabled'] ? 'border-amber-500/50 dark:border-amber-500/50' : 'border-gray-100 dark:border-slate-800' }}">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 {{ $maintenance['enabled'] ? 'bg-amber-500/10 text-amber-500' : 'bg-emerald-500/10 text-emerald-500' }} rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">System Maintenance Mode</h3>
                            @if($maintenance['enabled'])
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    MAINTENANCE ACTIVE
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    SYSTEM ONLINE
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-400 dark:text-slate-500 font-medium mt-1">
                            Turn maintenance mode ON or OFF when updating the application.
                        </p>
                    </div>
                </div>

                <!-- Sleek Toggle Switch -->
                <form action="{{ route('admin.maintenance.toggle') }}" method="POST" id="maintenanceToggleForm" class="flex-shrink-0 flex items-center gap-3">
                    @csrf
                    <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                        {{ $maintenance['enabled'] ? 'ON' : 'OFF' }}
                    </span>
                    <button type="submit" onclick="return confirmAction(event, '{{ $maintenance['enabled'] ? 'Turn OFF Maintenance Mode?' : 'Turn ON Maintenance Mode?' }}', '{{ $maintenance['enabled'] ? 'Allow non-administrator users to access the system again?' : 'Restrict system access to administrators only while performing maintenance?' }}', false, '{{ $maintenance['enabled'] ? 'Turn OFF' : 'Turn ON' }}');"
                        class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $maintenance['enabled'] ? 'bg-amber-500' : 'bg-gray-300 dark:bg-slate-700' }}"
                        role="switch" aria-checked="{{ $maintenance['enabled'] ? 'true' : 'false' }}">
                        <span class="pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $maintenance['enabled'] ? 'translate-x-6' : 'translate-x-0' }}"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-layout>
