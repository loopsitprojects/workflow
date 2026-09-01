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
                Edit
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
    <div>
        {{-- Section header --}}
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">{{ $typeLabels[$type] ?? 'Projects' }}</h2>
            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 text-[10px] font-bold rounded">{{ $projects->count() }}</span>
            @if($type === 'retainer')
                <a href="{{ route('brands.retainer-board', $brand) }}"
                   class="ml-auto text-[11px] font-semibold text-blue-500 dark:text-blue-400 hover:underline flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    View Retainer Board
                </a>
            @endif
        </div>

        {{-- Project tiles --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($projects as $project)
            <div class="flex items-center justify-between gap-3 px-4 py-3 bg-white dark:bg-[#111827] rounded-xl border border-gray-200 dark:border-white/[0.08] hover:border-blue-500 dark:hover:border-blue-500 transition-all group">
                <div class="flex items-center gap-2 min-w-0">
                    @if($project->job_number)
                        <span class="text-[12px] font-extrabold text-blue-600 dark:text-blue-400 flex-shrink-0">[{{ $project->job_number }}]</span>
                    @endif
                    <a href="{{ route('projects.show', $project) }}" class="text-[13px] font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                        {{ $project->name }}
                    </a>
                </div>
                <a href="{{ route('projects.show', $project) }}" class="flex-shrink-0 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold rounded-lg transition-all flex items-center gap-1">
                    View Board
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
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

    {{-- Pending Deliverables (Collapsible at bottom) --}}
    @if($pendingDeliverables->count() > 0)
    <div x-data="{ open: true }" class="pt-6 border-t border-gray-200 dark:border-slate-800 mt-4">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between py-1 text-left focus:outline-none group cursor-pointer">
            <div class="flex items-center gap-2">
                <h2 class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest group-hover:text-gray-600 dark:group-hover:text-slate-300 transition-colors">Pending Deliverables</h2>
                <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 text-[10px] font-bold rounded">{{ $pendingDeliverables->count() }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] font-semibold text-gray-400 dark:text-slate-500 group-hover:text-gray-600 dark:group-hover:text-slate-300 transition-colors">
                <span x-text="open ? 'Collapse' : 'Expand'"></span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>
        <div x-show="open" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
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
                        @if($task->client_status && $task->client_status !== 'Not Sent')
                            <span class="opacity-30 text-[10px]">·</span>
                            <span class="text-[10px] font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-wide bg-blue-50 dark:bg-blue-500/10 px-1.5 py-0.5 rounded">{{ $task->client_status }}</span>
                        @endif
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

</div>
</x-layout>
