<x-layout title="{{ $brand->name }}">
<div class="flex flex-col gap-6 pb-12">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-[11px] font-semibold text-gray-400 dark:text-slate-500">
        <a href="{{ route('brands.index') }}" class="hover:text-gray-600 dark:hover:text-slate-300 transition-colors">Brands</a>
        <span class="opacity-40">/</span>
        <span class="text-gray-700 dark:text-slate-300">{{ $brand->name }}</span>
    </nav>

    {{-- Brand Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-white/[0.05] border border-gray-100 dark:border-white/[0.08] overflow-hidden flex items-center justify-center flex-shrink-0">
                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                     class="w-10 h-10 object-contain"
                     onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($brand->name) }}&background=E2E8F0&color=475569&bold=true';">
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $brand->name }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    @if($brand->location)
                        <span class="flex items-center gap-1 text-[12px] text-gray-400 dark:text-slate-500">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            {{ $brand->location }}
                        </span>
                        <span class="opacity-30 text-gray-400">·</span>
                    @endif
                    <span class="text-[12px] text-gray-400 dark:text-slate-500">{{ $brand->total_members }} {{ Str::plural('member', $brand->total_members) }}</span>
                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold rounded-md uppercase tracking-wide border border-emerald-100 dark:border-emerald-500/20">Active</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(auth()->user()->isAdmin() || auth()->user()->role === 'Brand Manager')
            <a href="{{ route('brands.edit', $brand) }}"
               class="px-4 py-2 bg-white dark:bg-white/[0.05] border border-gray-200 dark:border-white/[0.08] rounded-lg text-[12px] font-600 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/[0.08] transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit Brand
            </a>
            @endif
            @if(auth()->user()->isAdmin() || in_array(auth()->user()->role, ['Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator']))
            <a href="{{ route('projects.create', ['brand_id' => $brand->id]) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[12px] font-semibold transition-colors flex items-center gap-1.5 shadow-sm shadow-blue-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Project
            </a>
            @endif
        </div>
    </div>

    {{-- Projects by type --}}
    @php
        $typeOrder = ['retainer', 'campaign', 'pitch'];
        $groupedProjects = $brand->projects->groupBy('workflow_type')->sortBy(function($val, $key) use ($typeOrder) {
            return array_search($key, $typeOrder) ?? 99;
        });
        $typeLabels = ['retainer' => 'Retainer Jobs', 'campaign' => 'Campaigns', 'pitch' => 'Pitches'];
    @endphp

    @forelse($groupedProjects as $type => $projects)
    <div class="flex flex-col gap-3">
        {{-- Section header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <h2 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">{{ $typeLabels[$type] ?? 'Projects' }}</h2>
                <span class="px-2 py-0.5 bg-gray-100 dark:bg-white/[0.06] text-gray-600 dark:text-slate-300 text-[11px] font-extrabold rounded-md border border-gray-200/60 dark:border-white/[0.08]">{{ $projects->count() }}</span>
            </div>
            @if($type === 'retainer')
                <a href="{{ route('brands.retainer-board', $brand) }}"
                   class="text-[12px] font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    View Retainer Board
                </a>
            @endif
        </div>

        {{-- Modern Project Tiles Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($projects as $project)
            @php
                $dDate = $project->deadline ? \Carbon\Carbon::parse($project->deadline) : null;
                $daysLeft = $dDate ? now()->startOfDay()->diffInDays($dDate->startOfDay(), false) : null;
                $wfType = strtolower($project->workflow_type ?: $type);
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
            @endphp
            <a href="{{ route('projects.show', $project) }}"
               class="group relative flex flex-col justify-between bg-white dark:bg-[#111827] rounded-xl border border-gray-200/80 dark:border-white/[0.08] hover:border-blue-500/50 dark:hover:border-blue-500/50 shadow-sm hover:shadow-lg hover:shadow-blue-500/5 hover:-translate-y-0.5 transition-all duration-200 overflow-hidden p-5 min-h-[112px] cursor-pointer block">
                
                {{-- Top subtle accent gradient bar --}}
                <div class="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r {{ 
                    $wfType === 'retainer' ? 'from-blue-600 via-indigo-500 to-cyan-400' : 
                    ($wfType === 'campaign' ? 'from-violet-600 via-purple-500 to-fuchsia-400' : 'from-amber-500 via-orange-500 to-rose-400') 
                }} opacity-80 group-hover:opacity-100 transition-opacity"></div>

                {{-- Row 1: Badges & Due Date --}}
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span style="font-size:10px; font-weight:800; color:{{ $typeMeta['color'] }}; background:{{ $typeMeta['bg'] }}; border:1px solid {{ $typeMeta['border'] }}; padding:2.5px 8px; border-radius:6px; letter-spacing:0.04em; text-transform:uppercase;">
                            {{ $typeMeta['name'] }}
                        </span>
                        @if($project->job_number)
                            <span style="font-size:10px; font-weight:800; color:#60a5fa; background:rgba(59, 130, 246, 0.12); border:1px solid rgba(96, 165, 250, 0.3); padding:2.5px 7px; border-radius:6px; letter-spacing:0.03em;">
                                [{{ $project->job_number }}]
                            </span>
                        @endif
                    </div>

                    @if($dDate)
                        @php
                            $dueMeta = match(true) {
                                $daysLeft < 0 => [
                                    'bg' => 'rgba(239, 68, 68, 0.12)',
                                    'color' => '#f87171',
                                    'border' => 'rgba(248, 113, 113, 0.3)',
                                    'label' => 'Overdue (' . $dDate->format('M j') . ')',
                                ],
                                $daysLeft === 0 => [
                                    'bg' => 'rgba(249, 115, 22, 0.12)',
                                    'color' => '#fb923c',
                                    'border' => 'rgba(251, 146, 60, 0.3)',
                                    'label' => 'Due Today',
                                ],
                                $daysLeft <= 3 => [
                                    'bg' => 'rgba(245, 158, 11, 0.12)',
                                    'color' => '#fbbf24',
                                    'border' => 'rgba(251, 191, 36, 0.3)',
                                    'label' => 'Due in ' . $daysLeft . 'd',
                                ],
                                default => [
                                    'bg' => 'rgba(148, 163, 184, 0.08)',
                                    'color' => '#94a3b8',
                                    'border' => 'rgba(148, 163, 184, 0.2)',
                                    'label' => 'Due ' . $dDate->format('M j, Y'),
                                ],
                            };
                        @endphp
                        <span style="font-size:10px; font-weight:700; color:{{ $dueMeta['color'] }}; background:{{ $dueMeta['bg'] }}; border:1px solid {{ $dueMeta['border'] }}; padding:2px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $dueMeta['label'] }}</span>
                        </span>
                    @endif
                </div>

                {{-- Row 2: Project Title & View Board Action --}}
                <div class="flex items-center justify-between gap-3 pt-3">
                    <span class="text-[16px] font-bold text-gray-900 dark:text-white group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors truncate tracking-tight">
                        {{ $project->name }}
                    </span>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 dark:text-slate-300 bg-gray-100 dark:bg-white/[0.05] border border-gray-200 dark:border-white/[0.08] group-hover:border-blue-500/50 group-hover:bg-blue-500/10 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-all duration-200 flex-shrink-0">
                        View Board
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </span>
                </div>

            </a>
            @endforeach
        </div>
    </div>
    @empty
    <div class="py-16 text-center">
        <p class="text-sm text-gray-400 dark:text-slate-500">No projects yet for this brand.</p>
        <a href="{{ route('projects.create', ['brand_id' => $brand->id]) }}" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-blue-500 hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create the first project
        </a>
    </div>
    @endforelse

</div>
</x-layout>
