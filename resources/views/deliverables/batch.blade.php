<x-layout :title="$deliverable->title . ' — Batch'">
<style>
.bv-wrap { max-width: 900px; margin: 0 auto; padding-bottom: 60px; }
.bv-header { background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 14px; padding: 22px 28px; margin-bottom: 20px; }
.bv-breadcrumb { font-size: 11px; font-weight: 600; color: var(--color-text-secondary); display: flex; align-items: center; gap: 5px; margin-bottom: 14px; }
.bv-breadcrumb a { color: inherit; text-decoration: none; }
.bv-breadcrumb a:hover { color: var(--color-text-primary); }
.bv-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.bv-title { font-size: 22px; font-weight: 800; color: var(--color-text-primary); letter-spacing: -0.02em; }
.bv-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
.bv-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; border: 1px solid; }
.bv-stage { color: #3b82f6; background: rgba(59,130,246,0.08); border-color: rgba(59,130,246,0.2); }
.bv-bar-wrap { display: flex; align-items: center; gap: 8px; margin-top: 14px; }
.bv-bar { flex: 1; height: 5px; background: var(--color-border-primary); border-radius: 10px; overflow: hidden; max-width: 220px; }
.bv-bar-fill { height: 100%; border-radius: 10px; background: #3b82f6; transition: width 0.4s; }

/* Cards */
.post-card { background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 12px; overflow: hidden; margin-bottom: 14px; }
.post-card-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 20px; border-bottom: 1px solid var(--color-border-primary); background: var(--color-bg-secondary); flex-wrap: wrap; }
.post-card-title { font-size: 14px; font-weight: 800; color: var(--color-text-primary); }
.post-card-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.stage-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.05em; white-space: nowrap; }
.stage-open { background: rgba(59,130,246,0.08); color: #3b82f6; border: 1px solid rgba(59,130,246,0.2); }
.stage-done { background: rgba(16,185,129,0.08); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.stage-rev  { background: rgba(239,68,68,0.08);  color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }

.post-card-body { display: grid; grid-template-columns: 1fr; }
@media(min-width:640px) { .post-card-body { grid-template-columns: 1fr 1fr; } }
.pc-field { padding: 14px 20px; border-bottom: 1px solid var(--color-border-primary); }
@media(min-width:640px) { .pc-field:nth-child(odd):not(.full) { border-right: 1px solid var(--color-border-primary); } }
.pc-field.full { grid-column: 1 / -1; }
.pc-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--color-text-secondary); margin-bottom: 6px; }
.pc-val { font-size: 13px; font-weight: 500; color: var(--color-text-primary); line-height: 1.6; white-space: pre-wrap; }
.pc-empty { color: var(--color-text-secondary); opacity: 0.4; font-style: italic; font-weight: 400; }

.assignee-row { display: flex; gap: 8px; flex-wrap: wrap; }
.assignee-chip { display: flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); color: var(--color-text-secondary); }
.assignee-chip .chip-role { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.55; }

.rev-item { padding: 10px 12px; border-radius: 8px; background: rgba(239,68,68,0.04); border: 1px solid rgba(239,68,68,0.15); margin-bottom: 7px; }
.rev-item:last-child { margin-bottom: 0; }
.rev-item-head { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
.rev-item-text { font-size: 12px; color: var(--color-text-primary); line-height: 1.5; }
.rev-item-fixed { font-size: 10px; color: #10b981; font-weight: 600; margin-top: 4px; }

.artwork-thumb { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid var(--color-border-primary); cursor: pointer; }
.ref-link { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; color: #3b82f6; text-decoration: none; padding: 4px 10px; background: rgba(59,130,246,0.06); border: 1px solid rgba(59,130,246,0.2); border-radius: 6px; }

/* Action footer */
.post-card-footer { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding: 12px 20px; border-top: 1px solid var(--color-border-primary); background: var(--color-bg-secondary); flex-wrap: wrap; }
.act-btn { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; border: none; transition: all 0.12s; white-space: nowrap; }
.act-approve { background: #0055D4; color: #fff; box-shadow: 0 3px 10px rgba(0,85,212,0.2); }
.act-approve:hover { background: #0044aa; }
.act-revise  { background: rgba(239,68,68,0.08); color: #ef4444; border: 1.5px solid rgba(239,68,68,0.25); }
.act-revise:hover  { background: rgba(239,68,68,0.14); }
.rev-note { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: #ef4444; background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2); border-radius: 6px; padding: 5px 10px; cursor: pointer; }

/* Revision modal */
.bv-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); backdrop-filter: blur(6px); z-index: 9000; display: none; align-items: center; justify-content: center; padding: 24px; }
.bv-overlay.open { display: flex; }
.bv-modal { background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 14px; width: 100%; max-width: 480px; box-shadow: 0 30px 80px rgba(0,0,0,0.25); overflow: hidden; }
.bv-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid var(--color-border-primary); background: var(--color-bg-secondary); }
.bv-modal-title { font-size: 16px; font-weight: 800; color: var(--color-text-primary); }
.bv-modal-body { padding: 24px; }
.bv-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 16px 24px; border-top: 1px solid var(--color-border-primary); }
.bv-textarea { width: 100%; min-height: 110px; padding: 12px; border-radius: 10px; border: 1.5px solid rgba(239,68,68,0.25); background: var(--color-bg-secondary); color: var(--color-text-primary); font-size: 13px; line-height: 1.6; resize: vertical; outline: none; box-sizing: border-box; }
.bv-textarea:focus { border-color: #ef4444; }
.bv-target-row { display: flex; gap: 12px; margin-bottom: 14px; }
.bv-target-opt { display: flex; align-items: center; gap: 7px; cursor: pointer; font-size: 12px; font-weight: 600; color: var(--color-text-primary); }
.btn-cancel { padding: 8px 18px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--color-text-secondary); background: transparent; border: 1.5px solid var(--color-border-primary); cursor: pointer; }
.btn-confirm { padding: 8px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; color: #fff; background: #ef4444; border: none; cursor: pointer; }

/* Submit confirm modal */
.bv-confirm-text { font-size: 13px; font-weight: 500; color: var(--color-text-secondary); line-height: 1.6; }
.bv-confirm-next { font-size: 14px; font-weight: 800; color: var(--color-text-primary); margin-top: 8px; }
.btn-submit-confirm { padding: 8px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; color: #fff; background: #0055D4; border: none; cursor: pointer; box-shadow: 0 3px 10px rgba(0,85,212,0.2); }

/* Lightbox */
.bv-lightbox { position: fixed; inset: 0; background: rgba(0,0,0,0.88); z-index: 9999; display: none; align-items: center; justify-content: center; cursor: zoom-out; }
.bv-lightbox.open { display: flex; }
</style>

@php
    $authUser    = auth()->user();
    $authId      = $authUser->id;
    $authRole    = strtolower(str_replace(' ', '', $authUser->role));
    $isAdmin     = $authUser->isAdmin();
@endphp

<div class="bv-wrap">

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="margin-bottom:12px;padding:12px 18px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:10px;font-size:13px;font-weight:600;color:#10b981;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="margin-bottom:12px;padding:12px 18px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:10px;font-size:13px;font-weight:600;color:#ef4444;">
        {{ session('error') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="bv-header">
        <div class="bv-breadcrumb">
            <a href="{{ route('brands.index') }}">Brands</a>
            <span style="opacity:.35;">/</span>
            <a href="{{ route('brands.show', $deliverable->project->brand->slug) }}">{{ $deliverable->project->brand->name }}</a>
            <span style="opacity:.35;">/</span>
            <a href="{{ route('projects.show', $deliverable->project) }}">{{ $deliverable->project->name }}</a>
            <span style="opacity:.35;">/</span>
            <span style="color:var(--color-text-primary);">{{ $deliverable->title }}</span>
        </div>

        <div class="bv-title-row">
            <div>
                <div class="bv-title">{{ $deliverable->title }}</div>
                <div class="bv-meta">
                    @if($deliverable->post_type)
                        <span class="bv-badge" style="color:#7c3aed;background:rgba(124,58,237,0.08);border-color:rgba(124,58,237,0.2);">{{ $deliverable->post_type }}</span>
                    @endif
                    <span class="bv-badge bv-stage">{{ $deliverable->approval_stage }}</span>
                    <span class="bv-badge" style="color:var(--color-text-secondary);background:var(--color-bg-secondary);border-color:var(--color-border-primary);">{{ $deliverable->subtasks->count() }} posts</span>
                    @if($deliverable->deadline)
                        <span class="bv-badge" style="color:var(--color-text-secondary);background:var(--color-bg-secondary);border-color:var(--color-border-primary);">Due {{ \Carbon\Carbon::parse($deliverable->deadline)->format('M d, Y') }}</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                <a href="{{ route('deliverables.export-batch.ppt', $deliverable->id) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:700;color:#0055D4;background:rgba(0,85,212,0.06);border:1.5px solid rgba(0,85,212,0.2);text-decoration:none;white-space:nowrap;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download PPT
                </a>
                <a href="{{ route('projects.show', $deliverable->project) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);text-decoration:none;white-space:nowrap;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Project
                </a>
            </div>
        </div>

        @php
            $total  = $deliverable->subtasks->count();
            $closed = $deliverable->subtasks->where('approval_stage', 'Closed')->count();
            $pct    = $total > 0 ? round($closed / $total * 100) : 0;
        @endphp
        <div class="bv-bar-wrap">
            <div class="bv-bar"><div class="bv-bar-fill" style="width:{{ $pct }}%;"></div></div>
            <span style="font-size:11px;font-weight:700;color:{{ $closed === $total && $total > 0 ? '#10b981' : 'var(--color-text-secondary)' }};">{{ $closed }}/{{ $total }} closed</span>
        </div>
    </div>

    {{-- Post Cards --}}
    @forelse($deliverable->subtasks as $post)
    @php
        $stage     = $post->approval_stage ?? 'Writer';
        $nextStage = $post->getNextStage();
        $stageClass = $stage === 'Closed' ? 'stage-done' : ($post->revisions > 0 && in_array($stage, ['Writer','Assignee']) ? 'stage-rev' : 'stage-open');
        $revs      = $post->getRelation('revisionsHistory') ?? collect();
        $approvals = $post->getRelation('approvalsHistory') ?? collect();
        $isImg     = $post->final_designs && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $post->final_designs);

        $canApprove = $isAdmin || (
            (in_array($stage, ['Writer','Assignee','Writer Review']) && in_array($authRole, ['writer','assignee']) && (!$post->writer_id || $post->writer_id == $authId)) ||
            (in_array($stage, ['Approver','Approver Review','Further Approver']) && in_array($authRole, ['approver', 'approvercoordinator']) && (!$post->approver_id || $post->approver_id == $authId)) ||
            (in_array($stage, ['Brand Manager','AM/BD','Final Approval']) && $authRole === 'brandmanager' && (!$post->brand_manager_id || $post->brand_manager_id == $authId)) ||
            ($stage === 'Coordinator' && in_array($authRole, ['coordinator', 'approvercoordinator']) && (!$post->coordinator_id || $post->coordinator_id == $authId)) ||
            ($stage === 'Designer' && $authRole === 'designer' && (!$post->designer_id || $post->designer_id == $authId))
        );

        $canRevise = $isAdmin || (
            ($stage === 'Writer Review' && in_array($authRole, ['writer','assignee']) && (!$post->writer_id || $post->writer_id == $authId)) ||
            (in_array($stage, ['Approver','Approver Review','Further Approver']) && in_array($authRole, ['approver', 'approvercoordinator']) && (!$post->approver_id || $post->approver_id == $authId)) ||
            (in_array($stage, ['Brand Manager','AM/BD','Final Approval']) && $authRole === 'brandmanager' && (!$post->brand_manager_id || $post->brand_manager_id == $authId))
        );

        $btnLabel = match(true) {
            in_array($stage, ['Writer','Assignee']) => 'Submit',
            $stage === 'Coordinator'                => 'Assign to Designer',
            $stage === 'Designer'                   => 'Send Artwork',
            default                                 => 'Approve',
        };

        $showTarget = in_array($stage, ['Final Approval','Writer Review','Approver Review']);
    @endphp

    <div class="post-card">
        {{-- Header --}}
        <div class="post-card-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:24px;height:24px;border-radius:6px;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:var(--color-text-secondary);flex-shrink:0;">{{ $loop->iteration }}</span>
                <span class="post-card-title">{{ $post->title }}</span>
            </div>
            <div class="post-card-meta">
                @if($post->deadline)
                    <span style="font-size:11px;font-weight:600;color:var(--color-text-secondary);">{{ \Carbon\Carbon::parse($post->deadline)->format('M d') }}</span>
                @endif
                @if($post->revisions > 0)
                    <span style="display:inline-flex;align-items:baseline;gap:1px;padding:3px 6px;background:rgba(239,68,68,0.1);color:#ef4444;border:1.5px solid rgba(239,68,68,0.3);border-radius:5px;font-size:11px;font-weight:900;line-height:1;"><span style="font-size:8px;font-weight:700;opacity:0.7;">R</span>{{ $post->revisions }}</span>
                @endif
                @if($post->work_hours)
                    <span style="font-size:11px;font-weight:600;color:var(--color-text-secondary);">{{ number_format($post->work_hours, 1) }}h</span>
                @endif
                <span class="stage-badge {{ $stageClass }}">{{ $stage }}</span>
            </div>
        </div>

        {{-- Body --}}
        <div class="post-card-body">
            {{-- Revision notice --}}
            @if($post->revision_instructions)
            <div class="pc-field full" style="background:rgba(239,68,68,0.03);">
                <div class="pc-label" style="color:#ef4444;">Revision Instructions</div>
                <div class="pc-val" style="color:#ef4444;">{{ $post->revision_instructions }}</div>
                @php $latestRevImg = $revs->last()?->image_path; @endphp
                @if($latestRevImg)
                    <a href="{{ $latestRevImg }}" target="_blank" style="display:block;margin-top:10px;">
                        <img src="{{ $latestRevImg }}" alt="Revision reference" style="max-width:100%;max-height:260px;border-radius:8px;border:1px solid rgba(239,68,68,0.2);object-fit:contain;cursor:pointer;">
                    </a>
                @endif
            </div>
            @endif

            <div class="pc-field">
                <div class="pc-label">Concept</div>
                @if($post->concept)<div class="pc-val">{{ $post->concept }}</div>
                @else<div class="pc-val pc-empty">No concept yet</div>@endif
            </div>

            <div class="pc-field">
                <div class="pc-label">Caption</div>
                @if($post->caption)<div class="pc-val">{{ $post->caption }}</div>
                @else<div class="pc-val pc-empty">No caption yet</div>@endif
            </div>

            <div class="pc-field full">
                <div class="pc-label">Post Copy</div>
                @if($post->post_copy)<div class="pc-val">{{ $post->post_copy }}</div>
                @else<div class="pc-val pc-empty">No copy yet</div>@endif
            </div>

            <div class="pc-field">
                <div class="pc-label">Reference</div>
                @if($post->reference_file)
                    @php $isRefImg = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $post->reference_file); @endphp
                    @if($isRefImg)
                        <img src="{{ $post->reference_file }}" class="artwork-thumb" onclick="openImg('{{ $post->reference_file }}')" alt="Reference">
                    @else
                        <a href="{{ $post->reference_file }}" target="_blank" class="ref-link">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download File
                        </a>
                    @endif
                @elseif($post->reference)
                    <a href="{{ $post->reference }}" target="_blank" class="ref-link">
                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        View Reference
                    </a>
                @else<div class="pc-val pc-empty">None</div>@endif
            </div>

            <div class="pc-field">
                <div class="pc-label">Final Artwork</div>
                @if($post->final_designs)
                    @if($isImg)
                        <img src="{{ $post->final_designs }}" class="artwork-thumb" onclick="openImg('{{ $post->final_designs }}')" alt="Artwork">
                    @else
                        <a href="{{ $post->final_designs }}" target="_blank" class="ref-link" style="color:#10b981;background:rgba(16,185,129,0.06);border-color:rgba(16,185,129,0.2);">Download</a>
                    @endif
                @elseif($post->final_designs_link)
                    <a href="{{ $post->final_designs_link }}" target="_blank" class="ref-link" style="color:#10b981;background:rgba(16,185,129,0.06);border-color:rgba(16,185,129,0.2);">View Link</a>
                @else<div class="pc-val pc-empty">No artwork yet</div>@endif
            </div>

            <div class="pc-field full">
                <div class="pc-label">Assignees</div>
                <div class="assignee-row">
                    @foreach([
                        'Writer'        => $post->writer        ?? $deliverable->writer,
                        'Approver'      => $post->approver      ?? $deliverable->approver,
                        'Brand Manager' => $post->brandManager  ?? $deliverable->brandManager,
                        'Coordinator'   => $post->coordinator   ?? $deliverable->coordinator,
                        'Designer'      => $post->designer      ?? $deliverable->designer,
                    ] as $roleLabel => $person)
                        @if($person)
                        <div class="assignee-chip">
                            <img src="{{ $person->avatarUrl }}" style="width:18px;height:18px;border-radius:50%;object-fit:cover;" alt="">
                            <span class="chip-role">{{ $roleLabel }}:</span>
                            {{ $person->name }}
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            @if($revs->isNotEmpty())
            <div class="pc-field full">
                <div class="pc-label" style="color:#ef4444;">Revision History ({{ $revs->count() }})</div>
                @foreach($revs as $rev)
                <div class="rev-item">
                    <div class="rev-item-head">
                        <span style="font-size:10px;font-weight:700;color:#ef4444;text-transform:uppercase;letter-spacing:.06em;">Rev @ {{ $rev->stage_at_revision }}</span>
                        <span style="font-size:10px;color:var(--color-text-secondary);">by {{ $rev->user?->name ?? '—' }}</span>
                        <span style="font-size:10px;color:var(--color-text-secondary);margin-left:auto;">{{ $rev->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($rev->instructions)<div class="rev-item-text">{{ $rev->instructions }}</div>@endif
                    @if($rev->image_path)
                        <a href="{{ $rev->image_path }}" target="_blank" style="display:block;margin-top:8px;">
                            <img src="{{ $rev->image_path }}" alt="Revision reference" style="max-width:100%;max-height:260px;border-radius:8px;border:1px solid rgba(239,68,68,0.2);object-fit:contain;cursor:pointer;">
                        </a>
                    @endif
                    @if($rev->fixed_at)<div class="rev-item-fixed">Fixed by {{ $rev->fixedByUser?->name ?? '—' }} · {{ \Carbon\Carbon::parse($rev->fixed_at)->format('M d, Y') }}</div>@endif
                </div>
                @endforeach
            </div>
            @endif

            @if($approvals->isNotEmpty())
            <div class="pc-field full">
                <div class="pc-label" style="color:#10b981;">Approval History</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($approvals as $approval)
                    <div style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.15);color:#10b981;">
                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $approval->stage }}
                        <span style="opacity:.55;font-size:10px;">· {{ $approval->user?->name ?? '—' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Action Footer --}}
        @if($canApprove || $canRevise)
        <div class="post-card-footer">
            @if($canRevise && $stage !== 'Writer' && $stage !== 'Assignee')
                <button type="button" class="act-btn act-revise"
                    onclick="openReviseModal({{ $post->id }}, '{{ $stage }}', {{ $showTarget ? 'true' : 'false' }})">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01"/></svg>
                    Request Revision
                </button>
            @endif
            @if($canApprove && $nextStage)
                <button type="button" class="act-btn act-approve"
                    onclick="openSubmitModal({{ $post->id }}, '{{ $nextStage }}', '{{ $btnLabel }}')">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    {{ $btnLabel }}
                </button>
            @endif
        </div>
        @endif
    </div>
    @empty
    <div style="text-align:center;padding:48px 24px;color:var(--color-text-secondary);font-size:13px;font-weight:500;">
        No posts in this batch yet.
    </div>
    @endforelse
</div>

{{-- Revision Modal --}}
<div id="reviseOverlay" class="bv-overlay" onclick="if(event.target===this)closeReviseModal()">
    <div class="bv-modal">
        <div class="bv-modal-head">
            <span class="bv-modal-title">Request Revision</span>
            <button onclick="closeReviseModal()" style="background:none;border:none;color:var(--color-text-secondary);cursor:pointer;padding:4px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="reviseForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bv-modal-body">
                <div id="reviseTargetRow" class="bv-target-row" style="display:none;">
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#ef4444;align-self:center;">Send back to</span>
                    <label class="bv-target-opt">
                        <input type="radio" name="revision_target" value="designer" checked style="accent-color:#ef4444;"> Designer
                    </label>
                    <label class="bv-target-opt">
                        <input type="radio" name="revision_target" value="writer" style="accent-color:#ef4444;"> Writer
                    </label>
                </div>
                <label class="pc-label" style="color:#ef4444;display:block;margin-bottom:8px;">Revision Instructions <span style="color:#ef4444;">*</span></label>
                <textarea name="revision_instructions" id="reviseInstructions" class="bv-textarea" placeholder="Describe what needs to be changed…" required></textarea>
                <p id="reviseSendNote" style="font-size:11px;color:var(--color-text-secondary);font-weight:500;margin-top:8px;margin-bottom:12px;"></p>
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#ef4444;margin-bottom:8px;">Attach Image <span style="font-weight:500;opacity:0.6;text-transform:none;letter-spacing:0;">(optional)</span></label>
                <label for="bvRevisionImageInput" style="display:inline-flex;align-items:center;gap:8px;padding:9px 14px;border-radius:8px;border:1.5px dashed rgba(239,68,68,0.3);background:rgba(239,68,68,0.04);cursor:pointer;font-size:12px;font-weight:600;color:#ef4444;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span id="bvRevisionImageLabel">Choose image…</span>
                </label>
                <input type="file" id="bvRevisionImageInput" name="revision_image" accept="image/*" style="display:none;" onchange="
                    const f = this.files[0];
                    document.getElementById('bvRevisionImageLabel').textContent = f ? f.name : 'Choose image…';
                    const prev = document.getElementById('bvRevisionImagePreview');
                    if (f) { const r = new FileReader(); r.onload = e => { prev.src = e.target.result; prev.style.display='block'; }; r.readAsDataURL(f); }
                    else { prev.style.display='none'; prev.src=''; }
                ">
                <img id="bvRevisionImagePreview" src="" alt="" style="display:none;margin-top:10px;max-width:100%;max-height:200px;border-radius:8px;border:1px solid rgba(239,68,68,0.2);object-fit:contain;">
            </div>
            <div class="bv-modal-foot">
                <button type="button" class="btn-cancel" onclick="closeReviseModal()">Cancel</button>
                <button type="submit" class="btn-confirm">Send for Revision</button>
            </div>
        </form>
    </div>
</div>

{{-- Submit Confirm Modal --}}
<div id="submitOverlay" class="bv-overlay" onclick="if(event.target===this)closeSubmitModal()">
    <div class="bv-modal">
        <div class="bv-modal-head">
            <span class="bv-modal-title" id="submitModalTitle">Confirm Submission</span>
            <button onclick="closeSubmitModal()" style="background:none;border:none;color:var(--color-text-secondary);cursor:pointer;padding:4px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="submitForm" method="POST">
            @csrf
            <div class="bv-modal-body">
                <p class="bv-confirm-text">You are about to advance this deliverable to:</p>
                <p class="bv-confirm-next" id="submitNextStageLabel"></p>
            </div>
            <div class="bv-modal-foot">
                <button type="button" class="btn-cancel" onclick="closeSubmitModal()">Cancel</button>
                <button type="submit" class="btn-submit-confirm" id="submitConfirmBtn">Confirm</button>
            </div>
        </form>
    </div>
</div>

{{-- Lightbox --}}
<div id="bvLightbox" class="bv-lightbox" onclick="closeLightbox()">
    <img id="bvLightboxImg" src="" style="max-width:90vw;max-height:90vh;border-radius:10px;box-shadow:0 30px 80px rgba(0,0,0,0.5);">
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openImg(src) {
    document.getElementById('bvLightboxImg').src = src;
    document.getElementById('bvLightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('bvLightbox').classList.remove('open');
}

function openReviseModal(postId, stage, showTarget) {
    document.getElementById('reviseForm').action = `/deliverables/${postId}/revisions`;
    document.getElementById('reviseInstructions').value = '';
    document.getElementById('reviseTargetRow').style.display = showTarget ? 'flex' : 'none';
    const sendNote = document.getElementById('reviseSendNote');
    sendNote.textContent = showTarget
        ? 'Choose whether to send back to the Designer or the Writer.'
        : 'This post will be sent back to the Writer.';
    document.getElementById('reviseOverlay').classList.add('open');
    setTimeout(() => document.getElementById('reviseInstructions').focus(), 80);
}
function closeReviseModal() {
    document.getElementById('reviseOverlay').classList.remove('open');
    // Reset file input and preview
    const inp = document.getElementById('bvRevisionImageInput');
    if (inp) inp.value = '';
    document.getElementById('bvRevisionImageLabel').textContent = 'Choose image…';
    const prev = document.getElementById('bvRevisionImagePreview');
    prev.src = ''; prev.style.display = 'none';
}

function openSubmitModal(postId, nextStage, btnLabel) {
    document.getElementById('submitForm').action = `/deliverables/${postId}/submit`;
    document.getElementById('submitModalTitle').textContent = btnLabel;
    document.getElementById('submitNextStageLabel').textContent = nextStage;
    document.getElementById('submitConfirmBtn').textContent = btnLabel;
    document.getElementById('submitOverlay').classList.add('open');
}
function closeSubmitModal() {
    document.getElementById('submitOverlay').classList.remove('open');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeReviseModal(); closeSubmitModal(); closeLightbox(); }
});
</script>
</x-layout>
