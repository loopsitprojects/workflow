@php
    $tasks = $project->deliverables->whereNull('parent_deliverable_id');

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
             style="background: var(--color-bg-secondary); border: 1.5px solid var(--color-border-primary); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: var(--color-bg-primary); border: 1.5px dashed #94a3b8; color: var(--color-text-secondary); font-size: 11.5px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-dasharray="3 3">
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                        <span>TO DO</span>
                    </div>
                    <span id="badge-count-todo" style="font-size: 13px; font-weight: 800; color: var(--color-text-secondary);">{{ $todoTasks->count() }}</span>
                </div>
            </div>

            {{-- Inline Add Task Card for TO DO --}}
            <div id="inline-creator-todo" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'To Do'])
            </div>

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-todo" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($todoTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-todo"
                    onclick="openInlineCreator('todo')"
                    style="background: transparent; border: none; padding: 8px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; color: var(--color-text-secondary); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; margin-top: auto;"
                    onmouseover="this.style.background='var(--color-bg-primary)'; this.style.color='var(--color-text-primary)'"
                    onmouseout="this.style.background='transparent'; this.style.color='var(--color-text-secondary)'">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
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
             style="background: rgba(99, 102, 241, 0.04); border: 1.5px solid rgba(99, 102, 241, 0.2); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #6366f1; color: #ffffff; font-size: 11.5px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 2px 6px rgba(99, 102, 241, 0.25);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                        <span>IN PROGRESS</span>
                    </div>
                    <span id="badge-count-inprogress" style="font-size: 13px; font-weight: 800; color: #6366f1;">{{ $inProgressTasks->count() }}</span>
                </div>
            </div>

            {{-- Inline Add Task Card for IN PROGRESS --}}
            <div id="inline-creator-inprogress" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'In Progress'])
            </div>

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-inprogress" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($inProgressTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-inprogress"
                    onclick="openInlineCreator('inprogress')"
                    style="background: transparent; border: none; padding: 8px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; color: #6366f1; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; margin-top: auto;"
                    onmouseover="this.style.background='rgba(99, 102, 241, 0.1)'"
                    onmouseout="this.style.background='transparent'">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
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
             style="background: rgba(245, 158, 11, 0.04); border: 1.5px solid rgba(245, 158, 11, 0.2); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #f59e0b; color: #ffffff; font-size: 11.5px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.25);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>APPROVAL STAGE</span>
                    </div>
                    <span id="badge-count-approval" style="font-size: 13px; font-weight: 800; color: #f59e0b;">{{ $approvalTasks->count() }}</span>
                </div>
            </div>

            {{-- Inline Add Task Card for APPROVAL STAGE --}}
            <div id="inline-creator-approval" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'Approval Stage'])
            </div>

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-approval" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($approvalTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-approval"
                    onclick="openInlineCreator('approval')"
                    style="background: transparent; border: none; padding: 8px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; color: #f59e0b; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; margin-top: auto;"
                    onmouseover="this.style.background='rgba(245, 158, 11, 0.1)'"
                    onmouseout="this.style.background='transparent'">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
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
             style="background: rgba(16, 185, 129, 0.04); border: 1.5px solid rgba(16, 185, 129, 0.2); border-radius: 18px; padding: 16px; min-height: 480px; display: flex; flex-direction: column; gap: 12px; transition: all 0.2s ease;">
            
            {{-- Column Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 2px 4px 6px 4px;">
                <div style="display: inline-flex; align-items: center; gap: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #10b981; color: #ffffff; font-size: 11.5px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>COMPLETE</span>
                    </div>
                    <span id="badge-count-complete" style="font-size: 13px; font-weight: 800; color: #10b981;">{{ $completeTasks->count() }}</span>
                </div>
            </div>

            {{-- Inline Add Task Card for COMPLETE --}}
            <div id="inline-creator-complete" style="display: none;">
                @include('projects.partials.kanban_inline_creator', ['colStatus' => 'Done'])
            </div>

            {{-- Cards List --}}
            <div class="kanban-cards-list" id="cards-container-complete" style="display: flex; flex-direction: column; gap: 10px; min-height: 40px;">
                @foreach($completeTasks as $task)
                    @include('projects.partials.kanban_card', ['task' => $task])
                @endforeach
            </div>

            {{-- Add Task Button --}}
            <button type="button" 
                    id="btn-open-creator-complete"
                    onclick="openInlineCreator('complete')"
                    style="background: transparent; border: none; padding: 8px 10px; border-radius: 10px; font-size: 12px; font-weight: 700; color: #10b981; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.15s; margin-top: auto;"
                    onmouseover="this.style.background='rgba(16, 185, 129, 0.1)'"
                    onmouseout="this.style.background='transparent'">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add Task</span>
            </button>
        </div>

    </div>
</div>

{{-- Kanban Drag & Drop and Quick Creator JavaScript --}}
<script>
let draggedTaskId = null;
let draggedCardElement = null;
let currentlyEditingCardId = null;
let isDraggingKanbanCard = false;

const kanbanSelectableUsers = @json($selectableUsers->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role]));
const kanbanSubtaskTypes = @json($subtaskTypes->map(fn($st) => ['name' => $st->name]));
const kanbanCurrentUserId = {{ auth()->id() ?? 'null' }};
const kanbanCurrentUserRole = "{{ auth()->user()?->role ?? '' }}";
const kanbanIsAdminOrManager = {{ (auth()->user() && (auth()->user()->isAdmin() || in_array(auth()->user()->role, ['Admin', 'Owner', 'Operations Manager']))) ? 'true' : 'false' }};
const kanbanProjectApproverId = {{ $project->approver_id ?? ($project->brand_manager_id ?? ($project->lead_id ?? 'null')) }};

function checkCardMovePermission(d) {
    const status = (d.status || 'To Do').trim();
    const statusLower = status.toLowerCase();
    const isInApproval = ['approval stage', 'approval', 'review', 'under review'].includes(statusLower);
    const isDone = ['done', 'completed', 'complete'].includes(statusLower);

    const assignedId = d.writer_id || d.designer_id || null;
    const isAssigned = (assignedId && kanbanCurrentUserId == assignedId);
    const isUnassigned = !assignedId;

    const approverId = d.approver_id || kanbanProjectApproverId;
    const isApprover = (approverId && kanbanCurrentUserId == approverId) ||
                       (!approverId && ['Approver', 'Approver Coordinator', 'Brand Manager'].includes(kanbanCurrentUserRole));

    if (isInApproval) {
        if (isApprover || kanbanIsAdminOrManager) {
            return { canMove: true, reason: '' };
        }
        return { canMove: false, reason: 'Submitted to approver. Only the assigned approver can move this deliverable.' };
    }

    if (isDone) {
        if (isApprover || kanbanIsAdminOrManager) {
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
    if (stageBadge && (d.approval_stage || d.stage)) {
        stageBadge.textContent = d.approval_stage || d.stage;
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

    // Update status border accent
    updateCardStatusUI(card, targetStatus);
    recalculateKanbanCounts();

    // Call API to persist
    try {
        const response = await fetch('/deliverables/' + taskId + '/update-kanban-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ status: targetStatus })
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
            if (result.approval_stage) {
                const stageBadge = document.getElementById('card-view-stage-' + taskId);
                if (stageBadge) stageBadge.textContent = result.approval_stage;
            }
            if (typeof result.can_move !== 'undefined') {
                card.setAttribute('data-can-move', result.can_move ? 'true' : 'false');
                card.setAttribute('draggable', result.can_move ? 'true' : 'false');
                card.style.cursor = result.can_move ? 'grab' : 'pointer';
                if (!result.can_move) {
                    const reason = (targetStatus === 'Approval Stage')
                        ? 'Submitted to approver. Only the approver can move this deliverable.'
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

    const initial = d.assignee_name && d.assignee_name !== 'Unassigned' 
        ? d.assignee_name.trim().charAt(0).toUpperCase() 
        : 'U';

    const prio = d.priority || 'Medium';
    const prioColor = (prio === 'High Priority' || prio === 'Urgent' || prio === 'High') ? '#ef4444' : (prio === 'Low' ? '#64748b' : '#10b981');

    let borderLeft = 'border-left: 3px solid var(--color-border-primary);';
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
        postTypeOptions += `<option value="${escapeHtml(st.name)}" ${isSelected}>${escapeHtml(st.name)}</option>`;
    });

    // Build assignee options
    let assigneeOptions = `<option value="">Add assignee…</option>`;
    kanbanSelectableUsers.forEach(u => {
        const isSelected = (d.writer_id == u.id) ? 'selected' : '';
        assigneeOptions += `<option value="${u.id}" ${isSelected}>${escapeHtml(u.name)} (${escapeHtml(u.role)})</option>`;
    });

    // Build approver options
    let approverOptions = `<option value="">Add approver…</option>`;
    kanbanSelectableUsers.forEach(u => {
        const isSelected = (d.approver_id == u.id) ? 'selected' : '';
        approverOptions += `<option value="${u.id}" ${isSelected}>${escapeHtml(u.name)} (${escapeHtml(u.role)})</option>`;
    });

    const perm = checkCardMovePermission(d);
    const canMove = perm.canMove;
    const restrictionReason = perm.reason;

    const hasAssignee = d.assignee_name && d.assignee_name !== 'Unassigned';
    const assigneeDisplay = hasAssignee ? d.assignee_name : 'Unassigned';
    const badgeColor = hasAssignee ? '#3b82f6' : 'var(--color-text-secondary)';
    const badgeBg = hasAssignee ? 'rgba(59,130,246,0.1)' : 'var(--color-bg-secondary)';
    const badgeBorder = hasAssignee ? '1px solid rgba(59,130,246,0.2)' : '1px solid var(--color-border-primary)';
    const stageDisplay = d.approval_stage || d.stage || 'Assignee';

    const cardHtml = `
        <div class="kanban-card" 
             id="kanban-card-${d.id}"
             data-id="${d.id}"
             data-status="${escapeHtml(d.status || 'To Do')}"
             data-can-move="${canMove ? 'true' : 'false'}"
             data-restriction-reason="${escapeHtml(restrictionReason)}"
             draggable="${canMove ? 'true' : 'false'}"
             ondragstart="kanbanOnDragStart(event, ${d.id})"
             ondragend="kanbanOnDragEnd(event)"
             onclick="handleCardClick(${d.id})"
             style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); ${borderLeft} border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.03); cursor: ${canMove ? 'grab' : 'pointer'}; transition: all 0.15s ease; position: relative;"
             title="${escapeHtml(restrictionReason)}"
             onmouseover="if(!isCardEditorOpen(${d.id})) { this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'; this.style.transform='translateY(-1px)'; }"
             onmouseout="if(!isCardEditorOpen(${d.id})) { this.style.boxShadow='0 1px 4px rgba(0,0,0,0.03)'; this.style.transform='none'; }">
            
            {{-- Collapsed Card View --}}
            <div class="kanban-card-view" id="kanban-card-view-${d.id}">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                    <div style="display: inline-flex; align-items: center; gap: 4px;">
                        <span id="card-view-post-type-${d.id}" style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                            ${escapeHtml(d.post_type || 'Outlines')}
                        </span>
                        <span id="card-view-stage-${d.id}" style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                            ${escapeHtml(stageDisplay)}
                        </span>
                    </div>
                    <span id="card-view-assignee-badge-${d.id}" 
                          style="font-size: 10px; font-weight: 700; color: ${badgeColor}; background: ${badgeBg}; border: ${badgeBorder}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-flex; align-items: center; gap: 3px;"
                          title="${hasAssignee ? 'Assigned to ' + escapeHtml(assigneeDisplay) : 'No assignee'}">
                        <span>${escapeHtml(assigneeDisplay)}</span>
                    </span>
                </div>

                <h4 id="card-view-title-${d.id}" style="margin: 0 0 10px 0; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); line-height: 1.35; letter-spacing: -0.01em;">
                    ${escapeHtml(d.title)}
                </h4>

                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 6px; border-top: 1px solid var(--color-border-primary); margin-top: 4px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <div id="card-view-avatar-${d.id}" 
                             style="width: 22px; height: 22px; border-radius: 50%; background: #7c3aed; color: #ffffff; font-size: 10.5px; font-weight: 800; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(124,58,237,0.3);" 
                             title="${escapeHtml(d.assignee_name || 'Unassigned')}">
                            ${escapeHtml(initial)}
                        </div>
                        <div id="card-view-date-${d.id}" style="display: ${formattedDate ? 'inline-flex' : 'none'}; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 600; color: var(--color-text-secondary);" title="Due ${escapeHtml(formattedDate)}">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>${escapeHtml(formattedDate)}</span>
                        </div>
                    </div>
                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg id="card-view-prio-${d.id}" width="12" height="12" fill="${prioColor}" viewBox="0 0 16 16" title="${escapeHtml(prio)}">
                            <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                        </svg>
                        <svg width="12" height="12" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Expanded Inline Editor --}}
            <div class="kanban-card-editor" id="kanban-card-editor-${d.id}" style="display: none;" onclick="event.stopPropagation()">
                <form onsubmit="submitInlineCardEdit(event, ${d.id})">
                    {{-- Top Header: Stage on top very small --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
                        <div style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--color-text-secondary); background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                                ${escapeHtml(d.post_type || 'Outlines')}
                            </span>
                            <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                                ${escapeHtml(stageDisplay)}
                            </span>
                        </div>
                        <span style="font-size: 10px; font-weight: 700; color: ${badgeColor}; background: ${badgeBg}; border: ${badgeBorder}; padding: 1px 6px; border-radius: 5px; max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
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
                               style="width: 100%; border: none; background: transparent; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); outline: none; padding: 2px 0;"
                               onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitInlineCardEdit(event, ${d.id}); } else if(event.key === 'Escape') { toggleCardEditor(${d.id}, false); }">
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

                        {{-- Approver --}}
                        <div style="display: flex; align-items: center; gap: 8px;">
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
                                <option value="Medium" ${d.priority === 'Medium' ? 'selected' : ''}>Add priority (Medium)</option>
                                <option value="High Priority" ${(d.priority === 'High Priority' || d.priority === 'High') ? 'selected' : ''}>High Priority</option>
                                <option value="Urgent" ${d.priority === 'Urgent' ? 'selected' : ''}>Urgent</option>
                                <option value="Low" ${d.priority === 'Low' ? 'selected' : ''}>Low</option>
                            </select>
                        </div>
                    </div>

                    {{-- Footer actions: Details on left, Cancel & Save on right (All buttons at the bottom) --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--color-border-primary);">
                        <button type="button" 
                                onclick="event.stopPropagation(); openTaskModal(${d.id})"
                                style="background: transparent; border: none; font-size: 11px; font-weight: 600; color: #3b82f6; cursor: pointer; padding: 3px 4px; display: inline-flex; align-items: center; gap: 3px;"
                                title="Open full view with briefs, assets and artwork">
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
                                    style="background: #e2e8f0; color: #1e293b; border: none; border-radius: 6px; padding: 4px 10px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.15s;"
                                    onmouseover="this.style.background='#cbd5e1'"
                                    onmouseout="this.style.background='#e2e8f0'">
                                <span>Save</span>
                                <span style="font-size: 11px; opacity: 0.7;">↵</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('afterbegin', cardHtml);
}
</script>
