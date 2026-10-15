@php
    $assigneeInitial = !empty($task->assignee_name) && $task->assignee_name !== 'Unassigned'
        ? mb_strtoupper(mb_substr(trim($task->assignee_name), 0, 1))
        : (!empty($task->writer?->name) 
            ? mb_strtoupper(mb_substr(trim($task->writer->name), 0, 1)) 
            : (!empty($task->designer?->name) 
                ? mb_strtoupper(mb_substr(trim($task->designer->name), 0, 1)) 
                : 'U'));
    
    $assigneeFullName = $task->assignee_name ?: ($task->writer?->name ?? ($task->designer?->name ?? 'Unassigned'));

    $prio = $task->priority ?? 'Medium';
    $prioColor = match($prio) {
        'High Priority', 'Urgent', 'High' => '#ef4444',
        'Low' => '#64748b',
        default => '#10b981',
    };

    $subtasksCount = $task->subtasks ? $task->subtasks->count() : 0;
    
    $postTypeLabel = $task->post_type ?: ($task->flow_type === 'direct_design' ? 'Design' : 'Deliverable');
    
    $stage = $task->approval_stage ?: 'Assignee';

    $statusBorder = match($task->status) {
        'Done', 'Completed', 'Complete' => 'border-left: 3px solid #10b981;',
        'Approval Stage', 'Approval', 'Review', 'Under Review' => 'border-left: 3px solid #f59e0b;',
        'In Progress', 'on hold' => 'border-left: 3px solid #6366f1;',
        default => 'border-left: 3px solid var(--color-border-primary);',
    };
@endphp

<div class="kanban-card" 
     id="kanban-card-{{ $task->id }}"
     data-id="{{ $task->id }}"
     data-status="{{ $task->status ?? 'To Do' }}"
     draggable="true"
     ondragstart="kanbanOnDragStart(event, {{ $task->id }})"
     ondragend="kanbanOnDragEnd(event)"
     onclick="openTaskModal({{ $task->id }})"
     style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); {{ $statusBorder }} border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.03); cursor: pointer; transition: all 0.15s ease; position: relative;"
     onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'; this.style.transform='translateY(-1px)';"
     onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,0.03)'; this.style.transform='none';">
    
    {{-- Card Header: Type & Stage --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); padding: 2px 6px; border-radius: 5px;">
            {{ $postTypeLabel }}
        </span>
        <span style="font-size: 10px; font-weight: 700; color: #3b82f6; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); padding: 1px 6px; border-radius: 5px;">
            {{ $stage }}
        </span>
    </div>

    {{-- Title --}}
    <h4 style="margin: 0 0 8px 0; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); line-height: 1.35; letter-spacing: -0.01em;">
        {{ $task->title }}
    </h4>

    {{-- Subtasks Badge if any --}}
    @if($subtasksCount > 0)
    <div style="margin-bottom: 8px; display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 700; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 4px; padding: 1px 6px;">
        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <span>{{ $subtasksCount }} {{ \Illuminate\Support\Str::plural('subtask', $subtasksCount) }}</span>
    </div>
    @endif

    {{-- Metadata Footer Row (matching reference screenshot) --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 6px; border-top: 1px solid var(--color-border-primary); margin-top: 4px;">
        <div style="display: inline-flex; align-items: center; gap: 6px;">
            {{-- Assignee Avatar Initial (like the purple S in reference) --}}
            <div style="width: 22px; height: 22px; border-radius: 50%; background: #7c3aed; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(124,58,237,0.3);" 
                 title="{{ $assigneeFullName }}">
                {{ $assigneeInitial }}
            </div>

            {{-- Due Date with Calendar icon --}}
            @if($task->deadline)
            <div style="display: inline-flex; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 600; color: var(--color-text-secondary);" title="Due {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y') }}">
                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>{{ \Carbon\Carbon::parse($task->deadline)->format('M d') }}</span>
            </div>
            @endif
        </div>

        {{-- Right Action/Info Icons: Priority Flag + Tag icon --}}
        <div style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="12" height="12" fill="{{ $prioColor }}" viewBox="0 0 16 16" title="{{ $prio }}">
                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
            </svg>
            <svg width="12" height="12" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" title="{{ $postTypeLabel }}">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
        </div>
    </div>
</div>
