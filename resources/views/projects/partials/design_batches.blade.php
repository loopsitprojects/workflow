@php
    $designBatches = $project->deliverables->whereNull('parent_deliverable_id')->where('flow_type', 'direct_design');
    $directDesignTasks = $designBatches;
    $currentUserId = auth()->id();
    $userRole = strtolower(str_replace(' ', '', auth()->user()->role ?? ''));
    $isAdmin = (auth()->check() && auth()->user()->isAdmin());
@endphp

@if($designBatches->count() > 0)
<div class="cd-table-wrap" style="margin-top: 24px; border: 1.5px solid rgba(99,102,241,0.25);">
    <div class="cd-header" style="background: rgba(99,102,241,0.03);">
        <div class="cd-header-left" style="display:flex; align-items:center; gap:10px;">
            <div style="width:10px; height:10px; border-radius:50%; background:#6366f1;"></div>
            <h2 style="margin:0; font-size:15px; font-weight:800; color:var(--color-text-primary);">Fast Track Deliverables</h2>
            <span style="font-size:11px; font-weight:700; color:#818cf8; background:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); padding:2px 8px; border-radius:6px;">
                {{ $designBatches->count() }} {{ \Illuminate\Support\Str::plural('deliverable', $designBatches->count()) }}
            </span>
        </div>
        <div class="cd-header-right">
            @can('create-deliverable')
            <a href="{{ route('deliverables.create', ['project_id' => $project->id, 'flow' => 'design']) }}"
               style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#6366f1; border-radius:8px; font-size:11px; font-weight:700; color:#fff; text-decoration:none; transition:all 0.15s; box-shadow: 0 2px 8px rgba(99,102,241,0.3);">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add Fast Track Deliverable
            </a>
            @endcan
        </div>
    </div>

    <div style="width:100%; overflow-x:auto;">
        <table class="cd-table">
            <thead>
                <tr>
                    <th style="width:190px;">Deliverable / Batch</th>
                    <th style="width:120px;">Post Type</th>
                    <th style="width:130px;">Designer</th>
                    <th style="width:120px;">Deadline</th>
                    <th style="width:150px;">Brief</th>
                    <th style="width:90px;">Ref</th>
                    <th style="width:110px;">Artwork</th>
                    <th style="width:65px; text-align:center;">Rev</th>
                    <th style="width:110px;">Stage</th>
                    <th style="width:180px; text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($designBatches as $task)
                    @php
                        $subtasks = $task->subtasks;
                        $hasSubtasks = $subtasks->count() > 0;
                        $isClosed = $task->approval_stage === 'Closed';
                        $stage = $task->approval_stage;
                        $isDesignerStage = $stage === 'Designer';
                        $isWriterReviewStage = $stage === 'Writer Review';
                        $isApproverReviewStage = $stage === 'Approver Review';
                        $isFurtherApproverStage = $stage === 'Further Approver';
                        $isFinalApprovalStage = in_array($stage, ['Final Approval', 'Manager Review']);
                        $isReviewStage = in_array($stage, ['Writer Review', 'Approver Review', 'Further Approver', 'Final Approval', 'Manager Review']);
                        
                        $isAssignedDesigner = $task->designer_id && $currentUserId == $task->designer_id;
                        $isAssignedWriter = $task->writer_id && $currentUserId == $task->writer_id;
                        $isAssignedApprover = $task->approver_id && $currentUserId == $task->approver_id;
                        $isAssignedFurtherApprover = $task->further_approver_id && $currentUserId == $task->further_approver_id;
                        $isAssignedBrandMgr = $task->brand_manager_id && $currentUserId == $task->brand_manager_id;

                        $canDesignerSubmit = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']) || ($isDesignerStage && ($isAssignedDesigner || (!$task->designer_id && $userRole === 'designer')));
                        $canWriterReview = $isAdmin || ($userRole === 'operationsmanager') || ($isWriterReviewStage && ($isAssignedWriter || (!$task->writer_id && in_array($userRole, ['writer', 'assignee']))));
                        $canApproverReview = $isAdmin || ($userRole === 'operationsmanager') || ($isApproverReviewStage && ($isAssignedApprover || (!$task->approver_id && in_array($userRole, ['approver', 'approvercoordinator']))));
                        $canFurtherApprover = $isAdmin || ($userRole === 'operationsmanager') || ($isFurtherApproverStage && ($isAssignedFurtherApprover || (!$task->further_approver_id && in_array($userRole, ['approver', 'approvercoordinator']))));
                        $canFinalApprove = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']);

                        $canRevise = $isAdmin || ($userRole === 'operationsmanager') ||
                            ($isWriterReviewStage && ($isAssignedWriter || (!$task->writer_id && in_array($userRole, ['writer', 'assignee'])))) ||
                            ($isApproverReviewStage && ($isAssignedApprover || (!$task->approver_id && in_array($userRole, ['approver', 'approvercoordinator'])))) ||
                            ($isFurtherApproverStage && ($isAssignedFurtherApprover || (!$task->further_approver_id && in_array($userRole, ['approver', 'approvercoordinator'])))) ||
                            ($isFinalApprovalStage && in_array($userRole, ['brandmanager', 'operationsmanager']));
                    @endphp

                    {{-- Parent Row --}}
                    <tr id="deliverable-row-{{ $task->id }}" data-deliverable-id="{{ $task->id }}" class="{{ $hasSubtasks ? 'rtb-heading-row' : '' }} {{ $isClosed ? 'task-closed' : '' }}"
                        style="{{ $hasSubtasks ? 'background:var(--color-bg-secondary); border-left:3px solid #6366f1; cursor:pointer;' : '' }}"
                        @if($hasSubtasks) onclick="toggleSubtasks(event, {{ $task->id }})" @else onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $task->id }}] ? window.projectDeliverables[{{ $task->id }}] : {{ $task->id }})" style="cursor:pointer;" @endif>
                        
                        {{-- Title & Format --}}
                        <td>
                            <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                @if($hasSubtasks)
                                    <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle active" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:6px; outline:none;"></button>
                                @endif
                                @php
                                    $taskPrio = $task->priority ?? 'Medium';
                                    $taskPrioColor = $taskPrio === 'High Priority' ? '#ef4444' : '#10b981';
                                @endphp
                                <span onclick="event.stopPropagation(); openPriorityInlineEditor(event, {{ $task->id }}, '{{ $taskPrio }}')" style="cursor:pointer; display:inline-flex; align-items:center; gap:4px; flex-shrink:0;" title="{{ $taskPrio }} (Click to edit)">
                                    <svg width="14" height="14" fill="{{ $taskPrioColor }}" viewBox="0 0 16 16" style="flex-shrink:0;">
                                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                                    </svg>
                                </span>
                                <div>
                                    <div style="font-weight:700; color:var(--color-text-primary);">{{ $task->title }}</div>
                                    @if($hasSubtasks)
                                        <div style="font-size:10px; font-weight:700; color:#6366f1;">{{ $subtasks->count() }} subtasks</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Post Type --}}
                        <td>
                            @if($task->post_type)
                                <span class="subtask-pill" style="font-size:9.5px; font-weight:800; background:rgba(99,102,241,0.1); color:#818cf8; border-color:rgba(99,102,241,0.3);">
                                    {{ $task->post_type }}
                                </span>
                            @else
                                <span style="color:var(--color-text-secondary); opacity:0.5; font-size:12px;">—</span>
                            @endif
                        </td>

                        {{-- Designer --}}
                        <td>
                            @if($hasSubtasks)
                                <span style="color:var(--color-text-secondary); opacity:0.5; font-size:12px;">—</span>
                            @else
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <div style="width:22px; height:22px; border-radius:50%; background:#6366f1; color:#fff; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800;">
                                        {{ substr($task->designer->name ?? ($task->assignee_name ?? 'D'), 0, 1) }}
                                    </div>
                                    <span style="font-weight:600; font-size:12px; color:var(--color-text-primary);">
                                        {{ $task->designer->name ?? ($task->assignee_name ?? 'Unassigned') }}
                                    </span>
                                </div>
                            @endif
                        </td>

                        {{-- Deadline --}}
                        <td>
                            @php
                                $dDate = $task->deadline ?? $task->designer_deadline;
                            @endphp
                            @if($dDate)
                                <div style="font-size:11px; font-weight:600; color:var(--color-text-secondary);">
                                    {{ \Carbon\Carbon::parse($dDate)->format('M d, Y') }}
                                </div>
                            @else
                                <span style="color:var(--color-text-secondary); opacity:0.5;">—</span>
                            @endif
                            @if($task->revision_deadline && ($task->revision_instructions || ($task->revisions && $task->revisions > 0)))
                                <div style="margin-top:2px; display:inline-flex; align-items:center; gap:3px; font-size:9.5px; font-weight:800; color:#ef4444; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.25); padding:1px 5px; border-radius:4px;" title="Revision Deadline">
                                    <svg width="8" height="8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Rev: {{ \Carbon\Carbon::parse($task->revision_deadline)->format('M d') }}
                                </div>
                            @endif
                        </td>

                        {{-- Concept / Brief --}}
                        <td>
                            @if($task->concept || $task->notes)
                                <div class="cell-text" onclick="event.stopPropagation();openTextPreview('Creative Brief', {{ json_encode($task->concept ?? $task->notes) }})"
                                     style="max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:11px; color:var(--color-text-secondary); cursor:pointer;">
                                    {{ html_entity_decode(strip_tags($task->concept ?? $task->notes), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                </div>
                            @else
                                <span style="color:var(--color-text-secondary); opacity:0.5;">—</span>
                            @endif
                        </td>

                        {{-- Ref --}}
                        <td>
                            @php
                                $refFiles = $task->getReferenceFilesArray();
                                $refUrls = $task->getReferenceUrlsArray();
                            @endphp
                            @if(!empty($refFiles) || !empty($refUrls))
                                <div style="display:flex; gap:4px; align-items:center;">
                                    @if(!empty($refFiles))
                                        <a href="{{ $refFiles[0] }}" target="_blank" onclick="event.stopPropagation()" title="View Reference File"
                                           style="padding:3px 6px; background:rgba(99,102,241,0.1); border-radius:5px; color:#6366f1; text-decoration:none; font-size:10px; font-weight:700;">
                                            File
                                        </a>
                                    @endif
                                    @if(!empty($refUrls))
                                        <a href="{{ $refUrls[0] }}" target="_blank" onclick="event.stopPropagation()" title="{{ $refUrls[0] }}"
                                           style="padding:3px 6px; background:rgba(59,130,246,0.1); border-radius:5px; color:#3b82f6; text-decoration:none; font-size:10px; font-weight:700;">
                                            Link
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span style="color:var(--color-text-secondary); opacity:0.5;">—</span>
                            @endif
                        </td>

                        {{-- Artwork --}}
                        <td>
                            @php
                                $artFiles = $task->getFinalDesignsArray();
                                $artUrls = $task->getFinalDesignsUrlsArray();
                            @endphp
                            @if(!empty($artFiles) || !empty($artUrls))
                                <div style="display:flex; gap:4px; align-items:center;">
                                    @if(!empty($artFiles))
                                        <a href="{{ $artFiles[0] }}" target="_blank" onclick="event.stopPropagation()" title="View Artwork"
                                           style="padding:3px 8px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.25); border-radius:5px; color:#10b981; text-decoration:none; font-size:10px; font-weight:800;">
                                            Artwork
                                        </a>
                                    @endif
                                    @if(!empty($artUrls))
                                        <a href="{{ $artUrls[0] }}" target="_blank" onclick="event.stopPropagation()" title="{{ $artUrls[0] }}"
                                           style="padding:3px 8px; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25); border-radius:5px; color:#3b82f6; text-decoration:none; font-size:10px; font-weight:800;">
                                            Link
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span style="font-size:11px; color:var(--color-text-secondary); opacity:0.6;">Pending</span>
                            @endif
                        </td>

                        {{-- Rev --}}
                        <td style="text-align:center;">
                            @if($task->revisions > 0)
                                <span style="display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; background:#ef4444; color:#fff; font-size:10px; font-weight:800;">
                                    {{ $task->revisions }}
                                </span>
                            @else
                                <span style="color:var(--color-text-secondary); opacity:0.5;">0</span>
                            @endif
                        </td>

                        {{-- Stage Badge --}}
                        <td>
                            @if($task->approval_stage === 'Closed')
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;">
                                    ✓ Closed
                                </span>
                            @elseif(in_array($task->approval_stage, ['Final Approval', 'Manager Review']))
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe;">
                                    Final Approval
                                </span>
                            @elseif($task->approval_stage === 'Writer Review')
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#dbeafe; color:#1d4ed8; border:1px solid #bfdbfe;">
                                    Writer Review
                                </span>
                            @elseif($task->approval_stage === 'Approver Review')
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#ffedd5; color:#c2410c; border:1px solid #fed7aa;">
                                    Approver Review
                                </span>
                            @elseif($task->approval_stage === 'Further Approver')
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#fef3c7; color:#b45309; border:1px solid #fde68a;">
                                    Further Approver
                                </span>
                            @else
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#fef3c7; color:#b45309; border:1px solid #fde68a;">
                                    Designer
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:center; padding:8px 6px;" onclick="event.stopPropagation()">
                            <div class="quick-actions-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:4px; max-width:140px; margin:0 auto;">
                                {{-- 1. Submit Button --}}
                                @php
                                    $hasArt = !empty($task->getFinalDesignsArray()) || !empty($task->getFinalDesignsUrlsArray());
                                    $taskStakeholders = "{writer: " . ($task->writer_id ?? 'null') . ", approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "', approverName: '" . addslashes($task->approver->name ?? $project->approver->name ?? '') . "', currentStage: '" . addslashes($stage) . "'}";
                                @endphp
                                @if($isDesignerStage && $canDesignerSubmit)
                                    @if($hasArt)
                                        <button type="button" onclick="openBatchModal(event, {{ $task->id }}, 'Writer Review', {{ $hasSubtasks ? $subtasks->count() : 1 }}, 'submit', {{ $taskStakeholders }}, false)" class="quick-action-btn btn-approve-quick" title="Submit to Writer Review">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Submit
                                        </button>
                                    @else
                                        <button type="button" onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $task->id }}] ? window.projectDeliverables[{{ $task->id }}] : {{ $task->id }})" class="quick-action-btn btn-approve-quick" title="Upload Artwork & Submit">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Submit
                                        </button>
                                    @endif
                                @elseif(($isWriterReviewStage && $canWriterReview) || ($isApproverReviewStage && $canApproverReview) || ($isFurtherApproverStage && $canFurtherApprover))
                                    <button type="button" onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $task->id }}] ? window.projectDeliverables[{{ $task->id }}] : {{ $task->id }})" class="quick-action-btn btn-approve-quick" title="Review Deliverable">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        Review
                                    </button>
                                @elseif($isFinalApprovalStage && $canFinalApprove)
                                    <form action="{{ route('deliverables.submit', $task->id) }}" method="POST" onsubmit="return confirmAction(event, 'Approve Deliverable?', 'Approve artwork and close this deliverable?', false, 'Approve', 'success')" style="display:contents;" onclick="event.stopPropagation()">
                                        @csrf
                                        <button type="submit" class="quick-action-btn btn-approve-quick" title="Approve">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Approve
                                        </button>
                                    </form>
                                @endif

                                 {{-- 2. View/Edit Button --}}
                                @if($hasSubtasks)
                                    <a href="{{ route('deliverables.showBatch', $task->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()" title="Batch View">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </a>
                                @else
                                    <button type="button" onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $task->id }}] ? window.projectDeliverables[{{ $task->id }}] : {{ $task->id }})" class="quick-action-btn btn-view-quick" title="Edit Details">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Edit
                                    </button>
                                @endif

                                {{-- 3. + Add Button (Batches only) --}}
                                @if($hasSubtasks)
                                    @can('create-deliverable')
                                        <a href="{{ route('deliverables.create', ['project_id' => $project->id, 'flow' => 'design', 'parent_id' => $task->id]) }}"
                                           class="quick-action-btn btn-edit-quick" style="text-decoration:none;"
                                           title="Add Subtask">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            Add
                                        </a>
                                    @endcan
                                @endif

                                {{-- Revise Button --}}
                                @if($canRevise)
                                    <button type="button" onclick="openDirectRevisionPrompt({{ $task->id }}, {{ $hasSubtasks ? 'true' : 'false' }})" class="quick-action-btn btn-revise-quick" title="Request Revisions">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        Revise
                                    </button>
                                @endif

                                {{-- 4. Delete Button --}}
                                @if($isAdmin || $userRole === 'brandmanager')
                                    <form action="{{ route('deliverables.destroy', $task) }}" method="POST" onsubmit="return confirmAction(event, 'Delete Deliverable?', 'Are you sure you want to delete this deliverable? This action cannot be undone.', true)" style="display:contents;" onclick="event.stopPropagation()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="quick-action-btn btn-delete-quick" title="Delete Deliverable">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Del
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Subtasks Rows --}}
                    @if($hasSubtasks)
                        @foreach($subtasks as $sub)
                            @php
                                $subIsClosed = $sub->approval_stage === 'Closed';
                                $subStage = $sub->approval_stage;
                                $subIsDesignerStage = $subStage === 'Designer';
                                $subIsWriterReviewStage = $subStage === 'Writer Review';
                                $subIsApproverReviewStage = $subStage === 'Approver Review';
                                $subIsFurtherApproverStage = $subStage === 'Further Approver';
                                $subIsFinalApprovalStage = in_array($subStage, ['Final Approval', 'Manager Review']);
                                
                                $subIsAssignedDesigner = $sub->designer_id && $currentUserId == $sub->designer_id;
                                $subIsAssignedWriter = $sub->writer_id && $currentUserId == $sub->writer_id;
                                $subIsAssignedApprover = $sub->approver_id && $currentUserId == $sub->approver_id;
                                $subIsAssignedFurtherApprover = $sub->further_approver_id && $currentUserId == $sub->further_approver_id;
                                $subIsAssignedBrandMgr = $sub->brand_manager_id && $currentUserId == $sub->brand_manager_id;

                                $canSubDesignerSubmit = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']) || ($subIsDesignerStage && ($subIsAssignedDesigner || (!$sub->designer_id && $userRole === 'designer')));
                                $canSubWriterReview = $isAdmin || ($userRole === 'operationsmanager') || ($subIsWriterReviewStage && ($subIsAssignedWriter || (!$sub->writer_id && in_array($userRole, ['writer', 'assignee']))));
                                $canSubApproverReview = $isAdmin || ($userRole === 'operationsmanager') || ($subIsApproverReviewStage && ($subIsAssignedApprover || (!$sub->approver_id && in_array($userRole, ['approver', 'approvercoordinator']))));
                                $canSubFurtherApprover = $isAdmin || ($userRole === 'operationsmanager') || ($subIsFurtherApproverStage && ($subIsAssignedFurtherApprover || (!$sub->further_approver_id && in_array($userRole, ['approver', 'approvercoordinator']))));
                                $canSubFinalApprove = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']);

                                $canSubRevise = $isAdmin || ($userRole === 'operationsmanager') ||
                                    ($subIsWriterReviewStage && ($subIsAssignedWriter || (!$sub->writer_id && in_array($userRole, ['writer', 'assignee'])))) ||
                                    ($subIsApproverReviewStage && ($subIsAssignedApprover || (!$sub->approver_id && in_array($userRole, ['approver', 'approvercoordinator'])))) ||
                                    ($subIsFurtherApproverStage && ($subIsAssignedFurtherApprover || (!$sub->further_approver_id && in_array($userRole, ['approver', 'approvercoordinator'])))) ||
                                    ($subIsFinalApprovalStage && in_array($userRole, ['brandmanager', 'operationsmanager']));

                                $subArtFiles = $sub->getFinalDesignsArray();
                                $subArtUrls = $sub->getFinalDesignsUrlsArray();
                            @endphp
                            <tr id="deliverable-row-{{ $sub->id }}" data-deliverable-id="{{ $sub->id }}" class="subtask-row subtask-of-{{ $task->id }} {{ $subIsClosed ? 'task-closed' : '' }}"
                                onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $sub->id }}] ? window.projectDeliverables[{{ $sub->id }}] : {{ $sub->id }})" style="cursor:pointer;">
                                
                                {{-- Subtask Title & Format --}}
                                <td>
                                    <div style="padding-left:16px; display:flex; align-items:center; gap:8px;">
                                        @php
                                            $subPrio = $sub->priority ?? 'Medium';
                                            $subPrioColor = $subPrio === 'High Priority' ? '#ef4444' : '#10b981';
                                        @endphp
                                        <span onclick="event.stopPropagation(); openPriorityInlineEditor(event, {{ $sub->id }}, '{{ $subPrio }}')" style="cursor:pointer; display:inline-flex; align-items:center; gap:4px; flex-shrink:0;" title="{{ $subPrio }} (Click to edit)">
                                            <svg width="14" height="14" fill="{{ $subPrioColor }}" viewBox="0 0 16 16" style="flex-shrink:0;">
                                                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2z"/>
                                            </svg>
                                        </span>
                                        <div>
                                            <div style="font-weight:600; font-size:12px; color:var(--color-text-primary);">{{ $sub->title }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Post Type --}}
                                <td>
                                    @if($sub->post_type)
                                        <span class="subtask-pill" style="font-size:9px; font-weight:800; background:rgba(99,102,241,0.08); color:#818cf8; border-color:rgba(99,102,241,0.25);">
                                            {{ $sub->post_type }}
                                        </span>
                                    @else
                                        <span style="color:var(--color-text-secondary); opacity:0.5; font-size:12px;">—</span>
                                    @endif
                                </td>

                                {{-- Designer --}}
                                <td>
                                    <span style="font-size:11px; color:var(--color-text-secondary);">
                                        {{ $sub->designer->name ?? ($sub->assignee_name ?? 'Unassigned') }}
                                    </span>
                                </td>

                                {{-- Deadline --}}
                                <td>
                                    @php
                                        $subDeadline = $sub->deadline ?? $sub->designer_deadline ?? $task->deadline ?? $task->designer_deadline;
                                    @endphp
                                    <span style="font-size:11px; color:var(--color-text-secondary);">
                                        {{ $subDeadline ? \Carbon\Carbon::parse($subDeadline)->format('M d, Y') : '—' }}
                                    </span>
                                    @if($sub->revision_deadline && ($sub->revision_instructions || ($sub->revisions && $sub->revisions > 0)))
                                        <div style="margin-top:2px; display:inline-flex; align-items:center; gap:3px; font-size:9.5px; font-weight:800; color:#ef4444; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.25); padding:1px 5px; border-radius:4px;" title="Revision Deadline">
                                            <svg width="8" height="8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Rev: {{ \Carbon\Carbon::parse($sub->revision_deadline)->format('M d') }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Concept / Brief --}}
                                <td>
                                    @if($sub->concept || $sub->notes)
                                        <div class="cell-text" onclick="event.stopPropagation();openTextPreview('Subtask Brief', {{ json_encode($sub->concept ?? $sub->notes) }})"
                                             style="max-width:130px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:11px; color:var(--color-text-secondary); cursor:pointer;">
                                            {{ html_entity_decode(strip_tags($sub->concept ?? $sub->notes), ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
                                        </div>
                                    @else
                                        <span style="color:var(--color-text-secondary); opacity:0.5;">—</span>
                                    @endif
                                </td>

                                {{-- Ref --}}
                                <td>
                                    @php
                                        $sRefFiles = $sub->getReferenceFilesArray();
                                        $sRefUrls = $sub->getReferenceUrlsArray();
                                    @endphp
                                    @if(!empty($sRefFiles) || !empty($sRefUrls))
                                        <div style="display:flex; gap:4px; align-items:center;">
                                            @if(!empty($sRefFiles))
                                                <a href="{{ $sRefFiles[0] }}" target="_blank" onclick="event.stopPropagation()"
                                                   style="padding:2px 5px; background:rgba(99,102,241,0.1); border-radius:4px; color:#6366f1; text-decoration:none; font-size:9px; font-weight:700;">
                                                    File
                                                </a>
                                            @endif
                                            @if(!empty($sRefUrls))
                                                <a href="{{ $sRefUrls[0] }}" target="_blank" onclick="event.stopPropagation()"
                                                   style="padding:2px 5px; background:rgba(59,130,246,0.1); border-radius:4px; color:#3b82f6; text-decoration:none; font-size:9px; font-weight:700;">
                                                    Link
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color:var(--color-text-secondary); opacity:0.5;">—</span>
                                    @endif
                                </td>

                                {{-- Artwork --}}
                                <td>
                                    @if(!empty($subArtFiles) || !empty($subArtUrls))
                                        <div style="display:flex; gap:4px; align-items:center;">
                                            @if(!empty($subArtFiles))
                                                <a href="{{ $subArtFiles[0] }}" target="_blank" onclick="event.stopPropagation()"
                                                   style="padding:2px 6px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.25); border-radius:4px; color:#10b981; text-decoration:none; font-size:9.5px; font-weight:800;">
                                                    Artwork
                                                </a>
                                            @endif
                                            @if(!empty($subArtUrls))
                                                <a href="{{ $subArtUrls[0] }}" target="_blank" onclick="event.stopPropagation()"
                                                   style="padding:2px 6px; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.25); border-radius:4px; color:#3b82f6; text-decoration:none; font-size:9.5px; font-weight:800;">
                                                    Link
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span style="font-size:10.5px; color:var(--color-text-secondary); opacity:0.6;">Pending</span>
                                    @endif
                                </td>

                                {{-- Rev --}}
                                <td style="text-align:center;">
                                    <span style="font-size:10px; color:var(--color-text-secondary);">{{ $sub->revisions ?? 0 }}</span>
                                </td>

                                {{-- Stage --}}
                                <td>
                                    @if($sub->approval_stage === 'Closed')
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#065f46;">✓ Closed</span>
                                    @elseif(in_array($sub->approval_stage, ['Final Approval', 'Manager Review']))
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#6d28d9;">Final Approval</span>
                                    @elseif($sub->approval_stage === 'Writer Review')
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#1d4ed8;">Writer Review</span>
                                    @elseif($sub->approval_stage === 'Approver Review')
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#c2410c;">Approver Review</span>
                                    @elseif($sub->approval_stage === 'Further Approver')
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#b45309;">Further Approver</span>
                                    @else
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#b45309;">Designer</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td style="text-align:center; padding:6px 6px;" onclick="event.stopPropagation()">
                                    <div class="quick-actions-grid" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:4px; max-width:140px; margin:0 auto;">
                                        {{-- 1. Submit / Approve Button --}}
                                        @php
                                            $subHasArt = !empty($sub->getFinalDesignsArray()) || !empty($sub->getFinalDesignsUrlsArray());
                                            $subStakeholders = "{writer: " . ($sub->writer_id ?? 'null') . ", approver: " . ($sub->approver_id ?? 'null') . ", brand_manager: " . ($sub->brand_manager_id ?? 'null') . ", coordinator: " . ($sub->coordinator_id ?? 'null') . ", designer: " . ($sub->designer_id ?? 'null') . ", writerName: '" . addslashes($sub->writer->name ?? '') . "', approverName: '" . addslashes($sub->approver->name ?? $project->approver->name ?? '') . "', currentStage: '" . addslashes($subStage) . "'}";
                                        @endphp
                                        @if($subIsDesignerStage && $canSubDesignerSubmit)
                                            @if($subHasArt)
                                                <button type="button" onclick="openBatchModal(event, {{ $sub->id }}, 'Writer Review', 1, 'submit', {{ $subStakeholders }}, false)" class="quick-action-btn btn-approve-quick" title="Submit to Writer Review">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    Submit
                                                </button>
                                            @else
                                                <button type="button" onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $sub->id }}] ? window.projectDeliverables[{{ $sub->id }}] : {{ $sub->id }})" class="quick-action-btn btn-approve-quick" title="Upload Artwork & Submit">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    Submit
                                                </button>
                                            @endif
                                        @elseif(($subIsWriterReviewStage && $canSubWriterReview) || ($subIsApproverReviewStage && $canSubApproverReview) || ($subIsFurtherApproverStage && $canSubFurtherApprover))
                                            <button type="button" onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $sub->id }}] ? window.projectDeliverables[{{ $sub->id }}] : {{ $sub->id }})" class="quick-action-btn btn-approve-quick" title="Review Deliverable">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Review
                                            </button>
                                        @elseif($subIsFinalApprovalStage && $canSubFinalApprove)
                                            <form action="{{ route('deliverables.submit', $sub->id) }}" method="POST" onsubmit="return confirmAction(event, 'Approve Deliverable?', 'Approve artwork and close this deliverable?', false, 'Approve', 'success')" style="display:contents;" onclick="event.stopPropagation()">
                                                @csrf
                                                <button type="submit" class="quick-action-btn btn-approve-quick" title="Approve">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    Approve
                                                </button>
                                            </form>
                                        @endif

                                        {{-- 2. View Button --}}
                                        <button type="button" onclick="openTaskModal(window.projectDeliverables && window.projectDeliverables[{{ $sub->id }}] ? window.projectDeliverables[{{ $sub->id }}] : {{ $sub->id }})" class="quick-action-btn btn-view-quick" title="View Deliverable">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View
                                        </button>

                                        {{-- 3. Revise Button --}}
                                        @if($canSubRevise)
                                            <button type="button" onclick="openDirectRevisionPrompt({{ $sub->id }}, false)" class="quick-action-btn btn-revise-quick" title="Request Revisions">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                Revise
                                            </button>
                                        @endif

                                        {{-- 4. Delete Button --}}
                                        @if($isAdmin || $userRole === 'brandmanager')
                                            <form action="{{ route('deliverables.destroy', $sub) }}" method="POST" onsubmit="return confirmAction(event, 'Delete Subtask?', 'Are you sure you want to delete this subtask? This action cannot be undone.', true)" style="display:contents;" onclick="event.stopPropagation()">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="quick-action-btn btn-delete-quick" title="Delete Subtask">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    Del
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Inline Revision Prompt Modal for Direct Design --}}
<div id="directRevisionModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(6px); z-index:99999; align-items:center; justify-content:center;">
    <div style="background:var(--color-bg-primary); border-radius:16px; border:1px solid var(--color-border-primary); max-width:480px; width:90%; padding:24px; box-shadow:0 30px 80px rgba(0,0,0,0.3);">
        <h3 id="directRevisionModalTitle" style="margin:0 0 8px; font-size:16px; font-weight:800; color:var(--color-text-primary);">Request Revisions</h3>
        <p id="directRevisionModalSub" style="font-size:12px; color:var(--color-text-secondary); margin:0 0 16px;">Describe the revisions required. This deliverable will be routed back to the designer.</p>
        
        <form id="directRevisionForm" method="POST" enctype="multipart/form-data">
            @csrf
            <textarea name="revision_instructions" required placeholder="Specify what needs to be changed in the artwork..."
                      style="width:100%; min-height:100px; padding:12px; background:var(--color-bg-secondary); border:1.5px solid var(--color-border-primary); border-radius:8px; font-family:inherit; font-size:13px; color:var(--color-text-primary); outline:none; resize:vertical; box-sizing:border-box; margin-bottom:14px;"></textarea>

            {{-- Optional Revision Deadline --}}
            <div style="margin-bottom:14px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#ef4444; margin-bottom:6px;">
                    Revision Deadline <span style="font-weight:500; opacity:0.6; text-transform:none; letter-spacing:0;">(optional)</span>
                </label>
                <input type="date" id="directRevisionDeadlineInput" name="revision_deadline" min="{{ date('Y-m-d') }}"
                       style="width:100%; padding:8px 12px; background:var(--color-bg-secondary); border:1.5px solid var(--color-border-primary); border-radius:8px; font-family:inherit; font-size:13px; color:var(--color-text-primary); outline:none; box-sizing:border-box;">
            </div>

            {{-- Optional Image or File Attachment --}}
            <div style="margin-bottom:18px;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#ef4444; margin-bottom:6px;">
                    Attach Image or File <span style="font-weight:500; opacity:0.6; text-transform:none; letter-spacing:0;">(optional)</span>
                </label>
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <label for="directRevisionFileInput" style="display:inline-flex; align-items:center; gap:8px; padding:9px 14px; border-radius:8px; border:1.5px dashed rgba(239,68,68,0.35); background:rgba(239,68,68,0.04); cursor:pointer; font-size:12px; font-weight:600; color:#ef4444; transition:all 0.15s;" onmouseover="this.style.background='rgba(239,68,68,0.08)'" onmouseout="this.style.background='rgba(239,68,68,0.04)'">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span id="directRevisionFileLabel">Choose file or image…</span>
                    </label>
                    <input type="file" id="directRevisionFileInput" name="revision_image" accept="image/*,video/*,application/pdf" style="display:none;" onchange="
                        const f = this.files[0];
                        document.getElementById('directRevisionFileLabel').textContent = f ? f.name : 'Choose file or image…';
                        const prev = document.getElementById('directRevisionFilePreview');
                        const clearBtn = document.getElementById('directRevisionFileClear');
                        if (f) {
                            clearBtn.style.display = 'inline-flex';
                            if (f.type.startsWith('image/')) {
                                const r = new FileReader();
                                r.onload = e => { prev.src = e.target.result; prev.style.display='block'; };
                                r.readAsDataURL(f);
                            } else {
                                prev.style.display = 'none';
                                prev.src = '';
                            }
                        } else {
                            clearBtn.style.display = 'none';
                            prev.style.display = 'none';
                            prev.src = '';
                        }
                    ">
                    <button type="button" id="directRevisionFileClear" style="display:none; padding:6px 10px; background:rgba(239,68,68,0.1); border:none; border-radius:6px; font-size:11px; font-weight:600; color:#ef4444; cursor:pointer;" onclick="
                        const inp = document.getElementById('directRevisionFileInput');
                        inp.value = '';
                        document.getElementById('directRevisionFileLabel').textContent = 'Choose file or image…';
                        document.getElementById('directRevisionFilePreview').style.display = 'none';
                        document.getElementById('directRevisionFilePreview').src = '';
                        this.style.display = 'none';
                    ">Remove</button>
                </div>
                <img id="directRevisionFilePreview" src="" alt="Revision Preview" style="display:none; margin-top:10px; max-width:100%; max-height:180px; border-radius:8px; border:1px solid rgba(239,68,68,0.2); object-fit:contain;">
            </div>
            
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" id="directRevisionCancelBtn" onclick="closeDirectRevisionPrompt()"
                        style="padding:8px 16px; background:transparent; border:1.5px solid var(--color-border-primary); border-radius:8px; font-size:12px; font-weight:600; color:var(--color-text-secondary); cursor:pointer; transition:all 0.15s;">
                    Cancel
                </button>
                <button type="submit" id="directRevisionSubmitBtn"
                        style="padding:8px 18px; background:#ef4444; border:none; border-radius:8px; font-size:12px; font-weight:700; color:#fff; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.15s;">
                    <span>Send to Designer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes directSpin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
    function openDirectRevisionPrompt(taskId, isBatch = false) {
        const modal = document.getElementById('directRevisionModal');
        const form = document.getElementById('directRevisionForm');
        form.action = isBatch ? `/deliverables/${taskId}/batch-revisions` : `/deliverables/${taskId}/revisions`;
        form.reset();

        const btn = document.getElementById('directRevisionSubmitBtn');
        const cancelBtn = document.getElementById('directRevisionCancelBtn');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<span>Send to Designer</span>';
        }
        if (cancelBtn) {
            cancelBtn.disabled = false;
            cancelBtn.style.opacity = '1';
            cancelBtn.style.cursor = 'pointer';
        }

        const title = document.getElementById('directRevisionModalTitle');
        const sub = document.getElementById('directRevisionModalSub');
        if (title && sub) {
            if (isBatch) {
                title.textContent = 'Request Batch Revisions';
                sub.textContent = 'Describe the revisions required. All deliverables in this batch will be routed back to the designer.';
            } else {
                title.textContent = 'Request Revisions';
                sub.textContent = 'Describe the revisions required. This deliverable will be routed back to the designer.';
            }
        }

        const fileLabel = document.getElementById('directRevisionFileLabel');
        if (fileLabel) fileLabel.textContent = 'Choose file or image…';
        const filePrev = document.getElementById('directRevisionFilePreview');
        if (filePrev) { filePrev.style.display = 'none'; filePrev.src = ''; }
        const clearBtn = document.getElementById('directRevisionFileClear');
        if (clearBtn) clearBtn.style.display = 'none';

        modal.style.display = 'flex';
    }

    function closeDirectRevisionPrompt() {
        const modal = document.getElementById('directRevisionModal');
        modal.style.display = 'none';

        const btn = document.getElementById('directRevisionSubmitBtn');
        const cancelBtn = document.getElementById('directRevisionCancelBtn');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<span>Send to Designer</span>';
        }
        if (cancelBtn) {
            cancelBtn.disabled = false;
            cancelBtn.style.opacity = '1';
            cancelBtn.style.cursor = 'pointer';
        }

        const fileLabel = document.getElementById('directRevisionFileLabel');
        if (fileLabel) fileLabel.textContent = 'Choose file or image…';
        const filePrev = document.getElementById('directRevisionFilePreview');
        if (filePrev) { filePrev.style.display = 'none'; filePrev.src = ''; }
        const clearBtn = document.getElementById('directRevisionFileClear');
        if (clearBtn) clearBtn.style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const revForm = document.getElementById('directRevisionForm');
        if (revForm) {
            revForm.addEventListener('submit', function() {
                const btn = document.getElementById('directRevisionSubmitBtn');
                const cancelBtn = document.getElementById('directRevisionCancelBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.75';
                    btn.style.cursor = 'not-allowed';
                    btn.innerHTML = `
                        <svg style="width:13px; height:13px; animation:directSpin 0.75s linear infinite;" fill="none" viewBox="0 0 24 24">
                            <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Sending...</span>
                    `;
                }
                if (cancelBtn) {
                    cancelBtn.disabled = true;
                    cancelBtn.style.opacity = '0.5';
                    cancelBtn.style.cursor = 'not-allowed';
                }
            });
        }
    });

    // Populate Fast Track client-side deliverables cache
    window.projectDeliverables = window.projectDeliverables || {};
    @foreach(($designBatches ?? $directDesignTasks ?? collect()) as $dt)
        window.projectDeliverables[{{ $dt->id }}] = @json($dt);
        @foreach($dt->subtasks as $dst)
            window.projectDeliverables[{{ $dst->id }}] = @json($dst);
        @endforeach
    @endforeach
</script>
@endif
