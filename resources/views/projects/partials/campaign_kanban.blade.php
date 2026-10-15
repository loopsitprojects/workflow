@php
    $tasks = $project->deliverables->whereNull('parent_deliverable_id')->filter(fn($t) => $t->flow_type !== 'direct_design');

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

function kanbanOnDragStart(event, taskId) {
    draggedTaskId = taskId;
    draggedCardElement = event.currentTarget;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', taskId);
    setTimeout(() => {
        if (draggedCardElement) draggedCardElement.style.opacity = '0.45';
    }, 0);
}

function kanbanOnDragEnd(event) {
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

    // Update status dot/badge on card if present
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
    const deadlineInput = creator.querySelector('input[name="deadline"]');
    const prioritySelect = creator.querySelector('select[name="priority"]');

    const postType = postTypeSelect ? postTypeSelect.value : 'Outlines';
    const writerId = assigneeSelect && assigneeSelect.value ? assigneeSelect.value : null;
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

function insertCreatedCardIntoColumn(colKey, d) {
    let containerId = 'cards-container-' + colKey;
    const container = document.getElementById(containerId);
    if (!container) return;

    const initial = d.assignee_name && d.assignee_name !== 'Unassigned' 
        ? d.assignee_name.trim().charAt(0).toUpperCase() 
        : 'U';

    const prioColor = (d.priority === 'High Priority' || d.priority === 'Urgent') ? '#ef4444' : '#10b981';

    let borderLeft = 'border-left: 3px solid var(--color-border-primary);';
    if (d.status === 'Done' || d.status === 'Completed') {
        borderLeft = 'border-left: 3px solid #10b981;';
    } else if (d.status === 'Approval Stage' || d.status === 'Approval' || d.status === 'Review' || d.status === 'Under Review') {
        borderLeft = 'border-left: 3px solid #f59e0b;';
    } else if (d.status === 'In Progress') {
        borderLeft = 'border-left: 3px solid #6366f1;';
    }

    let formattedDate = '';
    if (d.deadline) {
        try {
            const dt = new Date(d.deadline);
            formattedDate = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        } catch(e) {
            formattedDate = d.deadline;
        }
    }

    const cardHtml = `
        <div class="kanban-card" 
             id="kanban-card-${d.id}"
             data-id="${d.id}"
             data-status="${d.status}"
             draggable="true"
             ondragstart="kanbanOnDragStart(event, ${d.id})"
             ondragend="kanbanOnDragEnd(event)"
             onclick="openTaskModal(${d.id})"
             style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); ${borderLeft} border-radius: 12px; padding: 12px 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.03); cursor: pointer; transition: all 0.15s ease; position: relative;">
            
            <div style="display:flex; align-items:center; justify-content:space-between; gap:6px; margin-bottom:8px;">
                <span style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; color:var(--color-text-secondary); background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); padding:2px 6px; border-radius:5px;">
                    ${d.post_type || 'Outlines'}
                </span>
                <span style="font-size:10px; font-weight:700; color:#3b82f6; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); padding:1px 6px; border-radius:5px;">
                    ${d.approval_stage || 'Assignee'}
                </span>
            </div>

            <h4 style="margin:0 0 10px 0; font-size:13.5px; font-weight:700; color:var(--color-text-primary); line-height:1.35; letter-spacing:-0.01em;">
                ${d.title}
            </h4>

            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding-top:6px; border-top:1px solid var(--color-border-primary);">
                <div style="display:inline-flex; align-items:center; gap:6px;">
                    <div style="width:22px; height:22px; border-radius:50%; background:#7c3aed; color:#fff; font-size:10.5px; font-weight:800; display:flex; align-items:center; justify-content:center; box-shadow:0 1px 3px rgba(124,58,237,0.3);" title="${d.assignee_name || 'Unassigned'}">
                        ${initial}
                    </div>
                    ${formattedDate ? `
                        <div style="display:inline-flex; align-items:center; gap:3px; font-size:10.5px; font-weight:600; color:var(--color-text-secondary);">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>${formattedDate}</span>
                        </div>
                    ` : ''}
                </div>
                <div style="display:inline-flex; align-items:center; gap:6px;">
                    <svg width="12" height="12" fill="${prioColor}" viewBox="0 0 16 16" title="${d.priority || 'Medium'}">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                    </svg>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('afterbegin', cardHtml);
}
</script>
