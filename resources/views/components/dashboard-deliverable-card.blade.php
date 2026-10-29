@props(['group'])

@php
    $authUser = auth()->user();
    $authRole = strtolower(str_replace(' ', '', $authUser->role ?? ''));
    $isDesigner = $authRole === 'designer';
    $isWriter = in_array($authRole, ['writer', 'assignee']);

    $isBatch = $group['is_batch'];
    $mainTitle = $isBatch ? $group['parent']->title : $group['subtasks'][0]->title;
    $project = $group['project'];
    $priority = $group['priority'];
    $deadline = $group['deadline'];
    $subtasks = $group['subtasks'];
    $taskObj = !$isBatch && !empty($subtasks[0]) ? $subtasks[0] : ($group['parent'] ?? null);

    $designerDeadline = $group['designer_deadline'] ?? ($taskObj->designer_deadline ?? null);
    $revisionDeadline = $group['revision_deadline'] ?? ($taskObj->revision_deadline ?? null);
    if (!$revisionDeadline && $taskObj && method_exists($taskObj, 'revisionsHistory')) {
        $revisionDeadline = $taskObj->revisionsHistory()->latest()->first()?->revision_deadline;
    }
    $hasRevision = $taskObj ? (bool)($taskObj->revision_instructions || ($taskObj->revisions && (is_numeric($taskObj->revisions) ? $taskObj->revisions > 0 : $taskObj->revisions->count() > 0)) || $revisionDeadline) : false;

    // Determine target primary & secondary deadlines based on user role
    $primaryDate = null;
    $primaryLabel = '';
    $primaryType = 'deliverable';
    $secondaryDate = null;

    if ($isDesigner) {
        if ($revisionDeadline) {
            $primaryDate = $revisionDeadline;
            $primaryLabel = 'Rev: ';
            $primaryType = 'revision';
            $secondaryDate = $designerDeadline;
        } elseif ($designerDeadline) {
            $primaryDate = $designerDeadline;
            $primaryLabel = 'Des: ';
            $primaryType = 'designer';
        } else {
            // Fallback so card never shows "No date" if main deliverable has a deadline
            $primaryDate = $deadline;
            $primaryLabel = '';
            $primaryType = 'deliverable';
        }
    } elseif ($isWriter) {
        if ($hasRevision && $revisionDeadline) {
            $primaryDate = $revisionDeadline;
            $primaryLabel = 'Rev: ';
            $primaryType = 'revision';
        } else {
            $primaryDate = $deadline;
            $primaryLabel = '';
            $primaryType = 'deliverable';
        }
    } else {
        $primaryDate = $deadline;
        $primaryLabel = '';
        $primaryType = 'deliverable';
    }

    $priorityFills = [
        'High Priority' => '#ef4444',
        'Medium'        => '#f59e0b',
        'Low Priority'  => '#64748b',
    ];
    $prioFill = $priorityFills[$priority] ?? '#f59e0b';

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
   class="flex flex-col bg-white dark:bg-[#111827] rounded-xl px-5 py-4 border border-gray-200 dark:border-white/[0.08] card-shadow hover:border-blue-300 dark:hover:border-blue-500/50 hover:shadow-md transition-all mb-2.5 group block">
    <div class="flex items-start justify-between min-w-0">
        <div class="flex items-start gap-4 min-w-0">
            <svg width="12" height="12" fill="{{ $prioFill }}" viewBox="0 0 16 16" style="flex-shrink:0; margin-top:3px;">
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
            </svg>
            <div class="min-w-0">
                <h3 class="text-[13px] font-bold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                    {{ $mainTitle }}
                </h3>
                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap text-[10px]">
                    @if(!empty($project->brand->name))
                        <span class="text-slate-500 dark:text-slate-400">Brand: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->brand->name }}</span></span>
                    @endif
                    @if($project)
                        <span class="text-gray-300 dark:text-slate-600">·</span>
                        <span class="text-slate-500 dark:text-slate-400">Project: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $project->name }}</span></span>
                    @endif
                    @if($deliverableType)
                        <span class="text-gray-300 dark:text-slate-600">·</span>
                        <span class="text-slate-500 dark:text-slate-400">Type: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $deliverableType }}</span></span>
                    @endif
                    @if(!$isBatch && !$isDesigner && !empty($subtasks[0]->approval_stage))
                        <span class="text-gray-300 dark:text-slate-600">·</span>
                        <span class="text-slate-500 dark:text-slate-400">Stage: <span class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">{{ $subtasks[0]->approval_stage }}</span></span>
                    @endif
                    @if(!$isBatch && !empty($subtasks[0]->client_status) && $subtasks[0]->client_status !== 'Not Sent')
                        <span class="text-gray-300 dark:text-slate-600">·</span>
                        <span class="text-[10px] font-extrabold text-blue-700 dark:text-blue-300 uppercase tracking-wide bg-blue-100 dark:bg-blue-500/20 px-2 py-0.5 rounded-full border border-blue-200 dark:border-blue-500/30">{{ $subtasks[0]->client_status }}</span>
                    @endif

                    {{-- Main deliverable deadline displayed as date data --}}
                    @if($deadline)
                        <span class="text-gray-300 dark:text-slate-600">·</span>
                        <span class="text-slate-500 dark:text-slate-400 inline-flex items-center gap-1">
                            Due: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($deadline)->format('M d, Y') }}</span>
                        </span>
                    @endif

                    {{-- Revision milestone badge --}}
                    @if($revisionDeadline)
                        <span class="text-gray-300 dark:text-slate-600">·</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9.5px] font-extrabold text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/40" title="Revision Deadline">
                            <svg class="w-2.5 h-2.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Revision: <span class="font-black">{{ \Carbon\Carbon::parse($revisionDeadline)->format('M d, Y') }}</span>
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex-shrink-0 text-right ml-4 flex flex-col items-end gap-1.5">
            @if($primaryDate)
                @php $daysLeft = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($primaryDate)->startOfDay(), false); @endphp
                @if($daysLeft < 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-100 text-red-800 dark:bg-red-950/80 dark:text-red-300 border border-red-300 dark:border-red-700/60 shadow-xs" title="{{ $primaryType === 'revision' ? 'Revision Deadline' : ($primaryType === 'designer' ? 'Designer Deadline' : 'Deliverable Deadline') }}: {{ \Carbon\Carbon::parse($primaryDate)->format('M d, Y') }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 dark:bg-red-400"></span>
                        {{ $primaryLabel }}Overdue
                    </span>
                @elseif($daysLeft === 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 shadow-xs" title="{{ $primaryType === 'revision' ? 'Revision Deadline' : ($primaryType === 'designer' ? 'Designer Deadline' : 'Deliverable Deadline') }}: {{ \Carbon\Carbon::parse($primaryDate)->format('M d, Y') }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                        {{ $primaryLabel }}Due Today
                    </span>
                @elseif($daysLeft <= 3)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300 border border-orange-200 dark:border-orange-800/50" title="{{ $primaryType === 'revision' ? 'Revision Deadline' : ($primaryType === 'designer' ? 'Designer Deadline' : 'Deliverable Deadline') }}: {{ \Carbon\Carbon::parse($primaryDate)->format('M d, Y') }}">
                        {{ $primaryLabel }}{{ $daysLeft }}d left
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-semibold text-slate-600 dark:text-slate-400 bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-white/5" title="{{ $primaryType === 'revision' ? 'Revision Deadline' : ($primaryType === 'designer' ? 'Designer Deadline' : 'Deliverable Deadline') }}: {{ \Carbon\Carbon::parse($primaryDate)->format('M d, Y') }}">
                        {{ $primaryLabel }}{{ \Carbon\Carbon::parse($primaryDate)->format('M j') }}
                    </span>
                @endif
            @else
                <span class="text-[10px] text-gray-400 dark:text-slate-600">No date</span>
            @endif

            @if($secondaryDate)
                @php $secDaysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($secondaryDate)->startOfDay(), false); @endphp
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide {{ $secDaysLeft < 0 ? 'bg-pink-100 text-pink-700 dark:bg-pink-950/80 dark:text-pink-300 border border-pink-300 dark:border-pink-800/50' : 'bg-pink-50 text-pink-600 dark:bg-pink-950/50 dark:text-pink-400 border border-pink-200 dark:border-pink-800/40' }}" title="Designer Deadline: {{ \Carbon\Carbon::parse($secondaryDate)->format('M d, Y') }}">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Des: {{ \Carbon\Carbon::parse($secondaryDate)->format('M j') }}
                </span>
            @endif

            @if(!$isDesigner && !$isWriter)
                @if($revisionDeadline)
                    @php $revDaysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($revisionDeadline)->startOfDay(), false); @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wide {{ $revDaysLeft < 0 ? 'bg-red-100 text-red-700 dark:bg-red-950/80 dark:text-red-300 border border-red-300 dark:border-red-800/60 shadow-xs' : 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400 border border-red-200 dark:border-red-800/40' }}" title="Revision Deadline: {{ \Carbon\Carbon::parse($revisionDeadline)->format('M d, Y') }}">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Rev: {{ \Carbon\Carbon::parse($revisionDeadline)->format('M j') }}
                    </span>
                @endif
                @if($designerDeadline && $designerDeadline != $deadline)
                    @php $desDaysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($designerDeadline)->startOfDay(), false); @endphp
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide {{ $desDaysLeft < 0 ? 'bg-pink-100 text-pink-700 dark:bg-pink-950/80 dark:text-pink-300 border border-pink-300 dark:border-pink-800/50' : 'bg-pink-50 text-pink-600 dark:bg-pink-950/50 dark:text-pink-400 border border-pink-200 dark:border-pink-800/40' }}" title="Designer Deadline: {{ \Carbon\Carbon::parse($designerDeadline)->format('M d, Y') }}">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Des: {{ \Carbon\Carbon::parse($designerDeadline)->format('M j') }}
                    </span>
                @endif
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
                    <div class="flex items-center gap-2 flex-wrap">
                        @php
                            $subRevDeadline = $t->revision_deadline;
                            if (!$subRevDeadline && method_exists($t, 'revisionsHistory')) {
                                $subRevDeadline = $t->revisionsHistory()->latest()->first()?->revision_deadline;
                            }
                            $subHasRev = (bool)($t->revision_instructions || ($t->revisions && (is_numeric($t->revisions) ? $t->revisions > 0 : $t->revisions->count() > 0)) || $subRevDeadline);
                        @endphp

                        @if($isDesigner)
                            @if($t->deadline)
                                <span class="text-[9px] font-medium text-slate-500 dark:text-slate-400">Due: {{ \Carbon\Carbon::parse($t->deadline)->format('M d') }}</span>
                            @endif
                            @if($subRevDeadline)
                                <span class="text-[9px] font-extrabold text-red-600 dark:text-red-400">Rev: {{ \Carbon\Carbon::parse($subRevDeadline)->format('M d') }}</span>
                            @endif
                        @elseif($isWriter)
                            @if($t->deadline)
                                <span class="text-[9px] font-medium text-slate-500 dark:text-slate-400">Due: {{ \Carbon\Carbon::parse($t->deadline)->format('M d') }}</span>
                            @endif
                            @if($subHasRev && $subRevDeadline)
                                <span class="text-[9px] font-extrabold text-red-600 dark:text-red-400">Rev: {{ \Carbon\Carbon::parse($subRevDeadline)->format('M d') }}</span>
                            @endif
                        @else
                            @if($t->deadline)
                                <span class="text-[9px] font-medium text-slate-500 dark:text-slate-400">Due: {{ \Carbon\Carbon::parse($t->deadline)->format('M d') }}</span>
                            @endif
                            @if($t->designer_deadline && $t->designer_deadline != $t->deadline)
                                <span class="text-[9px] font-bold text-pink-600 dark:text-pink-400">Des: {{ \Carbon\Carbon::parse($t->designer_deadline)->format('M d') }}</span>
                            @endif
                            @if($subRevDeadline)
                                <span class="text-[9px] font-extrabold text-red-600 dark:text-red-400">Rev: {{ \Carbon\Carbon::parse($subRevDeadline)->format('M d') }}</span>
                            @endif
                        @endif

                        @if($t->approval_stage)
                            <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider flex-shrink-0">{{ $t->approval_stage }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</a>
