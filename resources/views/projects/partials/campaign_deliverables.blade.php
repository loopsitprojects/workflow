@php
    $currentUserId = auth()->id();
    $userRole = strtolower(str_replace(' ', '', auth()->user()->role ?? ''));
    $isAdmin = (auth()->check() && auth()->user()->isAdmin());
    $currentUserIsAdmin = $isAdmin;

    $outlineTasks = $project->deliverables->whereNull('parent_deliverable_id')->filter(function($t) {
        $type = strtolower(trim($t->post_type ?? ''));
        return $type === 'outlines' || $type === 'outline';
    });

    $otherTasks = $project->deliverables->whereNull('parent_deliverable_id')->filter(function($t) {
        $type = strtolower(trim($t->post_type ?? ''));
        return $type !== 'outlines' && $type !== 'outline';
    });
@endphp

{{-- ========================================================= --}}
{{-- 1. SECTION: OUTLINES                                     --}}
{{-- ========================================================= --}}
<div class="cd-table-wrap" style="margin-bottom: 24px;">
    <div class="cd-header">
        <div class="cd-header-left" style="display:flex; align-items:center; gap:10px;">
            <h2 style="margin:0;">Outlines</h2>
            <span style="font-size:11px; font-weight:700; color:#3b82f6; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25); padding:2px 8px; border-radius:6px;">
                {{ $outlineTasks->count() }} {{ \Illuminate\Support\Str::plural('outline', $outlineTasks->count()) }}
            </span>
        </div>
        <div class="cd-header-right">
            <div style="position:relative;">
                <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--color-text-secondary);" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search outlines..." onkeyup="searchDeliverables(this.value)" style="padding:6px 12px 6px 30px; border-radius:8px; border:1px solid var(--color-border-primary); background:var(--color-bg-primary); color:var(--color-text-primary); font-size:12px; outline:none; transition:border-color 0.2s; width: 220px;" onfocus="this.style.borderColor='#0055D4'" onblur="this.style.borderColor='var(--color-border-primary)'">
            </div>
        </div>
    </div>

    <div style="width:100%; overflow-x:auto;">
        <table class="cd-table">
            <thead>
                <tr>
                    <th style="width:170px;">Deliverable</th>
                    <th style="width:80px;">Due</th>
                    <th style="width:120px;">Concept</th>
                    <th style="width:120px;">Caption</th>
                    <th style="width:140px;">Post Copy</th>
                    <th style="width:85px;">Ref</th>
                    <th style="width:90px;">Artwork</th>
                    <th style="width:70px;">Rev</th>
                    <th style="width:85px;">Stage</th>
                    <th style="width:110px;">Client</th>
                    <th style="width:130px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outlineTasks as $task)
                    @if($task->subtasks->count() > 0)
                        {{-- Heading Row for Deliverable with Subtasks --}}
                        <tr class="rtb-heading-row" style="background:var(--color-bg-secondary); border-left:3px solid #3b82f6; cursor:pointer;" onclick="toggleSubtasks(event, {{ $task->id }})">
                            <td colspan="4">
                                <div class="deliverable-name-cell" style="padding: 8px 0; display:flex; align-items:center; gap:10px;">
                                    <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle active" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:6px; outline:none;"></button>
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:800; color:var(--color-text-primary); font-size:13px; letter-spacing:-0.01em;">{{ $task->title }}</span>
                                    <span style="font-size:10px; font-weight:700; color:var(--color-text-secondary); background:var(--color-bg-primary); border:1px solid var(--color-border-primary); padding:2px 7px; border-radius:6px;">{{ $task->subtasks->count() }} posts</span>
                                </div>
                            </td>
                            <td colspan="7" style="padding-right:15px;" onclick="event.stopPropagation()">
                                @php
                                    $stage = $task->approval_stage;
                                    $nextStage = $task->getNextStage();
                                    $canApproveBatch = $isAdmin || (
                                        (($stage === 'Writer' || $stage === 'Assignee') && ($userRole === 'writer' || $userRole === 'assignee') && (!$task->writer_id || $task->writer_id == $currentUserId)) ||
                                        (($stage === 'AM/BD' || $stage === 'Final Approval') && $userRole === 'brandmanager')
                                    );
                                    $subtasks = $task->subtasks;
                                    $totalInBatch = $subtasks->count();
                                    $parentStageNorm = $stage ?: 'Assignee';
                                    $readyInBatch = $subtasks->filter(fn($t) => ($t->approval_stage ?: 'Assignee') === $parentStageNorm)->count();
                                    $allReady = $readyInBatch === $totalInBatch;
                                    $isGated = !$allReady;
                                    $batchStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                @endphp
                                <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px;">
                                    <span style="font-size:10px; font-weight:700; color:#3b82f6; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); padding:3px 9px; border-radius:6px;">
                                        {{ $task->approval_stage ?: 'Assignee' }}
                                    </span>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <a href="{{ route('deliverables.showBatch', $task->id) }}" onclick="event.stopPropagation()" style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:11px;font-weight:600;color:var(--color-text-secondary);background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:7px;text-decoration:none;white-space:nowrap;">View</a>
                                        @if($canApproveBatch && $nextStage)
                                            <button onclick="event.stopPropagation(); openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', {{ $totalInBatch }}, 'submit', {{ $batchStakeholders }})"
                                                    style="padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; background:#0055D4; color:#fff; border:1px solid #0055D4; cursor:pointer;" {{ $isGated ? 'disabled' : '' }}>
                                                Approve Batch
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($task->subtasks as $subIndex => $subtask)
                            @php
                                $canEditInline = $isAdmin || (($subtask->approval_stage === 'Assignee' || $subtask->approval_stage === 'Writer') && ($userRole === 'writer' || $userRole === 'assignee') && (!$subtask->writer_id || auth()->id() == $subtask->writer_id));
                            @endphp
                            <tr class="subtask-row rtb-subtask-row subtask-of-{{ $task->id }} {{ $subtask->approval_stage === 'Closed' ? 'task-closed' : '' }}">
                                <td>
                                    <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; color:var(--color-text-primary); font-size:13px;">{{ $subtask->title }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php $displayDeadline = $subtask->deadline ?? $task->deadline ?? $project->deadline; @endphp
                                    <div style="font-weight:800;">{{ $displayDeadline ? \Carbon\Carbon::parse($displayDeadline)->format('M d, Y') : '—' }}</div>
                                </td>
                                <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                    @if($canEditInline)
                                        <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="concept" placeholder="N/A" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary); cursor:pointer;">{{ strip_tags($subtask->concept) }}</textarea>
                                    @else
                                        @if($subtask->concept)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Concept',{{ json_encode($subtask->concept) }})">{{ strip_tags($subtask->concept) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                    @endif
                                </td>
                                <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                    @if($canEditInline)
                                        <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="caption" placeholder="N/A" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary); cursor:pointer;">{{ strip_tags($subtask->caption) }}</textarea>
                                    @else
                                        @if($subtask->caption)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Caption',{{ json_encode($subtask->caption) }})">{{ strip_tags($subtask->caption) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                    @endif
                                </td>
                                <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                    @if($canEditInline)
                                        <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="post_copy" placeholder="N/A" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary); cursor:pointer;">{{ strip_tags($subtask->post_copy) }}</textarea>
                                    @else
                                        @if($subtask->post_copy)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Post Copy',{{ json_encode($subtask->post_copy) }})">{{ strip_tags($subtask->post_copy) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation()">
                                    @if($subtask->reference)
                                        <a href="{{ $subtask->reference }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1);" title="Visit Link">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </a>
                                    @elseif($subtask->reference_file)
                                        <a href="{{ $subtask->reference_file }}" target="_blank" style="display:inline-flex; align-items:center; gap:4px; padding:3px 7px; border-radius:6px; background:rgba(0,85,212,0.08); color:#0055D4; font-size:10px; font-weight:700;">File</a>
                                    @else
                                        <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                    @endif
                                </td>
                                <td><span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span></td>
                                <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                                <td><div class="rtb-stage-label">{{ $subtask->approval_stage ?: 'Assignee' }}</div></td>
                                <td><div style="font-size:10px; font-weight:700; color:var(--color-text-secondary);">{{ $subtask->client_status ?: 'Not Sent' }}</div></td>
                                <td style="text-align:center;">
                                    <div class="quick-actions-grid">
                                        <a href="{{ route('deliverables.show', $subtask->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Standalone Outline Deliverable --}}
                        <tr class="{{ $task->approval_stage === 'Closed' ? 'task-closed' : '' }}">
                            <td>
                                <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:900; color:var(--color-text-primary);">{{ $task->title }}</span>
                                </div>
                            </td>
                            <td>
                                @php $displayDeadline = $task->deadline ?? $project->deadline; @endphp
                                <div style="font-weight:800;">{{ $displayDeadline ? \Carbon\Carbon::parse($displayDeadline)->format('M d, Y') : '—' }}</div>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="concept" placeholder="Concept..." onclick="openCellEditor(event)" readonly style="cursor:pointer !important; width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-secondary); color:var(--color-text-primary);">{{ strip_tags($task->concept) }}</textarea>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="caption" placeholder="Caption..." onclick="openCellEditor(event)" readonly style="cursor:pointer !important; width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-secondary); color:var(--color-text-primary);">{{ strip_tags($task->caption) }}</textarea>
                            </td>
                            <td onclick="event.stopPropagation()">
                                <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="post_copy" placeholder="Copy..." onclick="openCellEditor(event)" readonly style="cursor:pointer !important; width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-secondary); color:var(--color-text-primary);">{{ strip_tags($task->post_copy) }}</textarea>
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($task->reference)
                                    <a href="{{ $task->reference }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1); box-shadow:0 0 8px rgba(0,85,212,0.4);" title="Visit Link">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </a>
                                @elseif($task->reference_file)
                                    @php
                                        $ext = strtolower(pathinfo($task->reference_file, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                    @endphp
                                    @if($isImg)
                                        <img src="{{ $task->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $task->reference_file }}', false)" title="View Image">
                                    @else
                                        <a href="{{ $task->reference_file }}" target="_blank" style="display:inline-flex; align-items:center; gap:4px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); color:#0055D4; font-size:10px; font-weight:700;">File</a>
                                    @endif
                                @else
                                    <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($task->final_designs)
                                    <a href="{{ $task->final_designs }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                @else
                                    <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                @endif
                            </td>
                            <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                            <td>
                                <div style="font-size:10px; font-weight:900; color:#0055D4; text-transform:uppercase; letter-spacing:0.05em;">{{ $task->approval_stage }}</div>
                            </td>
                            <td>
                                @if($isAdmin || $userRole === 'brandmanager')
                                    <select onchange="updateClientStatusInline(this, {{ $task->id }})" onclick="event.stopPropagation()" style="padding:2px 4px; border-radius:4px; border:1px solid var(--color-border-primary); font-size:10px; background:var(--color-bg-primary); color:{{ $task->client_status === 'Client Approved' ? '#10b981' : 'var(--color-text-secondary)' }}; font-weight:700; max-width: 110px;">
                                        <option value="Not Sent" {{ !$task->client_status || $task->client_status === 'Not Sent' ? 'selected' : '' }}>Not Sent</option>
                                        <option value="Sent to Client" {{ $task->client_status === 'Sent to Client' ? 'selected' : '' }}>Sent to Client</option>
                                        <option value="Waiting for Feedback" {{ $task->client_status === 'Waiting for Feedback' ? 'selected' : '' }}>Waiting for Feedback</option>
                                        <option value="Client Approved" {{ $task->client_status === 'Client Approved' ? 'selected' : '' }}>Client Approved</option>
                                        <option value="Client Revisions" {{ $task->client_status === 'Client Revisions' ? 'selected' : '' }}>Client Revisions</option>
                                    </select>
                                @else
                                    <div style="font-size:10px; font-weight:700; color:{{ $task->client_status === 'Client Approved' ? '#10b981' : 'var(--color-text-secondary)' }}; opacity:0.8;">
                                        {{ $task->client_status ?: 'Not Sent' }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align:center; padding: 12px 8px;">
                                <div class="quick-actions-grid">
                                    @php
                                        $stage = $task->approval_stage;
                                        $nextStage = $task->getNextStage();
                                        $canApprove = $isAdmin || (
                                            (($stage === 'Writer' || $stage === 'Assignee') && ($userRole === 'writer' || $userRole === 'assignee') && (!$task->writer_id || $task->writer_id == $currentUserId)) ||
                                            (($stage === 'AM/BD' || $stage === 'Final Approval') && $userRole === 'brandmanager')
                                        );
                                        $taskStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                    @endphp
                                    @if($canApprove && $nextStage)
                                        <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', 1, 'submit', {{ $taskStakeholders }}, false)" class="quick-action-btn btn-approve-quick">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Submit
                                        </button>
                                    @endif
                                    <a href="{{ route('deliverables.show', $task->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                    <a href="{{ route('deliverables.create', ['project_id' => $project->id, 'parent_id' => $task->id]) }}" onclick="event.stopPropagation()" class="quick-action-btn btn-edit-quick" style="text-decoration:none;">+ Sub</a>
                                    @if($isAdmin || $userRole === 'brandmanager')
                                        <form action="{{ route('deliverables.destroy', $task) }}" method="POST" onsubmit="return confirmAction(event, 'Delete Deliverable?', 'Are you sure you want to delete this deliverable? This action cannot be undone.', true)" style="display:contents;" onclick="event.stopPropagation()">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="quick-action-btn btn-delete-quick">Del</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="11" style="padding:48px 24px; text-align:center;">
                            <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;">
                                <div style="width:44px;height:44px;border-radius:50%;background:var(--color-bg-secondary);border:2px dashed var(--color-border-primary);display:flex;align-items:center;justify-content:center;">
                                    <svg width="18" height="18" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:var(--color-text-primary);">No outlines yet</div>
                                    <div style="font-size:11px;font-weight:500;color:var(--color-text-secondary);">Create an Outline deliverable above to get started</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 2. SECTION: OTHER DELIVERABLES                            --}}
{{-- ========================================================= --}}
<div class="cd-table-wrap">
    <div class="cd-header">
        <div class="cd-header-left" style="display:flex; align-items:center; gap:10px;">
            <h2 style="margin:0;">Other Deliverables</h2>
            <span style="font-size:11px; font-weight:700; color:#0ea5e9; background:rgba(14,165,233,0.1); border:1px solid rgba(14,165,233,0.25); padding:2px 8px; border-radius:6px;">
                {{ $otherTasks->count() }} {{ \Illuminate\Support\Str::plural('deliverable', $otherTasks->count()) }}
            </span>
        </div>
        <div class="cd-header-right">
            <div style="position:relative;">
                <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--color-text-secondary);" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search other deliverables..." onkeyup="searchDeliverables(this.value)" style="padding:6px 12px 6px 30px; border-radius:8px; border:1px solid var(--color-border-primary); background:var(--color-bg-primary); color:var(--color-text-primary); font-size:12px; outline:none; transition:border-color 0.2s; width: 220px;" onfocus="this.style.borderColor='#0055D4'" onblur="this.style.borderColor='var(--color-border-primary)'">
            </div>
        </div>
    </div>

    <div style="width:100%; overflow-x:auto;">
        <table class="cd-table">
            <thead>
                <tr>
                    <th style="width:170px;">Deliverable</th>
                    <th style="width:80px;">Due</th>
                    <th style="width:100px;">Type</th>
                    <th style="width:240px;">Brief</th>
                    <th style="width:100px;">File / Ref</th>
                    <th style="width:90px;">Artwork</th>
                    <th style="width:70px;">Rev</th>
                    <th style="width:85px;">Stage</th>
                    <th style="width:110px;">Client</th>
                    <th style="width:130px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($otherTasks as $task)
                    @if($task->subtasks->count() > 0)
                        {{-- Heading Row for Deliverable with Subtasks --}}
                        <tr class="rtb-heading-row" style="background:var(--color-bg-secondary); border-left:3px solid #0ea5e9; cursor:pointer;" onclick="toggleSubtasks(event, {{ $task->id }})">
                            <td colspan="4">
                                <div class="deliverable-name-cell" style="padding: 8px 0; display:flex; align-items:center; gap:10px;">
                                    <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle active" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:6px; outline:none;"></button>
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:800; color:var(--color-text-primary); font-size:13px; letter-spacing:-0.01em;">{{ $task->title }}</span>
                                    <span style="font-size:10px; font-weight:700; color:var(--color-text-secondary); background:var(--color-bg-primary); border:1px solid var(--color-border-primary); padding:2px 7px; border-radius:6px;">{{ $task->subtasks->count() }} items</span>
                                </div>
                            </td>
                            <td colspan="6" style="padding-right:15px;" onclick="event.stopPropagation()">
                                @php
                                    $stage = $task->approval_stage;
                                    $nextStage = $task->getNextStage();
                                    $canApproveBatch = $isAdmin || (
                                        (($stage === 'Writer' || $stage === 'Assignee') && ($userRole === 'writer' || $userRole === 'assignee') && (!$task->writer_id || $task->writer_id == $currentUserId)) ||
                                        (($stage === 'AM/BD' || $stage === 'Final Approval') && $userRole === 'brandmanager')
                                    );
                                    $subtasks = $task->subtasks;
                                    $totalInBatch = $subtasks->count();
                                    $parentStageNorm = $stage ?: 'Assignee';
                                    $readyInBatch = $subtasks->filter(fn($t) => ($t->approval_stage ?: 'Assignee') === $parentStageNorm)->count();
                                    $allReady = $readyInBatch === $totalInBatch;
                                    $isGated = !$allReady;
                                    $batchStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                @endphp
                                <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px;">
                                    <span style="font-size:10px; font-weight:700; color:#0ea5e9; background:rgba(14,165,233,0.1); border:1px solid rgba(14,165,233,0.2); padding:3px 9px; border-radius:6px;">
                                        {{ $task->approval_stage ?: 'Assignee' }}
                                    </span>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <a href="{{ route('deliverables.showBatch', $task->id) }}" onclick="event.stopPropagation()" style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:11px;font-weight:600;color:var(--color-text-secondary);background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:7px;text-decoration:none;white-space:nowrap;">View</a>
                                        @if($canApproveBatch && $nextStage)
                                            <button onclick="event.stopPropagation(); openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', {{ $totalInBatch }}, 'submit', {{ $batchStakeholders }})"
                                                    style="padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; background:#0055D4; color:#fff; border:1px solid #0055D4; cursor:pointer;" {{ $isGated ? 'disabled' : '' }}>
                                                Approve Batch
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($task->subtasks as $subIndex => $subtask)
                            @php
                                $canEditInline = $isAdmin || (($subtask->approval_stage === 'Assignee' || $subtask->approval_stage === 'Writer') && ($userRole === 'writer' || $userRole === 'assignee') && (!$subtask->writer_id || auth()->id() == $subtask->writer_id));
                            @endphp
                            <tr class="subtask-row rtb-subtask-row subtask-of-{{ $task->id }} {{ $subtask->approval_stage === 'Closed' ? 'task-closed' : '' }}">
                                <td>
                                    <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-weight:700; color:var(--color-text-primary); font-size:13px;">{{ $subtask->title }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php $displayDeadline = $subtask->deadline ?? $task->deadline ?? $project->deadline; @endphp
                                    <div style="font-weight:800;">{{ $displayDeadline ? \Carbon\Carbon::parse($displayDeadline)->format('M d, Y') : '—' }}</div>
                                </td>
                                <td>
                                    @php $colors = $subtaskTypeColors[$subtask->subtask_type ?: ($subtask->post_type ?: 'default')] ?? $subtaskTypeColors['default']; @endphp
                                    <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }}; font-size:10px; font-weight:700;">
                                        {{ $subtask->subtask_type ?: ($subtask->post_type ?: 'Standard') }}
                                    </span>
                                </td>
                                <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                    @php $briefContent = $subtask->concept ?? $subtask->notes; @endphp
                                    @if($canEditInline)
                                        <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="concept" placeholder="Enter brief..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary); cursor:pointer;">{{ strip_tags($briefContent) }}</textarea>
                                    @else
                                        @if($briefContent)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Brief',{{ json_encode($briefContent) }})">{{ strip_tags($briefContent) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation()">
                                    @if($subtask->reference_file)
                                        @php
                                            $ext = strtolower(pathinfo($subtask->reference_file, PATHINFO_EXTENSION));
                                            $isImgOrVid = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'mp4', 'webm', 'ogg', 'mov']);
                                        @endphp
                                        @if($isImgOrVid)
                                            <img src="{{ $subtask->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $subtask->reference_file }}', false)" title="View Asset">
                                        @else
                                            <a href="{{ $subtask->reference_file }}" target="_blank" style="display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.25); color:#0055D4; font-size:10px; font-weight:700; text-decoration:none;">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                File
                                            </a>
                                        @endif
                                    @elseif($subtask->reference)
                                        <a href="{{ $subtask->reference }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1);">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </a>
                                    @else
                                        <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                    @endif
                                </td>
                                <td><span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span></td>
                                <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                                <td><div class="rtb-stage-label">{{ $subtask->approval_stage ?: 'Assignee' }}</div></td>
                                <td><div style="font-size:10px; font-weight:700; color:var(--color-text-secondary);">{{ $subtask->client_status ?: 'Not Sent' }}</div></td>
                                <td style="text-align:center;">
                                    <div class="quick-actions-grid">
                                        <a href="{{ route('deliverables.show', $subtask->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Standalone Other Deliverable --}}
                        <tr class="{{ $task->approval_stage === 'Closed' ? 'task-closed' : '' }}">
                            <td>
                                <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:900; color:var(--color-text-primary);">{{ $task->title }}</span>
                                </div>
                            </td>
                            <td>
                                @php $displayDeadline = $task->deadline ?? $project->deadline; @endphp
                                <div style="font-weight:800;">{{ $displayDeadline ? \Carbon\Carbon::parse($displayDeadline)->format('M d, Y') : '—' }}</div>
                            </td>
                            <td>
                                @php $colors = $subtaskTypeColors[$task->subtask_type ?: ($task->post_type ?: 'default')] ?? $subtaskTypeColors['default']; @endphp
                                <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }}; font-size:10px; font-weight:700;">
                                    {{ $task->subtask_type ?: ($task->post_type ?: 'Standard') }}
                                </span>
                            </td>
                            <td onclick="event.stopPropagation()">
                                @php $briefContent = $task->concept ?? $task->notes; @endphp
                                <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="concept" placeholder="Brief..." onclick="openCellEditor(event)" readonly style="cursor:pointer !important; width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-secondary); color:var(--color-text-primary);">{{ strip_tags($briefContent) }}</textarea>
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($task->reference_file)
                                    @php
                                        $ext = strtolower(pathinfo($task->reference_file, PATHINFO_EXTENSION));
                                        $isImgOrVid = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'mp4', 'webm', 'ogg', 'mov']);
                                    @endphp
                                    @if($isImgOrVid)
                                        @if(in_array($ext, ['mp4', 'webm', 'ogg', 'mov']))
                                            <video src="{{ $task->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $task->reference_file }}', false)" title="View Video" preload="metadata"></video>
                                        @else
                                            <img src="{{ $task->reference_file }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $task->reference_file }}', false)" title="View Image">
                                        @endif
                                    @else
                                        <a href="{{ $task->reference_file }}" target="_blank" style="display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.25); color:#0055D4; font-size:10px; font-weight:700; text-decoration:none;" title="Download / View Attachment">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            File
                                        </a>
                                    @endif
                                @elseif($task->reference)
                                    <a href="{{ $task->reference }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1); box-shadow:0 0 8px rgba(0,85,212,0.4);" title="Visit Link">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </a>
                                @else
                                    <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($task->final_designs)
                                    <a href="{{ $task->final_designs }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                @else
                                    <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                @endif
                            </td>
                            <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                            <td>
                                <div style="font-size:10px; font-weight:900; color:#0055D4; text-transform:uppercase; letter-spacing:0.05em;">{{ $task->approval_stage }}</div>
                            </td>
                            <td>
                                @if($isAdmin || $userRole === 'brandmanager')
                                    <select onchange="updateClientStatusInline(this, {{ $task->id }})" onclick="event.stopPropagation()" style="padding:2px 4px; border-radius:4px; border:1px solid var(--color-border-primary); font-size:10px; background:var(--color-bg-primary); color:{{ $task->client_status === 'Client Approved' ? '#10b981' : 'var(--color-text-secondary)' }}; font-weight:700; max-width: 110px;">
                                        <option value="Not Sent" {{ !$task->client_status || $task->client_status === 'Not Sent' ? 'selected' : '' }}>Not Sent</option>
                                        <option value="Sent to Client" {{ $task->client_status === 'Sent to Client' ? 'selected' : '' }}>Sent to Client</option>
                                        <option value="Waiting for Feedback" {{ $task->client_status === 'Waiting for Feedback' ? 'selected' : '' }}>Waiting for Feedback</option>
                                        <option value="Client Approved" {{ $task->client_status === 'Client Approved' ? 'selected' : '' }}>Client Approved</option>
                                        <option value="Client Revisions" {{ $task->client_status === 'Client Revisions' ? 'selected' : '' }}>Client Revisions</option>
                                    </select>
                                @else
                                    <div style="font-size:10px; font-weight:700; color:{{ $task->client_status === 'Client Approved' ? '#10b981' : 'var(--color-text-secondary)' }}; opacity:0.8;">
                                        {{ $task->client_status ?: 'Not Sent' }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align:center; padding: 12px 8px;">
                                <div class="quick-actions-grid">
                                    @php
                                        $stage = $task->approval_stage;
                                        $nextStage = $task->getNextStage();
                                        $canApprove = $isAdmin || (
                                            (($stage === 'Writer' || $stage === 'Assignee') && ($userRole === 'writer' || $userRole === 'assignee') && (!$task->writer_id || $task->writer_id == $currentUserId)) ||
                                            (($stage === 'AM/BD' || $stage === 'Final Approval') && $userRole === 'brandmanager')
                                        );
                                        $taskStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                    @endphp
                                    @if($canApprove && $nextStage)
                                        <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', 1, 'submit', {{ $taskStakeholders }}, false)" class="quick-action-btn btn-approve-quick">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Submit
                                        </button>
                                    @endif
                                    <a href="{{ route('deliverables.show', $task->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                    <a href="{{ route('deliverables.create', ['project_id' => $project->id, 'parent_id' => $task->id]) }}" onclick="event.stopPropagation()" class="quick-action-btn btn-edit-quick" style="text-decoration:none;">+ Sub</a>
                                    @if($isAdmin || $userRole === 'brandmanager')
                                        <form action="{{ route('deliverables.destroy', $task) }}" method="POST" onsubmit="return confirmAction(event, 'Delete Deliverable?', 'Are you sure you want to delete this deliverable? This action cannot be undone.', true)" style="display:contents;" onclick="event.stopPropagation()">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="quick-action-btn btn-delete-quick">Del</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="10" style="padding:48px 24px; text-align:center;">
                            <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;">
                                <div style="width:44px;height:44px;border-radius:50%;background:var(--color-bg-secondary);border:2px dashed var(--color-border-primary);display:flex;align-items:center;justify-content:center;">
                                    <svg width="18" height="18" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div style="font-size:13px;font-weight:800;color:var(--color-text-primary);">No other deliverables yet</div>
                                    <div style="font-size:11px;font-weight:500;color:var(--color-text-secondary);">Create Radio script, KV, Presentation, or other deliverable types</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
