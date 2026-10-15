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

        <!-- Project Types Management Section -->
        <div class="bg-white dark:bg-[#0F172A] rounded-[32px] p-8 card-shadow border border-gray-100 dark:border-slate-800">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-gray-100 dark:border-slate-800/80">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-500/10 text-indigo-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Project Types Configuration</h3>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-indigo-500/10 text-indigo-500 border border-indigo-500/20">
                                {{ count(array_filter($projectTypesStatus ?? ['retainer'=>true,'campaign'=>true,'pitch'=>true])) }} of 3 Active
                            </span>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-slate-500 font-medium mt-1">
                            Enable or disable project workflows. Disabled project types cannot be selected during project creation.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $typesMeta = [
                        'retainer' => [
                            'name' => 'Retainer',
                            'flow' => '7-stage approval flow',
                            'desc' => 'Multi-stage pipeline for recurring brand deliverables and ongoing retainers.',
                            'color' => 'blue',
                            'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                        ],
                        'campaign' => [
                            'name' => 'Campaign',
                            'flow' => '4-stage initiative',
                            'desc' => 'Streamlined 4-stage pipeline for campaign deliverables and marketing pushes.',
                            'color' => 'indigo',
                            'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
                        ],
                        'pitch' => [
                            'name' => 'Pitch',
                            'flow' => 'Business proposal',
                            'desc' => 'Lean approval pipeline tailored for client pitch deliverables and proposals.',
                            'color' => 'amber',
                            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                        ],
                    ];
                @endphp

                @foreach($typesMeta as $key => $meta)
                    @php
                        $isEnabled = (bool) ($projectTypesStatus[$key] ?? true);
                    @endphp
                    <div class="rounded-2xl p-6 border transition-all flex flex-col justify-between {{ $isEnabled ? 'bg-slate-50/70 dark:bg-slate-900/40 border-gray-200/80 dark:border-slate-800' : 'bg-gray-50/40 dark:bg-slate-950/40 border-dashed border-gray-200 dark:border-slate-800/60 opacity-75' }}">
                        <div>
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $isEnabled ? 'bg-' . $meta['color'] . '-50 dark:bg-' . $meta['color'] . '-900/20 text-' . $meta['color'] . '-600' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 dark:text-slate-500' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
                                    </svg>
                                </div>

                                <!-- Toggle Switch -->
                                <form action="{{ route('admin.features.toggle-project-type') }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="project_type" value="{{ $key }}">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                        {{ $isEnabled ? 'ON' : 'OFF' }}
                                    </span>
                                    <button type="submit"
                                        onclick="return confirmAction(event, '{{ $isEnabled ? 'Disable ' . $meta['name'] . ' Project Type?' : 'Enable ' . $meta['name'] . ' Project Type?' }}', '{{ $isEnabled ? 'This will hide ' . $meta['name'] . ' from the project creation options. Existing ' . $meta['name'] . ' projects will remain intact.' : 'Allow teams to create new ' . $meta['name'] . ' projects.' }}', {{ $isEnabled ? 'true' : 'false' }}, '{{ $isEnabled ? 'Disable' : 'Enable' }}');"
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isEnabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-slate-700' }}"
                                        role="switch" aria-checked="{{ $isEnabled ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </div>

                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-base font-bold text-gray-900 dark:text-white">{{ $meta['name'] }}</h4>
                                @if($isEnabled)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ACTIVE
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-500/10 text-slate-400 border border-slate-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> DISABLED
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 mb-2">{{ $meta['flow'] }}</div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed font-medium">{{ $meta['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
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
