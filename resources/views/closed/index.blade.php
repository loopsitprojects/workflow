<x-layout title="Closed Projects">
    <style>
        .modal-copy-content, .modal-copy-content *,
        .modal-notes-content, .modal-notes-content * {
            color: var(--color-text-primary) !important;
        }
    </style>
    <div x-data="{
        activeTab: '{{ $tab }}',
        artworkModalOpen: false,
        artworkUrl: '',
        artworkTitle: '',
        openArtwork(url, title) {
            this.artworkUrl = url;
            this.artworkTitle = title;
            this.artworkModalOpen = true;
        },
        detailsModalOpen: false,
        selectedDeliverable: null,
        copySuccess: false,
        openDetails(data) {
            this.selectedDeliverable = data;
            this.detailsModalOpen = true;
            this.copySuccess = false;
        },
        closeDetails() {
            this.detailsModalOpen = false;
            this.selectedDeliverable = null;
        },
        copyText(text) {
            if (!text) return;
            // Clean up any HTML if present
            const tmp = document.createElement('div');
            tmp.innerHTML = text;
            const plainText = tmp.textContent || tmp.innerText || text;
            navigator.clipboard.writeText(plainText.trim());
            this.copySuccess = true;
            setTimeout(() => this.copySuccess = false, 2000);
        }
    }" class="flex flex-col gap-6 pb-16">

        {{-- Top Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-1 flex items-center gap-1.5">
                    <span x-show="$store.theme.halloween" x-cloak class="select-none text-xs">🎃</span>
                    <span x-show="$store.theme.christmas" x-cloak class="select-none text-xs">🎄</span>
                    Archived & Completed Work
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Closed Records
                    </span>
                </p>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-[#F4F0F8] tracking-tight flex items-center gap-2">
                    <span>Closed Work Archive</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium mt-1">
                    Centralized archive of finalized creative deliverables and completed projects across all brand workspaces.
                </p>
            </div>

            {{-- Quick Stats Summary Badges --}}
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-white dark:bg-[#111827] rounded-xl border border-gray-200/80 dark:border-white/[0.08] card-shadow flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-gray-400 dark:text-slate-500">Closed Projects</div>
                        <div class="text-lg font-extrabold text-gray-900 dark:text-white leading-none mt-0.5">{{ $totalClosedProjects }}</div>
                    </div>
                </div>

                <div class="px-4 py-2 bg-white dark:bg-[#111827] rounded-xl border border-gray-200/80 dark:border-white/[0.08] card-shadow flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    <div>
                        <div class="text-[10px] uppercase font-bold text-gray-400 dark:text-slate-500">Closed Deliverables</div>
                        <div class="text-lg font-extrabold text-gray-900 dark:text-white leading-none mt-0.5">{{ $totalClosedDeliverables }}</div>
                    </div>
                </div>
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
                        <p class="font-bold text-amber-600 dark:text-amber-400">Notice: Closed Tab is currently DISABLED for standard users.</p>
                        <p class="text-amber-700/80 dark:text-amber-300/80 mt-0.5">
                            Only administrators can view this page. You can re-enable it anytime here or in <a href="{{ route('admin.settings', ['tab' => 'features']) }}" class="underline font-bold">Admin Settings</a>.
                        </p>
                    </div>
                </div>
                @if(auth()->user() && auth()->user()->isAdmin())
                    <form action="{{ route('admin.features.toggle-closed-tab') }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg text-xs font-extrabold bg-amber-500 text-white hover:bg-amber-600 shadow-sm transition-all">
                            Enable Closed Tab Now
                        </button>
                    </form>
                @endif
            </div>
        @endif

        {{-- Filter & Search Bar --}}
        <div class="bg-white dark:bg-[#111827] rounded-2xl p-4 sm:p-5 card-shadow border border-gray-200/80 dark:border-white/[0.08]">
            <form method="GET" action="{{ route('closed.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                <input type="hidden" name="tab" :value="activeTab">

                {{-- Left: Search Input --}}
                <div class="relative flex-1 min-w-[240px]">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Search by title, project name, or job #..."
                           class="w-full pl-10 pr-4 py-2 text-xs font-medium rounded-xl bg-gray-50 dark:bg-slate-900/80 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                {{-- Middle: Filter Dropdowns --}}
                <div class="flex items-center gap-3 flex-wrap">
                    {{-- Brand Filter --}}
                    <div class="min-w-[170px]">
                        <select name="brand_id"
                                onchange="this.form.submit()"
                                class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-gray-50 dark:bg-slate-900/80 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">All Brands ({{ $brandsList->count() }})</option>
                            @foreach($brandsList as $b)
                                <option value="{{ $b->id }}" {{ (string)$selectedBrandId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Workflow Filter --}}
                    <div class="min-w-[140px]">
                        <select name="workflow_type"
                                onchange="this.form.submit()"
                                class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-gray-50 dark:bg-slate-900/80 border border-gray-200 dark:border-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">All Workflows</option>
                            <option value="retainer" {{ $selectedWorkflow === 'retainer' ? 'selected' : '' }}>Retainer</option>
                            <option value="campaign" {{ $selectedWorkflow === 'campaign' ? 'selected' : '' }}>Campaign</option>
                            <option value="pitch" {{ $selectedWorkflow === 'pitch' ? 'selected' : '' }}>Pitch</option>
                        </select>
                    </div>

                    {{-- Apply & Reset Buttons --}}
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
                        Filter
                    </button>

                    @if($search || $selectedBrandId || $selectedWorkflow || $dateFrom || $dateTo)
                        <a href="{{ route('closed.index', ['tab' => $tab]) }}"
                           class="px-3 py-2 rounded-xl text-xs font-semibold text-gray-500 hover:text-red-500 dark:text-slate-400 dark:hover:text-red-400 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Sub-tabs Pill Switcher --}}
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-slate-800 pb-2">
            <button type="button"
                    @click="activeTab = 'projects'"
                    :class="activeTab === 'projects' 
                        ? 'bg-emerald-600 text-white font-bold shadow-sm' 
                        : 'bg-white dark:bg-[#111827] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-200/80 dark:border-white/[0.08]'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span>Closed Projects</span>
                <span :class="activeTab === 'projects' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold">
                    {{ $totalClosedProjects }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'deliverables'"
                    :class="activeTab === 'deliverables' 
                        ? 'bg-emerald-600 text-white font-bold shadow-sm' 
                        : 'bg-white dark:bg-[#111827] text-gray-600 dark:text-slate-400 hover:text-gray-900 dark:hover:text-white border border-gray-200/80 dark:border-white/[0.08]'"
                    class="px-4 py-2 rounded-xl text-xs flex items-center gap-2.5 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Closed Deliverables</span>
                <span :class="activeTab === 'deliverables' ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300'"
                      class="px-2 py-0.5 rounded-full text-[10px] font-extrabold">
                    {{ $totalClosedDeliverables }}
                </span>
            </button>
        </div>

        {{-- TAB 1: CLOSED PROJECTS --}}
        <div x-show="activeTab === 'projects'" x-transition:enter="transition ease-out duration-150">
            @if($closedProjects->isEmpty())
                <div class="bg-white dark:bg-[#111827] rounded-2xl py-12 px-6 text-center border border-dashed border-gray-200 dark:border-white/[0.08] card-shadow">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">No Closed Projects Found</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        @if($search || $selectedBrandId || $selectedWorkflow)
                            No closed projects match your filter criteria. Try adjusting your filters.
                        @else
                            When projects are completed or all their deliverables are approved and closed, they will appear in this archive.
                        @endif
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($closedProjects as $project)
                        @php
                            $wfType = strtolower($project->workflow_type ?: 'retainer');
                            $typeMeta = match($wfType) {
                                'retainer' => [
                                    'bg' => 'rgba(14, 165, 233, 0.12)',
                                    'color' => '#38bdf8',
                                    'border' => 'rgba(56, 189, 248, 0.3)',
                                    'name' => 'Retainer',
                                ],
                                'campaign' => [
                                    'bg' => 'rgba(168, 85, 247, 0.12)',
                                    'color' => '#c084fc',
                                    'border' => 'rgba(192, 132, 252, 0.3)',
                                    'name' => 'Campaign',
                                ],
                                'pitch' => [
                                    'bg' => 'rgba(245, 158, 11, 0.12)',
                                    'color' => '#fbbf24',
                                    'border' => 'rgba(251, 191, 36, 0.3)',
                                    'name' => 'Pitch',
                                ],
                                default => [
                                    'bg' => 'rgba(148, 163, 184, 0.12)',
                                    'color' => '#cbd5e1',
                                    'border' => 'rgba(203, 213, 225, 0.25)',
                                    'name' => ucfirst($wfType),
                                ],
                            };
                            $delivCount = $project->deliverables->count();
                            $closedDate = $project->updated_at ? $project->updated_at->format('M d, Y') : null;
                        @endphp
                        <div class="card-shadow group relative flex flex-col justify-between bg-white dark:bg-[#111827] rounded-xl border border-gray-200/80 dark:border-white/[0.08] hover:border-emerald-500/50 dark:hover:border-emerald-500/50 shadow-sm hover:shadow-lg hover:shadow-emerald-500/5 hover:-translate-y-0.5 transition-all duration-200 overflow-hidden p-5">
                            
                            {{-- Top Emerald Bar --}}
                            <div class="absolute top-0 left-0 right-0 h-[2.5px] bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400"></div>

                            {{-- Row 1: Brand & Workflow Badges --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    @if($project->brand)
                                        <div class="w-6 h-6 rounded-md bg-gray-50 dark:bg-slate-800 border border-gray-100 dark:border-slate-700 overflow-hidden flex items-center justify-center flex-shrink-0">
                                            <img src="{{ $project->brand->logo_url }}" alt="{{ $project->brand->name }}" class="w-4 h-4 object-contain">
                                        </div>
                                        <span class="text-[11px] font-bold text-gray-500 dark:text-slate-400 truncate max-w-[130px]">
                                            {{ $project->brand->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span style="font-size:10px; font-weight:800; color:{{ $typeMeta['color'] }}; background:{{ $typeMeta['bg'] }}; border:1px solid {{ $typeMeta['border'] }}; padding:2px 7px; border-radius:6px; text-transform:uppercase;">
                                        {{ $typeMeta['name'] }}
                                    </span>
                                    @if($project->job_number)
                                        <span style="font-size:10px; font-weight:800; color:#60a5fa; background:rgba(59, 130, 246, 0.12); border:1px solid rgba(96, 165, 250, 0.3); padding:2px 6px; border-radius:6px;">
                                            [{{ $project->job_number }}]
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Row 2: Project Title --}}
                            <div class="pt-3 pb-2">
                                <a href="{{ route('projects.show', $project) }}" class="text-[16px] font-bold text-gray-900 dark:text-white group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-colors line-clamp-1 tracking-tight">
                                    {{ $project->name }}
                                </a>
                            </div>

                            {{-- Row 3: Meta & Status Badge --}}
                            <div class="pt-3 border-t border-gray-100 dark:border-white/[0.06] flex items-center justify-between text-xs text-gray-400 dark:text-slate-500">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20 uppercase tracking-wide">
                                        ✓ Completed
                                    </span>
                                    <span>{{ $delivCount }} {{ Str::plural('deliverable', $delivCount) }}</span>
                                </div>
                                <span class="text-[11px] font-mono">{{ $closedDate }}</span>
                            </div>

                            {{-- Row 4: Action Buttons --}}
                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/[0.06] flex items-center justify-between gap-2">
                                <a href="{{ route('projects.show', $project) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-white/[0.05] border border-gray-200 dark:border-white/[0.08] hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-500 dark:hover:text-emerald-400 transition-all">
                                    <span>View Board</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>

                                @if(auth()->user()->isAdmin() || in_array(auth()->user()->role, ['Operations Manager', 'Brand Manager']))
                                    <form method="POST" action="{{ route('projects.toggle-close', $project) }}" onsubmit="return confirm('Reopen this project and move it back to In Progress?')">
                                        @csrf
                                        <button type="submit"
                                                class="text-[11px] font-bold text-gray-400 hover:text-amber-500 transition-colors">
                                            Reopen
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- TAB 2: CLOSED DELIVERABLES --}}
        <div x-show="activeTab === 'deliverables'" x-cloak x-transition:enter="transition ease-out duration-150">
            @if($closedDeliverables->isEmpty())
                <div class="bg-white dark:bg-[#111827] rounded-2xl py-12 px-6 text-center border border-dashed border-gray-200 dark:border-white/[0.08] card-shadow">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">No Closed Deliverables Found</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        @if($search || $selectedBrandId || $selectedWorkflow)
                            No closed deliverables match your filter criteria. Try adjusting your filters.
                        @else
                            Deliverables that reach the Closed stage or are marked Done will appear in this list.
                        @endif
                    </p>
                </div>
            @else
                @php
                    $resolveMediaUrl = function($path) {
                        if (!$path) return null;
                        $path = trim($path, "\"' \t\n\r\0\x0B");
                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                            return $path;
                        }
                        if (str_starts_with($path, 'storage/')) {
                            return asset($path);
                        }
                        return asset('storage/' . ltrim($path, '/'));
                    };

                    $isImg = function($url) {
                        if (!$url) return false;
                        $path = parse_url($url, PHP_URL_PATH) ?? $url;
                        return (bool) preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $path);
                    };

                    $isPdf = function($url) {
                        if (!$url) return false;
                        $path = parse_url($url, PHP_URL_PATH) ?? $url;
                        return (bool) preg_match('/\.pdf$/i', $path);
                    };
                @endphp

                <div class="bg-white dark:bg-[#111827] rounded-2xl card-shadow border border-gray-200/80 dark:border-white/[0.08] overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-slate-800 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 bg-gray-50/50 dark:bg-slate-900/50">
                                    <th class="py-3 px-4">Deliverable</th>
                                    <th class="py-3 px-3">Project & Brand</th>
                                    <th class="py-3 px-3">Type</th>
                                    <th class="py-3 px-3">Team</th>
                                    <th class="py-3 px-3">Artwork</th>
                                    <th class="py-3 px-3">Client Status</th>
                                    <th class="py-3 px-3">Closed Date</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs">
                                @foreach($closedDeliverables as $d)
                                    @php
                                        $proj = $d->project;
                                        $brand = $proj?->brand;

                                        $rawArtFiles = $d->getAllArtworkFiles();
                                        $artworkFiles = array_values(array_filter(array_map($resolveMediaUrl, $rawArtFiles)));
                                        $artworkLinks = $d->getFinalDesignsUrlsArray();

                                        $rawRefFiles = $d->getReferenceFilesArray();
                                        $refFiles = array_values(array_filter(array_map($resolveMediaUrl, $rawRefFiles)));
                                        $refLinks = $d->getReferenceUrlsArray();

                                        $firstArtFile = !empty($artworkFiles) ? $artworkFiles[0] : null;
                                        $firstArtLink = !empty($artworkLinks) ? $artworkLinks[0] : null;
                                        $firstIsImage = $firstArtFile && $isImg($firstArtFile);
                                        $firstIsPdf = $firstArtFile && $isPdf($firstArtFile);

                                        $deliverableData = [
                                            'id' => $d->id,
                                            'title' => $d->title,
                                            'post_type' => $d->post_type ?: 'Deliverable',
                                            'status' => $d->status ?: 'Done',
                                            'approval_stage' => $d->approval_stage ?: 'Closed',
                                            'client_status' => $d->client_status ?: 'Client Approved',
                                            'priority' => $d->priority ?: 'Medium',
                                            'deadline' => $d->deadline ? \Carbon\Carbon::parse($d->deadline)->format('M d, Y') : null,
                                            'closed_date' => $d->updated_at ? $d->updated_at->format('M d, Y') : null,
                                            'created_date' => $d->created_at ? $d->created_at->format('M d, Y') : null,
                                            'project_id' => $proj?->id,
                                            'project_name' => $proj?->name ?? 'Unassigned Project',
                                            'project_url' => $proj ? route('projects.show', $proj) : '#',
                                            'job_number' => $proj?->job_number,
                                            'workflow_type' => $proj?->workflow_type ?? 'campaign',
                                            'brand_name' => $brand?->name ?? 'Unassigned Brand',
                                            'writer_name' => $d->writer?->name,
                                            'designer_name' => $d->designer?->name,
                                            'approver_name' => $d->approver?->name,
                                            'brand_manager_name' => $d->brandManager?->name,
                                            'coordinator_name' => $d->coordinator?->name,
                                            'copy' => $d->post_copy ?: $d->description,
                                            'caption' => $d->caption,
                                            'concept' => $d->concept,
                                            'notes' => $d->notes,
                                            'reference' => $d->reference,
                                            'artwork_files' => $artworkFiles,
                                            'artwork_links' => $artworkLinks,
                                            'ref_files' => $refFiles,
                                            'ref_links' => $refLinks,
                                            'full_url' => route('deliverables.show', $d),
                                            'batch_url' => $d->parent_deliverable_id ? route('deliverables.showBatch', $d->parent_deliverable_id) : null,
                                            'parent_title' => $d->parent?->title,
                                        ];
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-slate-900/40 transition">
                                        {{-- Deliverable Title --}}
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                                <div>
                                                    <button type="button"
                                                            @click="openDetails({{ \Illuminate\Support\Js::from($deliverableData) }})"
                                                            class="font-bold text-gray-900 dark:text-white hover:text-emerald-500 dark:hover:text-emerald-400 transition text-left cursor-pointer line-clamp-1">
                                                        {{ $d->title }}
                                                    </button>
                                                    @if($d->parent)
                                                        <span class="text-[10px] text-gray-400 block">Batch: {{ $d->parent->title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Project & Brand --}}
                                        <td class="py-3.5 px-3">
                                            @if($proj)
                                                <a href="{{ route('projects.show', $proj) }}" class="font-semibold text-gray-800 dark:text-slate-200 hover:text-blue-500 transition block truncate max-w-[180px]">
                                                    {{ $proj->name }}
                                                </a>
                                                <span class="text-[10px] text-gray-400 block">{{ $brand?->name ?? 'Unassigned' }}</span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>

                                        {{-- Post Type --}}
                                        <td class="py-3.5 px-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300 border border-gray-200/60 dark:border-slate-700">
                                                {{ $d->post_type ?: 'Deliverable' }}
                                            </span>
                                        </td>

                                        {{-- Team --}}
                                        <td class="py-3.5 px-3">
                                            <div class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-slate-400">
                                                @if($d->writer)
                                                    <span title="Writer: {{ $d->writer->name }}" class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold">
                                                        W: {{ explode(' ', $d->writer->name)[0] }}
                                                    </span>
                                                @endif
                                                @if($d->designer)
                                                    <span title="Designer: {{ $d->designer->name }}" class="px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 font-semibold">
                                                        D: {{ explode(' ', $d->designer->name)[0] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Artwork Thumbnail / Link --}}
                                        <td class="py-3.5 px-3">
                                            @if($firstIsImage)
                                                <button type="button"
                                                        @click="openArtwork('{{ $firstArtFile }}', '{{ addslashes($d->title) }}')"
                                                        class="inline-flex items-center gap-1.5 group cursor-pointer"
                                                        title="Click to zoom artwork">
                                                    <img src="{{ $firstArtFile }}" alt="Artwork" class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-slate-700 group-hover:ring-2 group-hover:ring-emerald-500 transition">
                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 group-hover:underline">Preview</span>
                                                </button>
                                            @elseif($firstIsPdf)
                                                <a href="{{ $firstArtFile }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 hover:bg-red-500/20 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                    <span>PDF</span>
                                                </a>
                                            @elseif($firstArtFile)
                                                <a href="{{ $firstArtFile }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-500 hover:underline">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    <span>Asset</span>
                                                </a>
                                            @elseif($firstArtLink)
                                                <a href="{{ $firstArtLink }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    <span>Link</span>
                                                </a>
                                            @else
                                                <span class="text-[11px] text-gray-400 italic">None</span>
                                            @endif
                                        </td>

                                        {{-- Client Status --}}
                                        <td class="py-3.5 px-3">
                                            @php
                                                $cStatus = $d->client_status ?: 'Client Approved';
                                                $cClass = match($cStatus) {
                                                    'Client Approved' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                                    'Client Revisions' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                                    'Sent to Client' => 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                                    default => 'bg-gray-500/10 text-gray-500 border-gray-500/20'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $cClass }}">
                                                {{ $cStatus }}
                                            </span>
                                        </td>

                                        {{-- Closed Date --}}
                                        <td class="py-3.5 px-3 font-mono text-[11px] text-gray-500 dark:text-slate-400 whitespace-nowrap">
                                            {{ $d->updated_at ? $d->updated_at->format('M d, Y') : '-' }}
                                        </td>

                                        {{-- Actions: Quick Details Popup --}}
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button"
                                                        @click="openDetails({{ \Illuminate\Support\Js::from($deliverableData) }})"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-gray-700 dark:text-slate-200 bg-gray-100 dark:bg-slate-800 hover:bg-emerald-500 hover:text-white dark:hover:bg-emerald-600 dark:hover:text-white transition shadow-sm cursor-pointer"
                                                        title="Quick View Details">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    <span>View</span>
                                                </button>
                                                @if($d->parent_deliverable_id)
                                                    <a href="{{ route('deliverables.showBatch', $d->parent_deliverable_id) }}"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 transition"
                                                       title="View Batch">
                                                        <span>Batch</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if($closedDeliverables->hasPages())
                        <div class="p-4 border-t border-gray-200 dark:border-slate-800">
                            {{ $closedDeliverables->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Deliverable Quick View Details Modal --}}
        <div x-show="detailsModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/80 backdrop-blur-md overflow-y-auto"
             @keydown.escape.window="closeDetails()">
            <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl flex flex-col overflow-hidden my-auto"
                 style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary);"
                 @click.away="closeDetails()">

                {{-- Modal Header --}}
                <div class="p-5 sm:p-6 flex-shrink-0"
                     style="background: var(--color-bg-secondary); border-bottom: 1px solid var(--color-border-primary);">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-2 flex-1 min-w-0">
                            {{-- Badges row --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Closed (100%)
                                </span>

                                <template x-if="selectedDeliverable?.workflow_type">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-wider bg-purple-500/15 text-purple-400 border border-purple-500/30"
                                          x-text="selectedDeliverable.workflow_type">
                                    </span>
                                </template>

                                <template x-if="selectedDeliverable?.post_type">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold"
                                          style="background: var(--color-bg-primary); color: var(--color-text-secondary); border: 1px solid var(--color-border-primary);"
                                          x-text="selectedDeliverable.post_type">
                                    </span>
                                </template>

                                <template x-if="selectedDeliverable?.job_number">
                                    <span class="font-mono text-[10px] font-extrabold px-2.5 py-1 rounded-md bg-blue-500/15 text-blue-400 border border-blue-500/30"
                                          x-text="'[' + selectedDeliverable.job_number + ']'">
                                    </span>
                                </template>
                            </div>

                            {{-- Title --}}
                            <h2 class="text-xl sm:text-2xl font-black leading-tight break-words"
                                style="color: var(--color-text-primary);"
                                x-text="selectedDeliverable?.title">
                            </h2>

                            {{-- Project & Brand info --}}
                            <div class="flex items-center gap-2 text-xs flex-wrap pt-0.5"
                                 style="color: var(--color-text-secondary);">
                                <span class="font-bold" style="color: var(--color-text-primary);" x-text="selectedDeliverable?.brand_name"></span>
                                <span>&bull;</span>
                                <a :href="selectedDeliverable?.project_url" class="font-medium hover:text-blue-400 transition underline underline-offset-2" x-text="selectedDeliverable?.project_name"></a>
                                <template x-if="selectedDeliverable?.parent_title">
                                    <span class="text-[11px]" style="color: var(--color-text-secondary);" x-text="'(Batch: ' + selectedDeliverable.parent_title + ')'"></span>
                                </template>
                            </div>
                        </div>

                        {{-- Close Button --}}
                        <button type="button"
                                @click="closeDetails()"
                                class="p-2 rounded-xl transition flex-shrink-0 cursor-pointer hover:opacity-80"
                                style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-secondary);"
                                title="Close details (Esc)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Scrollable Modal Body --}}
                <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1 max-h-[calc(85vh-140px)]"
                     style="background: var(--color-bg-primary); color: var(--color-text-primary);">

                    {{-- 1. Quick Info Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-2xl" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                            <span class="text-[10px] font-bold uppercase tracking-wider block" style="color: var(--color-text-secondary);">Client Status</span>
                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20"
                                  x-text="selectedDeliverable?.client_status || 'Client Approved'">
                            </span>
                        </div>

                        <div class="p-3 rounded-2xl" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                            <span class="text-[10px] font-bold uppercase tracking-wider block" style="color: var(--color-text-secondary);">Closed Date</span>
                            <span class="font-bold text-xs mt-1 block" style="color: var(--color-text-primary);"
                                  x-text="selectedDeliverable?.closed_date || '-'">
                            </span>
                        </div>

                        <div class="p-3 rounded-2xl" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                            <span class="text-[10px] font-bold uppercase tracking-wider block" style="color: var(--color-text-secondary);">Target Deadline</span>
                            <span class="font-bold text-xs mt-1 block" style="color: var(--color-text-primary);"
                                  x-text="selectedDeliverable?.deadline || '-'">
                            </span>
                        </div>

                        <div class="p-3 rounded-2xl" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                            <span class="text-[10px] font-bold uppercase tracking-wider block" style="color: var(--color-text-secondary);">Priority</span>
                            <span class="font-bold text-xs mt-1 block" style="color: var(--color-text-primary);"
                                  x-text="selectedDeliverable?.priority || 'Normal'">
                            </span>
                        </div>
                    </div>

                    {{-- 2. Team Assigned --}}
                    <div class="p-4 rounded-2xl" style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider block mb-2.5" style="color: var(--color-text-secondary);">Assigned Team</span>
                        <div class="flex items-center gap-3 flex-wrap">
                            {{-- Writer --}}
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl" style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary);">
                                <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-500 font-extrabold text-[10px] flex items-center justify-center">W</span>
                                <div>
                                    <span class="text-[9px] font-bold uppercase block leading-none" style="color: var(--color-text-secondary);">Writer</span>
                                    <span class="font-bold text-xs" style="color: var(--color-text-primary);" x-text="selectedDeliverable?.writer_name || 'Unassigned'"></span>
                                </div>
                            </div>

                            {{-- Designer --}}
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl" style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary);">
                                <span class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 font-extrabold text-[10px] flex items-center justify-center">D</span>
                                <div>
                                    <span class="text-[9px] font-bold uppercase block leading-none" style="color: var(--color-text-secondary);">Designer</span>
                                    <span class="font-bold text-xs" style="color: var(--color-text-primary);" x-text="selectedDeliverable?.designer_name || 'Unassigned'"></span>
                                </div>
                            </div>

                            {{-- Approver / Brand Manager --}}
                            <template x-if="selectedDeliverable?.brand_manager_name || selectedDeliverable?.approver_name">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl" style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary);">
                                    <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 font-extrabold text-[10px] flex items-center justify-center">BM</span>
                                    <div>
                                        <span class="text-[9px] font-bold uppercase block leading-none" style="color: var(--color-text-secondary);">Manager / Approver</span>
                                        <span class="font-bold text-xs" style="color: var(--color-text-primary);" x-text="selectedDeliverable?.brand_manager_name || selectedDeliverable?.approver_name"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- 3. Creative Copy & Content --}}
                    <template x-if="selectedDeliverable?.copy || selectedDeliverable?.caption || selectedDeliverable?.concept">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider flex items-center gap-1.5" style="color: var(--color-text-secondary);">
                                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Creative Copy & Content
                                </span>
                                <button type="button"
                                        @click="copyText(selectedDeliverable?.copy || selectedDeliverable?.caption || selectedDeliverable?.concept)"
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-500 hover:underline cursor-pointer">
                                    <template x-if="!copySuccess">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            Copy
                                        </span>
                                    </template>
                                    <template x-if="copySuccess">
                                        <span class="flex items-center gap-1 text-emerald-400 font-extrabold">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Copied!
                                        </span>
                                    </template>
                                </button>
                            </div>

                            <div class="modal-copy-content p-4 rounded-2xl leading-relaxed font-sans max-h-60 overflow-y-auto"
                                 style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary);">
                                <template x-if="selectedDeliverable?.concept">
                                    <div class="mb-3 pb-3" style="border-bottom: 1px solid var(--color-border-primary);">
                                        <span class="text-[10px] font-extrabold uppercase block mb-1" style="color: var(--color-text-secondary);">Concept</span>
                                        <div class="whitespace-pre-wrap text-xs" x-html="selectedDeliverable.concept"></div>
                                    </div>
                                </template>

                                <template x-if="selectedDeliverable?.copy">
                                    <div>
                                        <div class="whitespace-pre-wrap text-xs sm:text-sm" x-html="selectedDeliverable.copy"></div>
                                    </div>
                                </template>

                                <template x-if="selectedDeliverable?.caption && !selectedDeliverable?.copy">
                                    <div>
                                        <div class="whitespace-pre-wrap text-xs sm:text-sm" x-html="selectedDeliverable.caption"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- 4. Notes (if any) --}}
                    <template x-if="selectedDeliverable?.notes">
                        <div class="space-y-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider flex items-center gap-1.5" style="color: var(--color-text-secondary);">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                Deliverable Notes
                            </span>
                            <div class="modal-notes-content p-4 rounded-2xl text-xs leading-relaxed max-h-48 overflow-y-auto"
                                 style="background: var(--color-bg-secondary); border: 1px solid rgba(245, 158, 11, 0.35); color: var(--color-text-primary);"
                                 x-html="selectedDeliverable.notes">
                            </div>
                        </div>
                    </template>

                    {{-- 5. Final Artwork & Delivered Assets --}}
                    <template x-if="(selectedDeliverable?.artwork_files && selectedDeliverable.artwork_files.length > 0) || (selectedDeliverable?.artwork_links && selectedDeliverable.artwork_links.length > 0)">
                        <div class="space-y-2.5">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider flex items-center gap-1.5" style="color: var(--color-text-secondary);">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Delivered Artwork & Assets
                            </span>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {{-- Files --}}
                                <template x-for="(file, idx) in (selectedDeliverable?.artwork_files || [])" :key="'art-file-' + idx">
                                    <div class="p-3 rounded-2xl flex items-center justify-between gap-3"
                                         style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <template x-if="file.match(/\.(jpg|jpeg|png|webp|gif|svg)$/i)">
                                                <img :src="file" alt="Artwork" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 cursor-pointer"
                                                     style="border: 1px solid var(--color-border-primary);"
                                                     @click="openArtwork(file, selectedDeliverable.title)">
                                            </template>
                                            <template x-if="!file.match(/\.(jpg|jpeg|png|webp|gif|svg)$/i)">
                                                <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    PDF
                                                </div>
                                            </template>
                                            <div class="truncate">
                                                <span class="font-bold text-xs truncate block" style="color: var(--color-text-primary);" x-text="file.split('/').pop()"></span>
                                                <span class="text-[10px]" style="color: var(--color-text-secondary);">Artwork Asset</span>
                                            </div>
                                        </div>

                                        <a :href="file" target="_blank" class="px-2.5 py-1 rounded-lg text-xs font-bold text-blue-500 bg-blue-500/10 hover:bg-blue-500/20 transition flex-shrink-0 flex items-center gap-1">
                                            <span>Open</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                </template>

                                {{-- Links (Figma / Drive) --}}
                                <template x-for="(link, idx) in (selectedDeliverable?.artwork_links || [])" :key="'art-link-' + idx">
                                    <div class="p-3 rounded-2xl flex items-center justify-between gap-3"
                                         style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                            </div>
                                            <div class="truncate">
                                                <span class="font-bold text-xs truncate block" style="color: var(--color-text-primary);" x-text="link"></span>
                                                <span class="text-[10px]" style="color: var(--color-text-secondary);">Cloud / External Link</span>
                                            </div>
                                        </div>

                                        <a :href="link" target="_blank" class="px-2.5 py-1 rounded-lg text-xs font-bold text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 transition flex-shrink-0 flex items-center gap-1">
                                            <span>Visit</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- 6. References & Materials --}}
                    <template x-if="(selectedDeliverable?.ref_files && selectedDeliverable.ref_files.length > 0) || (selectedDeliverable?.ref_links && selectedDeliverable.ref_links.length > 0) || selectedDeliverable?.reference">
                        <div class="space-y-2">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider flex items-center gap-1.5" style="color: var(--color-text-secondary);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--color-text-secondary);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Reference Materials
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <template x-for="(rf, idx) in (selectedDeliverable?.ref_files || [])" :key="'rf-' + idx">
                                    <a :href="rf" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold hover:text-blue-500 transition"
                                       style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--color-text-secondary);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span x-text="rf.split('/').pop()"></span>
                                    </a>
                                </template>
                                <template x-for="(rl, idx) in (selectedDeliverable?.ref_links || [])" :key="'rl-' + idx">
                                    <a :href="rl" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-blue-500 hover:underline"
                                       style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span x-text="rl"></span>
                                    </a>
                                </template>
                                <template x-if="selectedDeliverable?.reference && (!selectedDeliverable?.ref_links || selectedDeliverable.ref_links.length === 0)">
                                    <a :href="selectedDeliverable.reference" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-blue-500 hover:underline"
                                       style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        <span x-text="selectedDeliverable.reference"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 sm:p-5 flex items-center justify-between gap-3 flex-shrink-0"
                     style="background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-primary);">
                    <div>
                        <a :href="selectedDeliverable?.full_url"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold transition hover:opacity-80"
                           style="color: var(--color-text-secondary);">
                            <span>Open Full Page</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                                @click="closeDetails()"
                                class="px-5 py-2 rounded-xl text-xs font-extrabold cursor-pointer transition hover:opacity-90"
                                style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary);">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Artwork Preview Lightbox Modal (z-[70] so it sits above details modal) --}}
        <div x-show="artworkModalOpen"
             x-cloak
             class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md"
             @keydown.escape.window="artworkModalOpen = false">
            <div class="bg-white dark:bg-[#0F172A] rounded-2xl max-w-3xl w-full p-4 card-shadow border border-gray-200 dark:border-slate-800 flex flex-col gap-3"
                 @click.away="artworkModalOpen = false">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white" x-text="artworkTitle"></h3>
                    <button type="button" @click="artworkModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-lg font-bold cursor-pointer">
                        &times;
                    </button>
                </div>
                <div class="rounded-xl overflow-hidden bg-black/10 dark:bg-black/40 flex items-center justify-center max-h-[75vh]">
                    <img :src="artworkUrl" alt="Artwork preview" class="w-full h-auto object-contain max-h-[75vh]">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-slate-800">
                    <a :href="artworkUrl" target="_blank" download class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                        Open Full Asset
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-layout>
