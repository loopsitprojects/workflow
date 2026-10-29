<div class="flex flex-col gap-6">
    {{-- Personal 4 KPI Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Tasks --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Assigned Work Items</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-500">
                    {{ $ir['kpis']['completion_rate'] }}% Completed
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $ir['kpis']['total_tasks'] }}</span>
                <span class="text-xs text-gray-400 font-medium">total deliverables</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-blue-500 h-full rounded-full" style="width: {{ $ir['kpis']['completion_rate'] }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 dark:border-slate-800/60 text-xs text-gray-500 dark:text-slate-400">
                <span>Done: <b class="text-emerald-500">{{ $ir['kpis']['completed_tasks'] }}</b></span>
                <span>Active: <b class="text-blue-500">{{ $ir['kpis']['active_tasks'] }}</b></span>
            </div>
        </div>

        {{-- Turnaround Velocity --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">On-Time Delivery</span>
                @if($ir['kpis']['overdue_tasks'] > 0)
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-red-500/10 text-red-500">
                        {{ $ir['kpis']['overdue_tasks'] }} Overdue
                    </span>
                @else
                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500">
                        100% On Schedule
                    </span>
                @endif
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $ir['kpis']['on_time_rate'] }}%</span>
                <span class="text-xs text-gray-400 font-medium">turnaround rate</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $ir['kpis']['on_time_rate'] }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 dark:border-slate-800/60 text-xs text-gray-500 dark:text-slate-400">
                <span>Overdue items:</span>
                <span class="font-bold {{ $ir['kpis']['overdue_tasks'] > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                    {{ $ir['kpis']['overdue_tasks'] }} tasks
                </span>
            </div>
        </div>

        {{-- Creative Quality / Revisions --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">First-Time Pass</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-500">
                    {{ $ir['kpis']['first_time_pass_rate'] }}% Accuracy
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $ir['kpis']['revisions_count'] }}</span>
                <span class="text-xs text-gray-400 font-medium">total revisions</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, $ir['kpis']['first_time_pass_rate']) }}%"></div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 dark:border-slate-800/60 text-xs text-gray-500 dark:text-slate-400">
                <span>Rounds requested:</span>
                <span class="font-bold text-amber-500">{{ $ir['kpis']['revisions_count'] }} correction cycles</span>
            </div>
        </div>

        {{-- Active Workspaces --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-5 card-shadow border border-gray-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Assigned Projects</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-500">
                    Workspaces
                </span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-mono">{{ count($ir['projects_involved'] ?? []) }}</span>
                <span class="text-xs text-gray-400 font-medium">active projects</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                <div class="bg-purple-500 h-full rounded-full" style="width: 100%"></div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 dark:border-slate-800/60 text-xs text-gray-500 dark:text-slate-400">
                <span>Total Work Items:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $ir['kpis']['total_tasks'] ?? 0 }} deliverables</span>
            </div>
        </div>
    </div>

    {{-- Urgent Watchlist & Current Stages Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Urgent tasks --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3 flex items-center justify-between">
                <span>🚨 Urgent Deadlines Watchlist</span>
                <span class="text-[10px] font-bold text-red-500">{{ $ir['at_risk_tasks']->count() }} Items</span>
            </h4>
            @if($ir['at_risk_tasks']->isEmpty())
                <p class="text-xs text-gray-400 py-6 text-center">No urgent or overdue tasks assigned to this member.</p>
            @else
                <div class="divide-y divide-gray-100 dark:divide-slate-800/80 text-xs">
                    @foreach($ir['at_risk_tasks'] as $task)
                        @php
                            $dl = \Carbon\Carbon::parse($task->deadline);
                        @endphp
                        <div class="py-2.5 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <button type="button"
                                        @click="viewDeliverableDetails({{ $task->id }})"
                                        class="text-left font-bold text-gray-900 dark:text-white hover:text-blue-500 line-clamp-1 cursor-pointer">
                                    {{ $task->title }}
                                </button>
                                <span class="text-[10px] text-gray-400">{{ $task->project?->name ?? 'N/A' }} • Stage: {{ $task->approval_stage }}</span>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-[11px] font-mono font-bold {{ $dl->isPast() ? 'text-red-500' : 'text-amber-500' }}">
                                    {{ $dl->format('M d') }} ({{ $dl->isPast() ? 'Overdue' : 'Due Soon' }})
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Current Stages Distribution --}}
        <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Tasks by Approval Stage</h4>
            <div class="space-y-3">
                @forelse($ir['stage_counts'] as $stg => $cnt)
                    @php
                        $tot = max(1, $ir['kpis']['total_tasks']);
                        $pct = round(($cnt / $tot) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs font-semibold mb-1">
                            <span class="text-gray-800 dark:text-slate-200">{{ $stg }}</span>
                            <span class="text-blue-500 font-bold">{{ $cnt }} items ({{ $pct }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-blue-600 h-full rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-6 text-center">No stage activity found.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Projects Involved Mini Cards --}}
    <div class="bg-white dark:bg-[#0F172A] rounded-2xl p-6 card-shadow border border-gray-100 dark:border-slate-800">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Projects & Brands Assigned</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($ir['projects_involved'] as $pInfo)
                @php
                    $p = $pInfo['project'];
                @endphp
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $pInfo['brand_name'] }}</span>
                    <h5 class="font-bold text-sm text-gray-900 dark:text-white truncate mt-0.5">{{ $p?->name ?? 'Project' }}</h5>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                        <span>Assigned items:</span>
                        <span class="font-bold text-blue-500">{{ $pInfo['tasks_count'] }} tasks</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                        <span>Completed:</span>
                        <span class="font-bold text-emerald-500">{{ $pInfo['completed_count'] }} done</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-xs text-gray-400 py-4 text-center">No projects assigned to this member.</div>
            @endforelse
        </div>
    </div>

    {{-- Deliverables Matrix for this Individual --}}
    @include('analytics.partials.deliverables_matrix_table', ['deliverables' => $ir['deliverables']])
</div>
