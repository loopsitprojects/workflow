@php
    $tasks = $project->deliverables->whereNull('parent_deliverable_id');
    $tasks->loadMissing(['revisionsHistory.user', 'revisionsHistory.fixedByUser', 'approvalsHistory.user']);

    $todoTasks = $tasks->filter(function($t) {
        $st = strtolower(trim($t->status ?? ''));
        return in_array($st, ['to do', 'to commence', 'not started', 'todo']) || empty($st);
    });

    $inProgressTasks = $tasks->filter(function($t) {
        $st = strtolower(trim($t->status ?? ''));
        return in_array($st, ['in progress', 'inprogress', 'on hold']);
    });

    $approvalTasks = $tasks->filter(function($t) {
        $st = strtolower(trim($t->status ?? ''));
        return in_array($st, ['approval stage', 'approval', 'review', 'under review', 'waiting for approval', 'waiting for feedback']);
    });

    $completeTasks = $tasks->filter(function($t) {
        $st = strtolower(trim($t->status ?? ''));
        return in_array($st, ['done', 'completed', 'complete']);
    });

    $brandMembers = $project->brand?->members ?? collect();
    $selectableUsers = $brandMembers
        ->merge($writers ?? collect())
        ->merge($designers ?? collect())
        ->merge($brandManagers ?? collect())
        ->merge($approvers ?? collect())
        ->merge($coordinators ?? collect())
        ->unique('id')
        ->values();

    if ($selectableUsers->isEmpty()) {
        $selectableUsers = \App\Models\User::orderBy('name')->get();
    }

    $subtaskTypes = \App\Models\SubtaskType::whereIn('workflow_type', [$project->workflow_type, 'all'])->orWhereNull('workflow_type')->get();
    if ($subtaskTypes->isEmpty()) {
        $subtaskTypes = \App\Models\SubtaskType::all();
    }
    if ($subtaskTypes->isEmpty()) {
        $subtaskTypes = collect([
            (object)['id' => 1, 'name' => 'Outlines'],
            (object)['id' => 2, 'name' => 'Static Post'],
            (object)['id' => 3, 'name' => 'Carousel'],
            (object)['id' => 4, 'name' => 'Reel'],
            (object)['id' => 5, 'name' => 'Presentation'],
            (object)['id' => 6, 'name' => 'Pitch Deck'],
        ]);
    }
    $currentKanbanUser = auth()->user();
    $currentKanbanUserId = auth()->id();
    $canManageDeliverables = $currentKanbanUser && (
        $currentKanbanUser->isAdmin() ||
        in_array($currentKanbanUser->role, ['Admin', 'Owner', 'Operations Manager', 'Brand Manager', 'Approver', 'Approver Coordinator']) ||
        $project->lead_id == $currentKanbanUserId ||
        $project->brand_manager_id == $currentKanbanUserId ||
        $project->approver_id == $currentKanbanUserId
    );

    $selectableApprovers = $selectableUsers->filter(function($u) {
        return in_array($u->role, ['Admin', 'Owner', 'Operations Manager', 'Brand Manager', 'Approver', 'Approver Coordinator']);
    })->values();
    if ($selectableApprovers->isEmpty()) {
        $selectableApprovers = $selectableUsers;
    }
@endphp

<div class="campaign-kanban-wrapper" style="margin-bottom: 30px; overflow-x: auto; padding-bottom: 12px;">
    {{-- Kanban Board Grid --}}
    <div style="display: grid; grid-template-columns: repeat(4, minmax(280px, 1fr)); gap: 18px; align-items: start; min-width: 1160px;">
        
        {{-- ========================================================= --}}
        {{-- COLUMN 1: TO DO                                           --}}
        {{-- ========================================================= --}}
        <div class="kanban-column" 
             id="kanban-col-todo" 
             data-status="To Do"
             ondragover="kanbanOnDragOver(event)" 
             ondragleave="kanbanOnDragLeave(event)" 
             ondrop="kanbanOnDrop(event, 'To Do')"
             style="background: var(--bg-column); border: 1px solid var(--border-column); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: var(--bg-card); border: 1px solid var(--border-card); color: var(--text-card-title); font-size: 11px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #94a3b8;"></span>
                        <span>TO DO</span>
                    </div>
                    <span id="badge-count-todo" style="font-size: 12px; font-weight: 800; color: var(--text-card-sub);">{{ $todoTasks->count() }}</span>
                </div>
            </div>

            @if($canManageDeliverables)
            {{-- Inline Add Task Card for TO DO --}}
            <div id="inline-creator-todo" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'To Do'])
            </div>
            @endif

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-todo" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($todoTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            @if($canManageDeliverables)
            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-todo"
                    onclick="openInlineCreator('todo')"
                    class="kanban-add-task-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
            @endif
        </div>

        {{-- ========================================================= --}}
        {{-- COLUMN 2: IN PROGRESS                                     --}}
        {{-- ========================================================= --}}
        <div class="kanban-column" 
             id="kanban-col-inprogress" 
             data-status="In Progress"
             ondragover="kanbanOnDragOver(event)" 
             ondragleave="kanbanOnDragLeave(event)" 
             ondrop="kanbanOnDrop(event, 'In Progress')"
             style="background: var(--bg-column); border: 1px solid var(--border-column); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.25); color: #3b82f6; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #3b82f6;"></span>
                        <span>IN PROGRESS</span>
                    </div>
                    <span id="badge-count-inprogress" style="font-size: 12px; font-weight: 800; color: #3b82f6;">{{ $inProgressTasks->count() }}</span>
                </div>
            </div>

            @if($canManageDeliverables)
            {{-- Inline Add Task Card for IN PROGRESS --}}
            <div id="inline-creator-inprogress" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'In Progress'])
            </div>
            @endif

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-inprogress" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($inProgressTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            @if($canManageDeliverables)
            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-inprogress"
                    onclick="openInlineCreator('inprogress')"
                    class="kanban-add-task-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
            @endif
        </div>

        {{-- ========================================================= --}}
        {{-- COLUMN 3: APPROVAL STAGE                                  --}}
        {{-- ========================================================= --}}
        <div class="kanban-column" 
             id="kanban-col-approval" 
             data-status="Approval Stage"
             ondragover="kanbanOnDragOver(event)" 
             ondragleave="kanbanOnDragLeave(event)" 
             ondrop="kanbanOnDrop(event, 'Approval Stage')"
             style="background: var(--bg-column); border: 1px solid var(--border-column); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); color: #f59e0b; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #f59e0b;"></span>
                        <span>APPROVAL STAGE</span>
                    </div>
                    <span id="badge-count-approval" style="font-size: 12px; font-weight: 800; color: #f59e0b;">{{ $approvalTasks->count() }}</span>
                </div>
            </div>

            @if($canManageDeliverables)
            {{-- Inline Add Task Card for APPROVAL STAGE --}}
            <div id="inline-creator-approval" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'Approval Stage'])
            </div>
            @endif

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-approval" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($approvalTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            @if($canManageDeliverables)
            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-approval"
                    onclick="openInlineCreator('approval')"
                    class="kanban-add-task-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
            @endif
        </div>

        {{-- ========================================================= --}}
        {{-- COLUMN 4: COMPLETE                                        --}}
        {{-- ========================================================= --}}
        <div class="kanban-column" 
             id="kanban-col-complete" 
             data-status="Done"
             ondragover="kanbanOnDragOver(event)" 
             ondragleave="kanbanOnDragLeave(event)" 
             ondrop="kanbanOnDrop(event, 'Done')"
             style="background: var(--bg-column); border: 1px solid var(--border-column); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); color: #10b981; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                        <span>COMPLETE</span>
                    </div>
                    <span id="badge-count-complete" style="font-size: 12px; font-weight: 800; color: #10b981;">{{ $completeTasks->count() }}</span>
                </div>
            </div>

            @if($canManageDeliverables)
            {{-- Inline Add Task Card for COMPLETE --}}
            <div id="inline-creator-complete" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'Done'])
            </div>
            @endif

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-complete" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($completeTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            @if($canManageDeliverables)
            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-complete"
                    onclick="openInlineCreator('complete')"
                    class="kanban-add-task-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
            @endif
        </div>

    </div>
</div>

{{-- Kanban Drag & Drop and Quick Creator JavaScript --}}
<script>
let draggedTaskId = null;
let draggedCardElement = null;
let currentlyEditingCardId = null;
let justOpenedCardId = null;
let justOpenedInlineCreator = null;
let isDraggingKanbanCard = false;
let pendingApproverAction = null;
let pendingRevisionAction = null;

const kanbanSelectableUsers = @json($selectableUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role]));
const kanbanSelectableApprovers = @json($selectableApprovers->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role]));
const kanbanSubtaskTypes = @json($subtaskTypes->map(fn($st) => ['name' => $st->name]));
const kanbanCurrentUserId = {{ auth()->id() ?? 'null' }};
const kanbanCurrentUserRole = "{{ auth()->user()?->role ?? '' }}";
const kanbanIsAdmin = {{ (auth()->user() && auth()->user()->isAdmin()) ? 'true' : 'false' }};
const kanbanIsAdminOrManager = {{ (auth()->user() && (auth()->user()->isAdmin() || in_array(auth()->user()->role, ['Admin', 'Owner', 'Operations Manager']))) ? 'true' : 'false' }};
const kanbanCanManageDeliverables = {{ $canManageDeliverables ? 'true' : 'false' }};
const kanbanProjectApproverId = {{ $project->approver_id ?? ($project->brand_manager_id ?? ($project->lead_id ?? 'null')) }};

const campaignDeliverablesData = {};
@foreach($tasks as $t)
@php
    $dlRaw = null;
    $dlFmt = null;
    if (!empty($t->deadline)) {
        try {
            $parsed = \Carbon\Carbon::parse($t->deadline);
            $dlRaw = $parsed->format('Y-m-d');
            $dlFmt = $parsed->format('d M, Y');
        } catch (\Throwable $e) {
            $dlRaw = (string)$t->deadline;
            $dlFmt = (string)$t->deadline;
        }
    }
    $createdFmt = null;
    if (!empty($t->created_at)) {
        try {
            $createdFmt = \Carbon\Carbon::parse($t->created_at)->format('d M, Y');
        } catch (\Throwable $e) {
            $createdFmt = (string)$t->created_at;
        }
    }
    $tRevsList = $t->revisionsHistory->map(function($r, $idx) use ($t) {
        return [
            'round' => (int)($r->round ?? ($t->revisionsHistory->count() - $idx)),
            'user_name' => $r->user?->name ?? 'Approver',
            'instructions' => $r->instructions,
            'stage' => $r->stage_at_revision ?? 'Approval Stage',
            'image_path' => $r->image_path ? asset('storage/' . $r->image_path) : null,
            'revision_deadline' => $r->revision_deadline ? \Carbon\Carbon::parse($r->revision_deadline)->format('d M, Y') : null,
            'date' => $r->created_at ? $r->created_at->format('d M, Y h:i A') : '',
            'created_at' => $r->created_at?->toISOString(),
            'fixed_at' => $r->fixed_at ? \Carbon\Carbon::parse($r->fixed_at)->format('d M, Y h:i A') : null,
            'fixed_by_user_name' => $r->fixedByUser?->name ?? null,
        ];
    })->values();

    $tApprovalsList = $t->approvalsHistory->map(function($a) {
        return [
            'user_name' => $a->user?->name ?? 'Team Member',
            'stage' => $a->stage,
            'notes' => $a->notes,
            'date' => $a->created_at ? $a->created_at->format('d M, Y h:i A') : '',
            'created_at' => $a->created_at?->toISOString(),
        ];
    })->values();
@endphp
campaignDeliverablesData[{{ $t->id }}] = {
    id: {{ $t->id }},
    title: @json($t->title),
    post_type: @json($t->post_type ?? 'Outlines'),
    status: @json($t->status ?? 'To Do'),
    priority: @json($t->priority ?? 'Medium'),
    deadline: @json($dlRaw),
    deadline_formatted: @json($dlFmt),
    concept: @json($t->concept ?? ''),
    caption: @json($t->caption ?? ''),
    post_copy: @json($t->post_copy ?? ($t->subtask_copy ?? '')),
    notes: @json($t->notes ?? ''),
    description: @json($t->description ?? ''),
    reference_files_list: @json($t->getReferenceFilesArray()),
    reference_urls_list: @json($t->getReferenceUrlsArray()),
    assignee_name: @json($t->writer?->name ?? ($t->designer?->name ?? 'Unassigned')),
    approver_name: @json($t->approver?->name ?? null),
    created_at_formatted: @json($createdFmt),
    revisions: {{ (int)($t->revisions ?? 0) }},
    revision_instructions: @json($t->revision_instructions ?? ''),
    revision_deadline: @json($t->revision_deadline ? \Carbon\Carbon::parse($t->revision_deadline)->format('d M, Y') : null),
    revisions_history: @json($tRevsList),
    approvals_history: @json($tApprovalsList)
};
@endforeach

function isAssignStageName(val) {
    const s = (val || '').toLowerCase().trim();
    return !s || s === 'assign' || s === 'assignee' || s === 'todo' || s === 'to do' || s === 'in progress' || s === 'writer';
}

function checkCardMovePermission(d) {
    if (kanbanIsAdminOrManager) {
        return { canMove: true, reason: '' };
    }
    const status = (d.status || 'To Do').trim();
    const statusLower = status.toLowerCase();
    const isInApproval = ['approval stage', 'approval', 'review', 'under review'].includes(statusLower);
    const isDone = ['done', 'completed', 'complete'].includes(statusLower);

    const assignedId = d.writer_id || d.designer_id || null;
    const isAssigned = (assignedId && kanbanCurrentUserId == assignedId);
    const isUnassigned = !assignedId;

    const approverId = d.approver_id || kanbanProjectApproverId;
    const isApprover = (approverId && kanbanCurrentUserId == approverId);

    if (isInApproval) {
        if (isApprover) {
            return { canMove: true, reason: '' };
        }
        const approverName = d.approver_name || 'the assigned approver';
        return { canMove: false, reason: `Submitted to ${approverName}. Only that approver can approve or return this deliverable.` };
    }

    if (isDone) {
        if (isApprover) {
            return { canMove: true, reason: '' };
        }
        return { canMove: false, reason: 'This deliverable is completed. Only an approver or admin can move it.' };
    }

    // In To Do or In Progress:
    if (isUnassigned) {
        return { canMove: false, reason: 'Please assign a team member before moving this deliverable.' };
    }

    if (isAssigned) {
        return { canMove: true, reason: '' };
    }

    const assigneeName = d.assignee_name || 'the assigned person';
    return { canMove: false, reason: `Only the assigned person (${assigneeName}) can move this deliverable.` };
}

function isCardEditorOpen(taskId) {
    return currentlyEditingCardId === taskId;
}

function handleCardClick(taskId) {
    if (isDraggingKanbanCard) return;
    if (currentlyEditingCardId === taskId) return;
    toggleCardEditor(taskId, true);
}

function toggleCardEditor(taskId, open) {
    const card = document.getElementById('kanban-card-' + taskId);
    const view = document.getElementById('kanban-card-view-' + taskId);
    const editor = document.getElementById('kanban-card-editor-' + taskId);
    if (!card || !view || !editor) return;

    if (open) {
        // Close any other open editor
        if (currentlyEditingCardId && currentlyEditingCardId !== taskId) {
            toggleCardEditor(currentlyEditingCardId, false);
        }
        currentlyEditingCardId = taskId;
        justOpenedCardId = taskId;
        setTimeout(() => {
            if (justOpenedCardId === taskId) justOpenedCardId = null;
        }, 100);

        card.setAttribute('draggable', 'false');
        card.style.border = '1.5px solid #64748b';
        card.style.boxShadow = '0 6px 18px rgba(0,0,0,0.08)';
        card.style.cursor = 'default';
        view.style.display = 'none';
        editor.style.display = 'block';

        const titleInput = document.getElementById('card-input-title-' + taskId);
        if (titleInput) {
            setTimeout(() => {
                titleInput.focus();
                titleInput.select();
            }, 50);
        }
    } else {
        if (currentlyEditingCardId === taskId) {
            currentlyEditingCardId = null;
        }
        const canMove = card.getAttribute('data-can-move') !== 'false';
        card.setAttribute('draggable', canMove ? 'true' : 'false');
        // Restore border based on status
        const status = card.getAttribute('data-status') || 'To Do';
        let statusBorderColor = 'var(--color-border-primary)';
        if (['Done', 'Completed', 'Complete'].includes(status)) statusBorderColor = '#10b981';
        else if (['Approval Stage', 'Approval', 'Review', 'Under Review'].includes(status)) statusBorderColor = '#f59e0b';
        else if (['In Progress', 'on hold'].includes(status)) statusBorderColor = '#6366f1';
        card.style.border = '1px solid var(--color-border-primary)';
        card.style.borderLeft = '3px solid ' + statusBorderColor;
        card.style.boxShadow = '0 1px 4px rgba(0,0,0,0.03)';
        card.style.cursor = canMove ? 'grab' : 'pointer';
        editor.style.display = 'none';
        view.style.display = 'block';
    }
}

async function submitInlineCardEdit(event, taskId) {
    event.preventDefault();
    const card = document.getElementById('kanban-card-' + taskId);
    const editor = document.getElementById('kanban-card-editor-' + taskId);
    if (!card || !editor) return;

    const titleInput = document.getElementById('card-input-title-' + taskId);
    const postTypeSelect = document.getElementById('card-input-post-type-' + taskId);
    const writerSelect = document.getElementById('card-input-writer-' + taskId);
    const deadlineInput = document.getElementById('card-input-deadline-' + taskId);
    const prioritySelect = document.getElementById('card-input-priority-' + taskId);

    const title = titleInput ? titleInput.value.trim() : '';
    if (!title) {
        alert('Please enter a task name.');
        if (titleInput) titleInput.focus();
        return;
    }

    const postType = postTypeSelect ? postTypeSelect.value : '';
    const writerId = writerSelect && writerSelect.value ? writerSelect.value : '';
    const approverSelect = document.getElementById('card-input-approver-' + taskId);
    const approverId = approverSelect && approverSelect.value ? approverSelect.value : '';
    const deadline = deadlineInput && deadlineInput.value ? deadlineInput.value : '';
    const priority = prioritySelect ? prioritySelect.value : 'Medium';

    if (deadline && deadlineInput && deadline !== deadlineInput.defaultValue) {
        const selDate = new Date(deadline + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selDate < today) {
            alert('Due date cannot be before today.');
            if (deadlineInput) deadlineInput.focus();
            return;
        }
    }

    const saveBtn = document.getElementById('card-btn-save-' + taskId);
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span>Saving…</span>';
    }

    try {
        const response = await fetch(`/deliverables/${taskId}/campaign-kanban/quick-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                title: title,
                post_type: postType,
                writer_id: writerId,
                approver_id: approverId,
                deadline: deadline,
                priority: priority,
            })
        });

        const result = await response.json();
        if (response.ok && result.success && result.deliverable) {
            const d = result.deliverable;
            updateCardViewElements(taskId, d);
            toggleCardEditor(taskId, false);
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: result.message || 'Deliverable updated', type: 'success' } }));
        } else {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: result.message || 'Failed to update deliverable.', type: 'error' } }));
        }
    } catch (err) {
        console.error('Error updating card inline:', err);
        alert('Network error while updating deliverable.');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<span>Save</span><span style="font-size:11px; opacity:0.7;">↵</span>';
        }
    }
}

function updateCardViewElements(taskId, d) {
    const card = document.getElementById('kanban-card-' + taskId);
    if (card) {
        if (d.approver_id) card.setAttribute('data-approver-id', d.approver_id);
        if (d.approver_name) card.setAttribute('data-approver-name', d.approver_name);
        const perm = checkCardMovePermission(d);
        card.setAttribute('data-can-move', perm.canMove ? 'true' : 'false');
        card.setAttribute('data-restriction-reason', perm.reason);
        card.setAttribute('draggable', perm.canMove ? 'true' : 'false');
        card.style.cursor = perm.canMove ? 'grab' : 'pointer';
        card.title = perm.reason;
    }

    const titleEl = document.getElementById('card-view-title-' + taskId);
    if (titleEl) titleEl.textContent = d.title;

    const postTypeEl = document.getElementById('card-view-post-type-' + taskId);
    if (postTypeEl) postTypeEl.textContent = d.post_type || 'Deliverable';

    const avatarEl = document.getElementById('card-view-avatar-' + taskId);
    const initial = (d.assignee_name && d.assignee_name !== 'Unassigned') ? d.assignee_name.trim().charAt(0).toUpperCase() : 'U';
    if (avatarEl) {
        avatarEl.textContent = initial;
        avatarEl.title = d.assignee_name || 'Unassigned';
    }

    const dateContainer = document.getElementById('card-view-date-' + taskId);
    if (dateContainer) {
        if (d.deadline) {
            try {
                const dt = new Date(d.deadline);
                const formatted = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                dateContainer.style.display = 'inline-flex';
                const span = dateContainer.querySelector('span');
                if (span) span.textContent = formatted;
            } catch(e) {
                dateContainer.style.display = 'inline-flex';
                const span = dateContainer.querySelector('span');
                if (span) span.textContent = d.deadline;
            }
        } else {
            dateContainer.style.display = 'none';
        }
    }

    const prioFlag = document.getElementById('card-view-prio-' + taskId);
    if (prioFlag) {
        const prioColor = (d.priority === 'High Priority' || d.priority === 'Urgent' || d.priority === 'High') ? '#ef4444' : (d.priority === 'Low' ? '#64748b' : '#10b981');
        prioFlag.setAttribute('fill', prioColor);
        prioFlag.title = d.priority || 'Medium';
    }

    const assigneeBadge = document.getElementById('card-view-assignee-badge-' + taskId);
    if (assigneeBadge) {
        const hasAssignee = d.assignee_name && d.assignee_name !== 'Unassigned';
        const name = hasAssignee ? d.assignee_name : 'Unassigned';
        const span = assigneeBadge.querySelector('span') || assigneeBadge;
        span.textContent = name;
        if (hasAssignee) {
            assigneeBadge.style.color = '#3b82f6';
            assigneeBadge.style.background = 'rgba(59,130,246,0.1)';
            assigneeBadge.style.border = '1px solid rgba(59,130,246,0.2)';
            assigneeBadge.title = 'Assigned to ' + name;
        } else {
            assigneeBadge.style.color = 'var(--color-text-secondary)';
            assigneeBadge.style.background = 'var(--color-bg-secondary)';
            assigneeBadge.style.border = '1px solid var(--color-border-primary)';
            assigneeBadge.title = 'No assignee';
        }
    }

    const stageBadge = document.getElementById('card-view-stage-' + taskId);
    if (stageBadge) {
        const stg = d.approval_stage || d.stage || '';
        if (isAssignStageName(stg)) {
            stageBadge.style.display = 'none';
        } else if (stg) {
            stageBadge.textContent = stg;
            stageBadge.style.display = 'inline-flex';
        }
    }

    if (d.approver_name) {
        const approverNameEl = document.getElementById('card-view-approver-name-' + taskId);
        if (approverNameEl) {
            approverNameEl.textContent = d.approver_name;
            approverNameEl.style.color = 'var(--color-text-primary)';
        }
    }

    if (typeof d.revisions !== 'undefined' || typeof d.revision_instructions !== 'undefined') {
        const revCount = d.revisions || 0;
        const revMsg = d.revision_instructions || '';
        const hasRevs = (revCount > 0 || Boolean(revMsg));

        const vBadge = document.getElementById('card-view-revision-badge-' + taskId);
        const vNum = document.getElementById('card-view-revision-num-' + taskId);
        if (vBadge) {
            vBadge.style.display = hasRevs ? 'inline-flex' : 'none';
            vBadge.title = revMsg ? ('Revision alert: ' + revMsg) : ('Revision Round ' + (revCount || 1));
        }
        if (vNum) vNum.textContent = revCount || 1;

        const eBadge = document.getElementById('card-editor-revision-badge-' + taskId);
        const eNum = document.getElementById('card-editor-revision-num-' + taskId);
        if (eBadge) eBadge.style.display = hasRevs ? 'inline-flex' : 'none';
        if (eNum) eNum.textContent = revCount || 1;

        const eBox = document.getElementById('card-editor-revision-box-' + taskId);
        const eText = document.getElementById('card-editor-revision-text-' + taskId);
        const eBoxNum = document.getElementById('card-editor-revision-box-num-' + taskId);
        if (eBox) eBox.style.display = revMsg ? 'block' : 'none';
        if (eText) eText.textContent = revMsg;
        if (eBoxNum) eBoxNum.textContent = 'Round ' + (revCount || 1);

        const roBadge = document.getElementById('card-readonly-revision-badge-' + taskId);
        const roNum = document.getElementById('card-readonly-revision-num-' + taskId);
        if (roBadge) roBadge.style.display = hasRevs ? 'inline-flex' : 'none';
        if (roNum) roNum.textContent = revCount || 1;

        const roBox = document.getElementById('card-readonly-revision-box-' + taskId);
        const roText = document.getElementById('card-readonly-revision-text-' + taskId);
        const roBoxNum = document.getElementById('card-readonly-revision-box-num-' + taskId);
        if (roBox) roBox.style.display = revMsg ? 'block' : 'none';
        if (roText) roText.textContent = revMsg;
        if (roBoxNum) roBoxNum.textContent = 'Round ' + (revCount || 1);

        const roRow = document.getElementById('card-readonly-revisions-row-' + taskId);
        const roTotal = document.getElementById('card-readonly-revisions-total-' + taskId);
        if (roRow) roRow.style.display = hasRevs ? 'flex' : 'none';
        if (roTotal) roTotal.textContent = revCount || 1;
    }
}

async function quickUploadCardFile(event, taskId) {
    const input = event.target;
    if (!input || !input.files || input.files.length === 0) return;

    const files = Array.from(input.files);
    const card = document.getElementById('kanban-card-' + taskId);
    const label = document.getElementById('card-upload-label-' + taskId);
    const origLabel = label ? label.innerHTML : 'Upload Work (Image or File)';

    if (label) {
        label.innerHTML = `<span style="display:inline-flex;align-items:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity:0.75;"></path></svg> Uploading...</span>`;
    }

    const fd = new FormData();
    fd.append('action', 'save_only');
    files.forEach(f => fd.append('final_designs_files[]', f));
    if (files.length === 1) fd.append('final_designs_file', files[0]);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        const res = await fetch(`/deliverables/${taskId}/submit`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: fd
        });

        const data = await res.json();
        if (data.success && data.deliverable) {
            if (card) {
                card.setAttribute('data-has-file', 'true');
            }
            if (window.projectDeliverables && window.projectDeliverables[taskId]) {
                window.projectDeliverables[taskId] = data.deliverable || window.projectDeliverables[taskId];
            }
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Deliverable file(s) uploaded successfully!', type: 'success' } }));
            updateCardArtworkUI(taskId, data.deliverable);
        } else {
            if (label) label.innerHTML = origLabel;
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Failed to upload file.', type: 'error' } }));
        }
    } catch (err) {
        console.error('Quick file upload error:', err);
        if (label) label.innerHTML = origLabel;
        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Network error uploading file.', type: 'error' } }));
    }
}

async function quickRemoveDeliverableFile(taskId, fileIndex) {
    if (!confirm('Are you sure you want to remove this uploaded work file?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    try {
        const res = await fetch(`/deliverables/${taskId}/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({
                delete_final_designs_file_index: fileIndex
            })
        });
        const data = await res.json();
        if (data.success && data.deliverable) {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'File removed successfully', type: 'success' } }));
            if (window.projectDeliverables && window.projectDeliverables[taskId]) {
                window.projectDeliverables[taskId] = data.deliverable;
            }
            updateCardArtworkUI(taskId, data.deliverable);
        } else {
            alert(data.message || 'Failed to remove file.');
        }
    } catch (err) {
        console.error('Error removing file:', err);
        alert('Network error while removing file.');
    }
}

function previewDeliverableArtwork(elOrUrl, filename, downloadUrl) {
    let fUrl = '';
    let fName = '';
    let dUrl = '';

    if (typeof elOrUrl === 'object' && elOrUrl !== null) {
        fUrl = elOrUrl.dataset.fileUrl || elOrUrl.getAttribute('data-file-url') || '';
        fName = elOrUrl.dataset.filename || elOrUrl.getAttribute('data-filename') || '';
        dUrl = elOrUrl.dataset.downloadUrl || elOrUrl.getAttribute('data-download-url') || '';
    } else {
        fUrl = elOrUrl || '';
        fName = filename || '';
        dUrl = downloadUrl || '';
    }

    if (!fUrl) return;

    const modal = document.getElementById('artworkPreviewModal');
    if (!modal) return;

    const titleEl = document.getElementById('artworkPreviewTitle');
    if (titleEl) titleEl.textContent = fName || 'Deliverable File Preview';

    const ext = (fName.split('.').pop() || '').toLowerCase();
    const metaEl = document.getElementById('artworkPreviewMeta');
    if (metaEl) {
        metaEl.textContent = ext ? `${ext.toUpperCase()} File` : 'Uploaded Deliverable Work';
    }

    const dlBtn = document.getElementById('artworkPreviewDownloadBtn');
    if (dlBtn) {
        dlBtn.href = dUrl || fUrl;
        dlBtn.setAttribute('download', fName || 'download');
    }

    const isImg = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext);
    const isPdf = ext === 'pdf';
    const isVideo = ['mp4', 'webm', 'ogg', 'mov'].includes(ext);

    const body = document.getElementById('artworkPreviewContent');
    if (body) {
        if (isImg) {
            body.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; max-height: 75vh;">
                    <img src="${escapeHtml(fUrl)}" alt="${escapeHtml(fName)}" style="max-height: 72vh; max-width: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.18);">
                </div>
            `;
        } else if (isPdf) {
            body.innerHTML = `
                <div style="width: 100%; height: 75vh; border-radius: 8px; overflow: hidden; background: #525659;">
                    <iframe src="${escapeHtml(fUrl)}" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            `;
        } else if (isVideo) {
            body.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; width: 100%; max-height: 75vh; background: #000; border-radius: 8px; overflow: hidden;">
                    <video src="${escapeHtml(fUrl)}" controls autoplay style="max-height: 72vh; max-width: 100%;"></video>
                </div>
            `;
        } else {
            body.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px 24px; text-align: center; background: var(--color-bg-secondary); border-radius: 12px; border: 1.5px dashed var(--color-border-primary); max-width: 440px; margin: auto;">
                    <div style="width: 64px; height: 64px; border-radius: 16px; background: rgba(59,130,246,0.12); color: #2563eb; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                        <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: var(--color-text-primary); word-break: break-all;">${escapeHtml(fName)}</h4>
                    <p style="margin: 0 0 20px 0; font-size: 12.5px; color: var(--color-text-secondary); line-height: 1.4;">This file type cannot be previewed directly in the browser. Click below to download and view on your device.</p>
                    <a href="${escapeHtml(dUrl || fUrl)}" download style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 8px; background: #10b981; color: #ffffff; font-size: 13px; font-weight: 700; text-decoration: none; box-shadow: 0 2px 8px rgba(16,185,129,0.35);">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download File</span>
                    </a>
                </div>
            `;
        }
    }

    modal.style.display = 'flex';
}

function closeArtworkPreviewModal() {
    const modal = document.getElementById('artworkPreviewModal');
    if (modal) {
        modal.style.display = 'none';
        const body = document.getElementById('artworkPreviewContent');
        if (body) body.innerHTML = '';
    }
}

function updateCardArtworkUI(taskId, d) {
    const card = document.getElementById('kanban-card-' + taskId);
    if (!card) return;

    let files = d.final_designs_list || [];
    if (!Array.isArray(files)) {
        files = files ? [files] : [];
    }
    const hasFiles = files.length > 0;
    card.setAttribute('data-has-file', hasFiles ? 'true' : 'false');

    const canUpload = card.getAttribute('data-can-manage') === 'true' || kanbanIsAdmin || (d.writer_id && kanbanCurrentUserId == d.writer_id);
    const status = card.getAttribute('data-status') || '';
    const isToDo = (status === 'To Do');

    // Update compact Work badge on the outside card face
    const workBadge = document.getElementById('card-view-work-badge-' + taskId);
    const workCount = document.getElementById('card-view-work-count-' + taskId);
    if (workBadge) {
        if (hasFiles) {
            workBadge.style.display = 'inline-flex';
            if (workCount) workCount.textContent = files.length;
            workBadge.title = `${files.length} deliverable file(s) uploaded`;
        } else {
            workBadge.style.display = 'none';
        }
    }

    let uploadBox = document.getElementById('card-upload-box-' + taskId);
    const editor = document.getElementById('kanban-card-editor-' + taskId);
    if (!uploadBox && editor) {
        uploadBox = document.createElement('div');
        uploadBox.id = 'card-upload-box-' + taskId;
        uploadBox.style.cssText = 'margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--color-border-primary);';
        uploadBox.onclick = (e) => e.stopPropagation();

        const formOrDiv = editor.querySelector('form') || editor.firstElementChild;
        const footer = (formOrDiv || editor).querySelector('div[style*="justify-content: space-between"]:last-child');
        if (footer && footer.parentNode) {
            footer.parentNode.insertBefore(uploadBox, footer);
        } else {
            (formOrDiv || editor).appendChild(uploadBox);
        }
    } else if (uploadBox && editor && !editor.contains(uploadBox)) {
        const formOrDiv = editor.querySelector('form') || editor.firstElementChild;
        const footer = (formOrDiv || editor).querySelector('div[style*="justify-content: space-between"]:last-child');
        if (footer && footer.parentNode) {
            footer.parentNode.insertBefore(uploadBox, footer);
        } else {
            (formOrDiv || editor).appendChild(uploadBox);
        }
    }

    if (!uploadBox) return;

    if (hasFiles) {
        let itemsHtml = '';
        files.forEach((fUrl, idx) => {
            const cleanUrl = fUrl.split('?')[0];
            const rawFilename = cleanUrl.substring(cleanUrl.lastIndexOf('/') + 1);
            let filename = decodeURIComponent(rawFilename);
            if (!filename) filename = 'File ' + (idx + 1);
            const ext = filename.split('.').pop().toLowerCase();
            const isImg = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext);

            const iconHtml = isImg
                ? `<img src="${fUrl}" alt="" style="width: 18px; height: 18px; border-radius: 3px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--color-border-primary);">`
                : `<div style="width: 18px; height: 18px; border-radius: 3px; background: rgba(59,130,246,0.12); color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>`;

            const downloadUrl = `/deliverables/${taskId}/download-artwork/${idx}`;

            itemsHtml += `
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; padding: 4px 6px; border-radius: 6px; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                    <div style="display: flex; align-items: center; gap: 5px; min-width: 0; flex: 1; cursor: pointer;" 
                         data-file-url="${escapeHtml(fUrl)}" 
                         data-filename="${escapeHtml(filename)}" 
                         data-download-url="${escapeHtml(downloadUrl)}" 
                         onclick="event.stopPropagation(); previewDeliverableArtwork(this);" 
                         title="Click to view file">
                        ${iconHtml}
                        <span style="font-size: 11px; font-weight: 600; color: var(--color-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(filename)}">${escapeHtml(filename)}</span>
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;">
                        <button type="button" 
                                data-file-url="${escapeHtml(fUrl)}" 
                                data-filename="${escapeHtml(filename)}" 
                                data-download-url="${escapeHtml(downloadUrl)}" 
                                onclick="event.stopPropagation(); previewDeliverableArtwork(this);" 
                                style="font-size: 10px; font-weight: 700; color: #3b82f6; border: none; cursor: pointer; padding: 2px 7px; border-radius: 4px; background: rgba(59,130,246,0.1); line-height: 1.4;" 
                                title="View in popup">View</button>
                        ${canUpload ? `<button type="button" onclick="event.stopPropagation(); quickRemoveDeliverableFile(${taskId}, ${idx})" style="background: transparent; border: none; cursor: pointer; color: #ef4444; font-size: 11px; font-weight: 800; padding: 1px 3px; border-radius: 3px; line-height: 1;" title="Remove this file">✕</button>` : ''}
                    </div>
                </div>
            `;
        });

        uploadBox.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                <span style="font-size: 10.5px; font-weight: 700; color: #10b981; display: inline-flex; align-items: center; gap: 4px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span>Uploaded Work (${files.length})</span>
                </span>
                ${canUpload ? `
                <label style="cursor: pointer; font-size: 10.5px; font-weight: 700; color: #3b82f6; display: inline-flex; align-items: center; gap: 2px;" title="Upload additional file or image" onclick="event.stopPropagation()">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span>Add</span>
                    <input type="file" multiple accept="image/*,video/*,application/pdf,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" style="display: none;" onchange="quickUploadCardFile(event, ${taskId})">
                </label>
                ` : ''}
            </div>
            <div id="card-files-list-${taskId}" style="display: flex; flex-direction: column; gap: 4px;">
                ${itemsHtml}
            </div>
        `;
    } else {
        if (canUpload) {
            uploadBox.innerHTML = `
                <label style="display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 6px 10px; border-radius: 8px; background: rgba(59, 130, 246, 0.08); border: 1.5px dashed rgba(59, 130, 246, 0.4); color: #2563eb; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s ease; box-sizing: border-box;" 
                       onmouseover="this.style.background='rgba(59,130,246,0.15)'; this.style.borderColor='#2563eb';" 
                       onmouseout="this.style.background='rgba(59,130,246,0.08)'; this.style.borderColor='rgba(59,130,246,0.4)';"
                       onclick="event.stopPropagation()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span id="card-upload-label-${taskId}">Upload Work (Image or File)</span>
                    <input type="file" multiple accept="image/*,video/*,application/pdf,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" style="display: none;" onchange="quickUploadCardFile(event, ${taskId})">
                </label>
            `;
        } else {
            uploadBox.innerHTML = `
                <div style="font-size: 10.5px; font-weight: 600; color: var(--color-text-secondary); text-align: center;">
                    Waiting for assignee to upload deliverable work
                </div>
            `;
        }
    }
}

function kanbanOnDragStart(event, taskId) {
    if (currentlyEditingCardId === taskId) {
        event.preventDefault();
        return;
    }
    const card = document.getElementById('kanban-card-' + taskId);
    if (card && card.getAttribute('data-can-move') === 'false') {
        event.preventDefault();
        const reason = card.getAttribute('data-restriction-reason') || 'You do not have permission to move this card.';
        window.dispatchEvent(new CustomEvent('toast', { detail: { message: reason, type: 'warning' } }));
        return;
    }
    isDraggingKanbanCard = true;
    draggedTaskId = taskId;
    draggedCardElement = event.currentTarget;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', taskId);
    setTimeout(() => {
        if (draggedCardElement) draggedCardElement.style.opacity = '0.45';
    }, 0);
}

function kanbanOnDragEnd(event) {
    setTimeout(() => { isDraggingKanbanCard = false; }, 100);
    if (draggedCardElement) {
        draggedCardElement.style.opacity = '1';
    }
    draggedTaskId = null;
    draggedCardElement = null;
    document.querySelectorAll('.kanban-column').forEach(col => {
        col.style.boxShadow = 'none';
        col.style.transform = 'none';
    });
}

function kanbanOnDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    const col = event.currentTarget;
    col.style.boxShadow = '0 0 0 2px #3b82f6 inset';
}

function kanbanOnDragLeave(event) {
    const col = event.currentTarget;
    col.style.boxShadow = 'none';
}

async function kanbanOnDrop(event, targetStatus) {
    event.preventDefault();
    const col = event.currentTarget;
    col.style.boxShadow = 'none';

    const taskId = event.dataTransfer.getData('text/plain') || draggedTaskId;
    if (!taskId) return;

    const card = document.getElementById('kanban-card-' + taskId);
    if (!card) return;

    const oldStatus = card.getAttribute('data-status');
    if (oldStatus === targetStatus) return;

    // Check if target is Approval Stage
    const isTargetApproval = ['Approval Stage', 'Approval', 'Review'].includes(targetStatus);
    const currentApproverId = card.getAttribute('data-approver-id');
    const hasFile = card.getAttribute('data-has-file') === 'true';

    if (isTargetApproval && !kanbanIsAdmin) {
        if (!hasFile) {
            openUploadDeliverableModal(taskId, targetStatus, oldStatus, card);
            return;
        }
        if (!currentApproverId) {
            openAssignApproverModal(taskId, targetStatus, oldStatus, card);
            return;
        }
    }

    // Check if moving out of Approval Stage back for revisions: Always route to In Progress!
    const isOldApproval = ['Approval Stage', 'Approval', 'Review', 'Under Review'].includes(oldStatus);
    if (isOldApproval) {
        if (targetStatus === 'To Do' || targetStatus === 'In Progress') {
            openRevisionPromptModal(taskId, 'In Progress', oldStatus, card);
            return;
        }
    }

    // Deliverables that already have revisions must remain in In Progress
    const revBadge = card.querySelector('#card-view-revision-badge-' + taskId);
    const hasRevs = (revBadge && revBadge.style.display !== 'none') 
        || parseInt(card.querySelector('#card-view-revision-num-' + taskId)?.textContent || '0', 10) > 0;
    if (hasRevs && targetStatus === 'To Do') {
        window.dispatchEvent(new CustomEvent('toast', { 
            detail: { message: 'Deliverables with revisions must remain in the In Progress column.', type: 'error' } 
        }));
        return;
    }

    // Ask confirmation before moving to a different column
    const taskTitle = card.querySelector('#card-view-title-' + taskId)?.textContent?.trim()
        || card.querySelector('[data-card-title]')?.textContent?.trim() 
        || card.querySelector('h4')?.textContent?.trim() 
        || 'this deliverable';

    const displayTarget = (targetStatus === 'Done') ? 'Completed' : targetStatus;
    const displayOld = (oldStatus === 'Done') ? 'Completed' : oldStatus;

    let confirmTitle = 'Move Deliverable?';
    let confirmMessage = `Are you sure you want to move <strong>"${escapeHtml(taskTitle)}"</strong> to <strong>${displayTarget}</strong>?`;
    let confirmBtnText = `Yes, Move`;
    let confirmType = 'info';

    if (targetStatus === 'Done') {
        confirmTitle = 'Complete Deliverable?';
        confirmMessage = `Are you sure you want to move <strong>"${escapeHtml(taskTitle)}"</strong> to <strong>Completed</strong> and mark it as done?`;
        confirmBtnText = 'Yes, Complete';
        confirmType = 'success';
    } else if (targetStatus === 'Approval Stage') {
        confirmTitle = 'Submit to Approval?';
        confirmMessage = `Are you sure you want to move <strong>"${escapeHtml(taskTitle)}"</strong> to <strong>Approval Stage</strong>?`;
        confirmBtnText = 'Yes, Submit';
        confirmType = 'info';
    } else if (targetStatus === 'In Progress') {
        confirmTitle = 'Move to In Progress?';
        confirmMessage = `Are you sure you want to move <strong>"${escapeHtml(taskTitle)}"</strong> to <strong>In Progress</strong>?`;
        confirmBtnText = 'Yes, Move';
        confirmType = 'info';
    } else if (targetStatus === 'To Do') {
        confirmTitle = 'Move to To Do?';
        confirmMessage = `Are you sure you want to move <strong>"${escapeHtml(taskTitle)}"</strong> back to <strong>To Do</strong>?`;
        confirmBtnText = 'Yes, Move';
        confirmType = 'info';
    }

    let confirmed = false;
    if (typeof window.customConfirm === 'function') {
        confirmed = await window.customConfirm({
            title: confirmTitle,
            message: confirmMessage,
            confirmText: confirmBtnText,
            cancelText: 'Cancel',
            isDanger: false,
            type: confirmType
        });
    } else {
        confirmed = window.confirm(`Are you sure you want to move "${taskTitle}" from ${displayOld} to ${displayTarget}?`);
    }

    if (!confirmed) {
        return;
    }

    executeKanbanMove(taskId, targetStatus, oldStatus, card, { approver_id: currentApproverId });
}

async function executeKanbanMove(taskId, targetStatus, oldStatus, card, extraData = {}) {
    // Target container
    let containerId = 'cards-container-todo';
    if (targetStatus === 'In Progress') containerId = 'cards-container-inprogress';
    else if (targetStatus === 'Approval Stage' || targetStatus === 'Approval' || targetStatus === 'Review') containerId = 'cards-container-approval';
    else if (targetStatus === 'Done') containerId = 'cards-container-complete';

    const targetContainer = document.getElementById(containerId);
    if (!targetContainer) return;

    // Optimistically move card in DOM
    const oldContainer = card.parentElement;
    targetContainer.appendChild(card);
    card.setAttribute('data-status', targetStatus);

    // Toggle approver visibility: hidden in To Do stage, shown in next stages
    const isNowToDo = (targetStatus === 'To Do');
    const approverRow = document.getElementById('card-view-approver-row-' + taskId);
    if (approverRow) {
        approverRow.style.display = isNowToDo ? 'none' : 'flex';
    }
    const approverEditorRow = document.getElementById('card-editor-approver-row-' + taskId);
    if (approverEditorRow) {
        approverEditorRow.style.display = isNowToDo ? 'none' : 'flex';
    }

    // Update status border accent
    updateCardStatusUI(card, targetStatus);
    recalculateKanbanCounts();

    // Call API to persist
    try {
        const payload = Object.assign({ status: targetStatus }, extraData);
        const response = await fetch('/deliverables/' + taskId + '/update-kanban-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            // Revert on failure
            oldContainer.appendChild(card);
            card.setAttribute('data-status', oldStatus);
            updateCardStatusUI(card, oldStatus);
            recalculateKanbanCounts();
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: result.message || 'Failed to update status.', type: 'error' } }));
        } else {
            // Update stage badge and permission on card
            if (result.approval_stage !== undefined) {
                const stageBadge = document.getElementById('card-view-stage-' + taskId);
                if (stageBadge) {
                    if (isAssignStageName(result.approval_stage)) {
                        stageBadge.style.display = 'none';
                    } else {
                        stageBadge.textContent = result.approval_stage;
                        stageBadge.style.display = 'inline-flex';
                    }
                }
            }
            if (result.approver_id) {
                card.setAttribute('data-approver-id', result.approver_id);
            }
            if (result.approver_name) {
                card.setAttribute('data-approver-name', result.approver_name);
                const approverNameEl = document.getElementById('card-view-approver-name-' + taskId);
                if (approverNameEl) {
                    approverNameEl.textContent = result.approver_name;
                    approverNameEl.style.color = 'var(--color-text-primary)';
                }
            }
            if (typeof result.has_file !== 'undefined') {
                card.setAttribute('data-has-file', result.has_file ? 'true' : 'false');
            }

            // Update revision badges & revision message callouts
            const revCount = (typeof result.revisions !== 'undefined') ? result.revisions : ((result.deliverable && result.deliverable.revisions) || 0);
            const revMsg = (typeof result.revision_instructions !== 'undefined') ? result.revision_instructions : ((result.deliverable && result.deliverable.revision_instructions) || '');
            const hasRevs = (revCount > 0 || Boolean(revMsg));

            // Collapsed view badge (Alert outside)
            const vBadge = document.getElementById('card-view-revision-badge-' + taskId);
            const vNum = document.getElementById('card-view-revision-num-' + taskId);
            if (vBadge) {
                vBadge.style.display = hasRevs ? 'inline-flex' : 'none';
                vBadge.title = revMsg ? ('Revision alert: ' + revMsg) : ('Revision Round ' + (revCount || 1));
            }
            if (vNum) vNum.textContent = revCount || 1;

            // Editor badge
            const eBadge = document.getElementById('card-editor-revision-badge-' + taskId);
            const eNum = document.getElementById('card-editor-revision-num-' + taskId);
            if (eBadge) eBadge.style.display = hasRevs ? 'inline-flex' : 'none';
            if (eNum) eNum.textContent = revCount || 1;

            // Editor message box
            const eBox = document.getElementById('card-editor-revision-box-' + taskId);
            const eText = document.getElementById('card-editor-revision-text-' + taskId);
            const eBoxNum = document.getElementById('card-editor-revision-box-num-' + taskId);
            if (eBox) eBox.style.display = revMsg ? 'block' : 'none';
            if (eText) eText.textContent = revMsg;
            if (eBoxNum) eBoxNum.textContent = 'Round ' + (revCount || 1);

            // Readonly badge, message box & row
            const roBadge = document.getElementById('card-readonly-revision-badge-' + taskId);
            const roNum = document.getElementById('card-readonly-revision-num-' + taskId);
            if (roBadge) roBadge.style.display = hasRevs ? 'inline-flex' : 'none';
            if (roNum) roNum.textContent = revCount || 1;

            const roBox = document.getElementById('card-readonly-revision-box-' + taskId);
            const roText = document.getElementById('card-readonly-revision-text-' + taskId);
            const roBoxNum = document.getElementById('card-readonly-revision-box-num-' + taskId);
            if (roBox) roBox.style.display = revMsg ? 'block' : 'none';
            if (roText) roText.textContent = revMsg;
            if (roBoxNum) roBoxNum.textContent = 'Round ' + (revCount || 1);

            const roRow = document.getElementById('card-readonly-revisions-row-' + taskId);
            const roTotal = document.getElementById('card-readonly-revisions-total-' + taskId);
            if (roRow) roRow.style.display = hasRevs ? 'flex' : 'none';
            if (roTotal) roTotal.textContent = revCount || 1;

            // In-memory projectDeliverables update
            if (window.projectDeliverables && window.projectDeliverables[taskId]) {
                window.projectDeliverables[taskId].status = targetStatus;
                if (result.approval_stage) window.projectDeliverables[taskId].approval_stage = result.approval_stage;
                window.projectDeliverables[taskId].revisions = revCount;
                window.projectDeliverables[taskId].revision_instructions = revMsg;
                if (result.deliverable) {
                    window.projectDeliverables[taskId] = Object.assign(window.projectDeliverables[taskId], result.deliverable);
                }
            }

            const currentData = (window.projectDeliverables && window.projectDeliverables[taskId]) || result.deliverable || {};
            updateCardArtworkUI(taskId, currentData);
            if (typeof result.can_move !== 'undefined') {
                card.setAttribute('data-can-move', result.can_move ? 'true' : 'false');
                card.setAttribute('draggable', result.can_move ? 'true' : 'false');
                card.style.cursor = result.can_move ? 'grab' : 'pointer';
                if (!result.can_move) {
                    const approverName = result.approver_name || card.getAttribute('data-approver-name') || 'the assigned approver';
                    const reason = (targetStatus === 'Approval Stage')
                        ? `Submitted to ${approverName}. Only that approver can approve or return this deliverable.`
                        : (targetStatus === 'Done' ? 'This deliverable is completed. Only an approver or admin can move it.' : 'Only the assigned person can move this deliverable.');
                    card.setAttribute('data-restriction-reason', reason);
                    card.title = reason;
                } else {
                    card.removeAttribute('data-restriction-reason');
                    card.title = '';
                }
            }
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: result.message || 'Status updated', type: 'success' } }));
        }
    } catch (err) {
        console.error('Kanban move error:', err);
        oldContainer.appendChild(card);
        card.setAttribute('data-status', oldStatus);
        updateCardStatusUI(card, oldStatus);
        recalculateKanbanCounts();
        alert('Network error while updating deliverable status.');
    }
}

let pendingUploadAction = null;

function openUploadDeliverableModal(taskId, targetStatus, oldStatus, card) {
    pendingUploadAction = { taskId, targetStatus, oldStatus, card };
    const modal = document.getElementById('uploadDeliverableModal');
    const titleEl = document.getElementById('uploadDeliverableModalTaskTitle');
    const cardTitle = card.querySelector('#card-view-title-' + taskId)?.textContent || 'Deliverable';
    if (titleEl) titleEl.textContent = cardTitle.trim();

    const fileInput = document.getElementById('uploadDeliverableModalFileInput');
    if (fileInput) fileInput.value = '';
    const fileLabel = document.getElementById('uploadDeliverableModalFileLabel');
    if (fileLabel) fileLabel.textContent = 'Choose file(s)…';

    const currentApproverId = card.getAttribute('data-approver-id');
    const approverGroup = document.getElementById('uploadDeliverableApproverGroup');
    const select = document.getElementById('uploadDeliverableApproverSelect');

    if (!currentApproverId) {
        if (approverGroup) approverGroup.style.display = 'block';
        if (select && select.options.length <= 1) {
            select.innerHTML = '<option value="">Choose an Approver…</option>';
            kanbanSelectableApprovers.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = `${u.name} (${u.role})`;
                select.appendChild(opt);
            });
        }
        if (select && kanbanProjectApproverId && !select.value) {
            select.value = kanbanProjectApproverId;
        }
    } else {
        if (approverGroup) approverGroup.style.display = 'none';
    }

    if (modal) modal.style.display = 'flex';
}

function closeUploadDeliverableModal() {
    const modal = document.getElementById('uploadDeliverableModal');
    if (modal) modal.style.display = 'none';
    pendingUploadAction = null;
}

async function confirmUploadDeliverable() {
    if (!pendingUploadAction) return;
    const { taskId, targetStatus, oldStatus, card } = pendingUploadAction;

    const fileInput = document.getElementById('uploadDeliverableModalFileInput');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Please select a deliverable file or design to upload.');
        return;
    }

    let approverId = card.getAttribute('data-approver-id');
    if (!approverId) {
        const select = document.getElementById('uploadDeliverableApproverSelect');
        approverId = select ? select.value : '';
        if (!approverId) {
            alert('Please select an approver before submitting.');
            return;
        }
    }

    const submitBtn = document.getElementById('uploadDeliverableModalSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Uploading file & submitting…';
    }

    const fd = new FormData();
    fd.append('action', 'save_only');
    const files = Array.from(fileInput.files);
    files.forEach(f => fd.append('final_designs_files[]', f));
    if (files.length === 1) fd.append('final_designs_file', files[0]);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    try {
        const res = await fetch(`/deliverables/${taskId}/submit`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json'
            },
            body: fd
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            alert(data.message || 'Failed to upload deliverable file.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Upload & Submit to Approval';
            }
            return;
        }

        // File uploaded! Mark card
        card.setAttribute('data-has-file', 'true');
        closeUploadDeliverableModal();

        // Now execute the kanban move to Approval Stage
        executeKanbanMove(taskId, targetStatus, oldStatus, card, { approver_id: approverId });
    } catch (err) {
        console.error('Error submitting file and moving to approval:', err);
        alert('Network error while uploading file.');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Upload & Submit to Approval';
        }
    }
}

function openAssignApproverModal(taskId, targetStatus, oldStatus, card) {
    pendingApproverAction = { taskId, targetStatus, oldStatus, card };
    const modal = document.getElementById('assignApproverModal');
    const titleEl = document.getElementById('assignApproverModalTaskTitle');
    const cardTitle = card.querySelector('#card-view-title-' + taskId)?.textContent || 'Deliverable';
    if (titleEl) titleEl.textContent = cardTitle.trim();

    const select = document.getElementById('assignApproverModalSelect');
    if (select && select.options.length <= 1) {
        select.innerHTML = '<option value="">Choose an Approver…</option>';
        kanbanSelectableApprovers.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = `${u.name} (${u.role})`;
            select.appendChild(opt);
        });
    }
    if (select && kanbanProjectApproverId && !select.value) {
        select.value = kanbanProjectApproverId;
    }
    if (modal) modal.style.display = 'flex';
}

function closeAssignApproverModal() {
    const modal = document.getElementById('assignApproverModal');
    if (modal) modal.style.display = 'none';
    pendingApproverAction = null;
}

function confirmAssignApprover() {
    if (!pendingApproverAction) return;
    const select = document.getElementById('assignApproverModalSelect');
    const approverId = select ? select.value : '';
    if (!approverId) {
        alert('Please select an approver.');
        return;
    }
    const { taskId, targetStatus, oldStatus, card } = pendingApproverAction;
    closeAssignApproverModal();
    executeKanbanMove(taskId, targetStatus, oldStatus, card, { approver_id: approverId });
}

function openRevisionPromptModal(taskId, targetStatus, oldStatus, card) {
    // Revisions must always route to In Progress
    targetStatus = 'In Progress';
    pendingRevisionAction = { taskId, targetStatus, oldStatus, card };
    const modal = document.getElementById('revisionPromptModal');
    const titleEl = document.getElementById('revisionPromptTaskTitle');
    const cardTitle = card.querySelector('#card-view-title-' + taskId)?.textContent || 'Deliverable';
    if (titleEl) titleEl.textContent = cardTitle.trim();

    const textarea = document.getElementById('revisionPromptInstructions');
    if (textarea) textarea.value = '';

    if (modal) modal.style.display = 'flex';
}

function closeRevisionPromptModal() {
    const modal = document.getElementById('revisionPromptModal');
    if (modal) modal.style.display = 'none';
    pendingRevisionAction = null;
}

function confirmRevisionPrompt() {
    if (!pendingRevisionAction) return;
    const { taskId, targetStatus, oldStatus, card } = pendingRevisionAction;
    const textarea = document.getElementById('revisionPromptInstructions');
    const instructions = textarea ? textarea.value.trim() : '';

    closeRevisionPromptModal();
    executeKanbanMove(taskId, targetStatus, oldStatus, card, { revision_instructions: instructions });
}

function updateCardStatusUI(card, status) {
    // Optionally update badge text or border accent
    if (status === 'Done') {
        card.style.borderLeft = '3px solid #10b981';
    } else if (status === 'Approval Stage' || status === 'Approval' || status === 'Review' || status === 'Under Review') {
        card.style.borderLeft = '3px solid #f59e0b';
    } else if (status === 'In Progress') {
        card.style.borderLeft = '3px solid #6366f1';
    } else {
        card.style.borderLeft = '3px solid var(--color-border-primary)';
    }
}

function recalculateKanbanCounts() {
    const todoCount = document.querySelectorAll('#cards-container-todo .kanban-card').length;
    const inprogressCount = document.querySelectorAll('#cards-container-inprogress .kanban-card').length;
    const approvalCount = document.querySelectorAll('#cards-container-approval .kanban-card').length;
    const completeCount = document.querySelectorAll('#cards-container-complete .kanban-card').length;

    const elTodo = document.getElementById('badge-count-todo');
    const elInprogress = document.getElementById('badge-count-inprogress');
    const elApproval = document.getElementById('badge-count-approval');
    const elComplete = document.getElementById('badge-count-complete');

    if (elTodo) elTodo.textContent = todoCount;
    if (elInprogress) elInprogress.textContent = inprogressCount;
    if (elApproval) elApproval.textContent = approvalCount;
    if (elComplete) elComplete.textContent = completeCount;
}

function openInlineCreator(colKey) {
    const creator = document.getElementById('inline-creator-' + colKey);
    const btn = document.getElementById('btn-open-creator-' + colKey);
    if (creator) {
        creator.style.display = 'block';
        justOpenedInlineCreator = colKey;
        setTimeout(() => {
            if (justOpenedInlineCreator === colKey) justOpenedInlineCreator = null;
        }, 100);

        const titleInput = creator.querySelector('input[name="task_title"]');
        if (titleInput) {
            titleInput.focus();
        }
    }
    if (btn) btn.style.display = 'none';
}

function closeInlineCreator(colKey) {
    const creator = document.getElementById('inline-creator-' + colKey);
    const btn = document.getElementById('btn-open-creator-' + colKey);
    if (creator) {
        creator.style.display = 'none';
        const form = creator.querySelector('form');
        if (form) form.reset();
    }
    if (btn) btn.style.display = 'inline-flex';
}

async function submitInlineTask(event, colKey, statusValue) {
    event.preventDefault();
    const creator = document.getElementById('inline-creator-' + colKey);
    if (!creator) return;

    const titleInput = creator.querySelector('input[name="task_title"]');
    const title = titleInput ? titleInput.value.trim() : '';
    if (!title) {
        alert('Please enter a task name.');
        if (titleInput) titleInput.focus();
        return;
    }

    const postTypeSelect = creator.querySelector('select[name="post_type"]');
    const assigneeSelect = creator.querySelector('select[name="writer_id"]');
    const approverSelect = creator.querySelector('select[name="approver_id"]');
    const deadlineInput = creator.querySelector('input[name="deadline"]');
    const prioritySelect = creator.querySelector('select[name="priority"]');

    const postType = postTypeSelect ? postTypeSelect.value : 'Outlines';
    const writerId = assigneeSelect && assigneeSelect.value ? assigneeSelect.value : null;
    const approverId = approverSelect && approverSelect.value ? approverSelect.value : null;
    const deadline = deadlineInput && deadlineInput.value ? deadlineInput.value : null;
    const priority = prioritySelect ? prioritySelect.value : 'Medium';

    if (deadline) {
        const selDate = new Date(deadline + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selDate < today) {
            alert('Due date cannot be before today.');
            if (deadlineInput) deadlineInput.focus();
            return;
        }
    }

    const saveBtn = creator.querySelector('button[type="submit"]');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Saving…';
    }

    try {
        const response = await fetch('/projects/{{ $project->id }}/campaign-kanban/quick-store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                title: title,
                status: statusValue,
                post_type: postType,
                writer_id: writerId,
                approver_id: approverId,
                deadline: deadline,
                priority: priority,
            })
        });

        const result = await response.json();
        if (response.ok && result.success && result.deliverable) {
            // Insert card into container
            const d = result.deliverable;
            insertCreatedCardIntoColumn(colKey, d);
            closeInlineCreator(colKey);
            recalculateKanbanCounts();
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: result.message || 'Task created successfully', type: 'success' } }));
        } else {
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: result.message || 'Failed to create task.', type: 'error' } }));
        }
    } catch (err) {
        console.error('Error creating inline task:', err);
        alert('Network error while saving deliverable.');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Save ↵';
        }
    }
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function insertCreatedCardIntoColumn(colKey, d) {
    let containerId = 'cards-container-' + colKey;
    const container = document.getElementById(containerId);
    if (!container) return;

    if (d && d.id) {
        campaignDeliverablesData[d.id] = {
            id: d.id,
            title: d.title || '',
            post_type: d.post_type || 'Outlines',
            status: d.status || 'To Do',
            priority: d.priority || 'Medium',
            deadline: d.deadline || null,
            deadline_formatted: d.deadline ? formatDateDisplay(d.deadline) : null,
            concept: d.concept || '',
            caption: d.caption || '',
            post_copy: d.post_copy || d.subtask_copy || '',
            notes: d.notes || '',
            description: d.description || '',
            reference_files_list: extractReferenceFiles(d),
            reference_urls_list: extractReferenceUrls(d),
            assignee_name: d.assignee_name || 'Unassigned',
            approver_name: d.approver_name || null,
            created_at_formatted: d.created_at ? formatDateDisplay(d.created_at) : null
        };
    }

    const initial = d.assignee_name && d.assignee_name !== 'Unassigned' 
        ? d.assignee_name.trim().charAt(0).toUpperCase() 
        : 'U';

    const prio = d.priority || 'Medium';
    const prioColor = (prio === 'High Priority' || prio === 'Urgent' || prio === 'High') ? '#ef4444' : (prio === 'Low' ? '#64748b' : '#10b981');

    let borderLeft = 'border-left: 3px solid #94a3b8;';
    if (d.status === 'Done' || d.status === 'Completed' || d.status === 'Complete') {
        borderLeft = 'border-left: 3px solid #10b981;';
    } else if (d.status === 'Approval Stage' || d.status === 'Approval' || d.status === 'Review' || d.status === 'Under Review') {
        borderLeft = 'border-left: 3px solid #f59e0b;';
    } else if (d.status === 'In Progress' || d.status === 'on hold') {
        borderLeft = 'border-left: 3px solid #6366f1;';
    }

    let formattedDate = '';
    let rawDate = '';
    if (d.deadline) {
        try {
            const dt = new Date(d.deadline);
            formattedDate = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            rawDate = dt.toISOString().split('T')[0];
        } catch(e) {
            formattedDate = d.deadline;
            rawDate = d.deadline;
        }
    }

    // Build post type options
    let postTypeOptions = '';
    kanbanSubtaskTypes.forEach(st => {
        const isSelected = (st.name.toLowerCase() === (d.post_type || '').toLowerCase()) ? 'selected' : '';
        postTypeOptions += `<option value="${escapeHtml(st.name)}" style="background: var(--bg-card); color: var(--text-card-title);" ${isSelected}>${escapeHtml(st.name)}</option>`;
    });

    // Build assignee options
    let assigneeOptions = `<option value="" style="background: var(--bg-card); color: var(--text-card-title);">Add assignee…</option>`;
    kanbanSelectableUsers.forEach(u => {
        const isSelected = (d.writer_id == u.id) ? 'selected' : '';
        assigneeOptions += `<option value="${u.id}" style="background: var(--bg-card); color: var(--text-card-title);" ${isSelected}>${escapeHtml(u.name)} (${escapeHtml(u.role)})</option>`;
    });

    // Build approver options
    let approverOptions = `<option value="" style="background: var(--bg-card); color: var(--text-card-title);">Add approver…</option>`;
    kanbanSelectableUsers.forEach(u => {
        const isSelected = (d.approver_id == u.id) ? 'selected' : '';
        approverOptions += `<option value="${u.id}" style="background: var(--bg-card); color: var(--text-card-title);" ${isSelected}>${escapeHtml(u.name)} (${escapeHtml(u.role)})</option>`;
    });

    const perm = checkCardMovePermission(d);
    const canMove = perm.canMove;
    const restrictionReason = perm.reason;
    const isColToDo = (colKey === 'todo' || (d.status || '').toLowerCase() === 'to do');

    const creatorId = d.created_by || d.effective_creator_id || null;
    const isCreator = creatorId && kanbanCurrentUserId == creatorId;
    const canEdit = kanbanIsAdmin || isCreator;
    const hasArtwork = Boolean(d.has_artwork || d.final_designs || d.final_designs_link || (d.final_designs_list && d.final_designs_list.length > 0));
    const isInProgress = (d.status === 'In Progress');
    const assignedId = d.writer_id || d.designer_id || null;
    const isAssignee = (assignedId && kanbanCurrentUserId == assignedId);
    const canUploadArtwork = isAssignee || kanbanIsAdmin || isCreator;

    const hasAssignee = d.assignee_name && d.assignee_name !== 'Unassigned';
    const assigneeDisplay = hasAssignee ? d.assignee_name : 'Unassigned';
    const badgeColor = hasAssignee ? '#3b82f6' : 'var(--text-card-sub)';
    const badgeBg = hasAssignee ? 'rgba(59,130,246,0.1)' : 'var(--bg-column)';
    const badgeBorder = hasAssignee ? '1px solid rgba(59,130,246,0.2)' : '1px solid var(--border-card)';
    const stageDisplay = d.approval_stage || d.stage || '';
    const isAssign = isAssignStageName(stageDisplay);

    const revCount = d.revisions || 0;
    const revMsg = d.revision_instructions || '';
    const hasRevs = (revCount > 0 || Boolean(revMsg));

    let referencesHtml = '';
    let refFiles = d.reference_files_list || [];
    if (!Array.isArray(refFiles)) refFiles = refFiles ? [refFiles] : [];
    let refUrls = d.reference_urls_list || [];
    if (!Array.isArray(refUrls)) refUrls = refUrls ? [refUrls] : [];
    const totalRefs = refFiles.length + refUrls.length;

    if (totalRefs > 0) {
        let refItemsHtml = '';
        refFiles.forEach((rUrl) => {
            const cleanUrl = rUrl.split('?')[0];
            const rawFilename = cleanUrl.substring(cleanUrl.lastIndexOf('/') + 1);
            let filename = decodeURIComponent(rawFilename) || 'Reference File';
            const ext = filename.split('.').pop().toLowerCase();
            const isImg = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext);

            const iconHtml = isImg
                ? `<img src="${rUrl}" alt="" style="width: 16px; height: 16px; border-radius: 3px; object-fit: cover; flex-shrink: 0;">`
                : `<div style="width: 16px; height: 16px; border-radius: 3px; background: rgba(2, 132, 199, 0.12); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 8px; font-weight: 800; flex-shrink: 0;">${escapeHtml(ext ? ext.substring(0,3).toUpperCase() : 'DOC')}</div>`;

            refItemsHtml += `
                <div style="display: flex; align-items: center; gap: 4px; padding: 3px 6px; border-radius: 6px; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); cursor: pointer; flex-shrink: 0; max-width: 140px;"
                     onclick="event.stopPropagation(); previewDeliverableArtwork('${escapeHtml(rUrl)}', '${escapeHtml(filename)}', '${escapeHtml(rUrl)}')"
                     title="Click to preview ${escapeHtml(filename)}">
                    ${iconHtml}
                    <span style="font-size: 10px; font-weight: 600; color: var(--color-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${escapeHtml(filename)}</span>
                </div>
            `;
        });
        refUrls.forEach((uUrl) => {
            refItemsHtml += `
                <a href="${escapeHtml(uUrl)}" target="_blank" onclick="event.stopPropagation()" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 6px; border-radius: 6px; background: rgba(2, 132, 199, 0.08); border: 1px solid rgba(2, 132, 199, 0.2); color: #0284c7; font-size: 10px; font-weight: 600; text-decoration: none; flex-shrink: 0;" title="${escapeHtml(uUrl)}">
                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    <span>Link</span>
                </a>
            `;
        });

        referencesHtml = `
            <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--color-border-primary);" onclick="event.stopPropagation()">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="font-size: 10.5px; font-weight: 700; color: #0284c7; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span>References (${totalRefs})</span>
                    </span>
                    <button type="button" 
                            onclick="event.stopPropagation(); toggleCardEditor(${d.id}, false); openCampaignDetailsModal(${d.id})" 
                            style="font-size: 10px; font-weight: 700; color: #0284c7; background: rgba(2, 132, 199, 0.1); border: 1px solid rgba(2, 132, 199, 0.25); border-radius: 4px; padding: 2px 7px; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;"
                            title="Open full references gallery">
                        <span>View All (${totalRefs})</span>
                        <svg width="9" height="9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </button>
                </div>
                <div style="display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin;">
                    ${refItemsHtml}
                </div>
            </div>
        `;
    }

    let uploadedWorkHtml = '';
    let cardFiles = d.final_designs_list || [];
    if (!Array.isArray(cardFiles)) cardFiles = cardFiles ? [cardFiles] : [];
    const hasCardFiles = cardFiles.length > 0;

    if (hasCardFiles || !isColToDo || canUploadArtwork) {
        if (hasCardFiles) {
            let itemsHtml = '';
            cardFiles.forEach((fUrl, idx) => {
                const cleanUrl = fUrl.split('?')[0];
                const rawFilename = cleanUrl.substring(cleanUrl.lastIndexOf('/') + 1);
                let filename = decodeURIComponent(rawFilename);
                if (!filename) filename = 'File ' + (idx + 1);
                const ext = filename.split('.').pop().toLowerCase();
                const isImg = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext);

                const iconHtml = isImg
                    ? `<img src="${fUrl}" alt="" style="width: 18px; height: 18px; border-radius: 3px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--color-border-primary);">`
                    : `<div style="width: 18px; height: 18px; border-radius: 3px; background: rgba(59,130,246,0.12); color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>`;

                const downloadUrl = `/deliverables/${d.id}/download-artwork/${idx}`;

                itemsHtml += `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; padding: 4px 6px; border-radius: 6px; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary);">
                        <div style="display: flex; align-items: center; gap: 5px; min-width: 0; flex: 1; cursor: pointer;" 
                             data-file-url="${escapeHtml(fUrl)}" 
                             data-filename="${escapeHtml(filename)}" 
                             data-download-url="${escapeHtml(downloadUrl)}" 
                             onclick="event.stopPropagation(); previewDeliverableArtwork(this);" 
                             title="Click to view file">
                            ${iconHtml}
                            <span style="font-size: 11px; font-weight: 600; color: var(--color-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(filename)}">${escapeHtml(filename)}</span>
                        </div>
                        <div style="display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;">
                            <button type="button" 
                                    data-file-url="${escapeHtml(fUrl)}" 
                                    data-filename="${escapeHtml(filename)}" 
                                    data-download-url="${escapeHtml(downloadUrl)}" 
                                    onclick="event.stopPropagation(); previewDeliverableArtwork(this);" 
                                    style="font-size: 10px; font-weight: 700; color: #3b82f6; border: none; cursor: pointer; padding: 2px 7px; border-radius: 4px; background: rgba(59,130,246,0.1); line-height: 1.4;" 
                                    title="View in popup">View</button>
                            ${canUploadArtwork ? `<button type="button" onclick="event.stopPropagation(); quickRemoveDeliverableFile(${d.id}, ${idx})" style="background: transparent; border: none; cursor: pointer; color: #ef4444; font-size: 11px; font-weight: 800; padding: 1px 3px; border-radius: 3px; line-height: 1;" title="Remove this file">✕</button>` : ''}
                        </div>
                    </div>
                `;
            });

            uploadedWorkHtml = `
                <div id="card-upload-box-${d.id}" style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--color-border-primary);" onclick="event.stopPropagation()">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 10.5px; font-weight: 700; color: #10b981; display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Uploaded Work (${cardFiles.length})</span>
                        </span>
                        ${canUploadArtwork ? `
                        <label style="cursor: pointer; font-size: 10.5px; font-weight: 700; color: #3b82f6; display: inline-flex; align-items: center; gap: 2px;" title="Upload additional file or image" onclick="event.stopPropagation()">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            <span>Add</span>
                            <input type="file" multiple accept="image/*,video/*,application/pdf,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" style="display: none;" onchange="quickUploadCardFile(event, ${d.id})">
                        </label>
                        ` : ''}
                    </div>
                    <div id="card-files-list-${d.id}" style="display: flex; flex-direction: column; gap: 4px;">
                        ${itemsHtml}
                    </div>
                </div>
            `;
        } else if (canUploadArtwork) {
            uploadedWorkHtml = `
                <div id="card-upload-box-${d.id}" style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--color-border-primary);" onclick="event.stopPropagation()">
                    <label style="display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 6px 10px; border-radius: 8px; background: rgba(59, 130, 246, 0.08); border: 1.5px dashed rgba(59, 130, 246, 0.4); color: #2563eb; font-size: 11px; font-weight: 700; cursor: pointer; transition: all 0.15s ease; box-sizing: border-box;" 
                           onmouseover="this.style.background='rgba(59,130,246,0.15)'; this.style.borderColor='#2563eb';" 
                           onmouseout="this.style.background='rgba(59,130,246,0.08)'; this.style.borderColor='rgba(59,130,246,0.4)';"
                           onclick="event.stopPropagation()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span id="card-upload-label-${d.id}">Upload Work (Image or File)</span>
                        <input type="file" multiple accept="image/*,video/*,application/pdf,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" style="display: none;" onchange="quickUploadCardFile(event, ${d.id})">
                    </label>
                </div>
            `;
        } else if (!isColToDo) {
            uploadedWorkHtml = `
                <div id="card-upload-box-${d.id}" style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--color-border-primary);" onclick="event.stopPropagation()">
                    <div style="font-size: 10.5px; font-weight: 600; color: var(--color-text-secondary); text-align: center;">
                        Waiting for assignee to upload deliverable work
                    </div>
                </div>
            `;
        }
    }

    const cardHtml = `
        <div class="kanban-card" 
             id="kanban-card-${d.id}"
             data-id="${d.id}"
             data-status="${escapeHtml(d.status || 'To Do')}"
             data-approver-id="${d.approver_id || ''}"
             data-approver-name="${escapeHtml(d.approver_name || '')}"
             data-creator-id="${creatorId || ''}"
             data-can-edit="${canEdit ? 'true' : 'false'}"
             data-can-manage="${canEdit ? 'true' : 'false'}"
             data-can-move="${canMove ? 'true' : 'false'}"
             data-has-file="${hasArtwork ? 'true' : 'false'}"
             data-restriction-reason="${escapeHtml(restrictionReason)}"
             draggable="${canMove ? 'true' : 'false'}"
             ondragstart="kanbanOnDragStart(event, ${d.id})"
             ondragend="kanbanOnDragEnd(event)"
             onclick="handleCardClick(${d.id})"
             style="background: var(--bg-card); border: 1px solid var(--border-card); ${borderLeft} border-radius: 12px; padding: 12px 14px; box-shadow: var(--shadow-card); cursor: ${canMove ? 'grab' : 'pointer'}; transition: all 0.15s ease; position: relative;"
             title="${escapeHtml(restrictionReason)}"
             onmouseover="if(!isCardEditorOpen(${d.id})) { this.style.boxShadow='var(--shadow-card), 0 4px 12px rgba(0,0,0,0.08)'; this.style.transform='translateY(-1px)'; }"
             onmouseout="if(!isCardEditorOpen(${d.id})) { this.style.boxShadow='var(--shadow-card)'; this.style.transform='none'; }">
            
            {{-- Collapsed Card View --}}
            <div class="kanban-card-view" id="kanban-card-view-${d.id}">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                        <span id="card-view-post-type-${d.id}" style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--text-card-sub); background: var(--bg-column); border: 1px solid var(--border-card); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                            ${escapeHtml(d.post_type || 'Outlines')}
                        </span>
                        <span id="card-view-stage-${d.id}" style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: ${isAssign ? 'none' : 'inline-flex'}; align-items: center;">
                            ${escapeHtml(stageDisplay)}
                        </span>
                        {{-- Revision Alert Badge --}}
                        <span id="card-view-revision-badge-${d.id}" 
                              style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em; color: #ef4444; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: ${hasRevs ? 'inline-flex' : 'none'}; align-items: center; gap: 3px;"
                              title="${escapeHtml(revMsg ? ('Revision alert: ' + revMsg) : ('Revision Round ' + (revCount || 1)))}">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>REV: <span id="card-view-revision-num-${d.id}">${revCount || 1}</span></span>
                        </span>
                    </div>
                    <span id="card-view-assignee-badge-${d.id}" 
                          style="font-size: 10px; font-weight: 600; color: ${badgeColor}; background: ${badgeBg}; border: ${badgeBorder}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;"
                          title="${hasAssignee ? 'Assigned to ' + escapeHtml(assigneeDisplay) : 'No assignee'}">
                        <span>${escapeHtml(assigneeDisplay)}</span>
                    </span>
                </div>

                <h4 id="card-view-title-${d.id}" style="margin: 0 0 8px 0; font-size: 13.5px; font-weight: 700; color: var(--text-card-title); line-height: 1.35; letter-spacing: -0.01em;">
                    ${escapeHtml(d.title)}
                </h4>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 6px; border-top: 1px solid var(--border-card); margin-top: 4px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <div id="card-view-avatar-${d.id}" 
                             style="width: 22px; height: 22px; border-radius: 50%; background: #7c3aed; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(124,58,237,0.3); flex-shrink: 0;" 
                             title="${escapeHtml(d.assignee_name || 'Unassigned')}">
                            ${escapeHtml(initial)}
                        </div>
                        <div id="card-view-date-${d.id}" style="display: ${formattedDate ? 'inline-flex' : 'none'}; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 600; color: var(--text-card-sub);" title="Due ${escapeHtml(formattedDate)}">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>${escapeHtml(formattedDate)}</span>
                        </div>
                        <div id="card-view-work-badge-${d.id}" style="display: ${hasCardFiles ? 'inline-flex' : 'none'}; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; color: #10b981; background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.2); padding: 1px 5px; border-radius: 4px;" title="${cardFiles.length} deliverable file(s) uploaded">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Work (<span id="card-view-work-count-${d.id}">${cardFiles.length}</span>)</span>
                        </div>
                        ${totalRefs > 0 ? `
                        <div onclick="event.stopPropagation(); openCampaignDetailsModal(${d.id})" 
                             style="display: inline-flex; align-items: center; gap: 3px; font-size: 10px; font-weight: 700; color: #0284c7; background: rgba(2, 132, 199, 0.08); border: 1px solid rgba(2, 132, 199, 0.2); padding: 1px 5px; border-radius: 4px; cursor: pointer; transition: all 0.15s;" 
                             onmouseover="this.style.background='rgba(2, 132, 199, 0.18)'; this.style.borderColor='#0284c7';" 
                             onmouseout="this.style.background='rgba(2, 132, 199, 0.08)'; this.style.borderColor='rgba(2, 132, 199, 0.2)';"
                             title="Click to view all ${totalRefs} reference(s)">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span>Ref (${totalRefs})</span>
                        </div>
                        ` : ''}
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;">
                        <svg id="card-view-prio-${d.id}" width="12" height="12" fill="${prioColor}" viewBox="0 0 16 16" title="${escapeHtml(prio)}">
                            <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                        </svg>
                        <svg width="12" height="12" fill="none" stroke="var(--text-card-sub)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            ${canEdit ? `
            {{-- Expanded Inline Editor --}}
            <div class="kanban-card-editor" id="kanban-card-editor-${d.id}" style="display: none;" onclick="event.stopPropagation()">
                <form onsubmit="submitInlineCardEdit(event, ${d.id})">
                    {{-- Top Header: Stage on top very small --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                        <div style="display: inline-flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                            <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--text-card-sub); background: var(--bg-column); border: 1px solid var(--border-card); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                                ${escapeHtml(d.post_type || 'Outlines')}
                            </span>
                            ${!isAssign ? `
                            <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                                ${escapeHtml(stageDisplay)}
                            </span>
                            ` : ''}
                            {{-- Revision Count Badge --}}
                            <span id="card-editor-revision-badge-${d.id}" 
                                  style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em; color: #ef4444; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: ${hasRevs ? 'inline-flex' : 'none'}; align-items: center; gap: 3px;"
                                  title="${escapeHtml(revMsg ? ('Revision: ' + revMsg) : ('Revisions: ' + (revCount || 1)))}">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>REV: <span id="card-editor-revision-num-${d.id}">${revCount || 1}</span></span>
                            </span>
                        </div>
                        <span style="font-size: 10px; font-weight: 600; color: ${badgeColor}; background: ${badgeBg}; border: ${badgeBorder}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            ${escapeHtml(assigneeDisplay)}
                        </span>
                    </div>

                    {{-- Row 1: Title Input (clean, full-width) --}}
                    <div style="margin-bottom: 8px;">
                        <input type="text" 
                               name="task_title" 
                               id="card-input-title-${d.id}"
                               value="${escapeHtml(d.title)}"
                               placeholder="Task Name…" 
                               required
                               autocomplete="off"
                               style="width: 100%; border: none; background: transparent; font-size: 13.5px; font-weight: 700; color: var(--text-card-title); outline: none; padding: 2px 0;"
                               onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitInlineCardEdit(event, ${d.id}); } else if(event.key === 'Escape') { toggleCardEditor(${d.id}, false); }">
                    </div>

                    {{-- Revision Request Message Banner in Card Editor --}}
                    <div id="card-editor-revision-box-${d.id}" 
                         style="display: ${revMsg ? 'block' : 'none'}; margin-bottom: 10px; padding: 8px 10px; border-radius: 8px; background: rgba(239, 68, 68, 0.08); border: 1.5px solid rgba(239, 68, 68, 0.3);">
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 3px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Revision Message (<span id="card-editor-revision-box-num-${d.id}">Round ${revCount || 1}</span>)</span>
                        </div>
                        <div id="card-editor-revision-text-${d.id}" style="font-size: 11.5px; font-weight: 500; color: var(--color-text-primary); line-height: 1.4; word-break: break-word;">
                            ${escapeHtml(revMsg)}
                        </div>
                    </div>

                    {{-- Hidden post_type input (locked once set) --}}
                    <input type="hidden" name="post_type" id="card-input-post-type-${d.id}" value="${escapeHtml(d.post_type || '')}">

                    <div style="display: flex; flex-direction: column; gap: 6px; padding-top: 6px; border-top: 1px dashed var(--color-border-primary);">
                        {{-- Assignee --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <select name="writer_id" 
                                    id="card-input-writer-${d.id}"
                                    style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                                ${assigneeOptions}
                            </select>
                        </div>

                        {{-- Approver (hidden in To Do stage, shown in In Progress / Approval / Done) --}}
                        <div id="card-editor-approver-row-${d.id}" style="display: ${isColToDo ? 'none' : 'flex'}; align-items: center; gap: 8px;">
                            <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <select name="approver_id" 
                                    id="card-input-approver-${d.id}"
                                    style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                                ${approverOptions}
                            </select>
                        </div>

                        {{-- Deadline --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <input type="date" 
                                   name="deadline" 
                                   id="card-input-deadline-${d.id}"
                                   min="${new Date().toISOString().split('T')[0]}"
                                   value="${rawDate}"
                                   placeholder="Add dates…"
                                   style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                        </div>

                        {{-- Priority --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                            </svg>
                            <select name="priority" 
                                    id="card-input-priority-${d.id}"
                                    style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                                <option value="Medium" style="background: var(--color-bg-primary); color: var(--color-text-primary);" ${d.priority === 'Medium' ? 'selected' : ''}>Add priority (Medium)</option>
                                <option value="High Priority" style="background: var(--color-bg-primary); color: var(--color-text-primary);" ${(d.priority === 'High Priority' || d.priority === 'High') ? 'selected' : ''}>High Priority</option>
                                <option value="Urgent" style="background: var(--color-bg-primary); color: var(--color-text-primary);" ${d.priority === 'Urgent' ? 'selected' : ''}>Urgent</option>
                                <option value="Low" style="background: var(--color-bg-primary); color: var(--color-text-primary);" ${d.priority === 'Low' ? 'selected' : ''}>Low</option>
                            </select>
                        </div>
                    </div>

                    ${referencesHtml}
                    ${uploadedWorkHtml}

                    {{-- Footer actions: Details on left, Cancel & Save on right (All buttons at the bottom) --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--color-border-primary);">
                        <button type="button" 
                                onclick="event.stopPropagation(); toggleCardEditor(${d.id}, false); openCampaignDetailsModal(${d.id})"
                                style="background: transparent; border: none; font-size: 11px; font-weight: 600; color: #3b82f6; cursor: pointer; padding: 3px 4px; display: inline-flex; align-items: center; gap: 3px;"
                                title="View brief and references">
                            <span>Details</span>
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                        <div style="display: inline-flex; align-items: center; gap: 8px;">
                            <button type="button" 
                                    onclick="event.stopPropagation(); toggleCardEditor(${d.id}, false)"
                                    style="background: transparent; border: none; font-size: 11.5px; font-weight: 700; color: var(--color-text-secondary); cursor: pointer; padding: 4px 8px; border-radius: 6px;">
                                Cancel
                            </button>
                            <button type="submit" 
                                    id="card-btn-save-${d.id}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-md bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors border-0 cursor-pointer">
                                <span>Save</span>
                                <span style="font-size: 11px; opacity: 0.7;">↵</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            ` : `
            {{-- Expanded Read-only Details (Anyone can expand and see details) --}}
            <div class="kanban-card-editor" id="kanban-card-editor-${d.id}" style="display: none;" onclick="event.stopPropagation()">
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                        <div style="display: inline-flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                            <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                                ${escapeHtml(d.post_type || 'Outlines')}
                            </span>
                            ${!isAssign ? `
                            <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                                ${escapeHtml(stageDisplay)}
                            </span>
                            ` : ''}
                            {{-- Revision Count Badge --}}
                            <span id="card-readonly-revision-badge-${d.id}" 
                                  style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.02em; color: #ef4444; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); padding: 1px 5px; border-radius: 4px; line-height: 1.2; display: ${hasRevs ? 'inline-flex' : 'none'}; align-items: center; gap: 3px;"
                                  title="${escapeHtml(revMsg ? ('Revision: ' + revMsg) : ('Revisions: ' + (revCount || 1)))}">
                                <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Rev: <span id="card-readonly-revision-num-${d.id}">${revCount || 1}</span></span>
                            </span>
                        </div>
                        <span style="font-size: 10px; font-weight: 700; color: ${badgeColor}; background: ${badgeBg}; border: ${badgeBorder}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            ${escapeHtml(assigneeDisplay)}
                        </span>
                    </div>

                    <div style="margin-bottom: 8px;">
                        <h4 style="margin: 0; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); line-height: 1.35; letter-spacing: -0.01em;">
                            ${escapeHtml(d.title)}
                        </h4>
                    </div>

                    {{-- Revision Request Message Banner in Read-only View --}}
                    <div id="card-readonly-revision-box-${d.id}" 
                         style="display: ${revMsg ? 'block' : 'none'}; margin-bottom: 10px; padding: 8px 10px; border-radius: 8px; background: rgba(239, 68, 68, 0.08); border: 1.5px solid rgba(239, 68, 68, 0.3);">
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 800; color: #ef4444; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 3px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Revision Message (<span id="card-readonly-revision-box-num-${d.id}">Round ${revCount || 1}</span>)</span>
                        </div>
                        <div id="card-readonly-revision-text-${d.id}" style="font-size: 11.5px; font-weight: 500; color: var(--color-text-primary); line-height: 1.4; word-break: break-word;">
                            ${escapeHtml(revMsg)}
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 7px; padding-top: 8px; border-top: 1px dashed var(--color-border-primary);">
                        <div id="card-readonly-revisions-row-${d.id}" style="display: ${hasRevs ? 'flex' : 'none'}; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary); font-weight: 500;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Revisions</span>
                            </span>
                            <span style="font-weight: 700; color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); padding: 1px 6px; border-radius: 4px; font-size: 10.5px;">
                                <span id="card-readonly-revisions-total-${d.id}">${revCount || 1}</span> round(s)
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary); font-weight: 500;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Assignee</span>
                            </span>
                            <span style="font-weight: 600; color: var(--color-text-primary);">
                                ${escapeHtml(assigneeDisplay)}
                            </span>
                        </div>

                        {{-- Approver (hidden in To Do stage, shown in In Progress / Approval / Done) --}}
                        <div id="card-view-approver-row-${d.id}" style="display: ${isColToDo ? 'none' : 'flex'}; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary); font-weight: 500;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>Approver</span>
                            </span>
                            <span id="card-view-approver-name-${d.id}" style="font-weight: 600; color: ${d.approver_name ? 'var(--color-text-primary)' : 'var(--color-text-secondary)'};">
                                ${escapeHtml(d.approver_name || 'Unassigned')}
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary); font-weight: 500;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Due Date</span>
                            </span>
                            <span style="font-weight: 600; color: var(--color-text-primary);">
                                ${escapeHtml(formattedDate || 'No deadline')}
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary); font-weight: 500;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                                <span>Priority</span>
                            </span>
                            <span style="font-weight: 700; color: ${prioColor}; display: inline-flex; align-items: center; gap: 4px;">
                                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: ${prioColor};"></span>
                                <span>${escapeHtml(prio)}</span>
                            </span>
                        </div>
                    </div>

                    ${referencesHtml}
                    ${uploadedWorkHtml}

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--color-border-primary);">
                        <button type="button" 
                                onclick="event.stopPropagation(); toggleCardEditor(${d.id}, false); openCampaignDetailsModal(${d.id})"
                                style="background: transparent; border: none; font-size: 11px; font-weight: 600; color: #3b82f6; cursor: pointer; padding: 3px 4px; display: inline-flex; align-items: center; gap: 3px;"
                                title="View brief and references">
                            <span>Details</span>
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                        <button type="button" 
                                onclick="event.stopPropagation(); toggleCardEditor(${d.id}, false)"
                                style="background: transparent; border: 1px solid var(--color-border-primary); font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; padding: 3px 9px; border-radius: 6px;">
                            Close
                        </button>
                    </div>
                </div>
            </div>
            `}
        </div>
    `;

    container.insertAdjacentHTML('afterbegin', cardHtml);
}

function getFilenameFromUrl(url) {
    if (!url) return 'file';
    try {
        const clean = String(url).split('?')[0].split('#')[0];
        const parts = clean.split('/');
        return decodeURIComponent(parts[parts.length - 1]) || 'file';
    } catch (e) {
        return 'file';
    }
}

function getFileExt(filename) {
    if (!filename) return '';
    const parts = filename.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
}

function formatDateDisplay(dStr) {
    if (!dStr) return '';
    try {
        const d = new Date(dStr);
        if (isNaN(d.getTime())) return dStr;
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch (e) {
        return dStr;
    }
}

function formatDateTimeDisplay(dStr) {
    if (!dStr) return '';
    try {
        const d = new Date(dStr);
        if (isNaN(d.getTime())) return dStr;
        return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + 
               d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    } catch (e) {
        return dStr;
    }
}

function extractRevisionsHistory(data, existingTask) {
    if (!data) return existingTask?.revisions_history || [];
    const list = data.revisions_history || data.revisionsHistory;
    if (Array.isArray(list) && list.length > 0) {
        return list.map((r, idx) => ({
            round: r.round !== undefined ? Number(r.round) : (list.length - idx),
            user_name: r.user?.name || r.user_name || 'Approver',
            instructions: r.instructions || '',
            stage: r.stage_at_revision || r.stage || 'Approval Stage',
            image_path: r.image_path ? (r.image_path.startsWith('http') || r.image_path.startsWith('/') ? r.image_path : `/storage/${r.image_path}`) : null,
            revision_deadline: r.revision_deadline ? formatDateDisplay(r.revision_deadline) : null,
            date: r.date || (r.created_at ? formatDateTimeDisplay(r.created_at) : ''),
            created_at: r.created_at,
            fixed_at: r.fixed_at ? formatDateTimeDisplay(r.fixed_at) : null,
            fixed_by_user_name: r.fixed_by_user?.name || r.fixedByUser?.name || r.fixed_by_user_name || null
        }));
    }
    return existingTask?.revisions_history || [];
}

function extractApprovalsHistory(data, existingTask) {
    if (!data) return existingTask?.approvals_history || [];
    const list = data.approvals_history || data.approvalsHistory;
    if (Array.isArray(list) && list.length > 0) {
        return list.map(a => ({
            user_name: a.user?.name || a.user_name || 'Team Member',
            stage: a.stage || 'Approval Stage',
            notes: a.notes || '',
            date: a.date || (a.created_at ? formatDateTimeDisplay(a.created_at) : ''),
            created_at: a.created_at
        }));
    }
    return existingTask?.approvals_history || [];
}

function extractReferenceFiles(data) {
    if (!data) return [];
    if (data.reference_files_list && Array.isArray(data.reference_files_list)) {
        return data.reference_files_list;
    }
    if (data.reference_file) {
        if (typeof data.reference_file === 'string' && data.reference_file.startsWith('[')) {
            try { return JSON.parse(data.reference_file); } catch(e) { return [data.reference_file]; }
        } else if (Array.isArray(data.reference_file)) {
            return data.reference_file;
        } else if (typeof data.reference_file === 'string' && data.reference_file.trim()) {
            return [data.reference_file.trim()];
        }
    }
    return [];
}

function extractReferenceUrls(data) {
    if (!data) return [];
    if (data.reference_urls_list && Array.isArray(data.reference_urls_list)) {
        return data.reference_urls_list;
    }
    if (data.reference) {
        if (typeof data.reference === 'string' && data.reference.startsWith('[')) {
            try { return JSON.parse(data.reference); } catch(e) { return [data.reference]; }
        } else if (Array.isArray(data.reference)) {
            return data.reference;
        } else if (typeof data.reference === 'string' && data.reference.trim()) {
            return data.reference.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean);
        }
    }
    return [];
}

function copyReferenceUrlToClipboard(url, btn) {
    if (!navigator.clipboard) {
        prompt('Copy reference URL:', url);
        return;
    }
    navigator.clipboard.writeText(url).then(() => {
        const origText = btn.innerHTML;
        btn.innerHTML = 'Copied!';
        setTimeout(() => {
            btn.innerHTML = origText;
        }, 1500);
    }).catch(err => {
        prompt('Copy reference URL:', url);
    });
}

async function openCampaignDetailsModal(taskId) {
    const modal = document.getElementById('campaignDetailsModalOverlay');
    if (!modal) return;

    modal.dataset.currentTaskId = taskId;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Update URL hash without scrolling
    if (window.location.hash !== '#deliverable-' + taskId) {
        history.pushState({ taskId: taskId }, '', '#deliverable-' + taskId);
    }

    let task = campaignDeliverablesData[taskId] || null;
    if (task) {
        renderCampaignDetailsModalContent(task);
    } else {
        renderCampaignDetailsModalLoading(taskId);
    }

    try {
        const res = await fetch(`/deliverables/${taskId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (res.ok) {
            const freshData = await res.json();
            campaignDeliverablesData[taskId] = {
                id: freshData.id,
                title: freshData.title || task?.title || '',
                post_type: freshData.post_type || task?.post_type || 'Outlines',
                status: freshData.status || task?.status || 'To Do',
                priority: freshData.priority || task?.priority || 'Medium',
                deadline: freshData.deadline || task?.deadline || null,
                deadline_formatted: freshData.deadline ? formatDateDisplay(freshData.deadline) : (task?.deadline_formatted || null),
                concept: freshData.concept || task?.concept || '',
                caption: freshData.caption || task?.caption || '',
                post_copy: freshData.post_copy || freshData.subtask_copy || task?.post_copy || '',
                notes: freshData.notes || task?.notes || '',
                description: freshData.description || task?.description || '',
                reference_files_list: extractReferenceFiles(freshData),
                reference_urls_list: extractReferenceUrls(freshData),
                assignee_name: freshData.writer?.name || freshData.designer?.name || task?.assignee_name || 'Unassigned',
                approver_name: freshData.approver?.name || task?.approver_name || null,
                created_at_formatted: freshData.created_at ? formatDateDisplay(freshData.created_at) : (task?.created_at_formatted || null),
                revisions: freshData.revisions !== undefined ? Number(freshData.revisions) : (task?.revisions ?? 0),
                revision_instructions: freshData.revision_instructions || task?.revision_instructions || '',
                revision_deadline: freshData.revision_deadline ? formatDateDisplay(freshData.revision_deadline) : (task?.revision_deadline || null),
                revisions_history: extractRevisionsHistory(freshData, task),
                approvals_history: extractApprovalsHistory(freshData, task)
            };
            if (modal.style.display !== 'none' && modal.dataset.currentTaskId == taskId) {
                renderCampaignDetailsModalContent(campaignDeliverablesData[taskId]);
            }
        }
    } catch (err) {
        console.warn('Could not refresh deliverable details from server:', err);
    }
}

function closeCampaignDetailsModal() {
    const modal = document.getElementById('campaignDetailsModalOverlay');
    if (modal) {
        modal.style.display = 'none';
        modal.dataset.currentTaskId = '';
        document.body.style.overflow = '';
    }
    if (window.location.hash && window.location.hash.startsWith('#deliverable-')) {
        history.pushState(null, '', window.location.pathname + window.location.search);
    }
}

window.addEventListener('popstate', function() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#deliverable-')) {
        const taskId = hash.replace('#deliverable-', '');
        if (taskId) {
            openCampaignDetailsModal(taskId);
        }
    } else {
        const modal = document.getElementById('campaignDetailsModalOverlay');
        if (modal && modal.style.display !== 'none') {
            modal.style.display = 'none';
            modal.dataset.currentTaskId = '';
            document.body.style.overflow = '';
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash;
    if (hash && hash.startsWith('#deliverable-')) {
        const taskId = hash.replace('#deliverable-', '');
        if (taskId) {
            setTimeout(() => {
                openCampaignDetailsModal(taskId);
            }, 120);
        }
    }
});

function renderCampaignDetailsModalLoading(taskId) {
    const idEl = document.getElementById('campaignModalIdBadge');
    if (idEl) idEl.textContent = taskId ? '#' + taskId : '#...';

    const titleEl = document.getElementById('campaignModalTitle');
    if (titleEl) titleEl.textContent = 'Loading Deliverable Details…';

    const body = document.getElementById('campaignModalBody');
    if (body) {
        body.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 80px 20px; color: var(--color-text-secondary); gap: 14px;">
                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-linecap="round"/></svg>
                <div style="font-size: 14px; font-weight: 700; color: var(--color-text-primary);">Loading deliverable details…</div>
                <div style="font-size: 12px; color: var(--color-text-secondary);">Fetching brief, references, revision cycles, and stage history.</div>
            </div>
        `;
    }
}

function renderCampaignDetailsModalContent(task) {
    if (!task) return;

    const idEl = document.getElementById('campaignModalIdBadge');
    if (idEl) idEl.textContent = '#' + task.id;

    const titleEl = document.getElementById('campaignModalTitle');
    if (titleEl) titleEl.textContent = task.title || 'Untitled Deliverable';

    const ptEl = document.getElementById('campaignModalPostTypeBadge');
    if (ptEl) ptEl.textContent = task.post_type || 'Outlines';

    const stEl = document.getElementById('campaignModalStatusBadge');
    if (stEl) {
        stEl.textContent = task.status || 'To Do';
        const stLower = (task.status || '').toLowerCase().trim();
        if (['done', 'completed', 'complete'].includes(stLower)) {
            stEl.style.color = '#10b981';
            stEl.style.background = 'rgba(16, 185, 129, 0.1)';
            stEl.style.borderColor = 'rgba(16, 185, 129, 0.3)';
        } else if (['approval stage', 'approval', 'review', 'under review'].includes(stLower)) {
            stEl.style.color = '#f59e0b';
            stEl.style.background = 'rgba(245, 158, 11, 0.1)';
            stEl.style.borderColor = 'rgba(245, 158, 11, 0.3)';
        } else if (['in progress', 'on hold'].includes(stLower)) {
            stEl.style.color = '#6366f1';
            stEl.style.background = 'rgba(99, 102, 241, 0.1)';
            stEl.style.borderColor = 'rgba(99, 102, 241, 0.3)';
        } else {
            stEl.style.color = 'var(--color-text-secondary)';
            stEl.style.background = 'var(--color-bg-secondary)';
            stEl.style.borderColor = 'var(--color-border-primary)';
        }
    }

    const prio = task.priority || 'Medium';
    const prioDot = document.getElementById('campaignModalPriorityDot');
    const prioText = document.getElementById('campaignModalPriorityText');
    const prioBadge = document.getElementById('campaignModalPriorityBadge');
    if (prioText) prioText.textContent = prio;
    const prioColor = (prio === 'High Priority' || prio === 'Urgent' || prio === 'High') ? '#ef4444' : (prio === 'Low' ? '#64748b' : '#10b981');
    if (prioDot) prioDot.style.background = prioColor;
    if (prioBadge) {
        prioBadge.style.color = prioColor;
        prioBadge.style.borderColor = prioColor + '40';
        prioBadge.style.background = prioColor + '15';
    }

    const asEl = document.getElementById('campaignModalAssignee');
    if (asEl) asEl.textContent = task.assignee_name || 'Unassigned';

    const apEl = document.getElementById('campaignModalApprover');
    if (apEl) apEl.textContent = task.approver_name || 'Unassigned';

    const dlEl = document.getElementById('campaignModalDeadline');
    if (dlEl) dlEl.textContent = task.deadline_formatted || 'No deadline';

    const ftEl = document.getElementById('campaignModalFooterMeta');
    if (ftEl) {
        let metaParts = [`Deliverable #${task.id}`];
        if (task.created_at_formatted) metaParts.push(`Created: ${task.created_at_formatted}`);
        ftEl.textContent = metaParts.join(' • ');
    }

    const body = document.getElementById('campaignModalBody');
    if (!body) return;

    let html = '';

    // 1. Brief Section
    html += `
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--color-border-primary);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 26px; height: 26px; border-radius: 7px; background: rgba(59, 130, 246, 0.12); color: #2563eb; display: flex; align-items: center; justify-content: center;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-primary);">
                        Deliverable Brief
                    </span>
                </div>
            </div>
    `;

    const hasConcept = Boolean(task.concept && String(task.concept).trim());
    const hasCaption = Boolean(task.caption && String(task.caption).trim());
    const hasPostCopy = Boolean(task.post_copy && String(task.post_copy).trim());
    const hasNotes = Boolean(task.notes && String(task.notes).trim() && String(task.notes).trim() !== String(task.concept || '').trim());

    if (!hasConcept && !hasCaption && !hasPostCopy && !hasNotes) {
        html += `
            <div style="padding: 24px 20px; background: var(--color-bg-secondary); border: 1.5px dashed var(--color-border-primary); border-radius: 12px; text-align: center; color: var(--color-text-secondary);">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" style="margin: 0 auto 8px auto; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                <div style="font-size: 12.5px; font-weight: 500;">No brief or creative notes have been added yet for this deliverable.</div>
            </div>
        `;
    } else {
        html += `<div style="display: flex; flex-direction: column; gap: 14px;">`;

        if (hasConcept) {
            const isHtml = /<[a-z][\s\S]*>/i.test(task.concept);
            html += `
                <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; padding: 14px 16px;">
                    <div style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #3b82f6; margin-bottom: 6px;">Concept / Creative Brief</div>
                    <div class="brief-body" style="font-size: 13px; line-height: 1.6; color: var(--color-text-primary); ${isHtml ? '' : 'white-space: pre-wrap;'} word-break: break-word;">
                        ${isHtml ? task.concept : escapeHtml(task.concept)}
                    </div>
                </div>
            `;
        }

        if (hasCaption) {
            const isHtml = /<[a-z][\s\S]*>/i.test(task.caption);
            html += `
                <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; padding: 14px 16px;">
                    <div style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #10b981; margin-bottom: 6px;">Caption</div>
                    <div class="brief-body" style="font-size: 13px; line-height: 1.6; color: var(--color-text-primary); ${isHtml ? '' : 'white-space: pre-wrap;'} word-break: break-word;">
                        ${isHtml ? task.caption : escapeHtml(task.caption)}
                    </div>
                </div>
            `;
        }

        if (hasPostCopy) {
            const isHtml = /<[a-z][\s\S]*>/i.test(task.post_copy);
            html += `
                <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; padding: 14px 16px;">
                    <div style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #8b5cf6; margin-bottom: 6px;">Post Copy</div>
                    <div class="brief-body" style="font-size: 13px; line-height: 1.6; color: var(--color-text-primary); ${isHtml ? '' : 'white-space: pre-wrap;'} word-break: break-word;">
                        ${isHtml ? task.post_copy : escapeHtml(task.post_copy)}
                    </div>
                </div>
            `;
        }

        if (hasNotes) {
            html += `
                <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; padding: 14px 16px;">
                    <div style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #f59e0b; margin-bottom: 6px;">Additional Notes</div>
                    <div class="brief-body" style="font-size: 13px; line-height: 1.6; color: var(--color-text-primary); white-space: pre-wrap; word-break: break-word;">
                        ${escapeHtml(task.notes)}
                    </div>
                </div>
            `;
        }

        html += `</div>`;
    }
    html += `</div>`;

    // 2. References Section
    html += `
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--color-border-primary);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 26px; height: 26px; border-radius: 7px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-primary);">
                        References & Assets
                    </span>
                </div>
            </div>
    `;

    const refFiles = Array.isArray(task.reference_files_list) ? task.reference_files_list : [];
    const refUrls = Array.isArray(task.reference_urls_list) ? task.reference_urls_list : [];

    if (refFiles.length === 0 && refUrls.length === 0) {
        html += `
            <div style="padding: 24px 20px; background: var(--color-bg-secondary); border: 1.5px dashed var(--color-border-primary); border-radius: 12px; text-align: center; color: var(--color-text-secondary);">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.6" style="margin: 0 auto 8px auto; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                <div style="font-size: 12.5px; font-weight: 500;">No reference files or links attached to this deliverable.</div>
            </div>
        `;
    } else {
        html += `<div style="display: flex; flex-direction: column; gap: 16px;">`;

        // Reference Files
        if (refFiles.length > 0) {
            html += `
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); margin-bottom: 8px;">
                        Reference Files (${refFiles.length})
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px;">
            `;

            refFiles.forEach(fileUrl => {
                const fname = getFilenameFromUrl(fileUrl);
                const ext = getFileExt(fname);
                const isImg = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(ext);
                const isPdf = ext === 'pdf';
                const isVid = ['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(ext);

                html += `
                    <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.15s, box-shadow 0.15s;"
                         onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)';"
                         onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                `;

                if (isImg) {
                    html += `
                        <div style="height: 110px; width: 100%; background: #000; overflow: hidden; position: relative; cursor: pointer;"
                             onclick="previewDeliverableArtwork('${escapeHtml(fileUrl)}', '${escapeHtml(fname)}', '${escapeHtml(fileUrl)}')">
                            <img src="${escapeHtml(fileUrl)}" alt="${escapeHtml(fname)}" style="width: 100%; height: 100%; object-fit: cover;">
                            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.3); opacity: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 11px; font-weight: 700; gap: 4px; transition: opacity 0.15s;"
                                 onmouseover="this.style.opacity='1'"
                                 onmouseout="this.style.opacity='0'">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Preview</span>
                            </div>
                        </div>
                    `;
                } else if (isPdf) {
                    html += `
                        <div style="height: 80px; width: 100%; background: rgba(239, 68, 68, 0.08); display: flex; align-items: center; justify-content: center; gap: 8px; color: #ef4444; cursor: pointer;"
                             onclick="previewDeliverableArtwork('${escapeHtml(fileUrl)}', '${escapeHtml(fname)}', '${escapeHtml(fileUrl)}')">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.05em;">PDF FILE</span>
                        </div>
                    `;
                } else if (isVid) {
                    html += `
                        <div style="height: 80px; width: 100%; background: rgba(139, 92, 246, 0.08); display: flex; align-items: center; justify-content: center; gap: 8px; color: #8b5cf6; cursor: pointer;"
                             onclick="previewDeliverableArtwork('${escapeHtml(fileUrl)}', '${escapeHtml(fname)}', '${escapeHtml(fileUrl)}')">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.05em;">VIDEO FILE</span>
                        </div>
                    `;
                } else {
                    html += `
                        <div style="height: 80px; width: 100%; background: rgba(59, 130, 246, 0.08); display: flex; align-items: center; justify-content: center; gap: 8px; color: #2563eb;">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span style="font-size: 11px; font-weight: 800; letter-spacing: 0.05em;">${escapeHtml(ext.toUpperCase() || 'FILE')}</span>
                        </div>
                    `;
                }

                html += `
                        <div style="padding: 10px; display: flex; flex-direction: column; gap: 8px; flex: 1;">
                            <div style="font-size: 11px; font-weight: 700; color: var(--color-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(fname)}">
                                ${escapeHtml(fname)}
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-top: auto; padding-top: 6px; border-top: 1px solid var(--color-border-primary);">
                                <button type="button" 
                                        onclick="previewDeliverableArtwork('${escapeHtml(fileUrl)}', '${escapeHtml(fname)}', '${escapeHtml(fileUrl)}')" 
                                        style="background: transparent; border: 1px solid var(--color-border-primary); color: #3b82f6; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Preview</span>
                                </button>
                                <a href="${escapeHtml(fileUrl)}" download="${escapeHtml(fname)}" 
                                   style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;"
                                   title="Download file">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        // Reference Links
        if (refUrls.length > 0) {
            html += `
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); margin-bottom: 8px;">
                        Reference Links (${refUrls.length})
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
            `;

            refUrls.forEach(url => {
                html += `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 12px; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                            <svg width="14" height="14" fill="none" stroke="#2563eb" viewBox="0 0 24 24" stroke-width="2.2" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" 
                               style="font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: underline; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                               title="${escapeHtml(url)}">
                                ${escapeHtml(url)}
                            </a>
                        </div>
                        <div style="display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;">
                            <button type="button" 
                                    onclick="copyReferenceUrlToClipboard('${escapeHtml(url)}', this)" 
                                    style="background: transparent; border: 1px solid var(--color-border-primary); color: var(--color-text-secondary); font-size: 10.5px; font-weight: 600; padding: 3px 8px; border-radius: 6px; cursor: pointer;"
                                    title="Copy link to clipboard">
                                Copy
                            </button>
                            <a href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer" 
                               style="background: #2563eb; color: #ffffff; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 3px;">
                                <span>Open ↗</span>
                            </a>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        html += `</div>`;
    }
    html += `</div>`;

    // 3. Revision History Section
    const revs = Array.isArray(task.revisions_history) ? [...task.revisions_history] : [];
    // Fallback: If revisions count > 0 or revision_instructions exist but array is empty, synthesize latest entry
    if (revs.length === 0 && (Number(task.revisions) > 0 || task.revision_instructions)) {
        revs.push({
            round: Number(task.revisions) || 1,
            user_name: task.approver_name || 'Approver',
            instructions: task.revision_instructions || 'Revision requested',
            stage: 'Approval Stage',
            image_path: null,
            revision_deadline: task.revision_deadline || null,
            date: task.created_at_formatted || '',
            created_at: null,
            fixed_at: null,
            fixed_by_user_name: null
        });
    }

    html += `
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--color-border-primary);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 26px; height: 26px; border-radius: 7px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-primary);">
                        Revision History
                    </span>
                    ${revs.length > 0 ? `
                        <span style="font-size: 10px; font-weight: 800; background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3); padding: 1px 7px; border-radius: 9999px;">
                            ${revs.length} ${revs.length === 1 ? 'Round' : 'Rounds'}
                        </span>
                    ` : ''}
                </div>
            </div>
    `;

    if (revs.length === 0) {
        html += `
            <div style="padding: 20px; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div style="font-size: 12.5px; font-weight: 700; color: var(--color-text-primary);">No revisions requested</div>
                    <div style="font-size: 11.5px; color: var(--color-text-secondary); margin-top: 2px;">This deliverable has not required any revision cycles.</div>
                </div>
            </div>
        `;
    } else {
        html += `<div style="display: flex; flex-direction: column; gap: 12px;">`;
        revs.forEach((r, idx) => {
            const roundNum = r.round !== undefined ? r.round : (revs.length - idx);
            const dateStr = r.date || (r.created_at ? formatDateTimeDisplay(r.created_at) : '');
            const hasImage = Boolean(r.image_path);
            const isResolved = Boolean(r.fixed_at);

            html += `
                <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(245, 158, 11, 0.15); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.35); padding: 2px 8px; border-radius: 6px;">
                                Round ${roundNum}
                            </span>
                            <span style="font-size: 11.5px; font-weight: 700; color: var(--color-text-primary);">
                                By ${escapeHtml(r.user_name || 'Approver')}
                            </span>
                            <span style="font-size: 11px; color: var(--color-text-secondary);">
                                • from ${escapeHtml(r.stage || 'Approval Stage')}
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            ${isResolved ? `
                                <span style="font-size: 10px; font-weight: 700; background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 7px; border-radius: 5px; display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Resolved
                                </span>
                            ` : `
                                <span style="font-size: 10px; font-weight: 700; background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.3); padding: 2px 7px; border-radius: 5px;">
                                    Awaiting Updates
                                </span>
                            `}
                            ${dateStr ? `
                                <span style="font-size: 11px; color: var(--color-text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    ${escapeHtml(dateStr)}
                                </span>
                            ` : ''}
                        </div>
                    </div>

                    ${r.instructions ? `
                        <div style="padding: 10px 12px; background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 8px; font-size: 12.5px; line-height: 1.55; color: var(--color-text-primary); white-space: pre-wrap; word-break: break-word;">
                            ${escapeHtml(r.instructions)}
                        </div>
                    ` : ''}

                    ${r.revision_deadline ? `
                        <div style="font-size: 11px; color: #ef4444; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Revision Deadline: ${escapeHtml(r.revision_deadline)}
                        </div>
                    ` : ''}

                    ${hasImage ? `
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 2px;">
                            <button type="button" 
                                    onclick="previewDeliverableArtwork('${escapeHtml(r.image_path)}', 'Revision Markup - Round ${roundNum}', '${escapeHtml(r.image_path)}')" 
                                    style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: #3b82f6; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>View Annotated Markup</span>
                            </button>
                        </div>
                    ` : ''}

                    ${isResolved && r.fixed_at ? `
                        <div style="font-size: 11px; color: var(--color-text-secondary); margin-top: 2px; display: inline-flex; align-items: center; gap: 4px;">
                            <svg width="12" height="12" fill="none" stroke="#10b981" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>Resolved on ${escapeHtml(r.fixed_at)}${r.fixed_by_user_name ? ' by ' + escapeHtml(r.fixed_by_user_name) : ''}</span>
                        </div>
                    ` : ''}
                </div>
            `;
        });
        html += `</div>`;
    }
    html += `</div>`;

    // 4. Sent for Approval & Stage History Section
    const approvals = Array.isArray(task.approvals_history) ? [...task.approvals_history] : [];

    html += `
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--color-border-primary);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 26px; height: 26px; border-radius: 7px; background: rgba(59, 130, 246, 0.12); color: #2563eb; display: flex; align-items: center; justify-content: center;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-primary);">
                        Sent for Approval & Stage History
                    </span>
                    ${approvals.length > 0 ? `
                        <span style="font-size: 10px; font-weight: 800; background: rgba(59, 130, 246, 0.12); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.25); padding: 1px 7px; border-radius: 9999px;">
                            ${approvals.length} ${approvals.length === 1 ? 'Event' : 'Events'}
                        </span>
                    ` : ''}
                </div>
            </div>
    `;

    if (approvals.length === 0) {
        html += `
            <div style="padding: 20px; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 12px; display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(59, 130, 246, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div style="font-size: 12.5px; font-weight: 700; color: var(--color-text-primary);">No approval submissions logged yet</div>
                    <div style="font-size: 11.5px; color: var(--color-text-secondary); margin-top: 2px;">When this deliverable is submitted for review or approved, stage history will appear here.</div>
                </div>
            </div>
        `;
    } else {
        html += `<div style="display: flex; flex-direction: column; gap: 10px;">`;
        approvals.forEach((a) => {
            const dateStr = a.date || (a.created_at ? formatDateTimeDisplay(a.created_at) : '');
            const stageLower = (a.stage || '').toLowerCase();
            const isApproved = stageLower.includes('approved') || stageLower.includes('complete');

            const badgeColor = isApproved ? '#10b981' : '#2563eb';
            const badgeBg = isApproved ? 'rgba(16, 185, 129, 0.12)' : 'rgba(59, 130, 246, 0.12)';
            const badgeBorder = isApproved ? 'rgba(16, 185, 129, 0.3)' : 'rgba(59, 130, 246, 0.25)';

            html += `
                <div style="background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; background: ${badgeBg}; color: ${badgeColor}; border: 1px solid ${badgeBorder}; padding: 2px 8px; border-radius: 6px;">
                                ${escapeHtml(a.stage || 'Stage Update')}
                            </span>
                            <span style="font-size: 11.5px; font-weight: 700; color: var(--color-text-primary);">
                                ${escapeHtml(a.user_name || 'Team Member')}
                            </span>
                        </div>
                        ${dateStr ? `
                            <span style="font-size: 11px; color: var(--color-text-secondary); display: inline-flex; align-items: center; gap: 4px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                ${escapeHtml(dateStr)}
                            </span>
                        ` : ''}
                    </div>
                    ${a.notes ? `
                        <div style="font-size: 12px; color: var(--color-text-secondary); line-height: 1.45; padding-left: 2px;">
                            ${escapeHtml(a.notes)}
                        </div>
                    ` : ''}
                </div>
            `;
        });
        html += `</div>`;
    }
    html += `</div>`;

    body.innerHTML = html;
}

// Collapse card editor or inline creator on click outside
document.addEventListener('click', function(event) {
    if (isDraggingKanbanCard) return;

    // 1. Deliverable Card Editor: Collapse on click outside
    if (currentlyEditingCardId && currentlyEditingCardId !== justOpenedCardId) {
        const card = document.getElementById('kanban-card-' + currentlyEditingCardId);
        if (card && !card.contains(event.target)) {
            toggleCardEditor(currentlyEditingCardId, false);
        }
    }

    // 2. Inline Task Creator: Close on click outside
    ['todo', 'inprogress', 'approval', 'complete'].forEach(colKey => {
        if (justOpenedInlineCreator === colKey) return;
        const creator = document.getElementById('inline-creator-' + colKey);
        const btn = document.getElementById('btn-open-creator-' + colKey);
        if (creator && creator.style.display !== 'none') {
            if (!creator.contains(event.target) && (!btn || !btn.contains(event.target))) {
                closeInlineCreator(colKey);
            }
        }
    });
});

// Escape key collapses card editor and closes inline creator / modals
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const artworkModal = document.getElementById('artworkPreviewModal');
        if (artworkModal && artworkModal.style.display !== 'none') {
            closeArtworkPreviewModal();
            return;
        }
        closeCampaignDetailsModal();
        closeUploadDeliverableModal();
        closeAssignApproverModal();
        closeRevisionPromptModal();
        if (currentlyEditingCardId) {
            toggleCardEditor(currentlyEditingCardId, false);
        }
        ['todo', 'inprogress', 'approval', 'complete'].forEach(colKey => {
            const creator = document.getElementById('inline-creator-' + colKey);
            if (creator && creator.style.display !== 'none') {
                closeInlineCreator(colKey);
            }
        });
    }
});
</script>

{{-- Modal: Upload Deliverable File when moving to Approval Stage --}}
<div id="uploadDeliverableModal" 
     style="display: none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; background: rgba(0,0,0,0.55); backdrop-filter: blur(3px); padding: 16px;" 
     onclick="if(event.target === this) closeUploadDeliverableModal()">
    <div style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 14px; width: 100%; max-width: 460px; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-border-primary); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(59, 130, 246, 0.12); color: #2563eb; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--color-text-primary);">Submit for Approval</h3>
            </div>
            <button type="button" onclick="closeUploadDeliverableModal()" style="background: transparent; border: none; cursor: pointer; color: var(--color-text-secondary); padding: 4px; border-radius: 6px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding: 20px; display: flex; flex-direction: column; gap: 14px;">
            <p style="margin: 0; font-size: 12.5px; color: var(--color-text-secondary); line-height: 1.4;">
                Please upload the completed deliverable file or design for <strong id="uploadDeliverableModalTaskTitle" style="color: var(--color-text-primary);">this deliverable</strong> before submitting to the Approval Stage.
            </p>
            
            {{-- File Upload Box --}}
            <div>
                <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); margin-bottom: 6px;">Deliverable File / Artwork</label>
                <label for="uploadDeliverableModalFileInput" 
                       style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1.5px dashed rgba(59, 130, 246, 0.4); border-radius: 10px; cursor: pointer; background: rgba(59, 130, 246, 0.04); transition: border-color 0.15s;"
                       onmouseenter="this.style.borderColor='#2563eb'" 
                       onmouseleave="this.style.borderColor='rgba(59, 130, 246, 0.4)'">
                    <svg width="20" height="20" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <div style="flex: 1; min-width: 0;">
                        <div id="uploadDeliverableModalFileLabel" style="font-size: 12px; font-weight: 700; color: #2563eb; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Choose file(s)…</div>
                        <div style="font-size: 10px; color: var(--color-text-secondary); margin-top: 1px;">Upload images or files (multiple allowed)</div>
                    </div>
                    <input id="uploadDeliverableModalFileInput" type="file" multiple accept="image/*,video/*,application/pdf,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt" style="display: none;" onchange="if(this.files && this.files.length) { document.getElementById('uploadDeliverableModalFileLabel').textContent = Array.from(this.files).map(f => f.name).join(', '); }">
                </label>
            </div>

            {{-- Optional Approver Selection (if deliverable doesn't have an approver assigned yet) --}}
            <div id="uploadDeliverableApproverGroup" style="display: none;">
                <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); margin-bottom: 6px;">Assign Approver</label>
                <select id="uploadDeliverableApproverSelect" style="width: 100%; padding: 9px 12px; font-size: 13px; font-weight: 600; background: var(--color-bg-secondary); color: var(--color-text-primary); border: 1px solid var(--color-border-primary); border-radius: 8px; outline: none;">
                    <option value="">Choose an Approver…</option>
                </select>
            </div>
        </div>
        <div style="padding: 12px 20px; background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-primary); display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" onclick="closeUploadDeliverableModal()" style="background: transparent; border: 1px solid var(--color-border-primary); padding: 7px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; color: var(--color-text-primary); cursor: pointer;">
                Cancel
            </button>
            <button type="button" id="uploadDeliverableModalSubmitBtn" onclick="confirmUploadDeliverable()" style="background: #2563eb; border: none; padding: 7px 16px; font-size: 12px; font-weight: 700; border-radius: 8px; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35);">
                <span>Upload & Submit to Approval</span>
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Modal: Assign Approver when moving to Approval Stage --}}
<div id="assignApproverModal" 
     style="display: none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; background: rgba(0,0,0,0.55); backdrop-filter: blur(3px); padding: 16px;" 
     onclick="if(event.target === this) closeAssignApproverModal()">
    <div style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 14px; width: 100%; max-width: 440px; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-border-primary); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(245, 158, 11, 0.12); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--color-text-primary);">Assign Approver</h3>
            </div>
            <button type="button" onclick="closeAssignApproverModal()" style="background: transparent; border: none; cursor: pointer; color: var(--color-text-secondary); padding: 4px; border-radius: 6px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding: 20px;">
            <p style="margin: 0 0 8px 0; font-size: 12.5px; color: var(--color-text-secondary); line-height: 1.4;">
                Select who will approve <strong id="assignApproverModalTaskTitle" style="color: var(--color-text-primary);">this deliverable</strong>:
            </p>
            <div style="margin-top: 12px;">
                <label style="display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); margin-bottom: 6px;">Approver</label>
                <select id="assignApproverModalSelect" style="width: 100%; padding: 9px 12px; font-size: 13px; font-weight: 600; background: var(--color-bg-secondary); color: var(--color-text-primary); border: 1px solid var(--color-border-primary); border-radius: 8px; outline: none;">
                    <option value="">Choose an Approver…</option>
                </select>
            </div>
        </div>
        <div style="padding: 12px 20px; background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-primary); display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" onclick="closeAssignApproverModal()" style="background: transparent; border: 1px solid var(--color-border-primary); padding: 7px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; color: var(--color-text-primary); cursor: pointer;">
                Cancel
            </button>
            <button type="button" onclick="confirmAssignApprover()" style="background: #f59e0b; border: none; padding: 7px 16px; font-size: 12px; font-weight: 700; border-radius: 8px; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);">
                <span>Submit to Approver</span>
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Modal: Send Back for Revisions --}}
<div id="revisionPromptModal" 
     style="display: none; position: fixed; inset: 0; z-index: 99999; align-items: center; justify-content: center; background: rgba(0,0,0,0.55); backdrop-filter: blur(3px); padding: 16px;" 
     onclick="if(event.target === this) closeRevisionPromptModal()">
    <div style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 14px; width: 100%; max-width: 440px; box-shadow: 0 20px 45px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--color-border-primary); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: var(--color-text-primary);">Send Back for Revisions</h3>
            </div>
            <button type="button" onclick="closeRevisionPromptModal()" style="background: transparent; border: none; cursor: pointer; color: var(--color-text-secondary); padding: 4px; border-radius: 6px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding: 20px;">
            <p style="margin: 0 0 10px 0; font-size: 12.5px; color: var(--color-text-secondary); line-height: 1.4;">
                Returning <strong id="revisionPromptTaskTitle" style="color: var(--color-text-primary);">this deliverable</strong> to the assignee for revisions in <strong>In Progress</strong>. Add feedback or instructions (optional):
            </p>
            <div>
                <textarea id="revisionPromptInstructions" 
                          rows="3" 
                          placeholder="Describe the changes or revisions required…" 
                          style="width: 100%; padding: 10px 12px; font-size: 12.5px; line-height: 1.4; background: var(--color-bg-secondary); color: var(--color-text-primary); border: 1px solid var(--color-border-primary); border-radius: 8px; outline: none; resize: vertical;"></textarea>
            </div>
        </div>
        <div style="padding: 12px 20px; background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-primary); display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" onclick="closeRevisionPromptModal()" style="background: transparent; border: 1px solid var(--color-border-primary); padding: 7px 14px; font-size: 12px; font-weight: 600; border-radius: 8px; color: var(--color-text-primary); cursor: pointer;">
                Cancel
            </button>
            <button type="button" onclick="confirmRevisionPrompt()" style="background: #ef4444; border: none; padding: 7px 16px; font-size: 12px; font-weight: 700; border-radius: 8px; color: #ffffff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);">
                <span>Request Revision</span>
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
            </button>
        </div>
    </div>
</div>

{{-- Modal: In-Page Deliverable Artwork / Work Preview with Download Option Inside --}}
<div id="artworkPreviewModal" 
     style="display: none; position: fixed; inset: 0; z-index: 100000; align-items: center; justify-content: center; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); padding: 16px;" 
     onclick="if(event.target === this) closeArtworkPreviewModal()">
    <div style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 14px; width: 100%; max-width: 900px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 60px rgba(0,0,0,0.4); overflow: hidden;">
        {{-- Modal Header --}}
        <div style="padding: 12px 18px; border-bottom: 1px solid var(--color-border-primary); display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-shrink: 0; background: var(--color-bg-secondary);">
            <div style="display: flex; align-items: center; gap: 10px; min-width: 0; flex: 1;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(59, 130, 246, 0.12); color: #2563eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div style="min-width: 0; flex: 1;">
                    <h3 id="artworkPreviewTitle" style="margin: 0; font-size: 14px; font-weight: 700; color: var(--color-text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">Deliverable File Preview</h3>
                    <div id="artworkPreviewMeta" style="font-size: 11px; color: var(--color-text-secondary); margin-top: 1px;">Uploaded Deliverable Work</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                {{-- Download Button Inside the Popup --}}
                <a id="artworkPreviewDownloadBtn" 
                   href="#" 
                   download 
                   style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 15px; border-radius: 8px; background: #10b981; color: #ffffff; font-size: 12px; font-weight: 700; text-decoration: none; box-shadow: 0 2px 6px rgba(16,185,129,0.3); transition: all 0.15s ease;"
                   onmouseover="this.style.background='#059669'" 
                   onmouseout="this.style.background='#10b981'"
                   title="Download this file">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download</span>
                </a>
                <button type="button" 
                        onclick="closeArtworkPreviewModal()" 
                        style="background: transparent; border: 1px solid var(--color-border-primary); cursor: pointer; color: var(--color-text-secondary); width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.15s;"
                        onmouseover="this.style.color='var(--color-text-primary)'; this.style.borderColor='var(--color-text-primary)';"
                        onmouseout="this.style.color='var(--color-text-secondary)'; this.style.borderColor='var(--color-border-primary)';"
                        title="Close (Esc)">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        
        {{-- Modal Content / Preview Area --}}
        <div id="artworkPreviewContent" style="padding: 16px; overflow: auto; flex: 1; display: flex; align-items: center; justify-content: center; min-height: 260px; background: var(--color-bg-primary);">
            {{-- Dynamic content injected here --}}
        </div>
    </div>
</div>

{{-- Full Page View: Deliverable Brief, References, Revision History & Stage Progression --}}
<div id="campaignDetailsModalOverlay" 
     style="display: none; position: fixed; inset: 0; z-index: 99990; width: 100vw; height: 100vh; background: var(--color-bg-primary); flex-direction: column; overflow: hidden;">
    <div id="campaignDeliverableDetailsModal" 
         style="background: var(--color-bg-primary); width: 100%; height: 100%; display: flex; flex-direction: column; overflow: hidden;">
        
        {{-- Full Page Top Navigation Bar --}}
        <div style="padding: 16px 28px; border-bottom: 1px solid var(--color-border-primary); background: var(--color-bg-secondary); flex-shrink: 0;">
            <div style="max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 14px; min-width: 0; flex-wrap: wrap;">
                    <button type="button" 
                            onclick="closeCampaignDetailsModal()" 
                            style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary); font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; flex-shrink: 0;"
                            onmouseover="this.style.borderColor='var(--color-text-primary)';"
                            onmouseout="this.style.borderColor='var(--color-border-primary)';"
                            title="Back to Kanban Board (Esc)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Back to Board</span>
                    </button>
                    
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span id="campaignModalIdBadge" style="font-size: 11px; font-family: monospace; font-weight: 800; background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-secondary); padding: 2px 7px; border-radius: 5px;">
                            #...
                        </span>
                        <span id="campaignModalPostTypeBadge" style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #3b82f6; background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.25); padding: 2px 8px; border-radius: 5px;">
                            Outlines
                        </span>
                        <span id="campaignModalStatusBadge" style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); padding: 2px 8px; border-radius: 5px;">
                            To Do
                        </span>
                        <span id="campaignModalPriorityBadge" style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #10b981; background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); padding: 2px 8px; border-radius: 5px; display: inline-flex; align-items: center; gap: 5px;">
                            <span id="campaignModalPriorityDot" style="width: 5px; height: 5px; border-radius: 50%; background: #10b981;"></span>
                            <span id="campaignModalPriorityText">Medium</span>
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                    <button type="button" 
                            onclick="closeCampaignDetailsModal()" 
                            style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-secondary); font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 4px;"
                            onmouseover="this.style.color='var(--color-text-primary)'; this.style.borderColor='var(--color-text-primary)';"
                            onmouseout="this.style.color='var(--color-text-secondary)'; this.style.borderColor='var(--color-border-primary)';"
                            title="Close full page (Esc)">
                        <span>Esc</span>
                    </button>
                    <button type="button" 
                            onclick="closeCampaignDetailsModal()" 
                            style="background: transparent; border: 1px solid var(--color-border-primary); cursor: pointer; color: var(--color-text-secondary); width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.15s;"
                            onmouseover="this.style.color='var(--color-text-primary)'; this.style.borderColor='var(--color-text-primary)';"
                            onmouseout="this.style.color='var(--color-text-secondary)'; this.style.borderColor='var(--color-border-primary)';"
                            title="Close (Esc)">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Title Row in Header --}}
            <div style="max-width: 1400px; margin: 12px auto 0 auto;">
                <h1 id="campaignModalTitle" style="margin: 0; font-size: 24px; font-weight: 900; color: var(--color-text-primary); line-height: 1.3; letter-spacing: -0.015em; word-break: break-word;">
                    Deliverable Details
                </h1>
            </div>
        </div>

        {{-- Meta Information Bar --}}
        <div style="padding: 12px 28px; background: var(--color-bg-primary); border-bottom: 1px solid var(--color-border-primary); flex-shrink: 0;">
            <div style="max-width: 1400px; margin: 0 auto; display: flex; align-items: center; gap: 24px; flex-wrap: wrap; font-size: 12px;">
                <div style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Assignee:</span>
                    <strong id="campaignModalAssignee" style="color: var(--color-text-primary); font-weight: 700;">Unassigned</strong>
                </div>
                <div style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Approver:</span>
                    <strong id="campaignModalApprover" style="color: var(--color-text-primary); font-weight: 700;">Unassigned</strong>
                </div>
                <div style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Due Date:</span>
                    <strong id="campaignModalDeadline" style="color: var(--color-text-primary); font-weight: 700;">No deadline</strong>
                </div>
                <div style="display: inline-flex; align-items: center; gap: 6px; color: var(--color-text-secondary); margin-left: auto;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Project:</span>
                    <strong style="color: var(--color-text-primary); font-weight: 700;">{{ $project->name }}</strong>
                </div>
            </div>
        </div>

        {{-- Full Page Scrollable Content Body --}}
        <div style="overflow-y: auto; flex: 1; width: 100%;">
            <div id="campaignModalBody" style="max-width: 1400px; margin: 0 auto; width: 100%; padding: 32px 28px 60px 28px; display: flex; flex-direction: column; gap: 32px;">
                {{-- Content injected by renderCampaignDetailsModalContent() --}}
            </div>
        </div>

        {{-- Full Page Bottom Bar --}}
        <div style="padding: 14px 28px; background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-primary); flex-shrink: 0;">
            <div style="max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                <div id="campaignModalFooterMeta" style="font-size: 11.5px; color: var(--color-text-secondary);">
                    {{-- ID / Created date --}}
                </div>
                <button type="button" 
                        onclick="closeCampaignDetailsModal()" 
                        style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary); font-size: 12px; font-weight: 700; padding: 7px 18px; border-radius: 8px; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 6px;"
                        onmouseover="this.style.background='var(--color-bg-secondary)';"
                        onmouseout="this.style.background='var(--color-bg-primary)';">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Kanban Board</span>
                </button>
            </div>
        </div>
    </div>
</div>
