<x-layout title="Analytics & Reports">
    @php
        $currentPeriod = $filters['period'] ?? 'all';
        $currentState = $filters['state'] ?? 'all';
        $defaultTab = $isManager ? (request('tab') ?: 'brands') : (request('tab') ?: 'deliverables');
    @endphp

    {{-- Chart.js Library --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div x-data="{
        activeTab: '{{ $defaultTab }}',
        searchQuery: '',
        customDatesOpen: {{ (!empty($filters['start_date']) && !empty($filters['end_date'])) ? 'true' : 'false' }},
        detailModalOpen: false,
        detailLoading: false,
        selectedDeliverableId: null,
        deliverableDetail: null,
        detailError: null,
        async viewDeliverableDetails(id) {
            this.selectedDeliverableId = id;
            this.detailModalOpen = true;
            this.detailLoading = true;
            this.deliverableDetail = null;
            this.detailError = null;
            try {
                const response = await fetch(`/analytics/deliverables/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) {
                    throw new Error('Unable to load deliverable details.');
                }
                this.deliverableDetail = await response.json();
            } catch (err) {
                this.detailError = err.message || 'An error occurred while loading details.';
            } finally {
                this.detailLoading = false;
            }
        },
        closeDeliverableModal() {
            this.detailModalOpen = false;
            this.selectedDeliverableId = null;
            this.deliverableDetail = null;
            this.detailError = null;
        }
    }" class="flex flex-col gap-6 pb-16">

        {{-- Top Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    @if($isManager)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                            Managerial Report
                        </span>
                        <span class="text-xs text-gray-400 dark:text-slate-500 font-medium">Brand-wise & Project-wise Oversight</span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Individual Report
                        </span>
                        <span class="text-xs text-gray-400 dark:text-slate-500 font-medium">Personal Output for {{ $currentUser->name }}</span>
                    @endif
                </div>

                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    @if($isManager)
                        Analytics & Reports
                    @else
                        My Performance & Reports
                    @endif
                </h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium mt-0.5">
                    @if($isManager)
                        Simplified delivery tracking: Active and Completed deliverables filtered by date.
                    @else
                        Your personal creative deliverables: Active and Completed tasks filtered by date.
                    @endif
                </p>
            </div>

            {{-- Action Buttons (Export CSV, Print & Admin Toggle) --}}
            <div class="flex items-center gap-2.5 flex-wrap">
                @if(auth()->user() && auth()->user()->isAdmin())
                    <form action="{{ route('admin.features.toggle-analytics') }}" method="POST" class="inline-flex items-center">
                        @csrf
                        <button type="submit" 
                                title="{{ !empty($isFeatureDisabled) ? 'Enable Analytics tab for all team members' : 'Disable Analytics tab (hide from team members)' }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all hover:-translate-y-0.5 card-shadow {{ !empty($isFeatureDisabled) ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' }}">
                            <span class="w-2 h-2 rounded-full {{ !empty($isFeatureDisabled) ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                            <span>{{ !empty($isFeatureDisabled) ? 'Analytics: OFF (Enable)' : 'Analytics: ON (Disable)' }}</span>
                        </button>
                    </form>
                @endif

                <a href="{{ route('analytics.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-gray-700 dark:text-slate-200 bg-white dark:bg-[#0F172A] border border-gray-200 dark:border-slate-800 hover:border-gray-300 dark:hover:border-slate-700 card-shadow transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export CSV</span>
                </a>

                <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 card-shadow transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        {{-- Admin Warning Banner if tab is toggled OFF --}}
        @if(!empty($isFeatureDisabled))
            <div class="rounded-2xl p-4 bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-xs">
                        <p class="font-bold text-amber-600 dark:text-amber-400">Notice: Analytics Tab is currently DISABLED for standard users.</p>
                        <p class="text-amber-700/80 dark:text-amber-300/80 mt-0.5">
                            Only administrators can view this page. You can re-enable it anytime here or in <a href="{{ route('admin.settings') }}" class="underline font-bold">Admin Settings</a>.
                        </p>
                    </div>
                </div>
                @if(auth()->user() && auth()->user()->isAdmin())
                    <form action="{{ route('admin.features.toggle-analytics') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold bg-amber-500 text-white hover:bg-amber-600 shadow-sm transition-all">
                            Enable Analytics Now
                        </button>
                    </form>
                @endif
            </div>
        @endif

        {{-- Date Filter Bar --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border border-gray-100 dark:border-slate-800">
            <div class="flex flex-col gap-4">
                {{-- Date Presets Row --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 p-1 bg-gray-100/80 dark:bg-slate-900/80 rounded-xl border border-gray-200/60 dark:border-slate-800 overflow-x-auto max-w-full no-scrollbar">
                        @php
                            $periodOptions = [
                                'all' => 'All Time',
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                '7d' => 'Last 7 Days',
                                '30d' => 'Last 30 Days',
                                'month' => 'This Month',
                                'last_month' => 'Last Month',
                            ];
                        @endphp
                        @foreach($periodOptions as $key => $label)
                            <a href="{{ route('analytics.index', array_merge(request()->except(['period', 'start_date', 'end_date']), ['period' => $key])) }}"
                               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all duration-150 {{ $currentPeriod === $key ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm font-bold' : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                        <button type="button"
                                @click="customDatesOpen = !customDatesOpen"
                                :class="customDatesOpen ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-white shadow-sm font-bold' : 'text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white'"
                                class="px-2.5 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-all duration-150 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Custom Date</span>
                        </button>
                    </div>

                    {{-- Clear Filters link if filtered --}}
                    @if(request()->hasAny(['brand_id', 'project_id', 'workflow_type', 'state', 'status', 'user_id', 'start_date', 'end_date']) || $currentPeriod !== 'all')
                        <a href="{{ route('analytics.index') }}"
                           class="text-xs font-bold text-red-500 hover:text-red-600 flex items-center gap-1 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Reset Filters</span>
                        </a>
                    @endif
                </div>

                {{-- Custom Date Form (Accordion) --}}
                <form method="GET" action="{{ route('analytics.index') }}" x-show="customDatesOpen" x-cloak class="pt-3 border-t border-gray-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <input type="hidden" name="period" value="custom">
                    @if(request('brand_id')) <input type="hidden" name="brand_id" value="{{ request('brand_id') }}"> @endif
                    @if(request('workflow_type')) <input type="hidden" name="workflow_type" value="{{ request('workflow_type') }}"> @endif
                    @if(request('state')) <input type="hidden" name="state" value="{{ request('state') }}"> @endif
                    @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif

                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" required
                               class="w-full px-3 py-1.5 text-xs rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" required
                               class="w-full px-3 py-1.5 text-xs rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 transition">
                            Filter by Range
                        </button>
                    </div>
                </form>

                {{-- Secondary Filters (Brand / Workflow) for Managers --}}
                @if($isManager)
                    <form method="GET" action="{{ route('analytics.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-slate-800">
                        <input type="hidden" name="period" value="{{ $currentPeriod }}">
                        @if($filters['start_date']) <input type="hidden" name="start_date" value="{{ $filters['start_date'] }}"> @endif
                        @if($filters['end_date']) <input type="hidden" name="end_date" value="{{ $filters['end_date'] }}"> @endif
                        @if(request('tab')) <input type="hidden" name="tab" value="{{ request('tab') }}"> @endif

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Filter by Brand</label>
                            <select name="brand_id" onchange="this.form.submit()"
                                    class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Brands ({{ $brands_list->count() }})</option>
                                @foreach($brands_list as $b)
                                    <option value="{{ $b->id }}" {{ (string)$filters['brand_id'] === (string)$b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Workflow Type</label>
                            <select name="workflow_type" onchange="this.form.submit()"
                                    class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Workflows</option>
                                <option value="retainer" {{ ($filters['workflow_type'] ?? '') === 'retainer' ? 'selected' : '' }}>Retainer</option>
                                <option value="campaign" {{ ($filters['workflow_type'] ?? '') === 'campaign' ? 'selected' : '' }}>Campaign</option>
                                <option value="pitch" {{ ($filters['workflow_type'] ?? '') === 'pitch' ? 'selected' : '' }}>Pitch</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mb-1">Team Member</label>
                            <select name="user_id" onchange="this.form.submit()"
                                    class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Team Members</option>
                                @foreach($users_list as $u)
                                    <option value="{{ $u->id }}" {{ (string)($filters['user_id'] ?? '') === (string)$u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->role }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- Core 4 Simplified Metric Cards: Total, Active, Completed, Overdue --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Deliverables --}}
            <a href="{{ route('analytics.index', array_merge(request()->except(['state']), ['state' => 'all'])) }}"
               class="block bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border transition-all hover:scale-[1.01] {{ $currentState === 'all' || empty($currentState) ? 'border-gray-300 dark:border-slate-700 ring-2 ring-gray-400/20' : 'border-gray-100 dark:border-slate-800' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-slate-500">Total Work</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400">
                        {{ $kpis['completion_rate'] }}% Rate
                    </span>
                </div>
                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $kpis['total_deliverables'] }}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-1">all deliverables in date range</p>
            </a>

            {{-- Active Deliverables --}}
            <a href="{{ route('analytics.index', array_merge(request()->except(['state']), ['state' => 'active'])) }}"
               class="block bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border transition-all hover:scale-[1.01] {{ $currentState === 'active' ? 'border-blue-500 dark:border-blue-500 ring-2 ring-blue-500/20' : 'border-gray-100 dark:border-slate-800' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">Active</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500">
                        In Progress
                    </span>
                </div>
                <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ $kpis['active_deliverables'] }}</p>
                <div class="flex items-center justify-between mt-1 text-xs text-gray-400 dark:text-slate-500 font-medium">
                    <span>in production or review</span>
                    @if($kpis['overdue_count'] > 0)
                        <span class="text-red-500 font-bold">({{ $kpis['overdue_count'] }} overdue)</span>
                    @endif
                </div>
            </a>

            {{-- Completed Deliverables (Close means completed) --}}
            <a href="{{ route('analytics.index', array_merge(request()->except(['state']), ['state' => 'completed'])) }}"
               class="block bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border transition-all hover:scale-[1.01] {{ in_array($currentState, ['completed', 'done', 'closed']) ? 'border-emerald-500 dark:border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-100 dark:border-slate-800' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Completed</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500">
                        Done / Closed
                    </span>
                </div>
                <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $kpis['completed_deliverables'] }}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-1">creative work finished</p>
            </a>

            {{-- Overdue Deliverables --}}
            <a href="{{ route('analytics.index', array_merge(request()->except(['state']), ['state' => 'overdue'])) }}"
               class="block bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border transition-all hover:scale-[1.01] {{ $currentState === 'overdue' ? 'border-red-500 dark:border-red-500 ring-2 ring-red-500/20' : 'border-gray-100 dark:border-slate-800' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-red-600 dark:text-red-400">Overdue</span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/10 text-red-500">
                        Attention
                    </span>
                </div>
                <p class="text-3xl font-black text-red-600 dark:text-red-400">{{ $kpis['overdue_count'] }}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-1">past target deadline</p>
            </a>
        </div>

        {{-- Active State Pill Notification if filtered by Active/Completed/Closed --}}
        @if(!empty($currentState) && $currentState !== 'all')
            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 text-xs">
                <span class="text-gray-600 dark:text-slate-300">
                    Currently filtering by state: <strong class="uppercase text-blue-600 dark:text-blue-400">{{ $currentState }}</strong> ({{ $deliverables->count() }} items)
                </span>
                <a href="{{ route('analytics.index', array_merge(request()->except(['state']), ['state' => 'all'])) }}" class="font-bold text-blue-500 hover:underline">
                    Show All States
                </a>
            </div>
        @endif

        {{-- Analytic Diagrams, Graphs & Pie Charts Section --}}
        @include('analytics.partials.charts_section', ['charts' => $charts])

        {{-- View Tabs Navigation --}}
        <div class="flex items-center gap-2 border-b border-gray-200/80 dark:border-slate-800 pb-2 overflow-x-auto no-scrollbar">
            @if($isManager)
                <button type="button" @click="activeTab = 'brands'"
                        :class="activeTab === 'brands' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#0F172A] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Brand-Wise Report</span>
                </button>

                <button type="button" @click="activeTab = 'projects'"
                        :class="activeTab === 'projects' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#0F172A] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>Project-Wise Report</span>
                </button>

                <button type="button" @click="activeTab = 'deliverables'"
                        :class="activeTab === 'deliverables' ? 'bg-blue-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#0F172A] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Deliverables List ({{ $deliverables->count() }})</span>
                </button>
            @else
                <button type="button" @click="activeTab = 'deliverables'"
                        :class="activeTab === 'deliverables' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#0F172A] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>My Deliverables ({{ $individual_report['deliverables']->count() }})</span>
                </button>

                <button type="button" @click="activeTab = 'projects'"
                        :class="activeTab === 'projects' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-white dark:bg-[#0F172A] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-slate-800'"
                        class="px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    <span>My Projects Breakdown</span>
                </button>
            @endif
        </div>

        {{-- ======================================================== --}}
        {{-- TAB CONTENT SECTIONS --}}
        {{-- ======================================================== --}}

        @if($isManager)
            {{-- 1. BRAND-WISE REPORT --}}
            <div x-show="activeTab === 'brands'" x-transition:enter="transition ease-out duration-200" class="flex flex-col gap-4">
                <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Brand-Wise Breakdown</h3>
                            <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-0.5">Summary of Active and Completed deliverables per brand.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                    <th class="py-3 px-3">Brand Workspace</th>
                                    <th class="py-3 px-3 text-center">Active</th>
                                    <th class="py-3 px-3 text-center">Completed</th>
                                    <th class="py-3 px-3 text-center">Total</th>
                                    <th class="py-3 px-3">Completion Rate</th>
                                    <th class="py-3 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-slate-800/60 text-xs">
                                @forelse($brand_performance as $bp)
                                    @php
                                        $b = $bp['brand'];
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-slate-900/40 transition">
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-3">
                                                @if($b->logo_url)
                                                    <img src="{{ $b->logo_url }}" alt="{{ $b->name }}" class="w-8 h-8 rounded-lg object-contain bg-gray-50 dark:bg-slate-800 p-0.5 border border-gray-200 dark:border-slate-700">
                                                @else
                                                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 font-bold flex items-center justify-center text-xs">
                                                        {{ substr($b->name, 0, 2) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('brands.show', $b->slug) }}" class="font-bold text-gray-900 dark:text-white hover:text-blue-500 transition">
                                                        {{ $b->name }}
                                                    </a>
                                                    <span class="text-[10px] text-gray-400 block">{{ $bp['projects_count'] }} projects</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-blue-600 dark:text-blue-400">
                                            {{ $bp['active_deliverables'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ $bp['completed_deliverables'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-gray-900 dark:text-white">
                                            {{ $bp['total_deliverables'] }}
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900 dark:text-white min-w-[32px]">{{ $bp['completion_rate'] }}%</span>
                                                <div class="w-20 bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                    <div class="bg-blue-600 h-full rounded-full" style="width: {{ $bp['completion_rate'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-right">
                                            <a href="{{ route('analytics.index', array_merge(request()->query(), ['brand_id' => $b->id, 'tab' => 'deliverables'])) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition">
                                                <span>Filter Brand</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-400">No brands found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 2. PROJECT-WISE REPORT --}}
            <div x-show="activeTab === 'projects'" x-cloak x-transition:enter="transition ease-out duration-200" class="flex flex-col gap-4">
                <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Project-Wise Breakdown</h3>
                            <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-0.5">Active and Completed deliverables grouped per project.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                    <th class="py-3 px-3">Project & Brand</th>
                                    <th class="py-3 px-3">Workflow</th>
                                    <th class="py-3 px-3 text-center">Active</th>
                                    <th class="py-3 px-3 text-center">Completed</th>
                                    <th class="py-3 px-3 text-center">Total</th>
                                    <th class="py-3 px-3">Rate</th>
                                    <th class="py-3 px-3">Deadline</th>
                                    <th class="py-3 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-slate-800/60 text-xs">
                                @forelse($project_performance as $pp)
                                    @php
                                        $proj = $pp['project'];
                                        $br = $pp['brand'];
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-slate-900/40 transition">
                                        <td class="py-3.5 px-3">
                                            <a href="{{ route('projects.show', $proj->id) }}" class="font-bold text-gray-900 dark:text-white hover:text-blue-500 transition line-clamp-1">
                                                {{ $proj->name }}
                                            </a>
                                            <span class="text-[10px] text-gray-400">{{ $br?->name ?? 'Unassigned Brand' }}</span>
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $pp['workflow_type'] === 'retainer' ? 'bg-blue-500/10 text-blue-500' : 'bg-indigo-500/10 text-indigo-500' }}">
                                                {{ ucfirst($pp['workflow_type']) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-blue-600 dark:text-blue-400">
                                            {{ $pp['active_deliverables'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ $pp['completed_deliverables'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-gray-900 dark:text-white">
                                            {{ $pp['total_deliverables'] }}
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900 dark:text-white min-w-[32px]">{{ $pp['completion_rate'] }}%</span>
                                                <div class="w-16 bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                                    <div class="bg-blue-600 h-full rounded-full" style="width: {{ $pp['completion_rate'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 font-mono text-[11px] text-gray-500">
                                            {{ $pp['deadline'] ? \Carbon\Carbon::parse($pp['deadline'])->format('M d, Y') : '-' }}
                                        </td>
                                        <td class="py-3.5 px-3 text-right">
                                            <a href="{{ route('projects.show', $proj->id) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition">
                                                <span>View</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-gray-400">No projects found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 3. DELIVERABLES LIST (MANAGERS) --}}
            <div x-show="activeTab === 'deliverables'" x-cloak x-transition:enter="transition ease-out duration-200" class="flex flex-col gap-4">
                @include('analytics.partials.deliverables_matrix_table', ['deliverables' => $deliverables])
            </div>

        @else
            {{-- ======================================================== --}}
            {{-- INDIVIDUAL TEAM MEMBER (PEOPLE) VIEW --}}
            {{-- ======================================================== --}}

            {{-- 1. MY DELIVERABLES LIST --}}
            <div x-show="activeTab === 'deliverables'" x-transition:enter="transition ease-out duration-200" class="flex flex-col gap-4">
                @include('analytics.partials.deliverables_matrix_table', ['deliverables' => $individual_report['deliverables']])
            </div>

            {{-- 2. MY PROJECTS BREAKDOWN --}}
            <div x-show="activeTab === 'projects'" x-cloak x-transition:enter="transition ease-out duration-200" class="flex flex-col gap-4">
                <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">My Projects Breakdown</h3>
                            <p class="text-xs text-gray-400 dark:text-slate-500 font-medium mt-0.5">Active and Completed deliverables on your assigned projects.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">
                                    <th class="py-3 px-3">Project & Brand</th>
                                    <th class="py-3 px-3 text-center">My Active</th>
                                    <th class="py-3 px-3 text-center">My Completed</th>
                                    <th class="py-3 px-3 text-center">Total Tasks</th>
                                    <th class="py-3 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-slate-800/60 text-xs">
                                @forelse($individual_report['projects_breakdown'] as $pInfo)
                                    @php
                                        $p = $pInfo['project'];
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-slate-900/40 transition">
                                        <td class="py-3.5 px-3">
                                            <a href="{{ route('projects.show', $p?->id) }}" class="font-bold text-gray-900 dark:text-white hover:text-blue-500 transition">
                                                {{ $p?->name ?? 'Project' }}
                                            </a>
                                            <span class="text-[10px] text-gray-400 block">{{ $pInfo['brand_name'] }}</span>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-blue-600 dark:text-blue-400">
                                            {{ $pInfo['active_tasks'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ $pInfo['completed_tasks'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-gray-900 dark:text-white">
                                            {{ $pInfo['total_tasks'] }}
                                        </td>
                                        <td class="py-3.5 px-3 text-right">
                                            <a href="{{ route('projects.show', $p?->id) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition">
                                                <span>View Project</span>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-400">No project assignments in this date range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Deliverable Read-Only Details Modal --}}
        @include('analytics.partials.deliverable_detail_modal')

    </div>

    {{-- Print Style --}}
    <style>
        @media print {
            nav, header, button, select, input, form, a[href*="export"], .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
            }
            .card-shadow {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
            }
        }
    </style>
</x-layout>
