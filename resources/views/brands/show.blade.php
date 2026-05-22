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
            <a href="{{ route('brands.edit', $brand) }}"
               class="px-4 py-2 bg-white dark:bg-white/[0.05] border border-gray-200 dark:border-white/[0.08] rounded-lg text-[12px] font-600 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/[0.08] transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit
            </a>
            <a href="{{ route('projects.create', ['brand_id' => $brand->id]) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[12px] font-semibold transition-colors flex items-center gap-1.5 shadow-sm shadow-blue-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Project
            </a>
        </div>
    </div>

    {{-- Pending Deliverables --}}
    @if($pendingDeliverables->count() > 0)
    <div>
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Pending Deliverables</h2>
            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/[0.06] text-gray-500 dark:text-slate-400 text-[10px] font-bold rounded">{{ $pendingDeliverables->count() }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach($pendingDeliverables as $task)
            @php
                $daysLeft = $task->deadline ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($task->deadline)->startOfDay(), false) : null;
            @endphp
            <a href="{{ route('projects.show', $task->project_id) }}"
               class="flex items-center justify-between px-4 py-3 bg-white dark:bg-[#111827] rounded-xl border border-gray-100 dark:border-white/[0.06] hover:border-blue-200 dark:hover:border-blue-500/30 transition-colors group">
                <div class="min-w-0">
                    <p class="text-[13px] font-semibold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $task->title }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[11px] font-medium text-blue-500 dark:text-blue-400 truncate max-w-[140px]">{{ $task->project->name }}</span>
                        <span class="opacity-30 text-[10px]">·</span>
                        <span class="text-[11px] text-gray-400 dark:text-slate-500 uppercase tracking-wide">{{ $task->approval_stage ?: $task->status }}</span>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-4 text-right">
                    @if($task->deadline)
                        @if($daysLeft < 0)
                            <p class="text-[10px] font-bold text-red-500 uppercase tracking-wide">Overdue</p>
                            <p class="text-[12px] font-bold text-red-500">{{ \Carbon\Carbon::parse($task->deadline)->format('M j') }}</p>
                        @elseif($daysLeft === 0)
                            <p class="text-[10px] font-bold text-orange-500 uppercase tracking-wide">Today</p>
                            <p class="text-[12px] font-bold text-orange-500">Due</p>
                        @elseif($daysLeft <= 3)
                            <p class="text-[10px] font-bold text-amber-500 uppercase tracking-wide">{{ $daysLeft }}d left</p>
                            <p class="text-[12px] font-semibold text-gray-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($task->deadline)->format('M j') }}</p>
                        @else
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 uppercase tracking-wide">Due</p>
                            <p class="text-[12px] font-semibold text-gray-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($task->deadline)->format('M j') }}</p>
                        @endif
                    @else
                        <p class="text-[11px] text-gray-300 dark:text-slate-600">No date</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Projects by type --}}
    @php
        $typeOrder = ['retainer', 'campaign', 'pitch'];
        $groupedProjects = $brand->projects->groupBy('workflow_type')->sortBy(function($val, $key) use ($typeOrder) {
            return array_search($key, $typeOrder) ?? 99;
        });
        $typeLabels = ['retainer' => 'Retainer Jobs', 'campaign' => 'Campaigns', 'pitch' => 'Pitches'];
    @endphp

    @forelse($groupedProjects as $type => $projects)
    <div>
        {{-- Section header --}}
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">{{ $typeLabels[$type] ?? 'Projects' }}</h2>
            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-white/[0.06] text-gray-500 dark:text-slate-400 text-[10px] font-bold rounded">{{ $projects->count() }}</span>
            @if($type === 'retainer')
                <a href="{{ route('brands.retainer-board', $brand) }}"
                   class="ml-auto text-[11px] font-semibold text-blue-500 dark:text-blue-400 hover:underline flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    View Retainer Board
                </a>
            @endif
        </div>

        {{-- Project rows --}}
        <div class="flex flex-col gap-2">
            @foreach($projects as $project)
            <div class="flex items-center gap-4 px-4 py-3.5 bg-white dark:bg-[#111827] rounded-xl border border-gray-100 dark:border-white/[0.06] hover:border-gray-200 dark:hover:border-white/[0.12] transition-colors group">

                {{-- Name + description --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2 flex-wrap">
                        @if($project->job_number)
                            <span class="text-[11px] font-bold text-blue-500 dark:text-blue-400 flex-shrink-0">[{{ $project->job_number }}]</span>
                        @endif
                        <a href="{{ route('projects.show', $project) }}"
                           class="text-[14px] font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate">
                            {{ $project->name }}
                        </a>
                    </div>
                    @if($project->description)
                        <p class="text-[12px] text-gray-400 dark:text-slate-500 truncate mt-0.5">{{ $project->description }}</p>
                    @endif
                </div>

                {{-- Member initials --}}
                <div class="flex items-center -space-x-1.5 flex-shrink-0">
                    @forelse($project->members->take(4) as $i => $member)
                        <div class="w-7 h-7 rounded-full border-2 border-white dark:border-[#111827] bg-gray-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-slate-300 flex-shrink-0 uppercase"
                             title="{{ $member->name }} · {{ $member->role }}"
                             style="z-index: {{ 10 - $i }}">
                            {{ substr($member->name, 0, 1) }}
                        </div>
                    @empty
                        <span class="text-[11px] text-gray-300 dark:text-slate-600 italic">Unassigned</span>
                    @endforelse
                    @if($project->members->count() > 4)
                        <div class="w-7 h-7 rounded-full border-2 border-white dark:border-[#111827] bg-gray-100 dark:bg-slate-800 flex items-center justify-center text-[9px] font-bold text-gray-500 dark:text-slate-400 flex-shrink-0">
                            +{{ $project->members->count() - 4 }}
                        </div>
                    @endif
                </div>

                {{-- Status --}}
                @php
                    $statusColors = [
                        'To commence' => 'bg-gray-100 dark:bg-white/[0.05] text-gray-500 dark:text-slate-400 border-gray-200 dark:border-white/[0.08]',
                        'In Progress'  => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-500/20',
                        'On Hold'      => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-500/20',
                        'Completed'    => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-500/20',
                        'Not Started'  => 'bg-gray-100 dark:bg-white/[0.05] text-gray-500 dark:text-slate-400 border-gray-200 dark:border-white/[0.08]',
                    ];
                    $sc = $statusColors[$project->status] ?? $statusColors['Not Started'];
                @endphp
                <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold border flex-shrink-0 {{ $sc }} uppercase tracking-wide whitespace-nowrap">
                    {{ $project->status }}
                </span>

                {{-- Progress --}}
                <div class="w-20 flex-shrink-0 hidden md:block">
                    <div class="w-full bg-gray-100 dark:bg-white/[0.06] h-1 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width:{{ $project->progress ?? 0 }}%; background: {{ ($project->progress ?? 0) >= 100 ? '#10b981' : '#3b82f6' }}"></div>
                    </div>
                    <p class="text-[9px] text-gray-400 dark:text-slate-600 mt-1 text-right">{{ $project->progress ?? 0 }}%</p>
                </div>

                {{-- View Board --}}
                <a href="{{ route('projects.show', $project) }}"
                   class="flex-shrink-0 px-3 py-1.5 text-[11px] font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 rounded-lg transition-colors whitespace-nowrap border border-blue-100 dark:border-blue-500/20">
                    View Board
                </a>
            </div>
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
