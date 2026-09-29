@php
    $designBatches = $project->deliverables->whereNull('parent_deliverable_id')->where('flow_type', 'direct_design');
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
            <span style="font-size:11px; font-weight:700; color:#6366f1; background:rgba(99,102,241,0.1); border:1px solid rgba(99,102,241,0.25); padding:2px 8px; border-radius:6px;">
                {{ $designBatches->count() }} {{ \Illuminate\Support\Str::plural('deliverable', $designBatches->count()) }}
            </span>
        </div>
        <div class="cd-header-right">
            @can('create-deliverable')
            <a href="{{ route('deliverables.create', ['project_id' => $project->id, 'flow' => 'design']) }}"
               style="display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:#6366f1; border-radius:8px; font-size:11px; font-weight:700; color:#fff; text-decoration:none; transition:all 0.15s;">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                + Add Fast Track Deliverable
            </a>
            @endcan
        </div>
    </div>

    <div style="width:100%; overflow-x:auto;">
        <table class="cd-table">
            <thead>
                <tr>
                    <th style="width:200px;">Deliverable / Batch</th>
                    <th style="width:130px;">Designer</th>
                    <th style="width:120px;">Deadline</th>
                    <th style="width:150px;">Brief / Concept</th>
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
                        $isDesignerStage = $task->approval_stage === 'Designer';
                        $isManagerReviewStage = $task->approval_stage === 'Manager Review';
                        
                        $isAssignedDesigner = $task->designer_id && $currentUserId == $task->designer_id;
                        $canDesignerSubmit = $isAdmin || ($userRole === 'operationsmanager') || ($isDesignerStage && ($isAssignedDesigner || (!$task->designer_id && $userRole === 'designer')));
                        $canManagerReview = $isAdmin || in_array($userRole, ['brandmanager', 'operationsmanager']);
                    @endphp

                    {{-- Parent Row --}}
                    <tr class="{{ $hasSubtasks ? 'rtb-heading-row' : '' }} {{ $isClosed ? 'task-closed' : '' }}"
                        style="{{ $hasSubtasks ? 'background:var(--color-bg-secondary); border-left:3px solid #6366f1; cursor:pointer;' : '' }}"
                        @if($hasSubtasks) onclick="toggleSubtasks(event, {{ $task->id }})" @else onclick="openTaskModal({{ $task->id }})" style="cursor:pointer;" @endif>
                        
                        {{-- Title & Format --}}
                        <td>
                            <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                @if($hasSubtasks)
                                    <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle active" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:6px; outline:none;"></button>
                                @endif
                                <div>
                                    <div style="font-weight:700; color:var(--color-text-primary);">{{ $task->title }}</div>
                                    @if($hasSubtasks)
                                        <div style="font-size:10px; font-weight:700; color:#6366f1;">{{ $subtasks->count() }} subtasks</div>
                                    @elseif($task->post_type)
                                        <span class="subtask-pill" style="margin-top:2px; font-size:8.5px; background:rgba(99,102,241,0.08); color:#6366f1; border-color:rgba(99,102,241,0.25);">
                                            {{ $task->post_type }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Designer --}}
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div style="width:22px; height:22px; border-radius:50%; background:#6366f1; color:#fff; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:800;">
                                    {{ substr($task->designer->name ?? ($task->assignee_name ?? 'D'), 0, 1) }}
                                </div>
                                <span style="font-weight:600; font-size:12px; color:var(--color-text-primary);">
                                    {{ $task->designer->name ?? ($task->assignee_name ?? 'Unassigned') }}
                                </span>
                            </div>
                        </td>

                        {{-- Deadline --}}
                        <td>
                            @php
                                $dDate = $task->designer_deadline ?? $task->deadline;
                            @endphp
                            @if($dDate)
                                <div style="font-size:11px; font-weight:600; color:var(--color-text-secondary);">
                                    {{ \Carbon\Carbon::parse($dDate)->format('M d, Y') }}
                                </div>
                            @else
                                <span style="color:var(--color-text-secondary); opacity:0.5;">—</span>
                            @endif
                        </td>

                        {{-- Concept / Brief --}}
                        <td>
                            @if($task->concept || $task->notes)
                                <div class="cell-text" onclick="event.stopPropagation();openTextPreview('Creative Brief', {{ json_encode($task->concept ?? $task->notes) }})"
                                     style="max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:11px; color:var(--color-text-secondary); cursor:pointer;">
                                    {{ strip_tags($task->concept ?? $task->notes) }}
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
                            @elseif($task->approval_stage === 'Manager Review')
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#ede9fe; color:#6d28d9; border:1px solid #ddd6fe;">
                                    Manager Review
                                </span>
                            @else
                                <span style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; background:#fef3c7; color:#b45309; border:1px solid #fde68a;">
                                    Designer
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <div style="display:flex; align-items:center; justify-content:center; gap:6px;">
                                @if($hasSubtasks)
                                    <a href="{{ route('deliverables.batch', $task->id) }}" class="cd-btn cd-btn-outline" style="padding:4px 8px; font-size:11px;">
                                        Batch View
                                    </a>
                                @endif

                                @if($isDesignerStage && $canDesignerSubmit)
                                    <button type="button" onclick="openTaskModal({{ $task->id }})"
                                            style="padding:5px 10px; background:#6366f1; color:#fff; border:none; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer;">
                                        Submit Artwork
                                    </button>
                                @elseif($isManagerReviewStage && $canManagerReview)
                                    <form action="{{ route('deliverables.submit', $task->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Approve artwork and close this deliverable?')"
                                                style="padding:5px 10px; background:#10b981; color:#fff; border:none; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer;">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button" onclick="openDirectRevisionPrompt({{ $task->id }})"
                                            style="padding:5px 10px; background:#ef4444; color:#fff; border:none; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer;">
                                        Revise
                                    </button>
                                @else
                                    <button type="button" onclick="openTaskModal({{ $task->id }})" class="cd-btn cd-btn-outline" style="padding:4px 8px; font-size:11px;">
                                        Details
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Subtasks Rows --}}
                    @if($hasSubtasks)
                        @foreach($subtasks as $sub)
                            @php
                                $subIsClosed = $sub->approval_stage === 'Closed';
                                $subArtFiles = $sub->getFinalDesignsArray();
                                $subArtUrls = $sub->getFinalDesignsUrlsArray();
                            @endphp
                            <tr class="subtask-row subtask-of-{{ $task->id }} {{ $subIsClosed ? 'task-closed' : '' }}"
                                onclick="openTaskModal({{ $sub->id }})" style="cursor:pointer;">
                                
                                {{-- Subtask Title & Format --}}
                                <td>
                                    <div style="padding-left:16px;">
                                        <div style="font-weight:600; font-size:12px; color:var(--color-text-primary);">{{ $sub->title }}</div>
                                        @if($sub->post_type)
                                            <span class="subtask-pill" style="font-size:8px; background:rgba(99,102,241,0.06); color:#6366f1; border-color:rgba(99,102,241,0.2);">
                                                {{ $sub->post_type }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Designer --}}
                                <td>
                                    <span style="font-size:11px; color:var(--color-text-secondary);">
                                        {{ $sub->designer->name ?? ($sub->assignee_name ?? $task->designer->name ?? 'Unassigned') }}
                                    </span>
                                </td>

                                {{-- Deadline --}}
                                <td>
                                    <span style="font-size:11px; color:var(--color-text-secondary);">
                                        {{ $sub->designer_deadline ? \Carbon\Carbon::parse($sub->designer_deadline)->format('M d, Y') : ($task->designer_deadline ? \Carbon\Carbon::parse($task->designer_deadline)->format('M d, Y') : '—') }}
                                    </span>
                                </td>

                                {{-- Concept / Brief --}}
                                <td>
                                    @if($sub->concept || $sub->notes)
                                        <div class="cell-text" onclick="event.stopPropagation();openTextPreview('Subtask Brief', {{ json_encode($sub->concept ?? $sub->notes) }})"
                                             style="max-width:130px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:11px; color:var(--color-text-secondary); cursor:pointer;">
                                            {{ strip_tags($sub->concept ?? $sub->notes) }}
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
                                    @elseif($sub->approval_stage === 'Manager Review')
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#6d28d9;">Manager Review</span>
                                    @else
                                        <span style="font-size:9.5px; font-weight:800; text-transform:uppercase; color:#b45309;">Designer</span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td style="text-align:center;" onclick="event.stopPropagation()">
                                    <button type="button" onclick="openTaskModal({{ $sub->id }})" class="cd-btn cd-btn-outline" style="padding:3px 7px; font-size:10px;">
                                        Open
                                    </button>
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
        <h3 style="margin:0 0 8px; font-size:16px; font-weight:800; color:var(--color-text-primary);">Request Revisions</h3>
        <p style="font-size:12px; color:var(--color-text-secondary); margin:0 0 16px;">Describe the revisions required. This deliverable will be routed back to the designer.</p>
        
        <form id="directRevisionForm" method="POST">
            @csrf
            <textarea name="revision_instructions" required placeholder="Specify what needs to be changed in the artwork..."
                      style="width:100%; min-height:100px; padding:12px; background:var(--color-bg-secondary); border:1.5px solid var(--color-border-primary); border-radius:8px; font-family:inherit; font-size:13px; color:var(--color-text-primary); outline:none; resize:vertical; box-sizing:border-box; margin-bottom:16px;"></textarea>
            
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" onclick="closeDirectRevisionPrompt()"
                        style="padding:8px 16px; background:transparent; border:1.5px solid var(--color-border-primary); border-radius:8px; font-size:12px; font-weight:600; color:var(--color-text-secondary); cursor:pointer;">
                    Cancel
                </button>
                <button type="submit"
                        style="padding:8px 18px; background:#ef4444; border:none; border-radius:8px; font-size:12px; font-weight:700; color:#fff; cursor:pointer;">
                    Send to Designer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDirectRevisionPrompt(taskId) {
        const modal = document.getElementById('directRevisionModal');
        const form = document.getElementById('directRevisionForm');
        form.action = `/deliverables/${taskId}/revisions`;
        modal.style.display = 'flex';
    }

    function closeDirectRevisionPrompt() {
        document.getElementById('directRevisionModal').style.display = 'none';
    }
</script>
@endif
