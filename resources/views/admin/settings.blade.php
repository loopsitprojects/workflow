<x-layout title="Admin Settings">
    <div x-data="{
        activeTab: window.location.hash ? window.location.hash.replace('#', '') : 'general',
        setTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
        }
    }" class="max-w-7xl mx-auto w-full flex flex-col gap-8 pb-16">

        <!-- Top Header & Context Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                        Administration
                    </span>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">• System Hub</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Admin Settings</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 font-medium mt-1">
                    Manage users, subtask taxonomies, workflow pipelines, feature toggles, themes, and platform maintenance.
                </p>
            </div>

            <!-- Quick Global Status Pill -->
            <div class="flex items-center gap-3">
                @if($maintenance['enabled'])
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>MAINTENANCE ACTIVE</span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>SYSTEM ONLINE</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Session Notification Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-between gap-3 text-sm font-bold card-shadow">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 flex items-center justify-between gap-3 text-sm font-bold card-shadow">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Main Body: Sidebar Navigation + Tabbed Content -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Sidebar Navigation -->
            <aside class="lg:col-span-3 flex flex-col gap-6 sticky top-6">
                <!-- Navigation Tabs Menu -->
                <nav class="bg-white dark:bg-[#0F172A] rounded-2xl p-3 card-shadow border border-slate-200 dark:border-slate-800 flex flex-row lg:flex-col gap-1.5 overflow-x-auto scrollbar-none">
                    
                    <button type="button" @click="setTab('general')"
                            :class="activeTab === 'general' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 border-transparent font-medium'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all border text-left whitespace-nowrap">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>General Overview</span>
                    </button>

                    <button type="button" @click="setTab('features')"
                            :class="activeTab === 'features' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 border-transparent font-medium'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all border text-left whitespace-nowrap">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <span class="flex-1">Feature Toggles</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    </button>

                    <button type="button" @click="setTab('projects')"
                            :class="activeTab === 'projects' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 border-transparent font-medium'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all border text-left whitespace-nowrap">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="flex-1">Project Workflows</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">3</span>
                    </button>

                    <button type="button" @click="setTab('appearance')"
                            :class="activeTab === 'appearance' ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 border-transparent font-medium'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all border text-left whitespace-nowrap">
                        <span class="w-4 h-4 flex items-center justify-center text-xs">
                            @if(($activeTheme ?? 'dark') === 'halloween')
                                🎃
                            @elseif(($activeTheme ?? 'dark') === 'christmas')
                                🎄
                            @else
                                🎨
                            @endif
                        </span>
                        <span class="flex-1">Appearance & Themes</span>
                    </button>

                    <button type="button" @click="setTab('maintenance')"
                            :class="activeTab === 'maintenance' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/30 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/60 border-transparent font-medium'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs transition-all border text-left whitespace-nowrap">
                        <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>System Maintenance</span>
                    </button>
                </nav>

                <!-- Direct Navigation Shortcuts Card -->
                <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-4 card-shadow border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500 px-2 pt-1">
                        Management Directory
                    </p>
                    
                    <a href="{{ route('users.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">User Management</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <a href="{{ route('subtask-types.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Subtask Types</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </aside>

            <!-- Right Content Panels -->
            <main class="lg:col-span-9 flex flex-col gap-6">

                <!-- TAB 1: GENERAL OVERVIEW -->
                <div x-show="activeTab === 'general'" class="flex flex-col gap-6">
                    <!-- Quick KPI Overview Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        
                        <!-- User Management Shortcut Card -->
                        <a href="{{ route('users.index') }}" class="group bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-slate-200 dark:border-slate-800 hover:border-blue-500/50 transition-all hover:-translate-y-1 flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">User Management</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                                    Add, edit, or remove team members, manage account security, and assign roles.
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-blue-600 dark:text-blue-400 text-xs font-bold">
                                <span>Manage Users</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </div>
                        </a>

                        <!-- Subtask Type Management Shortcut Card -->
                        <a href="{{ route('subtask-types.index') }}" class="group bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 transition-all hover:-translate-y-1 flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center mb-4 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">Subtask Types</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                                    Configure delivery categories such as Carousel, Reels, Outlines, or Pitch Deck.
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-amber-600 dark:text-amber-400 text-xs font-bold">
                                <span>Manage Types</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </div>
                        </a>

                        <!-- Platform Theme Quick Access Card -->
                        <button type="button" @click="setTab('appearance')" class="group text-left bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-slate-200 dark:border-slate-800 hover:border-purple-500/50 transition-all hover:-translate-y-1 flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors text-xl select-none">
                                    @if(($activeTheme ?? 'dark') === 'halloween')
                                        🎃
                                    @elseif(($activeTheme ?? 'dark') === 'christmas')
                                        🎄
                                    @else
                                        🎨
                                    @endif
                                </div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">Theme Settings</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                                    Currently: <strong class="text-slate-900 dark:text-white uppercase">{{ $activeTheme ?? 'dark' }} MODE</strong>. Switch to Dark, Light, Halloween, or Christmas.
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-purple-600 dark:text-purple-400 text-xs font-bold">
                                <span>Manage Theme</span>
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </div>
                        </button>

                    </div>

                    <!-- Platform Summary Banner -->
                    <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-slate-200 dark:border-slate-800">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Quick Health & Configuration Status</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">System State</span>
                                <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-1 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $maintenance['enabled'] ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                                    <span>{{ $maintenance['enabled'] ? 'Maintenance' : 'Operational' }}</span>
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active Theme</span>
                                <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-1 capitalize">
                                    {{ $activeTheme ?? 'dark' }} Mode
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Project Workflows</span>
                                <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-1">
                                    {{ count(array_filter($projectTypesStatus ?? ['retainer'=>true,'campaign'=>true,'pitch'=>true])) }} of 3 Enabled
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Modular Features</span>
                                <p class="text-sm font-extrabold text-slate-900 dark:text-white mt-1">
                                    3 Modules Ready
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: FEATURE TOGGLES -->
                <div x-show="activeTab === 'features'" class="flex flex-col gap-6">
                    <div class="bg-white dark:bg-[#0F172A] rounded-2xl card-shadow border border-slate-200 dark:border-slate-800 overflow-hidden">
                        
                        <!-- Header -->
                        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Platform Feature Toggles</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium mt-1">
                                    Control modular creative workflow capabilities across the agency.
                                </p>
                            </div>
                        </div>

                        <!-- Unified Feature Rows List -->
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            
                            <!-- 1. Send to Client / Client Review Portal -->
                            <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 {{ ($clientReviewEnabled ?? true) ? 'bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Send to Client (Client Review Portal)</h4>
                                            @if($clientReviewEnabled ?? true)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> FEATURE ENABLED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> FEATURE DISABLED
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-1">
                                            Enable or disable tokenized proof links, client annotations, and the "Send to Client" button.
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('admin.features.toggle-client-review') }}" method="POST" id="clientReviewToggleForm" class="flex items-center gap-3 self-end sm:self-center flex-shrink-0">
                                    @csrf
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        {{ ($clientReviewEnabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                    <button type="submit" onclick="return confirmAction(event, '{{ ($clientReviewEnabled ?? true) ? 'Disable Send to Client Feature?' : 'Enable Send to Client Feature?' }}', '{{ ($clientReviewEnabled ?? true) ? 'Hide Send to Client actions and pause external client review sessions?' : 'Allow teams to generate review links and send proofs to clients?' }}', false, '{{ ($clientReviewEnabled ?? true) ? 'Disable Feature' : 'Enable Feature' }}');"
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($clientReviewEnabled ?? true) ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-700' }}"
                                        role="switch" aria-checked="{{ ($clientReviewEnabled ?? true) ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($clientReviewEnabled ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </div>

                            <!-- 2. Direct Design Flow -->
                            <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 {{ ($directDesignEnabled ?? true) ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Direct Design Flow</h4>
                                            @if($directDesignEnabled ?? true)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> FEATURE ENABLED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> FEATURE DISABLED
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-1">
                                            Enable or disable direct design deliverables that bypass copywriting stages directly to designers.
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('admin.features.toggle-direct-design') }}" method="POST" id="directDesignToggleForm" class="flex items-center gap-3 self-end sm:self-center flex-shrink-0">
                                    @csrf
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        {{ ($directDesignEnabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                    <button type="submit" onclick="return confirmAction(event, '{{ ($directDesignEnabled ?? true) ? 'Disable Direct Design Feature?' : 'Enable Direct Design Feature?' }}', '{{ ($directDesignEnabled ?? true) ? 'Hide Direct Design actions and prevent creating new direct design deliverables?' : 'Allow teams to create direct design deliverables and batches?' }}', false, '{{ ($directDesignEnabled ?? true) ? 'Disable Feature' : 'Enable Feature' }}');"
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($directDesignEnabled ?? true) ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-700' }}"
                                        role="switch" aria-checked="{{ ($directDesignEnabled ?? true) ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($directDesignEnabled ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </div>

                            <!-- 3. Analytics & Reports Dashboard -->
                            <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 {{ ($analyticsEnabled ?? true) ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">Analytics & Reports Dashboard</h4>
                                            @if($analyticsEnabled ?? true)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> FEATURE ENABLED
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> FEATURE DISABLED
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-1">
                                            Enable or disable the Analytics tab in navigation and agency workflow reporting for team members.
                                        </p>
                                    </div>
                                </div>

                                <form action="{{ route('admin.features.toggle-analytics') }}" method="POST" id="analyticsToggleForm" class="flex items-center gap-3 self-end sm:self-center flex-shrink-0">
                                    @csrf
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        {{ ($analyticsEnabled ?? true) ? 'ON' : 'OFF' }}
                                    </span>
                                    <button type="submit" onclick="return confirmAction(event, '{{ ($analyticsEnabled ?? true) ? 'Disable Analytics Tab?' : 'Enable Analytics Tab?' }}', '{{ ($analyticsEnabled ?? true) ? 'Hide the Analytics tab and reports from the navigation bar for all team members?' : 'Enable the Analytics tab and reports for all team members?' }}', false, '{{ ($analyticsEnabled ?? true) ? 'Disable Tab' : 'Enable Tab' }}');"
                                        class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ ($analyticsEnabled ?? true) ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700' }}"
                                        role="switch" aria-checked="{{ ($analyticsEnabled ?? true) ? 'true' : 'false' }}">
                                        <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ ($analyticsEnabled ?? true) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- TAB 3: PROJECT CONFIGURATIONS -->
                <div x-show="activeTab === 'projects'" class="flex flex-col gap-6">
                    <div class="bg-white dark:bg-[#0F172A] rounded-2xl card-shadow border border-slate-200 dark:border-slate-800 overflow-hidden">
                        
                        <!-- Header -->
                        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Project Types Configuration</h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        {{ count(array_filter($projectTypesStatus ?? ['retainer'=>true,'campaign'=>true,'pitch'=>true])) }} of 3 Active
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium mt-1">
                                    Enable or disable project workflows. Disabled project types cannot be selected during project creation.
                                </p>
                            </div>
                        </div>

                        <!-- Types List -->
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @php
                                $typesMeta = [
                                    'retainer' => [
                                        'name' => 'Retainer',
                                        'flow' => '7-stage approval flow',
                                        'desc' => 'Multi-stage pipeline for recurring brand deliverables and ongoing retainers.',
                                        'icon_bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                                        'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                                    ],
                                    'campaign' => [
                                        'name' => 'Campaign',
                                        'flow' => '4-stage initiative',
                                        'desc' => 'Streamlined 4-stage pipeline for campaign deliverables and marketing pushes.',
                                        'icon_bg' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
                                        'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
                                    ],
                                    'pitch' => [
                                        'name' => 'Pitch',
                                        'flow' => 'Business proposal',
                                        'desc' => 'Lean approval pipeline tailored for client pitch deliverables and proposals.',
                                        'icon_bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                                    ],
                                ];
                            @endphp

                            @foreach($typesMeta as $key => $meta)
                                @php
                                    $isEnabled = (bool) ($projectTypesStatus[$key] ?? true);
                                @endphp
                                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6 hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors {{ !$isEnabled ? 'opacity-70' : '' }}">
                                    <div class="flex items-start gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mt-0.5 {{ $isEnabled ? $meta['icon_bg'] : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $meta['icon'] }}" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2.5 flex-wrap">
                                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $meta['name'] }}</h4>
                                                @if($isEnabled)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> ACTIVE
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> DISABLED
                                                    </span>
                                                @endif
                                                <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">• {{ $meta['flow'] }}</span>
                                            </div>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-1">
                                                {{ $meta['desc'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Toggle Switch -->
                                    <form action="{{ route('admin.features.toggle-project-type') }}" method="POST" class="flex items-center gap-3 self-end sm:self-center flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="project_type" value="{{ $key }}">
                                        <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {{ $isEnabled ? 'ON' : 'OFF' }}
                                        </span>
                                        <button type="submit"
                                            onclick="return confirmAction(event, '{{ $isEnabled ? 'Disable ' . $meta['name'] . ' Project Type?' : 'Enable ' . $meta['name'] . ' Project Type?' }}', '{{ $isEnabled ? 'This will hide ' . $meta['name'] . ' from the project creation options. Existing ' . $meta['name'] . ' projects will remain intact.' : 'Allow teams to create new ' . $meta['name'] . ' projects.' }}', {{ $isEnabled ? 'true' : 'false' }}, '{{ $isEnabled ? 'Disable' : 'Enable' }}');"
                                            class="relative inline-flex h-7 w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isEnabled ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-700' }}"
                                            role="switch" aria-checked="{{ $isEnabled ? 'true' : 'false' }}">
                                            <span class="pointer-events-none inline-block h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- TAB 4: APPEARANCE & THEMES -->
                <div x-show="activeTab === 'appearance'" class="flex flex-col gap-6">
                    <div id="theme-settings-section" class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 sm:p-8 card-shadow border border-slate-200 dark:border-slate-800">
                        
                        <!-- Header & Active Badge Indicator -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Platform Theme & Appearance Settings</h3>
                                    @if(($activeTheme ?? 'dark') === 'halloween')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-orange-500/15 text-orange-600 dark:text-orange-400 border border-orange-500/30">
                                            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                            HALLOWEEN MODE ACTIVE 🎃
                                        </span>
                                    @elseif(($activeTheme ?? 'dark') === 'christmas')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            CHRISTMAS MODE ACTIVE 🎄
                                        </span>
                                    @elseif(($activeTheme ?? 'dark') === 'dark')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            DARK THEME ACTIVE 🌙
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-500/10 text-amber-600 dark:text-amber-500 border border-amber-500/20">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            LIGHT THEME ACTIVE ☀️
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-1">
                                    Select the default platform theme for all workspaces. Click any card below to apply immediately.
                                </p>
                            </div>

                            <!-- Seasonal States Indicator (clean status badges) -->
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300">
                                    <span>🎄 Christmas:</span>
                                    <span class="{{ ($activeTheme ?? 'dark') === 'christmas' ? 'text-emerald-500 font-black' : 'text-slate-400' }}">
                                        {{ ($activeTheme ?? 'dark') === 'christmas' ? 'ON' : 'OFF' }}
                                    </span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 text-slate-600 dark:text-slate-300">
                                    <span>🎃 Halloween:</span>
                                    <span class="{{ ($activeTheme ?? 'dark') === 'halloween' ? 'text-orange-500 font-black' : 'text-slate-400' }}">
                                        {{ ($activeTheme ?? 'dark') === 'halloween' ? 'ON' : 'OFF' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- 4 Dedicated Selectable Theme Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                            
                            <!-- 1. Christmas Theme Card -->
                            <form action="{{ route('admin.settings.theme') }}" method="POST" class="h-full">
                                @csrf
                                <input type="hidden" name="theme" value="christmas">
                                <button type="submit" class="w-full h-full text-left p-5 rounded-2xl border-2 transition-all flex flex-col justify-between group cursor-pointer {{ ($activeTheme ?? 'dark') === 'christmas' ? 'border-emerald-500 bg-emerald-500/[0.04] dark:bg-emerald-950/20 shadow-md ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 hover:border-emerald-400 dark:hover:border-emerald-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/80' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-2xl select-none">🎄</span>
                                            <!-- Selectable Radio Indicator -->
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors {{ ($activeTheme ?? 'dark') === 'christmas' ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600' }}">
                                                @if(($activeTheme ?? 'dark') === 'christmas')
                                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                                @endif
                                            </div>
                                        </div>
                                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                            Christmas Festive Mode
                                        </h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                                            Snowy pine skyline, falling animated snowflakes, swinging bauble, winter holly accents, and crimson & emerald colors in Light and Dark modes.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs font-bold {{ ($activeTheme ?? 'dark') === 'christmas' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 group-hover:text-emerald-500' }}">
                                        {{ ($activeTheme ?? 'dark') === 'christmas' ? '✓ Currently Applied' : 'Activate Christmas Theme →' }}
                                    </div>
                                </button>
                            </form>

                            <!-- 2. Halloween Theme Card -->
                            <form action="{{ route('admin.settings.theme') }}" method="POST" class="h-full">
                                @csrf
                                <input type="hidden" name="theme" value="halloween">
                                <button type="submit" class="w-full h-full text-left p-5 rounded-2xl border-2 transition-all flex flex-col justify-between group cursor-pointer {{ ($activeTheme ?? 'dark') === 'halloween' ? 'border-orange-500 bg-orange-500/[0.04] dark:bg-orange-950/20 shadow-md ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 hover:border-orange-400 dark:hover:border-orange-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/80' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-2xl select-none">🎃</span>
                                            <!-- Selectable Radio Indicator -->
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors {{ ($activeTheme ?? 'dark') === 'halloween' ? 'border-orange-500 bg-orange-500 text-white' : 'border-slate-300 dark:border-slate-600' }}">
                                                @if(($activeTheme ?? 'dark') === 'halloween')
                                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                                @endif
                                            </div>
                                        </div>
                                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                            Halloween Spooky Mode
                                        </h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                                            Haunted skyline header, animated bats, swinging spider, pumpkin card watermarks, and festive orange accents across both Light and Dark modes.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs font-bold {{ ($activeTheme ?? 'dark') === 'halloween' ? 'text-orange-600 dark:text-orange-400' : 'text-slate-400 group-hover:text-orange-500' }}">
                                        {{ ($activeTheme ?? 'dark') === 'halloween' ? '✓ Currently Applied' : 'Activate Halloween Theme →' }}
                                    </div>
                                </button>
                            </form>

                            <!-- 3. Standard Dark Theme Card -->
                            <form action="{{ route('admin.settings.theme') }}" method="POST" class="h-full">
                                @csrf
                                <input type="hidden" name="theme" value="dark">
                                <button type="submit" class="w-full h-full text-left p-5 rounded-2xl border-2 transition-all flex flex-col justify-between group cursor-pointer {{ ($activeTheme ?? 'dark') === 'dark' ? 'border-blue-500 bg-blue-500/[0.04] dark:bg-blue-950/20 shadow-md ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 hover:border-blue-400 dark:hover:border-blue-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/80' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-2xl select-none">🌙</span>
                                            <!-- Selectable Radio Indicator -->
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors {{ ($activeTheme ?? 'dark') === 'dark' ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-300 dark:border-slate-600' }}">
                                                @if(($activeTheme ?? 'dark') === 'dark')
                                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                                @endif
                                            </div>
                                        </div>
                                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            Standard Dark Theme
                                        </h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                                            Deep charcoal navy surfaces, clean high contrast, electric blue highlights, and reduced eye strain.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs font-bold {{ ($activeTheme ?? 'dark') === 'dark' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 group-hover:text-blue-500' }}">
                                        {{ ($activeTheme ?? 'dark') === 'dark' ? '✓ Currently Applied' : 'Activate Dark Theme →' }}
                                    </div>
                                </button>
                            </form>

                            <!-- 4. Standard Light Theme Card -->
                            <form action="{{ route('admin.settings.theme') }}" method="POST" class="h-full">
                                @csrf
                                <input type="hidden" name="theme" value="light">
                                <button type="submit" class="w-full h-full text-left p-5 rounded-2xl border-2 transition-all flex flex-col justify-between group cursor-pointer {{ ($activeTheme ?? 'dark') === 'light' ? 'border-amber-500 bg-amber-500/[0.04] dark:bg-amber-950/20 shadow-md ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40 hover:border-amber-400 dark:hover:border-amber-500/50 hover:bg-slate-50 dark:hover:bg-slate-900/80' }}">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-2xl select-none">☀️</span>
                                            <!-- Selectable Radio Indicator -->
                                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors {{ ($activeTheme ?? 'dark') === 'light' ? 'border-amber-500 bg-amber-500 text-white' : 'border-slate-300 dark:border-slate-600' }}">
                                                @if(($activeTheme ?? 'dark') === 'light')
                                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                                @endif
                                            </div>
                                        </div>
                                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white mb-1 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                            Standard Light Theme
                                        </h4>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                                            Bright corporate white canvas, crisp typography, clean light slate borders, and vibrant agency accents.
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs font-bold {{ ($activeTheme ?? 'dark') === 'light' ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400 group-hover:text-amber-500' }}">
                                        {{ ($activeTheme ?? 'dark') === 'light' ? '✓ Currently Applied' : 'Activate Light Theme →' }}
                                    </div>
                                </button>
                            </form>

                        </div>
                    </div>
                </div>

                <!-- TAB 5: SYSTEM MAINTENANCE (Danger / System Zone) -->
                <div x-show="activeTab === 'maintenance'" class="flex flex-col gap-6">
                    <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 sm:p-8 card-shadow border {{ $maintenance['enabled'] ? 'border-amber-500/50 dark:border-amber-500/50 ring-2 ring-amber-500/20' : 'border-slate-200 dark:border-slate-800' }}">
                        
                        <!-- Header with Warning Accent -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 {{ $maintenance['enabled'] ? 'bg-amber-500/15 text-amber-500' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }} rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">System Maintenance Mode</h3>
                                        @if($maintenance['enabled'])
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30 animate-pulse">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                                MAINTENANCE ACTIVE
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                SYSTEM ONLINE
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-300 font-medium leading-relaxed mt-1">
                                        Restricts non-administrator access during scheduled database migrations and deployments.
                                    </p>
                                </div>
                            </div>

                            <!-- Maintenance Toggle Switch -->
                            <form action="{{ route('admin.maintenance.toggle') }}" method="POST" id="maintenanceToggleForm" class="flex items-center gap-3 self-end sm:self-center flex-shrink-0">
                                @csrf
                                <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    {{ $maintenance['enabled'] ? 'ON' : 'OFF' }}
                                </span>
                                <button type="submit" onclick="return confirmAction(event, '{{ $maintenance['enabled'] ? 'Turn OFF Maintenance Mode?' : 'Turn ON Maintenance Mode?' }}', '{{ $maintenance['enabled'] ? 'Allow non-administrator users to access the system again?' : 'Restrict system access to administrators only while performing maintenance?' }}', false, '{{ $maintenance['enabled'] ? 'Turn OFF' : 'Turn ON' }}');"
                                    class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $maintenance['enabled'] ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-700' }}"
                                    role="switch" aria-checked="{{ $maintenance['enabled'] ? 'true' : 'false' }}">
                                    <span class="pointer-events-none inline-block h-7 w-7 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $maintenance['enabled'] ? 'translate-x-6' : 'translate-x-0' }}"></span>
                                </button>
                            </form>
                        </div>

                        <!-- Cautionary Notice Box -->
                        <div class="mt-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/25 flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div class="text-xs">
                                <p class="font-bold text-amber-700 dark:text-amber-400">Impact Notice:</p>
                                <p class="text-slate-600 dark:text-slate-300 mt-0.5 leading-relaxed font-medium">
                                    When System Maintenance is enabled, standard users receive an HTTP 503 Maintenance Screen. Administrators maintain full platform access to test and verify new changes.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

            </main>
        </div>

    </div>
</x-layout>
