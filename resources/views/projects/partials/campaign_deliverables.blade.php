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

@if($outlineTasks->count() > 0)
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
                    <th style="width:140px;">Copy</th>
                    <th style="width:120px;">Caption</th>
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
                    @php
                        $outlineSubtasks = $task->subtasks->filter(function($s) use ($task) {
                            $type = strtolower(trim($s->post_type ?? ($task->post_type ?? '')));
                            return $type === 'outlines' || $type === 'outline';
                        });
                    @endphp
                    @if($outlineSubtasks->count() > 0)
                        {{-- Heading Row for Deliverable with Subtasks --}}
                        <tr class="rtb-heading-row" style="background:var(--color-bg-secondary); border-left:3px solid #3b82f6; cursor:pointer;" onclick="toggleSubtasks(event, {{ $task->id }})">
                            <td colspan="4">
                                <div class="deliverable-name-cell" style="padding: 8px 0; display:flex; align-items:center; gap:10px;">
                                    <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle active" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:6px; outline:none;"></button>
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:800; color:var(--color-text-primary); font-size:13px; letter-spacing:-0.01em;">{{ $task->title }}</span>
                                    <span style="font-size:10px; font-weight:700; color:var(--color-text-secondary); background:var(--color-bg-primary); border:1px solid var(--color-border-primary); padding:2px 7px; border-radius:6px;">{{ $outlineSubtasks->count() }} posts</span>
                                </div>
                            </td>
                            <td colspan="7" style="padding-right:15px;" onclick="event.stopPropagation()">
                                @php
                                    $stage = $task->approval_stage;
                                    $nextStage = $task->getNextStage();
                                    $canApproveBatch = $isAdmin || (
                                        (in_array($stage, ['Writer', 'Assignee', 'Writer Review']) && in_array($userRole, ['writer', 'assignee']) && (!$task->writer_id || $task->writer_id == $currentUserId)) ||
                                        (in_array($stage, ['Approver', 'Approver Review']) && in_array($userRole, ['approver', 'approvercoordinator', 'operationsmanager']) && (!$task->approver_id || $task->approver_id == $currentUserId)) ||
                                        ($stage === 'Further Approver' && in_array($userRole, ['approver', 'approvercoordinator', 'operationsmanager']) && (!$task->further_approver_id || $task->further_approver_id == $currentUserId)) ||
                                        (in_array($stage, ['Brand Manager', 'AM/BD', 'Final Approval']) && in_array($userRole, ['brandmanager', 'operationsmanager']) && (!$task->brand_manager_id || $task->brand_manager_id == $currentUserId)) ||
                                        ($stage === 'Coordinator' && in_array($userRole, ['coordinator', 'approvercoordinator', 'operationsmanager']) && (!$task->coordinator_id || $task->coordinator_id == $currentUserId)) ||
                                        ($stage === 'Designer' && $userRole === 'designer' && (!$task->designer_id || $task->designer_id == $currentUserId))
                                    );
                                    $subtasks = $outlineSubtasks;
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
                        @foreach($outlineSubtasks as $subIndex => $subtask)
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
                                <td onclick="event.stopPropagation()">
                                    @if($subtask->concept)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Concept',{{ json_encode($subtask->concept) }})">{{ strip_tags($subtask->concept) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                </td>
                                <td onclick="event.stopPropagation()">
                                    @if($subtask->post_copy)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Copy',{{ json_encode($subtask->post_copy) }})">{{ strip_tags($subtask->post_copy) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                </td>
                                <td onclick="event.stopPropagation()">
                                    @if($subtask->caption)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Caption',{{ json_encode($subtask->caption) }})">{{ strip_tags($subtask->caption) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                </td>
                                <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                    @php
                                        $subRefFiles = $subtask->getReferenceFilesArray();
                                        $subRefUrls  = $subtask->getReferenceUrlsArray();
                                        $totalSubRefFiles = count($subRefFiles);
                                        $totalSubRefUrls  = count($subRefUrls);
                                        $totalSubRefs     = $totalSubRefFiles + $totalSubRefUrls;
                                    @endphp
                                    @if($totalSubRefs === 1 && $totalSubRefFiles === 1)
                                        @php $singleRef = $subRefFiles[0]; @endphp
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                            @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                                <video src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Image">
                                            @endif
                                        @else
                                            <a href="{{ $singleRef }}" target="_blank" style="display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.25); color:#0055D4; font-size:10px; font-weight:700; text-decoration:none;" title="Download / View Attachment">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                File
                                            </a>
                                        @endif
                                    @elseif($totalSubRefs === 1 && $totalSubRefUrls === 1)
                                        <a href="{{ $subRefUrls[0] }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1); box-shadow:0 0 8px rgba(0,85,212,0.4);" title="Visit Link">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </a>
                                    @elseif($totalSubRefs > 1)
                                        <button type="button" onclick="openMediaGallery('Reference Media', {{ json_encode($subRefFiles) }}, {{ json_encode($subRefUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalSubRefs }} references">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Ref ({{ $totalSubRefs }})
                                        </button>
                                    @else
                                        <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                    @php
                                        $subArtFiles = $subtask->getFinalDesignsArray();
                                        $subArtUrls  = $subtask->getFinalDesignsUrlsArray();
                                        $totalSubArtFiles = count($subArtFiles);
                                        $totalSubArtUrls  = count($subArtUrls);
                                        $totalSubArt      = $totalSubArtFiles + $totalSubArtUrls;
                                    @endphp
                                    @if($totalSubArt === 1 && $totalSubArtFiles === 1)
                                        @php $singleArt = $subArtFiles[0]; @endphp
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)(?:$|\?)/i', $singleArt))
                                            @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleArt))
                                                <video src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Artwork">
                                            @endif
                                        @else
                                            <a href="{{ $singleArt }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                        @endif
                                    @elseif($totalSubArt === 1 && $totalSubArtUrls === 1)
                                        <a href="{{ $subArtUrls[0] }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                    @elseif($totalSubArt > 1)
                                        <button type="button" onclick="openMediaGallery('Artwork Media', {{ json_encode($subArtFiles) }}, {{ json_encode($subArtUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalSubArt }} artworks">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Artwork ({{ $totalSubArt }})
                                        </button>
                                    @else
                                        <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                    @endif
                                </td>
                                <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                                <td><div class="rtb-stage-label">{{ $subtask->approval_stage ?: 'Assignee' }}</div></td>
                                <td><div style="font-size:10px; font-weight:700; color:var(--color-text-secondary);">{{ $subtask->client_status ?: 'Not Sent' }}</div></td>
                                <td style="text-align:center;">
                                    <div class="quick-actions-grid">
                                        @if($canEditInline)
                                            <a href="{{ route('deliverables.show', $subtask->id) }}" class="quick-action-btn btn-edit-quick" onclick="event.stopPropagation()" style="text-decoration:none; background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.25);">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </a>
                                        @else
                                            <a href="{{ route('deliverables.show', $subtask->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Standalone Outline Deliverable --}}
                        <tr class="{{ $task->approval_stage === 'Closed' ? 'task-closed' : '' }}">
                            @php
                                $canEditStandalone = $isAdmin || (($task->approval_stage === 'Assignee' || $task->approval_stage === 'Writer') && ($userRole === 'writer' || $userRole === 'assignee') && (!$task->writer_id || auth()->id() == $task->writer_id));
                            @endphp
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
                                @if($task->concept)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Concept',{{ json_encode($task->concept) }})">{{ strip_tags($task->concept) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($task->post_copy)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Copy',{{ json_encode($task->post_copy) }})">{{ strip_tags($task->post_copy) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($task->caption)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Caption',{{ json_encode($task->caption) }})">{{ strip_tags($task->caption) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                            </td>
                                <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                    @php
                                        $taskRefFiles = $task->getReferenceFilesArray();
                                        $taskRefUrls  = $task->getReferenceUrlsArray();
                                        $totalTaskRefFiles = count($taskRefFiles);
                                        $totalTaskRefUrls  = count($taskRefUrls);
                                        $totalTaskRefs     = $totalTaskRefFiles + $totalTaskRefUrls;
                                    @endphp
                                    @if($totalTaskRefs === 1 && $totalTaskRefFiles === 1)
                                        @php $singleRef = $taskRefFiles[0]; @endphp
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                            @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                                <video src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Image">
                                            @endif
                                        @else
                                            <a href="{{ $singleRef }}" target="_blank" style="display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.25); color:#0055D4; font-size:10px; font-weight:700; text-decoration:none;" title="Download / View Attachment">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                File
                                            </a>
                                        @endif
                                    @elseif($totalTaskRefs === 1 && $totalTaskRefUrls === 1)
                                        <a href="{{ $taskRefUrls[0] }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1); box-shadow:0 0 8px rgba(0,85,212,0.4);" title="Visit Link">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </a>
                                    @elseif($totalTaskRefs > 1)
                                        <button type="button" onclick="openMediaGallery('Reference Media', {{ json_encode($taskRefFiles) }}, {{ json_encode($taskRefUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalTaskRefs }} references">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Ref ({{ $totalTaskRefs }})
                                        </button>
                                    @else
                                        <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                    @php
                                        $taskArtFiles = $task->getFinalDesignsArray();
                                        $taskArtUrls  = $task->getFinalDesignsUrlsArray();
                                        $totalTaskArtFiles = count($taskArtFiles);
                                        $totalTaskArtUrls  = count($taskArtUrls);
                                        $totalTaskArt      = $totalTaskArtFiles + $totalTaskArtUrls;
                                    @endphp
                                    @if($totalTaskArt === 1 && $totalTaskArtFiles === 1)
                                        @php $singleArt = $taskArtFiles[0]; @endphp
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)/i', $singleArt))
                                            @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleArt))
                                                <video src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Artwork">
                                            @endif
                                        @else
                                            <a href="{{ $singleArt }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                        @endif
                                    @elseif($totalTaskArt === 1 && $totalTaskArtUrls === 1)
                                        <a href="{{ $taskArtUrls[0] }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                    @elseif($totalTaskArt > 1)
                                        <button type="button" onclick="openMediaGallery('Artwork Media', {{ json_encode($taskArtFiles) }}, {{ json_encode($taskArtUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalTaskArt }} artworks">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Artwork ({{ $totalTaskArt }})
                                        </button>
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
                                            (in_array($stage, ['Writer', 'Assignee', 'Writer Review']) && in_array($userRole, ['writer', 'assignee']) && (!$task->writer_id || $task->writer_id == $currentUserId)) ||
                                            (in_array($stage, ['Approver', 'Approver Review']) && in_array($userRole, ['approver', 'approvercoordinator', 'operationsmanager']) && (!$task->approver_id || $task->approver_id == $currentUserId)) ||
                                            ($stage === 'Further Approver' && in_array($userRole, ['approver', 'approvercoordinator', 'operationsmanager']) && (!$task->further_approver_id || $task->further_approver_id == $currentUserId)) ||
                                            (in_array($stage, ['Brand Manager', 'AM/BD', 'Final Approval']) && in_array($userRole, ['brandmanager', 'operationsmanager']) && (!$task->brand_manager_id || $task->brand_manager_id == $currentUserId)) ||
                                            ($stage === 'Coordinator' && in_array($userRole, ['coordinator', 'approvercoordinator', 'operationsmanager']) && (!$task->coordinator_id || $task->coordinator_id == $currentUserId)) ||
                                            ($stage === 'Designer' && $userRole === 'designer' && (!$task->designer_id || $task->designer_id == $currentUserId))
                                        );
                                        $taskStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                    @endphp
                                    @if($canApprove && $nextStage)
                                        <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', 1, 'submit', {{ $taskStakeholders }}, false)" class="quick-action-btn btn-approve-quick">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Submit
                                        </button>
                                    @endif
                                    @if($canEditStandalone)
                                        <a href="{{ route('deliverables.show', $task->id) }}" class="quick-action-btn btn-edit-quick" onclick="event.stopPropagation()" style="text-decoration:none; background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.25);">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </a>
                                    @else
                                        <a href="{{ route('deliverables.show', $task->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                    @endif
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
@endif

{{-- ========================================================= --}}
{{-- 2. SECTION: OTHER DELIVERABLES                            --}}
{{-- ========================================================= --}}
<div class="cd-table-wrap">
    <div class="cd-header">
        <div class="cd-header-left" style="display:flex; align-items:center; gap:10px;">
            <h2 style="margin:0;">{{ $outlineTasks->count() > 0 ? 'Other Deliverables' : 'Deliverables' }}</h2>
            <span style="font-size:11px; font-weight:700; color:#0ea5e9; background:rgba(14,165,233,0.1); border:1px solid rgba(14,165,233,0.25); padding:2px 8px; border-radius:6px;">
                {{ $otherTasks->count() }} {{ \Illuminate\Support\Str::plural('deliverable', $otherTasks->count()) }}
            </span>
        </div>
        <div class="cd-header-right">
            <div style="position:relative;">
                <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--color-text-secondary);" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search {{ $outlineTasks->count() > 0 ? 'other deliverables' : 'deliverables' }}..." onkeyup="searchDeliverables(this.value)" style="padding:6px 12px 6px 30px; border-radius:8px; border:1px solid var(--color-border-primary); background:var(--color-bg-primary); color:var(--color-text-primary); font-size:12px; outline:none; transition:border-color 0.2s; width: 220px;" onfocus="this.style.borderColor='#0055D4'" onblur="this.style.borderColor='var(--color-border-primary)'">
            </div>
        </div>
    </div>

    <div style="width:100%; overflow-x:auto;">
        <table class="cd-table">
            <thead>
                <tr>
                    <th style="width:170px;">Deliverable</th>
                    <th style="width:80px;">Due</th>
                    <th style="width:130px;">Type</th>
                    <th style="width:210px;">Brief</th>
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
                    @php
                        $otherSubtasks = $task->subtasks->filter(function($s) {
                            $type = strtolower(trim($s->post_type ?? ''));
                            return $type !== 'outlines' && $type !== 'outline';
                        });
                    @endphp
                    @if($otherSubtasks->count() > 0)
                        {{-- Heading Row for Deliverable with Subtasks --}}
                        <tr class="rtb-heading-row" style="background:var(--color-bg-secondary); border-left:3px solid #0ea5e9; cursor:pointer;" onclick="toggleSubtasks(event, {{ $task->id }})">
                            <td colspan="4">
                                <div class="deliverable-name-cell" style="padding: 8px 0; display:flex; align-items:center; gap:10px;">
                                    <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle active" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:6px; outline:none;"></button>
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:800; color:var(--color-text-primary); font-size:13px; letter-spacing:-0.01em;">{{ $task->title }}</span>
                                    <span style="font-size:10px; font-weight:700; color:var(--color-text-secondary); background:var(--color-bg-primary); border:1px solid var(--color-border-primary); padding:2px 7px; border-radius:6px;">{{ $otherSubtasks->count() }} items</span>
                                </div>
                            </td>
                            <td colspan="6" style="padding-right:15px;" onclick="event.stopPropagation()">
                                @php
                                    $stage = $task->approval_stage ?: 'Assign';
                                    $nextStage = $task->getNextStage();
                                    $isManager = $isAdmin || $userRole === 'brandmanager' || $userRole === 'operationsmanager';
                                    $isAssigneePerson = (!$task->writer_id || $task->writer_id == $currentUserId || $task->designer_id == $currentUserId || in_array($userRole, ['writer', 'assignee', 'designer']));
                                    $canApproveBatch = $isAdmin || (
                                        (($stage === 'Writer' || $stage === 'Assignee' || $stage === 'Assign') && ($isAssigneePerson || $isManager)) ||
                                        (($stage === 'AM/BD' || $stage === 'Final Approval' || $stage === 'Approve') && $isManager)
                                    );
                                    $subtasks = $otherSubtasks;
                                    $totalInBatch = $subtasks->count();
                                    $parentStageNorm = $stage ?: 'Assign';
                                    $readyInBatch = $subtasks->filter(fn($t) => ($t->approval_stage ?: 'Assign') === $parentStageNorm)->count();
                                    $allReady = $readyInBatch === $totalInBatch;
                                    $isGated = !$allReady;
                                    $batchStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                @endphp
                                <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px;">
                                    <span style="font-size:10px; font-weight:700; color:#0ea5e9; background:rgba(14,165,233,0.1); border:1px solid rgba(14,165,233,0.2); padding:3px 9px; border-radius:6px;">
                                        {{ $task->approval_stage === 'Close' ? 'Closed' : ($task->approval_stage ?: 'Assign') }}
                                    </span>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <a href="{{ route('deliverables.showBatch', $task->id) }}" onclick="event.stopPropagation()" style="display:inline-flex;align-items:center;gap:4px;padding:6px 10px;font-size:11px;font-weight:600;color:var(--color-text-secondary);background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:7px;text-decoration:none;white-space:nowrap;">View</a>
                                        @if($canApproveBatch && $nextStage)
                                            <button onclick="event.stopPropagation(); openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', {{ $totalInBatch }}, 'submit', {{ $batchStakeholders }})"
                                                    style="padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; background:#0055D4; color:#fff; border:1px solid #0055D4; cursor:pointer;" {{ $isGated ? 'disabled' : '' }}>
                                                {{ $stage === 'Approve' ? 'Approve & Close' : 'Send for Approval' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($otherSubtasks as $subIndex => $subtask)
                            @php
                                $isAssignedPerson = (!$subtask->writer_id || auth()->id() == $subtask->writer_id || auth()->id() == $subtask->designer_id || in_array($userRole, ['writer', 'assignee', 'designer']));
                                $isSubManager = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']);
                                $canEditInline = $isAdmin || (($subtask->approval_stage === 'Assignee' || $subtask->approval_stage === 'Writer' || $subtask->approval_stage === 'Assign' || !$subtask->approval_stage) && ($isAssignedPerson || $isSubManager));
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
                                <td style="overflow:hidden;">
                                    @php $colors = $subtaskTypeColors[$subtask->subtask_type ?: ($subtask->post_type ?: 'default')] ?? ($subtaskTypeColors['default'] ?? ['bg' => 'rgba(14,165,233,0.1)', 'text' => '#0ea5e9', 'border' => 'rgba(14,165,233,0.25)']); @endphp
                                    <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }}; font-size:9.5px; font-weight:800; letter-spacing:0.04em; padding:3px 8px;">
                                        {{ $subtask->subtask_type ?: ($subtask->post_type ?: 'Standard') }}
                                    </span>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    @php $briefContent = $subtask->concept ?? $subtask->notes; @endphp
                                    @if($briefContent)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Brief',{{ json_encode($briefContent) }})">{{ strip_tags($briefContent) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                                </td>
                                <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                    @php
                                        $subRefFiles = $subtask->getReferenceFilesArray();
                                        $subRefUrls  = $subtask->getReferenceUrlsArray();
                                        $totalSubRefFiles = count($subRefFiles);
                                        $totalSubRefUrls  = count($subRefUrls);
                                        $totalSubRefs     = $totalSubRefFiles + $totalSubRefUrls;
                                    @endphp
                                    @if($totalSubRefs === 1 && $totalSubRefFiles === 1)
                                        @php $singleRef = $subRefFiles[0]; @endphp
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                            @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                                <video src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Image">
                                            @endif
                                        @else
                                            <a href="{{ $singleRef }}" target="_blank" style="display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.25); color:#0055D4; font-size:10px; font-weight:700; text-decoration:none;" title="Download / View Attachment">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                File
                                            </a>
                                        @endif
                                    @elseif($totalSubRefs === 1 && $totalSubRefUrls === 1)
                                        <a href="{{ $subRefUrls[0] }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1); box-shadow:0 0 8px rgba(0,85,212,0.4);" title="Visit Link">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        </a>
                                    @elseif($totalSubRefs > 1)
                                        <button type="button" onclick="openMediaGallery('Reference Media', {{ json_encode($subRefFiles) }}, {{ json_encode($subRefUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalSubRefs }} references">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Ref ({{ $totalSubRefs }})
                                        </button>
                                    @else
                                        <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                    @endif
                                </td>
                                <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                    @php
                                        $subArtFiles = $subtask->getFinalDesignsArray();
                                        $subArtUrls  = $subtask->getFinalDesignsUrlsArray();
                                        $totalSubArtFiles = count($subArtFiles);
                                        $totalSubArtUrls  = count($subArtUrls);
                                        $totalSubArt      = $totalSubArtFiles + $totalSubArtUrls;
                                    @endphp
                                    @if($totalSubArt === 1 && $totalSubArtFiles === 1)
                                        @php $singleArt = $subArtFiles[0]; @endphp
                                        @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)(?:$|\?)/i', $singleArt))
                                            @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleArt))
                                                <video src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Video" preload="metadata"></video>
                                            @else
                                                <img src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Artwork">
                                            @endif
                                        @else
                                            <a href="{{ $singleArt }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                        @endif
                                    @elseif($totalSubArt === 1 && $totalSubArtUrls === 1)
                                        <a href="{{ $subArtUrls[0] }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                    @elseif($totalSubArt > 1)
                                        <button type="button" onclick="openMediaGallery('Artwork Media', {{ json_encode($subArtFiles) }}, {{ json_encode($subArtUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalSubArt }} artworks">
                                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Artwork ({{ $totalSubArt }})
                                        </button>
                                    @else
                                        <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                    @endif
                                </td>
                                <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                                <td><div class="rtb-stage-label">{{ $subtask->approval_stage === 'Close' ? 'Closed' : ($subtask->approval_stage ?: 'Assign') }}</div></td>
                                <td><div style="font-size:10px; font-weight:700; color:var(--color-text-secondary);">{{ $subtask->client_status ?: 'Not Sent' }}</div></td>
                                <td style="text-align:center;">
                                    <div class="quick-actions-grid">
                                        @if($canEditInline)
                                            <a href="{{ route('deliverables.show', $subtask->id) }}" class="quick-action-btn btn-edit-quick" onclick="event.stopPropagation()" style="text-decoration:none; background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.25);">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                Edit
                                            </a>
                                        @else
                                            <a href="{{ route('deliverables.show', $subtask->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- Standalone Other Deliverable --}}
                        <tr class="{{ $task->approval_stage === 'Closed' ? 'task-closed' : '' }}">
                            @php
                                $isAssignedStandalonePerson = (!$task->writer_id || auth()->id() == $task->writer_id || auth()->id() == $task->designer_id || in_array($userRole, ['writer', 'assignee', 'designer']));
                                $isStandaloneManager = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']);
                                $canEditOtherStandalone = $isAdmin || (($task->approval_stage === 'Assignee' || $task->approval_stage === 'Writer' || $task->approval_stage === 'Assign' || !$task->approval_stage) && ($isAssignedStandalonePerson || $isStandaloneManager));
                            @endphp
                            <td>
                                <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                    <span class="dashboard-task-title-{{ $task->id }}" style="font-weight:900; color:var(--color-text-primary);">{{ $task->title }}</span>
                                </div>
                            </td>
                            <td>
                                @php $displayDeadline = $task->deadline ?? $project->deadline; @endphp
                                <div style="font-weight:800;">{{ $displayDeadline ? \Carbon\Carbon::parse($displayDeadline)->format('M d, Y') : '—' }}</div>
                            </td>
                            <td style="overflow:hidden;">
                                @php $colors = $subtaskTypeColors[$task->subtask_type ?: ($task->post_type ?: 'default')] ?? ($subtaskTypeColors['default'] ?? ['bg' => 'rgba(14,165,233,0.1)', 'text' => '#0ea5e9', 'border' => 'rgba(14,165,233,0.25)']); @endphp
                                <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }}; font-size:9.5px; font-weight:800; letter-spacing:0.04em; padding:3px 8px;">
                                    {{ $task->subtask_type ?: ($task->post_type ?: 'Standard') }}
                                </span>
                            </td>
                            <td onclick="event.stopPropagation()">
                                @php $briefContent = $task->concept ?? $task->notes; @endphp
                                @if($briefContent)<div class="cell-text" onclick="event.stopPropagation();openTextPreview('Brief',{{ json_encode($briefContent) }})">{{ strip_tags($briefContent) }}</div>@else<span style="color:var(--color-text-secondary);opacity:0.7;">N/A</span>@endif
                            </td>
                            <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                @php
                                    $taskRefFiles = $task->getReferenceFilesArray();
                                    $taskRefUrls  = $task->getReferenceUrlsArray();
                                    $totalTaskRefFiles = count($taskRefFiles);
                                    $totalTaskRefUrls  = count($taskRefUrls);
                                    $totalTaskRefs     = $totalTaskRefFiles + $totalTaskRefUrls;
                                @endphp
                                @if($totalTaskRefs === 1 && $totalTaskRefFiles === 1)
                                    @php $singleRef = $taskRefFiles[0]; @endphp
                                    @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                        @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleRef))
                                            <video src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Video" preload="metadata"></video>
                                        @else
                                            <img src="{{ $singleRef }}" class="rtb-ref-preview" style="margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleRef }}', false)" title="View Image">
                                        @endif
                                    @else
                                        <a href="{{ $singleRef }}" target="_blank" style="display:inline-flex; align-items:center; gap:5px; padding:4px 8px; border-radius:6px; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.25); color:#0055D4; font-size:10px; font-weight:700; text-decoration:none;" title="Download / View Attachment">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            File
                                        </a>
                                    @endif
                                @elseif($totalTaskRefs === 1 && $totalTaskRefUrls === 1)
                                    <a href="{{ $taskRefUrls[0] }}" target="_blank" style="display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:6px; cursor:pointer; color:#0055D4; border:1px solid rgba(0,85,212,0.35); background:rgba(0,85,212,0.1); box-shadow:0 0 8px rgba(0,85,212,0.4);" title="Visit Link">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </a>
                                @elseif($totalTaskRefs > 1)
                                    <button type="button" onclick="openMediaGallery('Reference Media', {{ json_encode($taskRefFiles) }}, {{ json_encode($taskRefUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalTaskRefs }} references">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Ref ({{ $totalTaskRefs }})
                                    </button>
                                @else
                                    <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation()" style="white-space:nowrap; vertical-align:middle;">
                                @php
                                    $taskArtFiles = $task->getFinalDesignsArray();
                                    $taskArtUrls  = $task->getFinalDesignsUrlsArray();
                                    $totalTaskArtFiles = count($taskArtFiles);
                                    $totalTaskArtUrls  = count($taskArtUrls);
                                    $totalTaskArt      = $totalTaskArtFiles + $totalTaskArtUrls;
                                @endphp
                                @if($totalTaskArt === 1 && $totalTaskArtFiles === 1)
                                    @php $singleArt = $taskArtFiles[0]; @endphp
                                    @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg|mp4|webm|ogg|mov)/i', $singleArt))
                                        @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $singleArt))
                                            <video src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Video" preload="metadata"></video>
                                        @else
                                            <img src="{{ $singleArt }}" class="rtb-ref-preview" style="border-color:rgba(16,185,129,0.3); margin-bottom:0; display:block; cursor:pointer;" onclick="openImagePreview('{{ $singleArt }}', false)" title="View Artwork">
                                        @endif
                                    @else
                                        <a href="{{ $singleArt }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                    @endif
                                @elseif($totalTaskArt === 1 && $totalTaskArtUrls === 1)
                                    <a href="{{ $taskArtUrls[0] }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                @elseif($totalTaskArt > 1)
                                    <button type="button" onclick="openMediaGallery('Artwork Media', {{ json_encode($taskArtFiles) }}, {{ json_encode($taskArtUrls) }})" style="background:rgba(16,185,129,0.12); color:#10b981; border:1px solid rgba(16,185,129,0.3); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; transition:all 0.15s;" title="View all {{ $totalTaskArt }} artworks">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Artwork ({{ $totalTaskArt }})
                                    </button>
                                @else
                                    <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                @endif
                            </td>
                            <td><span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);opacity:0.7;">N/A</span></td>
                            <td>
                                <div style="font-size:10px; font-weight:900; color:#0055D4; text-transform:uppercase; letter-spacing:0.05em;">{{ $task->approval_stage === 'Close' ? 'Closed' : ($task->approval_stage ?: 'Assign') }}</div>
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
                                        $stage = $task->approval_stage ?: 'Assign';
                                        $nextStage = $task->getNextStage();
                                        $isStandaloneManager = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']);
                                        $isStandaloneAssignee = (!$task->writer_id || $task->writer_id == $currentUserId || $task->designer_id == $currentUserId || in_array($userRole, ['writer', 'assignee', 'designer']));
                                        $canApprove = $isAdmin || (
                                            (($stage === 'Writer' || $stage === 'Assignee' || $stage === 'Assign') && ($isStandaloneAssignee || $isStandaloneManager)) ||
                                            (($stage === 'AM/BD' || $stage === 'Final Approval' || $stage === 'Approve') && $isStandaloneManager)
                                        );
                                        $taskStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . ", writerName: '" . addslashes($task->writer->name ?? '') . "'}";
                                    @endphp
                                    @if($canApprove && $nextStage)
                                        <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', 1, 'submit', {{ $taskStakeholders }}, false)" class="quick-action-btn btn-approve-quick">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            {{ $stage === 'Approve' ? 'Approve & Close' : 'Send for Approval' }}
                                        </button>
                                    @endif
                                    @if($canEditOtherStandalone)
                                        <a href="{{ route('deliverables.show', $task->id) }}" class="quick-action-btn btn-edit-quick" onclick="event.stopPropagation()" style="text-decoration:none; background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.25);">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </a>
                                    @else
                                        <a href="{{ route('deliverables.show', $task->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">View</a>
                                    @endif
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
                                    <div style="font-size:13px;font-weight:800;color:var(--color-text-primary);">{{ $outlineTasks->count() > 0 ? 'No other deliverables yet' : 'No deliverables yet' }}</div>
                                    <div style="font-size:11px;font-weight:500;color:var(--color-text-secondary);">{{ $outlineTasks->count() > 0 ? 'Create Radio script, KV, Presentation, or other deliverable types' : 'Create an Outline, Radio script, KV, Presentation, or other deliverable types above to get started' }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
