<x-layout title="Dashboard">
    <div class="flex flex-col gap-8">

        {{-- Header --}}
        <div class="flex justify-between items-end">
            <div>
                <p class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-1">Workspace Overview</p>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
                </h1>
            </div>
            <span class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">{{ now()->format('l, M j') }}</span>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-5 border border-gray-100 dark:border-white/[0.05] card-shadow">
                <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-2">Assigned</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $deliverables->count() }}</p>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">deliverables</p>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-500/10 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-500/20">
                <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mb-2">Completed</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $deliverables->where('status', 'Done')->count() }}</p>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1">closed out</p>
            </div>
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-5 border border-gray-100 dark:border-white/[0.05] card-shadow">
                <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-2">Pending</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $deliverables->where('status', '!=', 'Done')->count() }}</p>
                <p class="text-[11px] text-gray-400 dark:text-slate-500 mt-1">in progress</p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-500/10 rounded-2xl p-5 border border-blue-100 dark:border-blue-500/20">
                <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-2">Brands</p>
                <p class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $brandCount }}</p>
                <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-1">active workspaces</p>
            </div>
        </div>

        {{-- Brand quick-access --}}
        @if($brands->isNotEmpty())
        <div>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-[11px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Brand Directory</h2>
                <a href="{{ route('brands.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">View All →</a>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($brands as $brand)
                <a href="{{ route('brands.show', $brand) }}"
                   class="flex items-center gap-2.5 px-3 py-2 bg-white dark:bg-[#111827] border border-gray-100 dark:border-slate-800 rounded-xl hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-sm transition-all group">
                    <div class="w-6 h-6 rounded-md bg-gray-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                        @if($brand->logo_url)
                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="w-5 h-5 object-contain">
                        @else
                            <span class="text-[10px] font-extrabold text-gray-500">{{ substr($brand->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <span class="text-[12px] font-bold text-gray-700 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 whitespace-nowrap">{{ $brand->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Main content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Deliverables list --}}
            <div class="lg:col-span-2 flex flex-col gap-6">

                {{-- Active --}}
                <div>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3">Active Deliverables</p>
                    @php
                        $activeTasks = $deliverables->where('status', '!=', 'Done')->sortBy('deadline');
                        $groupedActive = [];
                        foreach($activeTasks as $t) {
                            $key = $t->parent_deliverable_id ? 'batch_' . $t->parent_deliverable_id : 'single_' . $t->id;
                            if (!isset($groupedActive[$key])) {
                                $groupedActive[$key] = [
                                    'is_batch' => (bool)$t->parent_deliverable_id,
                                    'parent' => $t->parent,
                                    'project' => $t->project,
                                    'priority' => $t->priority,
                                    'deadline' => $t->deadline,
                                    'subtasks' => []
                                ];
                            }
                            $groupedActive[$key]['subtasks'][] = $t;
                            if ($t->deadline && (!$groupedActive[$key]['deadline'] || $t->deadline < $groupedActive[$key]['deadline'])) {
                                $groupedActive[$key]['deadline'] = $t->deadline;
                            }
                        }
                        uasort($groupedActive, function($a, $b) {
                            if (!$a['deadline']) return 1;
                            if (!$b['deadline']) return -1;
                            return $a['deadline'] <=> $b['deadline'];
                        });
                    @endphp
                    @forelse($groupedActive as $group)
                    @php
                        $isBatch = $group['is_batch'];
                        $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
                        $project = $group['project'];
                        $priority = $group['priority'];
                        $deadline = $group['deadline'];
                        $subtasks = $group['subtasks'];

                        $priorityColors = [
                            'High Priority' => 'bg-red-400',
                            'Medium'        => 'bg-amber-400',
                            'Standard'      => 'bg-gray-300 dark:bg-slate-600',
                            'Low'           => 'bg-gray-300 dark:bg-slate-600',
                        ];
                        $dot = $priorityColors[$priority] ?? 'bg-gray-300 dark:bg-slate-600';
                    @endphp
                    <a href="{{ route('projects.show', $project->id) }}"
                       class="flex flex-col bg-white dark:bg-[#111827] rounded-xl px-5 py-4 border border-gray-100 dark:border-white/[0.05] card-shadow hover:border-blue-200 dark:hover:border-blue-500/40 hover:shadow-md transition-all mb-2 group block">
                        <div class="flex items-start justify-between min-w-0">
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="w-2 h-2 rounded-full flex-shrink-0 mt-1.5 {{ $dot }}"></div>
                                <div class="min-w-0">
                                    <h3 class="text-[13px] font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        {{ $mainTitle }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500">{{ $project->brand->name ?? '' }}</span>
                                        @if($project)
                                            <span class="text-gray-200 dark:text-slate-700">·</span>
                                            <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500 truncate">{{ $project->name }}</span>
                                        @endif
                                        @if(!$isBatch && $subtasks[0]->approval_stage)
                                            <span class="text-gray-200 dark:text-slate-700">·</span>
                                            <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wide">{{ $subtasks[0]->approval_stage }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right ml-4">
                                @if($deadline)
                                    @php $daysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($deadline)->startOfDay(), false); @endphp
                                    @if($daysLeft < 0)
                                        <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">Overdue</span>
                                    @elseif($daysLeft === 0)
                                        <span class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Due Today</span>
                                    @elseif($daysLeft <= 3)
                                        <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest">{{ $daysLeft }}d left</span>
                                    @else
                                        <span class="text-[10px] font-semibold text-gray-400 dark:text-slate-500">{{ \Carbon\Carbon::parse($deadline)->format('M j') }}</span>
                                    @endif
                                @else
                                    <span class="text-[10px] text-gray-300 dark:text-slate-700">No date</span>
                                @endif
                            </div>
                        </div>

                        @if($isBatch)
                            <div class="mt-3 ml-6 pl-3 border-l-2 border-gray-100 dark:border-slate-800 flex flex-col gap-2">
                                @foreach($subtasks as $t)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-semibold text-blue-500 dark:text-blue-400 truncate pr-2">{{ $t->title }}</span>
                                        @if($t->approval_stage)
                                            <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider flex-shrink-0">{{ $t->approval_stage }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </a>
                    @empty
                    <div class="bg-gray-50 dark:bg-white/[0.02] rounded-xl p-8 text-center border border-dashed border-gray-200 dark:border-white/[0.06]">
                        <p class="text-sm font-semibold text-gray-400 dark:text-slate-500">No active deliverables assigned to you.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Completed --}}
                @if($deliverables->where('status', 'Done')->count() > 0)
                <div>
                    <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-3">Completed</p>
                    @php
                        $completedTasks = $deliverables->where('status', 'Done');
                        $groupedCompleted = [];
                        foreach($completedTasks as $t) {
                            $key = $t->parent_deliverable_id ? 'batch_' . $t->parent_deliverable_id : 'single_' . $t->id;
                            if (!isset($groupedCompleted[$key])) {
                                $groupedCompleted[$key] = [
                                    'is_batch' => (bool)$t->parent_deliverable_id,
                                    'parent' => $t->parent,
                                    'project' => $t->project,
                                    'subtasks' => []
                                ];
                            }
                            $groupedCompleted[$key]['subtasks'][] = $t;
                        }
                    @endphp
                    @foreach($groupedCompleted as $group)
                    @php
                        $isBatch = $group['is_batch'];
                        $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
                        $project = $group['project'];
                        $subtasks = $group['subtasks'];
                    @endphp
                    <a href="{{ route('projects.show', $project->id) }}"
                       class="flex flex-col bg-white dark:bg-[#111827] rounded-xl px-5 py-3.5 border border-gray-50 dark:border-white/[0.03] mb-2 opacity-60 hover:opacity-90 transition-opacity group block">
                        <div class="flex items-start justify-between min-w-0">
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="w-2 h-2 rounded-full flex-shrink-0 mt-1.5 bg-emerald-400"></div>
                                <div class="min-w-0">
                                    <h3 class="text-[13px] font-bold text-gray-500 dark:text-slate-400 line-through truncate">{{ $mainTitle }}</h3>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span class="text-[10px] text-gray-400 dark:text-slate-600">{{ $project->brand->name ?? '' }}</span>
                                        @if($project)
                                            <span class="text-gray-200 dark:text-slate-700">·</span>
                                            <span class="text-[10px] text-gray-400 dark:text-slate-600 truncate">{{ $project->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest flex-shrink-0 ml-4">Closed</span>
                        </div>
                        @if($isBatch)
                            <div class="mt-2 ml-6 pl-3 border-l-2 border-gray-100 dark:border-slate-800 flex flex-col gap-1.5">
                                @foreach($subtasks as $t)
                                    <span class="text-[11px] font-semibold text-gray-400 dark:text-slate-500 line-through truncate">{{ $t->title }}</span>
                                @endforeach
                            </div>
                        @endif
                    </a>
                    @endforeach
                </div>
                @endif

            </div>

            {{-- Sidebar --}}
            <div class="flex flex-col gap-6">

                {{-- Upcoming deadlines --}}
                <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 border border-gray-100 dark:border-white/[0.05] card-shadow">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-[13px] font-extrabold text-gray-900 dark:text-white">Upcoming Deadlines</h3>
                        <svg class="w-4 h-4 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @php
                        $upcomingGroups = collect($groupedActive)->filter(function($g) { return !empty($g['deadline']); })->take(5);
                    @endphp
                    @forelse($upcomingGroups as $group)
                    @php
                        $isBatch = $group['is_batch'];
                        $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
                        $project = $group['project'];
                        $deadline = $group['deadline'];
                    @endphp
                    <a href="{{ route('projects.show', $project->id) }}" class="flex flex-col gap-2 mb-4 last:mb-0 group block">
                        <div class="flex items-start gap-3">
                            @php $daysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($deadline)->startOfDay(), false); @endphp
                            <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-[10px] font-black
                                {{ $daysLeft < 0 ? 'bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400' : ($daysLeft <= 2 ? 'bg-orange-100 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400' : 'bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-slate-400') }}">
                                {{ \Carbon\Carbon::parse($deadline)->format('d') }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12px] font-bold text-gray-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $mainTitle }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($deadline)->format('M j') }}
                                    @if($daysLeft < 0)
                                        · <span class="text-red-500 font-bold">{{ abs($daysLeft) }}d overdue</span>
                                    @elseif($daysLeft === 0)
                                        · <span class="text-orange-500 font-bold">today</span>
                                    @elseif($daysLeft <= 7)
                                        · <span class="text-amber-500 font-bold">{{ $daysLeft }}d left</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($isBatch)
                            <div class="ml-11 pl-2 border-l-2 border-gray-100 dark:border-slate-800 flex flex-col gap-1">
                                @foreach($group['subtasks'] as $t)
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-semibold text-gray-500 dark:text-slate-400 truncate">{{ $t->title }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </a>
                    @empty
                    <p class="text-[12px] text-gray-400 dark:text-slate-500 font-medium text-center py-4">No upcoming deadlines.</p>
                    @endforelse
                </div>

                {{-- Priority breakdown --}}
                @php
                    $highCount   = $deliverables->where('status','!=','Done')->where('priority','High Priority')->count();
                    $mediumCount = $deliverables->where('status','!=','Done')->where('priority','Medium')->count();
                    $lowCount    = $deliverables->where('status','!=','Done')->whereNotIn('priority',['High Priority','Medium'])->count();
                    $total       = $deliverables->where('status','!=','Done')->count();
                @endphp
                @if($total > 0)
                <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 border border-gray-100 dark:border-white/[0.05] card-shadow">
                    <h3 class="text-[13px] font-extrabold text-gray-900 dark:text-white mb-5">Priority Breakdown</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-red-400"></div>
                                <span class="text-[12px] font-semibold text-gray-600 dark:text-slate-400">High Priority</span>
                            </div>
                            <span class="text-[12px] font-extrabold text-gray-900 dark:text-white">{{ $highCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                <span class="text-[12px] font-semibold text-gray-600 dark:text-slate-400">Medium</span>
                            </div>
                            <span class="text-[12px] font-extrabold text-gray-900 dark:text-white">{{ $mediumCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-gray-300 dark:bg-slate-600"></div>
                                <span class="text-[12px] font-semibold text-gray-600 dark:text-slate-400">Standard / Low</span>
                            </div>
                            <span class="text-[12px] font-extrabold text-gray-900 dark:text-white">{{ $lowCount }}</span>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

    </div>
</x-layout>
