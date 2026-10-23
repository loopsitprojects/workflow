@php
    $assigneeFullName = $task->assignee_name ?: ($task->writer?->name ?? ($task->designer?->name ?? 'Unassigned'));
    $hasAssignee = !empty($assigneeFullName) && strtolower($assigneeFullName) !== 'unassigned';
    $assigneeDisplay = $hasAssignee ? $assigneeFullName : 'Unassigned';
    $assigneeInitial = $hasAssignee
        ? mb_strtoupper(mb_substr(trim($assigneeFullName), 0, 1))
        : 'U';

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

    $cardSelectableUsers = $selectableUsers ?? \App\Models\User::orderBy('name')->get();
    $cardSubtaskTypes = $subtaskTypes ?? \App\Models\SubtaskType::all();
    if ($cardSubtaskTypes->isEmpty()) {
        $cardSubtaskTypes = collect([
            (object)['name' => 'Outlines'],
            (object)['name' => 'Static Post'],
            (object)['name' => 'Carousel'],
            (object)['name' => 'Reel'],
            (object)['name' => 'Presentation'],
            (object)['name' => 'Pitch Deck'],
        ]);
    }

    $currentUserId = auth()->id();
    $currentUser = auth()->user();
    $isAdminOrManager = $currentUser && ($currentUser->isAdmin() || in_array($currentUser->role, ['Admin', 'Owner', 'Operations Manager']));

    $assignedUserId = $task->writer_id ?: ($task->designer_id ?: null);
    $isAssignee = $assignedUserId && $currentUserId == $assignedUserId;
    $isUnassigned = !$assignedUserId;

    // Approver resolution
    $approverUser = $task->approver ?: ($task->project?->approver ?: null);
    $approverId = $task->approver_id ?: ($task->project?->approver_id ?: null);
    $isApprover = ($approverId && $currentUserId == $approverId);

    $canManageDeliverables = $currentUser && (
        $currentUser->isAdmin() ||
        in_array($currentUser->role, ['Admin', 'Owner', 'Operations Manager', 'Brand Manager', 'Approver', 'Approver Coordinator']) ||
        $task->project?->lead_id == $currentUserId ||
        $task->project?->brand_manager_id == $currentUserId ||
        $task->project?->approver_id == $currentUserId ||
        $task->brand_manager_id == $currentUserId ||
        $task->approver_id == $currentUserId
    );

    $statusLower = strtolower(trim($task->status ?? ''));
    $isInApproval = in_array($statusLower, ['approval stage', 'approval', 'review', 'under review']);
    $isCompleted = in_array($statusLower, ['done', 'completed', 'complete']);

    if ($isAdminOrManager) {
        $canMove = true;
        $moveRestrictionReason = '';
    } elseif ($isInApproval) {
        $canMove = $isApprover;
        $approverDisplayName = $task->approver?->name ?? 'the assigned approver';
        $moveRestrictionReason = $canMove ? '' : "Submitted to {$approverDisplayName}. Only that approver can approve or return this deliverable.";
    } elseif ($isCompleted) {
        $canMove = $isApprover;
        $moveRestrictionReason = $canMove ? '' : 'Deliverable is completed. Only an approver or admin can move it.';
    } else {
        if ($isUnassigned) {
            $canMove = false;
            $moveRestrictionReason = 'Please assign a team member before moving this deliverable.';
        } elseif ($isAssignee) {
            $canMove = true;
            $moveRestrictionReason = '';
        } else {
            $canMove = false;
            $moveRestrictionReason = 'Only the assigned person (' . ($task->assignee_name ?: 'assignee') . ') can move this deliverable.';
        }
    }
@endphp

<div class="kanban-card" 
     id="kanban-card-{{ $task->id }}"
     data-id="{{ $task->id }}"
     data-status="{{ $task->status ?? 'To Do' }}"
     data-approver-id="{{ $task->approver_id ?? '' }}"
     data-approver-name="{{ $task->approver?->name ?? '' }}"
     data-can-manage="{{ $canManageDeliverables ? 'true' : 'false' }}"
     data-can-move="{{ $canMove ? 'true' : 'false' }}"
     data-restriction-reason="{{ $moveRestrictionReason }}"
     draggable="{{ $canMove ? 'true' : 'false' }}"
     ondragstart="kanbanOnDragStart(event, {{ $task->id }})"
     ondragend="kanbanOnDragEnd(event)"
     onclick="handleCardClick({{ $task->id }})"
     style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); {{ $statusBorder }} border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.03); cursor: {{ $canMove ? 'grab' : 'pointer' }}; transition: all 0.15s ease; position: relative;"
     title="{{ $moveRestrictionReason }}"
     onmouseover="if(!isCardEditorOpen({{ $task->id }})) { this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'; this.style.transform='translateY(-1px)'; }"
     onmouseout="if(!isCardEditorOpen({{ $task->id }})) { this.style.boxShadow='0 1px 4px rgba(0,0,0,0.03)'; this.style.transform='none'; }">
    
    {{-- ========================================================= --}}
    {{-- COLLAPSED CARD VIEW                                       --}}
    {{-- ========================================================= --}}
    <div class="kanban-card-view" id="kanban-card-view-{{ $task->id }}">
        {{-- Card Header: Type, Stage & Assignee --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
            <div style="display: inline-flex; align-items: center; gap: 4px;">
                <span id="card-view-post-type-{{ $task->id }}" style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                    {{ $postTypeLabel }}
                </span>
                <span id="card-view-stage-{{ $task->id }}" style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                    {{ $stage }}
                </span>
            </div>
            <span id="card-view-assignee-badge-{{ $task->id }}" 
                  style="font-size: 10px; font-weight: 700; color: {{ $hasAssignee ? '#3b82f6' : 'var(--color-text-secondary)' }}; background: {{ $hasAssignee ? 'rgba(59,130,246,0.1)' : 'var(--color-bg-secondary)' }}; border: 1px solid {{ $hasAssignee ? 'rgba(59,130,246,0.2)' : 'var(--color-border-primary)' }}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;" 
                  title="{{ $hasAssignee ? 'Assigned to ' . $assigneeFullName : 'No assignee' }}">
                <span>{{ $assigneeDisplay }}</span>
            </span>
        </div>

        {{-- Title --}}
        <h4 id="card-view-title-{{ $task->id }}" style="margin: 0 0 10px 0; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); line-height: 1.35; letter-spacing: -0.01em;">
            {{ $task->title }}
        </h4>

        {{-- Metadata Footer Row (matching reference screenshot) --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 6px; border-top: 1px solid var(--color-border-primary); margin-top: 4px;">
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                {{-- Assignee Avatar Initial (like the purple S in reference) --}}
                <div id="card-view-avatar-{{ $task->id }}" 
                     style="width: 22px; height: 22px; border-radius: 50%; background: #7c3aed; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(124,58,237,0.3);" 
                     title="{{ $assigneeFullName }}">
                    {{ $assigneeInitial }}
                </div>

                {{-- Due Date with Calendar icon --}}
                <div id="card-view-date-{{ $task->id }}" style="display: {{ $task->deadline ? 'inline-flex' : 'none' }}; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 600; color: var(--color-text-secondary);" title="Due {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y') : '' }}">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d') : '' }}</span>
                </div>

                {{-- Revision Due Date --}}
                @if($task->revision_deadline && ($task->revision_instructions || ($task->revisions && $task->revisions > 0)))
                <div style="display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 800; color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); padding: 1px 5px; border-radius: 4px;" title="Revision Due {{ \Carbon\Carbon::parse($task->revision_deadline)->format('M d, Y') }}">
                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Rev: {{ \Carbon\Carbon::parse($task->revision_deadline)->format('M d') }}</span>
                </div>
                @endif

                {{-- Reference Files / Links Indicator --}}
                @php
                    $cardRefFiles = $task->getReferenceFilesArray();
                    $cardRefUrls  = $task->getReferenceUrlsArray();
                    $cardTotalRefs = count($cardRefFiles) + count($cardRefUrls);
                @endphp
                @if($cardTotalRefs > 0)
                <div style="display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; color: #0284c7; background: rgba(2, 132, 199, 0.08); border: 1px solid rgba(2, 132, 199, 0.2); padding: 1px 5px; border-radius: 4px;" title="{{ $cardTotalRefs }} reference(s) attached">
                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span>Ref ({{ $cardTotalRefs }})</span>
                </div>
                @endif
            </div>

            {{-- Right Action/Info Icons: Priority Flag + Tag icon --}}
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <svg id="card-view-prio-{{ $task->id }}" width="12" height="12" fill="{{ $prioColor }}" viewBox="0 0 16 16" title="{{ $prio }}">
                    <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                </svg>
                <svg width="12" height="12" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" title="{{ $postTypeLabel }}">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- EXPANDED INLINE EDITOR (Managers and Approvers only)       --}}
    {{-- ========================================================= --}}
    @if($canManageDeliverables)
    <div class="kanban-card-editor" id="kanban-card-editor-{{ $task->id }}" style="display: none;" onclick="event.stopPropagation()">
        <form onsubmit="submitInlineCardEdit(event, {{ $task->id }})">
            {{-- Top Header: Stage on top very small --}}
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                <div style="display: inline-flex; align-items: center; gap: 4px;">
                    <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                        {{ $postTypeLabel }}
                    </span>
                    <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                        {{ $stage }}
                    </span>
                </div>
                <span style="font-size: 10px; font-weight: 700; color: {{ $hasAssignee ? '#3b82f6' : 'var(--color-text-secondary)' }}; background: {{ $hasAssignee ? 'rgba(59,130,246,0.1)' : 'var(--color-bg-secondary)' }}; border: 1px solid {{ $hasAssignee ? 'rgba(59,130,246,0.2)' : 'var(--color-border-primary)' }}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $assigneeDisplay }}
                </span>
            </div>

            {{-- Row 1: Title Input (clean, full-width) --}}
            <div style="margin-bottom: 8px;">
                <input type="text" 
                       name="task_title" 
                       id="card-input-title-{{ $task->id }}"
                       value="{{ $task->title }}"
                       placeholder="Task Name…" 
                       required
                       autocomplete="off"
                       style="width: 100%; border: none; background: transparent; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); outline: none; padding: 2px 0;"
                       onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitInlineCardEdit(event, {{ $task->id }}); } else if(event.key === 'Escape') { toggleCardEditor({{ $task->id }}, false); }">
            </div>

            {{-- Hidden post_type input (locked once set) --}}
            <input type="hidden" name="post_type" id="card-input-post-type-{{ $task->id }}" value="{{ $task->post_type }}">

            {{-- Row 2: Action Pickers with Icons (Matching reference layout) --}}
            <div style="display: flex; flex-direction: column; gap: 6px; padding-top: 6px; border-top: 1px dashed var(--color-border-primary);">
                
                {{-- Assignee --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <select name="writer_id" 
                            id="card-input-writer-{{ $task->id }}"
                            style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                        <option value="" style="background: var(--color-bg-primary); color: var(--color-text-primary);">Add assignee…</option>
                        @foreach($cardSelectableUsers as $u)
                            <option value="{{ $u->id }}" style="background: var(--color-bg-primary); color: var(--color-text-primary);" {{ ($task->writer_id == $u->id || (!$task->writer_id && $task->designer_id == $u->id)) ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Approver --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <select name="approver_id" 
                            id="card-input-approver-{{ $task->id }}"
                            style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                        <option value="" style="background: var(--color-bg-primary); color: var(--color-text-primary);">Add approver…</option>
                        @foreach($cardSelectableUsers as $u)
                            <option value="{{ $u->id }}" style="background: var(--color-bg-primary); color: var(--color-text-primary);" {{ ($task->approver_id == $u->id || (!$task->approver_id && $task->brand_manager_id == $u->id)) ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <input type="date" 
                           name="deadline" 
                           id="card-input-deadline-{{ $task->id }}"
                           value="{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') : '' }}"
                           placeholder="Add dates…"
                           style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                </div>

                {{-- Priority --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                    <select name="priority" 
                            id="card-input-priority-{{ $task->id }}"
                            style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                        <option value="Medium" style="background: var(--color-bg-primary); color: var(--color-text-primary);" {{ $task->priority === 'Medium' ? 'selected' : '' }}>Add priority (Medium)</option>
                        <option value="High Priority" style="background: var(--color-bg-primary); color: var(--color-text-primary);" {{ in_array($task->priority, ['High Priority', 'High']) ? 'selected' : '' }}>High Priority</option>
                        <option value="Urgent" style="background: var(--color-bg-primary); color: var(--color-text-primary);" {{ $task->priority === 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Low" style="background: var(--color-bg-primary); color: var(--color-text-primary);" {{ $task->priority === 'Low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
            </div>

            {{-- Footer actions: Details on left, Cancel & Save on right (All buttons at the bottom) --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--color-border-primary);">
                <button type="button" 
                        onclick="event.stopPropagation(); toggleCardEditor({{ $task->id }}, false); openTaskModal({{ $task->id }})"
                        style="background: transparent; border: none; font-size: 11px; font-weight: 600; color: #3b82f6; cursor: pointer; padding: 3px 4px; display: inline-flex; align-items: center; gap: 3px;"
                        title="Open full view with briefs, assets and artwork">
                    <span>Details</span>
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </button>
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <button type="button" 
                            onclick="event.stopPropagation(); toggleCardEditor({{ $task->id }}, false)"
                            style="background: transparent; border: none; font-size: 11.5px; font-weight: 700; color: var(--color-text-secondary); cursor: pointer; padding: 4px 8px; border-radius: 6px;">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="card-btn-save-{{ $task->id }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-md bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors border-0 cursor-pointer">
                        <span>Save</span>
                        <span style="font-size: 11px; opacity: 0.7;">↵</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
