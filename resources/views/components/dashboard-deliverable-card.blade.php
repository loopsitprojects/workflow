@props(['group'])

@php
    $isBatch = $group['is_batch'];
    $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
    $project = $group['project'];
    $priority = $group['priority'];
    $deadline = $group['deadline'];
    $subtasks = $group['subtasks'];

    $priorityFills = [
        'High Priority' => '#ef4444',
        'Medium'        => '#10b981',
        'Low Priority'  => '#f59e0b',
    ];
    $prioFill = $priorityFills[$priority] ?? '#10b981';

    $deliverableType = null;
    if (!$isBatch && !empty($subtasks[0])) {
        $t = $subtasks[0];
        if (!empty($t->post_type)) {
            $deliverableType = $t->post_type . ($t->flow_type === 'direct_design' ? ' (Direct Design)' : '');
        } elseif ($t->flow_type === 'direct_design') {
            $deliverableType = 'Direct Design';
        } elseif (!empty($t->task_type) && $t->task_type !== 'Deliverable') {
            $deliverableType = $t->task_type;
        }
    } elseif ($isBatch && !empty($group['parent'])) {
        $p = $group['parent'];
        if (!empty($p->post_type)) {
            $deliverableType = $p->post_type . ($p->flow_type === 'direct_design' ? ' (Direct Design)' : '');
        } elseif ($p->flow_type === 'direct_design') {
            $deliverableType = 'Direct Design';
        }
    }

    $targetDeliverableId = $isBatch ? ($group['parent']->id ?? null) : ($subtasks[0]->id ?? null);
@endphp

<a href="{{ route('projects.show', ['project' => $project->id, 'deliverable_id' => $targetDeliverableId]) }}"
   class="flex flex-col bg-white dark:bg-[#111827] rounded-xl px-5 py-4 border border-gray-100 dark:border-white/[0.05] card-shadow hover:border-blue-200 dark:hover:border-blue-500/40 hover:shadow-md transition-all mb-2 group block">
    <div class="flex items-start justify-between min-w-0">
        <div class="flex items-start gap-4 min-w-0">
            <svg width="12" height="12" fill="{{ $prioFill }}" viewBox="0 0 16 16" style="flex-shrink:0; margin-top:3px;">
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
            </svg>
            <div class="min-w-0">
                <h3 class="text-[13px] font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {{ $mainTitle }}
                </h3>
                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap text-[10px]">
                    @if(!empty($project->brand->name))
                        <span class="text-gray-400 dark:text-slate-500">Brand: <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $project->brand->name }}</span></span>
                    @endif
                    @if($project)
                        <span class="text-gray-200 dark:text-slate-700">·</span>
                        <span class="text-gray-400 dark:text-slate-500">Project: <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $project->name }}</span></span>
                    @endif
                    @if($deliverableType)
                        <span class="text-gray-200 dark:text-slate-700">·</span>
                        <span class="text-gray-400 dark:text-slate-500">Type: <span class="font-semibold text-gray-700 dark:text-slate-300">{{ $deliverableType }}</span></span>
                    @endif
                    @if(!$isBatch && !empty($subtasks[0]->approval_stage))
                        <span class="text-gray-200 dark:text-slate-700">·</span>
                        <span class="text-gray-400 dark:text-slate-500">Stage: <span class="font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wide">{{ $subtasks[0]->approval_stage }}</span></span>
                    @endif
                    @if(!$isBatch && !empty($subtasks[0]->client_status) && $subtasks[0]->client_status !== 'Not Sent')
                        <span class="text-gray-200 dark:text-slate-700">·</span>
                        <span class="text-[10px] font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-wide bg-blue-50 dark:bg-blue-500/10 px-1.5 py-0.5 rounded">{{ $subtasks[0]->client_status }}</span>
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
                    <a href="{{ route('projects.show', ['project' => $project->id, 'deliverable_id' => $t->id]) }}" class="text-[11px] font-semibold text-blue-500 dark:text-blue-400 pr-2 hover:underline">
                        {{ $t->title }}
                    </a>
                    @if($t->approval_stage)
                        <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider flex-shrink-0">{{ $t->approval_stage }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</a>
