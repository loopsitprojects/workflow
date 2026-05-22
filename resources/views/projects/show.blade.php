<x-layout title="{{ $project->name }} Board">
    @php
        $isAdmin = auth()->user()->isAdmin();
        $userRole = strtolower(str_replace(' ', '', auth()->user()->role));
    @endphp
    <style>
        /* Content Deliverables Table Styles */
        .cd-table-wrap { background:var(--color-bg-primary); border-radius:16px; border:1px solid var(--color-border-primary); overflow:hidden; transition: background 0.3s, border-color 0.3s; }
        .cd-header { display:flex; justify-content:space-between; align-items:center; padding:20px 24px 16px; border-bottom:1px solid var(--color-border-primary); }
        .cd-header-left h2 { font-size:15px; font-weight:700; color:var(--color-text-primary); letter-spacing:-0.01em; margin:0; }
        .cd-header-right { display:flex; gap:10px; align-items:center; }
        .cd-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; border:none; transition:all 0.15s; text-decoration:none; white-space:nowrap; }
        .cd-btn-outline { background:var(--color-bg-primary); color:var(--color-text-secondary); border:1.5px solid var(--color-border-primary); }
        .cd-btn-outline:hover { color:var(--color-text-primary); background:var(--color-bg-secondary); }
        .cd-btn-primary { background:#0055D4; color:#fff; box-shadow:0 4px 12px rgba(0,85,212,0.2); }
        .cd-btn-primary:hover { background:#0044aa; }
        .cd-table { width:100%; border-collapse:collapse; table-layout: fixed; }
        .cd-table thead tr { border-bottom:1px solid var(--color-border-primary); }
        .cd-table thead th { padding:10px 10px; text-align:left; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-text-secondary); white-space:nowrap; background:var(--color-bg-secondary); }
        .cd-table tbody tr { border-bottom:1px solid var(--color-border-primary); transition:background 0.12s; }
        .cd-table tbody tr:last-child { border-bottom:none; }
        .cd-table tbody tr:hover { background:rgba(59,130,246,0.03); }
        .cd-table td { padding:14px 10px; vertical-align:middle; font-size:12px; color:var(--color-text-primary); overflow: hidden; text-overflow: ellipsis; }
        .subtask-pill { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:8px; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.15em; border:1px solid; }
        .subtask-copy-box { background:var(--color-bg-secondary); border-radius:12px; padding:10px 14px; font-size:12px; color:var(--color-text-secondary); font-weight:500; line-height:1.5; border:1px solid var(--color-border-primary); max-height:70px; overflow:hidden; }
        .ref-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:rgba(37,99,235,0.1); border:1px solid rgba(37,99,235,0.2); border-radius:10px; font-size:11px; font-weight:700; color:#2563eb; text-decoration:none; transition:all 0.15s; }
        .ref-chip:hover { background:rgba(37,99,235,0.15); }

        /* Modal Styles */
        .cd-modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(8px); display:none; justify-content:center; align-items:center; z-index:9999; opacity:0; transition:all 0.3s ease; }
        .cd-modal { background:var(--color-bg-primary); width:90%; max-width:800px; border-radius:32px; box-shadow:0 40px 100px rgba(0,0,0,0.2); overflow:hidden; transform:scale(0.95); transition:all 0.3s ease; position:relative; }
        .cd-modal.active { transform:scale(1); }
        .cd-modal-header { padding:32px; border-bottom:1px solid var(--color-border-primary); display:flex; justify-content:space-between; align-items:flex-start; }
        .cd-modal-body { padding:32px; max-height:70vh; overflow-y:auto; }
        .cd-modal-footer { padding:24px 32px; background:var(--color-bg-secondary); border-top:1px solid var(--color-border-primary); display:flex; justify-content:flex-end; gap:12px; }
        .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
        .detail-item { margin-bottom:12px; }
        .detail-item.full { grid-column:span 2; }
        .detail-label { font-size:10px; font-weight:900; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.15em; margin-bottom:8px; display:block; }
        .detail-val-textarea { 
            display:block; 
            line-height:1.6; 
            padding:16px 20px; 
            min-height:100px; 
            width:100%; 
            border-radius:18px; 
            font-family:inherit; 
            font-size:14px; 
            font-weight:500;
            color:var(--color-text-primary);
            background:var(--color-bg-primary);
            border:1px solid var(--color-border-primary);
            box-shadow:inset 0 2px 4px rgba(0,0,0,0.02);
            resize:vertical;
        }
        .detail-val-textarea:read-only { background:var(--color-bg-secondary); cursor:default; border-color:var(--color-border-primary); box-shadow:none; }
        .cd-table tbody tr { cursor:pointer; }
        .cd-table tbody tr:hover { background:var(--color-bg-secondary); opacity: 0.8; }
        .subtask-row { background:var(--color-bg-secondary); }
        .subtask-row td:first-child { padding-left:44px; position:relative; }
        .subtask-row td:first-child::before { content:''; position:absolute; left:26px; top:50%; width:10px; height:10px; border-left:2px solid var(--color-border-primary); border-bottom:2px solid var(--color-border-primary); transform:translateY(-50%); border-radius:0 0 0 3px; }
        .subtask-row.collapsed { display: none; }
        .subtask-toggle { 
            display: inline-flex; align-items: center; justify-content: center;
            width: 20px; height: 20px; border-radius: 4px; border: 1px solid var(--color-border-primary);
            background: var(--color-bg-primary); color: var(--color-text-secondary); cursor: pointer; margin-right: 8px;
            transition: all 0.2s;
        }
        .subtask-toggle:hover { background: var(--color-bg-secondary); color: #0055D4; border-color: #0055D4; }
        .subtask-toggle svg { transition: transform 0.2s; }
        .subtask-toggle.active svg { transform: rotate(90deg); }
        .deliverable-name-cell { display: flex; align-items: center; }

        /* Workflow Steps Tracker */
        .workflow-steps { display: flex; justify-content: space-between; position: relative; margin-bottom: 40px; padding: 0 10px; }
        .workflow-steps::before { content: ''; position: absolute; top: 15px; left: 40px; right: 40px; height: 2px; background: var(--color-border-primary); z-index: 0; }
        .step-item { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; width: 60px; }
        .step-dot { width: 32px; height: 32px; border-radius: 50%; background: var(--color-bg-primary); border: 2px solid var(--color-border-primary); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: var(--color-text-secondary); transition: all 0.3s; }
        .step-label { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-secondary); text-align: center; white-space: nowrap; }
        
        .step-item.active .step-dot { border-color: #0055D4; color: #0055D4; background: rgba(0,85,212,0.1); box-shadow: 0 0 0 4px rgba(0,85,212,0.1); }
        .step-item.active .step-label { color: #0055D4; }
        .step-item.completed .step-dot { background: #10b981; border-color: #10b981; color: #fff; }
        .step-item.completed .step-label { color: #10b981; }

        /* Closed Task Styling */
        .task-closed { opacity: 0.5; filter: grayscale(0.5); transition: opacity 0.3s, filter 0.3s; }
        .task-closed:hover { opacity: 1; filter: grayscale(0); }
        .task-closed .deliverable-name-cell span { text-decoration: line-through; color: var(--color-text-secondary) !important; }

        /* Thumbnail Preview Styling */
        .task-thumbnail { display: block; width: 120px; height: 80px; object-fit: cover; border-radius: 12px; border: 2px solid var(--color-border-primary); transition: all 0.2s; cursor: pointer; background: var(--color-bg-secondary); }
        .task-thumbnail:hover { transform: scale(1.05); border-color: #10b981; box-shadow: 0 10px 20px rgba(16,185,129,0.1); }

        /* Retainer Board Specific Styles */
        .rtb-text-box { 
            font-size: 11px; 
            color: var(--color-text-secondary); 
            line-height: 1.5; 
            max-height: 60px; 
            overflow-y: auto; 
            background: var(--color-bg-secondary); 
            padding: 8px 10px; 
            border-radius: 8px; 
            font-weight: 500;
            scrollbar-width: thin;
            border: 1px solid var(--color-border-primary);
        }
        .rtb-ref-preview { display: block; width: 80px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-border-primary); }

        .rtb-stage-tracker { display: flex; align-items: center; gap: 4px; margin-top: 6px; }
        .rtb-stage-dot { width: 8px; height: 8px; border-radius: 50%; border: 1.5px solid var(--color-border-primary); display: flex; align-items: center; justify-content: center; }
        .rtb-stage-dot.active { background: #0055D4; border-color: #0055D4; color: #fff; }
        .rtb-stage-dot.completed { background: #10b981; border-color: #10b981; color: #fff; }
        .rtb-stage-label { 
            display: inline-block; 
            font-size: 10px; 
            font-weight: 900; 
            text-transform: uppercase; 
            color: #0055D4; 
            background: rgba(0, 85, 212, 0.08); 
            padding: 4px 12px; 
            border-radius: 8px;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .rtb-heading-row { background: var(--color-bg-secondary); border-bottom: 1.5px solid var(--color-border-primary) !important; cursor: default !important; }
        .rtb-heading-row:hover { background: rgba(255,255,255,0.03) !important; opacity: 1 !important; }
        .rtb-subtask-row td:first-child { padding-left: 32px !important; position: relative; }
        .rtb-subtask-row td:first-child::before { content: none !important; }

        .rtb-editable-cell { padding: 4px !important; transition: all 0.2s; }
        .rtb-input { 
            width: 100% !important; 
            min-height: 34px !important; 
            height: 34px !important;
            background: transparent !important; 
            border: 1px solid transparent !important; 
            color: var(--color-text-primary) !important; 
            font-size: 11px !important; 
            font-weight: 700 !important;
            line-height: 1.4 !important; 
            padding: 8px 10px !important; 
            border-radius: 8px !important; 
            resize: none !important; 
            overflow: hidden !important; 
            display: block !important;
            transition: all 0.2s !important;
        }
        .rtb-input:focus { 
            background: var(--color-bg-primary) !important; 
            border-color: #0055D4 !important; 
            box-shadow: 0 0 0 3px rgba(0,85,212,0.15) !important; 
            outline: none !important;
            min-height: 70px !important;
            height: auto !important;
            z-index: 10;
            position: relative;
        }
        .rtb-input::placeholder { 
            color: #64748b !important; 
            opacity: 0.8 !important; 
            font-weight: 600 !important;
        }
        .dark .rtb-input::placeholder {
            color: #94a3b8 !important;
        }
        .rtb-editable-cell:hover .rtb-input:not(:focus) { 
            border-color: var(--color-border-primary) !important; 
            background: rgba(0,0,0,0.02) !important; 
        }
        .dark .rtb-editable-cell:hover .rtb-input:not(:focus) {
            background: rgba(255,255,255,0.03) !important;
        }

        /* Batch Modal Specifics */
        .batch-select {
            width: 100%; padding: 12px 16px; border-radius: 14px; 
            border: 1.5px solid var(--color-border-primary); background: var(--color-bg-primary);
            font-size: 13px; font-weight: 600; color: var(--color-text-primary);
            outline: none; transition: all 0.2s; -webkit-appearance: none;
        }
        .batch-select:focus { border-color: #0055D4; box-shadow: 0 0 0 4px rgba(0,85,212,0.1); }

        /* Stage color pills */
        .stage-pill { display:inline-flex; align-items:center; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:700; white-space:nowrap; }
        .stage-writer    { background:rgba(14,165,233,0.1);  color:#0ea5e9;  border:1px solid rgba(14,165,233,0.2); }
        .stage-approver  { background:rgba(245,158,11,0.1);  color:#d97706;  border:1px solid rgba(245,158,11,0.2); }
        .stage-manager   { background:rgba(59,130,246,0.1);  color:#3b82f6;  border:1px solid rgba(59,130,246,0.2); }
        .stage-coordinator { background:rgba(99,102,241,0.1); color:#6366f1; border:1px solid rgba(99,102,241,0.2); }
        .stage-designer  { background:rgba(236,72,153,0.1);  color:#ec4899;  border:1px solid rgba(236,72,153,0.2); }
        .stage-final     { background:rgba(16,185,129,0.1);  color:#10b981;  border:1px solid rgba(16,185,129,0.2); }
        .stage-closed    { background:rgba(100,116,139,0.08); color:#64748b; border:1px solid rgba(100,116,139,0.15); }
        .stage-default   { background:rgba(59,130,246,0.08); color:#3b82f6;  border:1px solid rgba(59,130,246,0.15); }

        /* Quick Action Buttons */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 4px;
            width: 100%;
        }
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 5px 7px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 600;
            transition: all 0.15s;
            border: 1px solid transparent;
            width: 100%;
            justify-content: center;
            white-space: nowrap;
            cursor: pointer;
            background: none;
            line-height: 1;
        }
        .btn-approve-quick { background: rgba(16, 185, 129, 0.08); color: #10b981; border-color: rgba(16, 185, 129, 0.1); }
        .btn-approve-quick:hover { background: #10b981; color: white; }
        .btn-revise-quick { background: rgba(239, 68, 68, 0.08); color: #ef4444; border-color: rgba(239, 68, 68, 0.1); }
        .btn-revise-quick:hover { background: #ef4444; color: white; }
        .btn-edit-quick { background: rgba(37, 99, 235, 0.08); color: #2563eb; border-color: rgba(37, 99, 235, 0.1); }
        .btn-edit-quick:hover { background: #2563eb; color: white; }
        .btn-delete-quick { background: rgba(100, 116, 139, 0.08); color: #64748b; border-color: rgba(100, 116, 139, 0.1); }
        .btn-delete-quick:hover { background: #ef4444; color: white; border-color: #ef4444; }
        .btn-view-quick { background: rgba(14, 165, 233, 0.08); color: #0ea5e9; border-color: rgba(14, 165, 233, 0.1); }
        .btn-view-quick:hover { background: #0ea5e9; color: white; }
        /* Revision Styles */
    .revision-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #ffedd5;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        margin-left: 6px;
    }
    .dark .revision-badge {
        background: rgba(194, 65, 12, 0.1);
        color: #fb923c;
        border-color: rgba(194, 65, 12, 0.2);
    }
    .redelivered-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 6px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #d1fae5;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        margin-left: 6px;
        animation: badge-pulse 2s infinite;
    }
    .dark .redelivered-badge {
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        border-color: rgba(16, 185, 129, 0.2);
    }
    .revision-badge.revision-ready {
        background: #ecfdf5;
        color: #047857;
        border-color: #d1fae5;
        animation: badge-pulse 2s infinite;
    }
    .dark .revision-badge.revision-ready {
        background: rgba(16, 185, 129, 0.1);
        color: #34d399;
        border-color: rgba(16, 185, 129, 0.2);
    }
    @keyframes badge-pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.05); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Board Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px;">
            <div style="min-width: 0;">
                <nav style="display: flex; gap: 6px; align-items: center; font-size: 11px; font-weight: 600; color: var(--color-text-secondary); margin-bottom: 8px;">
                    <a href="/brands" style="text-decoration: none; color: inherit; hover:color: var(--color-text-primary);">Brands</a>
                    <span style="opacity:0.4;">/</span>
                    <a href="{{ route('brands.show', $project->brand) }}" style="text-decoration: none; color: inherit;">{{ $project->brand->name }}</a>
                </nav>
                <h1 style="font-size: 24px; font-weight: 800; color: var(--color-text-primary); letter-spacing: -0.02em; display:flex; align-items:baseline; gap:8px; flex-wrap:wrap;">
                    @if($project->job_number)
                        <span style="color: #3b82f6; font-size:16px; font-weight:700; opacity:0.9;">[{{ $project->job_number }}]</span>
                    @endif
                    {{ $project->name }}
                    <span style="font-size:11px; font-weight:600; color:var(--color-text-secondary); background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); padding:3px 8px; border-radius:6px; letter-spacing:0.04em; text-transform:uppercase;">{{ ucfirst($project->workflow_type) }}</span>
                </h1>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-shrink:0;">
                <!-- Overlapping team avatars -->
                <div style="display: flex; align-items: center;">
                    @foreach($project->members->take(5) as $i => $member)
                        <div style="width: 30px; height: 30px; border-radius: 50%; border: 2px solid var(--color-bg-primary); overflow: hidden; margin-left: {{ $i > 0 ? '-8px' : '0' }}; position: relative; z-index: {{ 10 - $i }};" title="{{ $member->name }} ({{ $member->role }})">
                            <img src="{{ $member->avatar_url }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @endforeach
                    @if($project->members->count() > 5)
                        <div style="width:30px;height:30px;border-radius:50%;border:2px solid var(--color-bg-primary);background:var(--color-bg-secondary);display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:800;color:var(--color-text-secondary);margin-left:-8px;z-index:5;">
                            +{{ $project->members->count() - 5 }}
                        </div>
                    @endif
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('projects.edit', $project) }}"
                       style="padding: 8px 16px; background: var(--color-bg-primary); border: 1.5px solid var(--color-border-primary); border-radius: 9px; font-size: 12px; font-weight: 600; color: var(--color-text-secondary); text-decoration: none; transition: all 0.15s; display:inline-flex; align-items:center; gap:6px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Settings
                    </a>
                    @can('create-deliverable')
                    <a href="{{ route('deliverables.create', ['project_id' => $project->id]) }}"
                       style="padding: 8px 16px; background: #0055D4; border-radius: 9px; font-size: 12px; font-weight: 700; color: #fff; text-decoration: none; box-shadow: 0 4px 12px rgba(0,85,212,0.25); transition: all 0.15s; display:inline-flex; align-items:center; gap:6px;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Deliverable
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        @if($project->description || $project->brief_file_path)
            <div style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); border-radius: 12px; padding: 16px 20px; display:flex; align-items:flex-start; gap:12px;">
                <svg width="14" height="14" fill="none" stroke="#3b82f6" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:3px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <div style="flex:1; min-width:0;">
                    <div style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--color-text-secondary); margin-bottom:6px;">Project Brief</div>
                    @if($project->description)
                        <div style="font-size:13px; color:var(--color-text-primary); line-height:1.6; font-weight:500;">{!! nl2br(e($project->description)) !!}</div>
                    @endif
                    @if($project->brief_file_path)
                        <a href="{{ asset('storage/' . $project->brief_file_path) }}" target="_blank"
                           style="display:inline-flex; align-items:center; gap:6px; margin-top:{{ $project->description ? '10px' : '0' }}; font-size:11px; font-weight:700; color:#3b82f6; text-decoration:none; padding:6px 12px; background:rgba(59,130,246,0.06); border:1px solid rgba(59,130,246,0.2); border-radius:7px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            View Brief Document
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @php
            $subtaskTypeColors = [
                'Standard' => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'],
                'Static Post' => ['bg' => '#f0fdf4', 'text' => '#15803d', 'border' => '#bbf7d0'],
                'Carousel'    => ['bg' => '#faf5ff', 'text' => '#7c3aed', 'border' => '#e9d5ff'],
                'Reels'       => ['bg' => '#fef2f2', 'text' => '#ef4444', 'border' => '#fecaca'],
                'Story'       => ['bg' => '#fff7ed', 'text' => '#c2410c', 'border' => '#fed7aa'],
                'Radio script' => ['bg' => '#ecfeff', 'text' => '#0891b2', 'border' => '#a5f3fc'],
                'KV'           => ['bg' => '#fff1f2', 'text' => '#e11d48', 'border' => '#fecdd3'],
                'Presentation' => ['bg' => '#f0f9ff', 'text' => '#0284c7', 'border' => '#bae6fd'],
                'Video script' => ['bg' => '#fdf2f8', 'text' => '#db2777', 'border' => '#fbcfe8'],
                'Ideation/Brainstorm' => ['bg' => '#fefce8', 'text' => '#ca8a04', 'border' => '#fef9c3'],
                'Review'       => ['bg' => '#f0fdfa', 'text' => '#0d9488', 'border' => '#ccfbf1'],
                'Client meeting' => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'border' => '#ddd6fe'],
                'Internal meeting' => ['bg' => '#f8fafc', 'text' => '#64748b', 'border' => '#e2e8f0'],
                'Upload file'  => ['bg' => '#ecfdf5', 'text' => '#059669', 'border' => '#a7f3d0'],
                'Text field'   => ['bg' => '#fffbeb', 'text' => '#d97706', 'border' => '#fef3c7'],
                'default'     => ['bg' => '#f8fafc', 'text' => '#475569', 'border' => '#e2e8f0'],
            ];
        @endphp

        <div class="cd-table-wrap">
            <div class="cd-header">
                <div class="cd-header-left">
                    <h2>Content Deliverables</h2>
                </div>
                <div class="cd-header-right">
                </div>
            </div>

            <div style="width:100%; overflow-x:auto;">
                <table class="cd-table">
                    @if($project->workflow_type === 'retainer')
                        <thead>
                            <tr>
                                <th style="width:140px;">Deliverable</th>
                                <th style="width:75px;">Due</th>
                                <th style="width:90px;">Notes</th>
                                <th style="width:90px;">Concept</th>
                                <th style="width:80px;">Type</th>
                                <th style="width:90px;">Caption</th>
                                <th style="width:90px;">Post Copy</th>
                                <th style="width:50px;">Ref</th>
                                <th style="width:65px;">Artwork</th>
                                <th style="width:70px;">Rev</th>
                                <th style="width:80px;">Stage</th>
                                <th style="width:130px; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($project->deliverables->whereNull('parent_deliverable_id') as $task)
                                @if($task->subtasks->count() > 0)
                                    <!-- Heading Row for Deliverable with Subtasks -->
                                    <tr class="rtb-heading-row" style="background:var(--color-bg-secondary); border-left:3px solid #3b82f6;">
                                        <td colspan="5">
                                            <div class="deliverable-name-cell" style="padding: 8px 0; display:flex; align-items:center; gap:10px;">
                                                <span style="font-weight:800; color:var(--color-text-primary); font-size:13px; letter-spacing:-0.01em;">{{ $task->title }}</span>
                                                <span style="font-size:10px; font-weight:700; color:var(--color-text-secondary); background:var(--color-bg-primary); border:1px solid var(--color-border-primary); padding:2px 7px; border-radius:6px;">{{ $task->subtasks->count() }} posts</span>
                                            </div>
                                        </td>
                                        <td colspan="7" style="padding-right:15px;">
                                            @php
                                                $userRole = strtolower(str_replace(' ', '', auth()->user()->role));
                                                $isAdmin = $userRole === 'admin';
                                                $stage = $task->approval_stage;
                                                
                                                // Only Approver and Brand Manager stages get batch action buttons
                                                $approverReviewStages = ['Approver', 'Brand Manager'];
                                                $isApproverReviewStage = in_array($stage, $approverReviewStages);

                                                $canApproveBatch = $isAdmin || ($isApproverReviewStage && (
                                                    ($stage === 'Approver' && $userRole === 'approver') ||
                                                    ($stage === 'Brand Manager' && $userRole === 'brandmanager')));

                                                $canReviseBatch = $isAdmin || ($isApproverReviewStage && (
                                                    ($stage === 'Approver' && $userRole === 'approver') ||
                                                    ($stage === 'Brand Manager' && $userRole === 'brandmanager')));
                                                
                                                $nextStage = $task->getNextStage();
                                                $label = "Approve Batch";
                                                if ($nextStage) {
                                                    if ($stage === 'Writer' || $stage === 'Assignee') $label = "Submit Batch to Approver";
                                                    elseif ($stage === 'Approver') $label = "Submit to Brand Manager";
                                                    elseif ($stage === 'Brand Manager') $label = "Submit to Coordinator";
                                                    elseif ($stage === 'Coordinator') $label = "Submit to Designer";
                                                    elseif ($stage === 'Designer') $label = "Batch Design Delivery";
                                                    elseif ($nextStage === 'Closed') $label = "Approve & Close Batch";
                                                }

                                                $subtasks = $task->subtasks;
                                                $allTasksForBatch = $subtasks;
                                                $totalInBatch = $allTasksForBatch->count();
                                                
                                                $stageOrder = ['Writer', 'Assignee', 'Approver', 'Brand Manager', 'AM/BD', 'Coordinator', 'Designer', 'Final Approval', 'Closed'];
                                                $currIdx = array_search($stage, $stageOrder);

                                                $readyInBatch = $allTasksForBatch->filter(function($t) use ($stage, $stageOrder, $currIdx) {
                                                    $tIdx = array_search($t->approval_stage, $stageOrder);
                                                    if ($tIdx > $currIdx) return true; // Already moved ahead
                                                    if ($tIdx < $currIdx) return false; // Behind (Revision Request)
                                                    
                                                    $isSubmitted = !in_array($t->approval_stage, ['Writer', 'Assignee']);
                                                    return $t->is_ready || $isSubmitted;
                                                })->count();

                                                $progressBatch = ($totalInBatch > 0) ? ($readyInBatch / $totalInBatch) * 100 : 0;
                                                $allReady = $readyInBatch === $totalInBatch;

                                                // Only gate for regular users; Admins bypass readiness check
                                                $isGated = !$isAdmin && !$allReady;
                                                $batchStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . "}";
                                            @endphp
                                            <div style="display:flex; justify-content:flex-end; align-items:center; gap:12px;">
                                                {{-- Stage badge --}}
                                                <span style="font-size:10px; font-weight:700; color:#3b82f6; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.2); padding:3px 9px; border-radius:6px; white-space:nowrap;">
                                                    {{ $task->approval_stage ?: 'Writer' }}
                                                </span>

                                                {{-- Progress --}}
                                                <div style="display:flex; align-items:center; gap:6px;">
                                                    <span style="font-size:11px; font-weight:700; color:{{ $allReady ? '#10b981' : 'var(--color-text-secondary)' }}; white-space:nowrap;">{{ $readyInBatch }}/{{ $totalInBatch }} ready</span>
                                                    <div style="width:60px; height:4px; background:var(--color-border-primary); border-radius:10px; overflow:hidden;">
                                                        <div style="width:{{ $progressBatch }}%; height:100%; background:{{ $allReady ? '#10b981' : '#3b82f6' }}; transition:width 0.3s;"></div>
                                                    </div>
                                                </div>

                                                {{-- Actions --}}
                                                <div style="display:flex; align-items:center; gap:6px;">
                                                    @if($canReviseBatch)
                                                        <button onclick="openBatchModal(event, {{ $task->id }}, '{{ $stage }}', {{ $totalInBatch }}, 'revision', {{ $batchStakeholders }})"
                                                                style="padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; color:#ef4444; background:rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.2); cursor:pointer; white-space:nowrap; transition:all 0.15s;">
                                                            Revise All
                                                        </button>
                                                    @endif
                                                    @if($canApproveBatch && $nextStage)
                                                        <button onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', {{ $totalInBatch }}, 'submit', {{ $batchStakeholders }})"
                                                                style="padding:6px 12px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; transition:all 0.15s; {{ !$isGated ? 'background:#0055D4; color:#fff; border:1px solid #0055D4; cursor:pointer;' : 'background:var(--color-bg-secondary); color:var(--color-text-secondary); border:1px solid var(--color-border-primary); cursor:not-allowed; opacity:0.6;' }}"
                                                                {{ $isGated ? 'disabled' : '' }}>
                                                            {{ $label }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach($task->subtasks as $subIndex => $subtask)
                                    @php
                                        $isWriterStage = ($subtask->approval_stage === 'Writer' || $subtask->approval_stage === 'Assignee');
                                        $isAssignedWriter = (auth()->id() == $subtask->writer_id || $isAdmin);
                                        $canEditInline = $isWriterStage && $isAssignedWriter;
                                    @endphp
                                    <tr class="subtask-row rtb-subtask-row subtask-of-{{ $task->id }} {{ $subIndex === $task->subtasks->count() - 1 ? 'last-subtask' : '' }} {{ $subtask->approval_stage === 'Closed' ? 'task-closed' : '' }}" onclick="openTaskModal({{ $subtask->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history'])->toJson() }})">
                                        <td>
                                            <div class="deliverable-name-cell">
                                                <span style="font-weight:700; color:var(--color-text-primary); font-size:13px;">{{ $subtask->title }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight:800;">{{ $subtask->deadline ? \Carbon\Carbon::parse($subtask->deadline)->format('M d, Y') : '—' }}</div>
                                            <div style="font-size:9px; color:var(--color-text-secondary);">{{ $subtask->deadline ? \Carbon\Carbon::parse($subtask->deadline)->format('H:i') : '' }}</div>
                                        </td>
                                        <td class="{{ ($isAdmin || $userRole === 'brandmanager') ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($isAdmin || $userRole === 'brandmanager')
                                                <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="notes" placeholder="Notes..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->notes }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $subtask->notes ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($canEditInline)
                                                <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="concept" placeholder="Enter Concept..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->concept }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $subtask->concept ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @php $subColors = $subtaskTypeColors[$subtask->subtask_type] ?? $subtaskTypeColors['default']; @endphp
                                            <span class="subtask-pill" style="background:{{ $subColors['bg'] }}; color:{{ $subColors['text'] }}; border-color:{{ $subColors['border'] }};">
                                                {{ $subtask->subtask_type ?: 'Standard' }}
                                            </span>
                                        </td>
                                        <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($canEditInline)
                                                <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="caption" placeholder="Enter Caption..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->caption }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $subtask->caption ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td class="{{ $canEditInline ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($canEditInline)
                                                <textarea class="rtb-input batch-field" data-task-id="{{ $subtask->id }}" data-field="post_copy" placeholder="Enter Copy..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->post_copy }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $subtask->post_copy ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($subtask->reference_file)
                                                <img src="{{ $subtask->reference_file }}" class="rtb-ref-preview">
                                            @elseif($subtask->reference)
                                                <a href="{{ $subtask->reference }}" target="_blank" class="ref-chip" style="padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($subtask->final_designs)
                                                @php $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $subtask->final_designs); @endphp
                                                @if($isImg)
                                                    <img src="{{ $subtask->final_designs }}" class="rtb-ref-preview" style="border-color:#10b981;">
                                                @else
                                                    <a href="{{ $subtask->final_designs }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                                @endif
                                            @elseif($subtask->final_designs_link)
                                                <a href="{{ $subtask->final_designs_link }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php 
                                                $isSubmitted = !in_array($subtask->approval_stage, ['Writer', 'Assignee']);
                                                $readyClass = ($subtask->is_ready || $isSubmitted) ? 'revision-ready' : '';
                                            @endphp
                                            @if($subtask->revisions > 0 || $subtask->is_ready || $isSubmitted)
                                                <span class="revision-badge {{ $readyClass }}">
                                                    @if($subtask->revisions > 0) R{{ $subtask->revisions }} @endif
                                                    @if($subtask->is_ready || $isSubmitted)
                                                        <span style="margin-left:{{ $subtask->revisions > 0 ? '4px' : '0' }}; opacity:0.8; font-size:8px;">{{ $isSubmitted ? 'SUBMITTED' : 'READY' }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="rtb-stage-label">
                                                {{ $subtask->approval_stage ?: 'Writer' }}
                                            </div>
                                        </td>
                                        <td style="text-align:center; padding: 12px 8px;">
                                            <div class="quick-actions-grid">
                                                @php
                                                    $subStage = $subtask->approval_stage;
                                                    $subNextStage = $subtask->getNextStage();
                                                    $canApproveSub = $isAdmin || (
                                                        ($subStage === 'Writer' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($subStage === 'Assignee' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($subStage === 'Approver' && $userRole === 'approver') ||
                                                        ($subStage === 'Brand Manager' && $userRole === 'brandmanager') ||
                                                        ($subStage === 'AM/BD' && $userRole === 'brandmanager') ||
                                                        ($subStage === 'Final Approval' && $userRole === 'brandmanager') ||
                                                        ($subStage === 'Coordinator' && ($userRole === 'coordinator' || $userRole === 'trafficcoordinator')) ||
                                                        ($subStage === 'Designer' && $userRole === 'designer')
                                                    );
                                                    $canReviseSub = $isAdmin || (
                                                        ($subStage === 'Approver' && $userRole === 'approver') ||
                                                        (in_array($subStage, ['Brand Manager', 'AM/BD', 'Final Approval']) && $userRole === 'brandmanager')
                                                    );
                                                    
                                                    // Contextual label
                                                    $btnLabel = 'Approve';
                                                    if ($subStage === 'Writer' || $subStage === 'Assignee') $btnLabel = 'Submit';
                                                    elseif ($subStage === 'Coordinator') $btnLabel = 'Assign';
                                                    elseif ($subStage === 'Designer') $btnLabel = 'Send';
                                                @endphp

                                                @php 
                                                    $subStakeholders = "{approver: " . ($subtask->approver_id ?? 'null') . ", brand_manager: " . ($subtask->brand_manager_id ?? 'null') . ", coordinator: " . ($subtask->coordinator_id ?? 'null') . ", designer: " . ($subtask->designer_id ?? 'null') . "}";
                                                @endphp
                                                @if($canApproveSub && $subNextStage)
                                                    <button type="button" onclick="openBatchModal(event, {{ $subtask->id }}, '{{ $subNextStage }}', 1, 'submit', {{ $subStakeholders }})" class="quick-action-btn btn-approve-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        {{ ($subStage === 'Writer' || $subStage === 'Assignee') ? 'Submit' : 'Approve' }}
                                                    </button>
                                                @endif

                                                @if($canReviseSub && $subStage !== 'Writer' && $subStage !== 'Assignee')
                                                    <button type="button" onclick="openBatchModal(event, {{ $subtask->id }}, '{{ $subStage }}', 1, 'revision', {{ $subStakeholders }})" class="quick-action-btn btn-revise-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                        Revise
                                                    </button>
                                                @endif


                                                @if($isAdmin || $userRole === 'brandmanager')
                                                <form action="{{ route('deliverables.destroy', $subtask) }}" method="POST" onsubmit="return confirm('Delete Deliverable?')" style="display:contents;" onclick="event.stopPropagation()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="quick-action-btn btn-delete-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        Del
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <!-- Standard Row for Deliverable without Subtasks -->
                                    <tr class="{{ $task->approval_stage === 'Closed' ? 'task-closed' : '' }}" onclick="openTaskModal({{ $task->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history'])->toJson() }})">
                                        <td>
                                            <div class="deliverable-name-cell">
                                                <span style="font-weight:900; color:#0055D4; font-size:13px;">{{ $task->title }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight:800;">{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y') : '—' }}</div>
                                            <div style="font-size:9px; color:var(--color-text-secondary);">{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('H:i') : '' }}</div>
                                        </td>
                                        <td class="{{ ($isAdmin || $userRole === 'brandmanager') ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($isAdmin || $userRole === 'brandmanager')
                                                <textarea class="rtb-input batch-field" data-task-id="{{ $task->id }}" data-field="notes" placeholder="Notes..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $task->notes }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $task->notes ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td><div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $task->concept ?: '—' }}</div></td>
                                        <td>
                                            @php $taskColors = $subtaskTypeColors[$task->subtask_type] ?? $subtaskTypeColors['default']; @endphp
                                            <span class="subtask-pill" style="background:{{ $taskColors['bg'] }}; color:{{ $taskColors['text'] }}; border-color:{{ $taskColors['border'] }};">
                                                {{ $task->subtask_type ?: 'Standard' }}
                                            </span>
                                        </td>
                                        <td><div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $task->caption ?: '—' }}</div></td>
                                        <td><div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $task->post_copy ?: '—' }}</div></td>
                                        <td>
                                            @if($task->reference_file)
                                                <img src="{{ $task->reference_file }}" class="rtb-ref-preview">
                                            @elseif($task->reference)
                                                <a href="{{ $task->reference }}" target="_blank" class="ref-chip" style="padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->final_designs)
                                                @php $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $task->final_designs); @endphp
                                                @if($isImg)
                                                    <img src="{{ $task->final_designs }}" class="rtb-ref-preview" style="border-color:#10b981;">
                                                @else
                                                    <a href="{{ $task->final_designs }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                                @endif
                                            @elseif($task->final_designs_link)
                                                <a href="{{ $task->final_designs_link }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php 
                                                $isSubmitted = !in_array($task->approval_stage, ['Writer', 'Assignee']);
                                                $readyClass = ($task->is_ready || $isSubmitted) ? 'revision-ready' : '';
                                            @endphp
                                            @if($task->revisions > 0 || $task->is_ready || $isSubmitted)
                                                <span class="revision-badge {{ $readyClass }}">
                                                    @if($task->revisions > 0) R{{ $task->revisions }} @endif
                                                    @if($task->is_ready || $isSubmitted)
                                                        <span style="margin-left:{{ $task->revisions > 0 ? '4px' : '0' }}; opacity:0.8; font-size:8px;">{{ $isSubmitted ? 'SUBMITTED' : 'READY' }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="rtb-stage-label">
                                                {{ $task->approval_stage ?: 'Writer' }}
                                            </div>
                                        </td>
                                        <td style="text-align:center;">
                                            <div class="quick-actions-grid">
                                                @php
                                                    $stage = $task->approval_stage;
                                                    $nextStage = $task->getNextStage();
                                                    $canApproveIndividual = $isAdmin || (
                                                        ($stage === 'Writer' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($stage === 'Assignee' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($stage === 'Approver' && $userRole === 'approver') ||
                                                        ($stage === 'Brand Manager' && $userRole === 'brandmanager') ||
                                                        ($stage === 'AM/BD' && $userRole === 'brandmanager') ||
                                                        ($stage === 'Final Approval' && $userRole === 'brandmanager') ||
                                                        ($stage === 'Coordinator' && ($userRole === 'coordinator' || $userRole === 'trafficcoordinator')) ||
                                                        ($stage === 'Designer' && $userRole === 'designer')
                                                    );
                                                    $canReviseIndividual = $isAdmin || (
                                                        ($stage === 'Approver' && $userRole === 'approver') ||
                                                        (in_array($stage, ['Brand Manager', 'AM/BD', 'Final Approval']) && $userRole === 'brandmanager')
                                                    );
                                                    
                                                    // Contextual label
                                                    $btnLabel = 'Approve';
                                                    if ($stage === 'Writer' || $stage === 'Assignee') $btnLabel = 'Submit';
                                                    elseif ($stage === 'Coordinator') $btnLabel = 'Assign';
                                                    elseif ($stage === 'Designer') $btnLabel = 'Send';
                                                @endphp

                                                @if($canApproveIndividual && $nextStage)
                                                    <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', 1, 'submit')" class="quick-action-btn btn-approve-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        {{ $btnLabel }}
                                                    </button>
                                                @endif

                                                @if($canReviseIndividual && $stage !== 'Writer' && $stage !== 'Assignee')
                                                    <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $stage }}', 1, 'revision')" class="quick-action-btn btn-revise-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                        Revise
                                                    </button>
                                                @endif


                                                @if($isAdmin || $userRole === 'brandmanager')
                                                <form action="{{ route('deliverables.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete Deliverable?')" style="display:contents;" onclick="event.stopPropagation()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="quick-action-btn btn-delete-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        Del
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                            <tr>
                                <td colspan="12" style="padding:60px 24px; text-align:center;">
                                    <div style="display:inline-flex;flex-direction:column;align-items:center;gap:14px;">
                                        <div style="width:56px;height:56px;border-radius:50%;background:var(--color-bg-secondary);border:2px dashed var(--color-border-primary);display:flex;align-items:center;justify-content:center;">
                                            <svg width="22" height="22" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div>
                                            <div style="font-size:14px;font-weight:900;color:var(--color-text-primary);margin-bottom:4px;">No deliverables yet</div>
                                            <div style="font-size:12px;font-weight:600;color:var(--color-text-secondary);">Add your first deliverable to get started</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th style="width:140px;">Deliverable</th>
                                <th style="width:75px;">Due Date</th>
                                <th style="width:90px;">Notes</th>
                                <th style="width:90px;">Concept</th>
                                <th style="width:80px;">Post Type</th>
                                <th style="width:90px;">Caption</th>
                                <th style="width:90px;">Post Copy</th>
                                <th style="width:45px;">Ref</th>
                                <th style="width:65px;">Artwork</th>
                                <th style="width:70px;">Rev</th>
                                <th style="width:80px;">Stage</th>
                                <th style="width:130px; text-align:center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                                @forelse($project->deliverables->whereNull('parent_deliverable_id') as $task)
                                @if($task->subtasks->count() > 0)
                                    <!-- Heading Row for Deliverable with Subtasks -->
                                    <tr class="rtb-heading-row" style="background:rgba(255,255,255,0.03); border-left:4px solid #0055D4;">
                                        <td colspan="9">
                                            <div class="deliverable-name-cell" style="padding: 10px 0; display:flex; align-items:center; gap:12px;">
                                                <button id="toggle-btn-{{ $task->id }}" class="subtask-toggle" onclick="toggleSubtasks(event, {{ $task->id }})" style="margin-right:4px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:4px; padding:2px;">
                                                    <svg width="10" height="10" fill="none" stroke="#0055D4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                                                </button>
                                                <span style="font-weight:900; color:#0055D4; font-size:15px; text-transform:uppercase; letter-spacing:0.05em;">{{ $task->title }}</span>
                                                <span style="font-size:11px; font-weight:700; color:var(--color-text-secondary); background:rgba(255,255,255,0.05); padding:2px 8px; border-radius:20px;">{{ $task->subtasks->count() }} Tasks</span>
                                            </div>
                                        </td>
                                        <td></td>
                                        <td>
                                            <div class="rtb-stage-label" style="background:#0055D4; color:#ffffff; font-weight:800; padding:4px 10px; border-radius:6px; font-size:10px; box-shadow:0 2px 4px rgba(0,0,0,0.3);">
                                                {{ $task->approval_stage ?: 'Writer' }}
                                            </div>
                                        </td>
                                        <td style="text-align:right; padding-right:15px;">
                                            @php
                                                $userRole = strtolower(str_replace(' ', '', auth()->user()->role));
                                                $isAdmin = $userRole === 'admin';
                                                $stage = $task->approval_stage;
                                                
                                                $isReviewStage = in_array($stage, ['Approver', 'Brand Manager', 'Final Approval', 'AM/BD', 'Coordinator', 'Designer']);
                                                
                                                // Only Approver and Brand Manager stages get batch action buttons
                                                $approverReviewStages = ['Approver', 'Brand Manager'];
                                                $isApproverReviewStage = in_array($stage, $approverReviewStages);

                                                $canApproveBatch = $isAdmin || ($isApproverReviewStage && (
                                                    ($stage === 'Approver' && $userRole === 'approver') ||
                                                    ($stage === 'Brand Manager' && $userRole === 'brandmanager')));

                                                $canReviseBatch = $isAdmin || ($isApproverReviewStage && (
                                                    ($stage === 'Approver' && $userRole === 'approver') ||
                                                    ($stage === 'Brand Manager' && $userRole === 'brandmanager')));
                                                
                                                $nextStage = $task->getNextStage();
                                                $label = "Approve Batch";
                                                if ($nextStage) {
                                                    if ($stage === 'Writer' || $stage === 'Assignee') $label = "Submit Batch to Approver";
                                                    elseif ($stage === 'Approver') $label = "Submit to Brand Manager";
                                                    elseif ($stage === 'Brand Manager') $label = "Submit to Coordinator";
                                                    elseif ($stage === 'Coordinator') $label = "Submit to Designer";
                                                    elseif ($stage === 'Designer') $label = "Batch Design Delivery";
                                                    elseif ($nextStage === 'Closed') $label = "Approve & Close Batch";
                                                }

                                                $subtasks = $task->subtasks;
                                                $allTasksForBatch = $subtasks;
                                                $totalInBatch = $allTasksForBatch->count();

                                                $stageOrder = ['Writer', 'Assignee', 'Approver', 'Brand Manager', 'AM/BD', 'Coordinator', 'Designer', 'Final Approval', 'Closed'];
                                                $currIdx = array_search($stage, $stageOrder);

                                                $readyInBatch = $allTasksForBatch->filter(function($t) use ($stage, $stageOrder, $currIdx) {
                                                    $tIdx = array_search($t->approval_stage, $stageOrder);
                                                    if ($tIdx > $currIdx) return true; // Already moved ahead
                                                    if ($tIdx < $currIdx) return false; // Behind (Revision Request)
                                                    
                                                    $isSubmitted = !in_array($t->approval_stage, ['Writer', 'Assignee']);
                                                    return $t->is_ready || $isSubmitted;
                                                })->count();

                                                $progressBatch = ($totalInBatch > 0) ? ($readyInBatch / $totalInBatch) * 100 : 0;
                                                $allReady = $readyInBatch === $totalInBatch;
                                                $isGated = !$isAdmin && !$allReady;
                                            @endphp
                                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                                                <div style="display:flex; align-items:center; gap:10px; margin-bottom: 2px;">
                                                    <div style="text-align:right;">
                                                        <div style="font-size:9px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:2px;">Batch Progress</div>
                                                        <div style="font-size:11px; font-weight:900; color:{{ $allReady ? '#10b981' : '#0055D4' }};">{{ $readyInBatch }} / {{ $totalInBatch }} Ready</div>
                                                    </div>
                                                    <div style="width:60px; height:6px; background:rgba(0,85,212,0.1); border-radius:10px; overflow:hidden;">
                                                        <div style="width:{{ $progressBatch }}%; height:100%; background:{{ $allReady ? '#10b981' : '#0055D4' }}; transition:width 0.3s ease;"></div>
                                                    </div>
                                                </div>
                                                <div style="display:flex; flex-direction:column; align-items:stretch; gap:8px; width: 100%;">
                                                    @php 
                                                        $batchStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . "}";
                                                    @endphp
                                                    @if($canReviseBatch)
                                                        <button onclick="openBatchModal(event, {{ $task->id }}, '{{ $stage }}', {{ $totalInBatch }}, 'revision', {{ $batchStakeholders }})" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:rgba(239,68,68,0.2); padding:6px 12px; border-radius:8px; font-size:9px; letter-spacing:0.05em; background:rgba(239,68,68,0.05); width: 100%; justify-content: center;">
                                                            Request Batch Revisions
                                                        </button>
                                                    @endif
                                                    @if($canApproveBatch && $nextStage)
                                                        <button onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', {{ $totalInBatch }}, 'submit', {{ $batchStakeholders }})" 
                                                                class="cd-btn {{ (!$isGated) ? 'cd-btn-primary' : 'cd-btn-outline' }}" 
                                                                style="padding:6px 12px; border-radius:8px; font-size:9px; letter-spacing:0.05em; box-shadow:none; width: 100%; justify-content: center; {{ $isGated ? 'opacity:0.6; cursor:not-allowed; color:#94a3b8; border-color:#e2e8f0;' : '' }}"
                                                                {{ $isGated ? 'disabled' : '' }}>
                                                            {{ $label }}
                                                        </button>
                                                    @endif
                                                </div>
                                        </td>
                                    </tr>
                                    @foreach($task->subtasks as $subtask)
                                    <tr class="subtask-row subtask-of-{{ $task->id }} collapsed {{ $subtask->approval_stage === 'Closed' ? 'task-closed' : '' }}" onclick="openTaskModal({{ $subtask->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history'])->toJson() }})">
                                        <td>
                                            <div class="deliverable-name-cell" style="display:flex; align-items:center; gap:8px;">
                                                <span style="font-weight:700; color:#475569;">{{ $subtask->title }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $subtask->deadline ? \Carbon\Carbon::parse($subtask->deadline)->format('M d, Y') : '—' }}</td>
                                        <td class="{{ ($isAdmin || $userRole === 'brandmanager') ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($isAdmin || $userRole === 'brandmanager')
                                                <textarea class="batch-field rtb-input" data-task-id="{{ $subtask->id }}" data-field="notes" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->notes }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $subtask->notes ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td onclick="event.stopPropagation()">
                                            <textarea class="batch-field rtb-input" data-task-id="{{ $subtask->id }}" data-field="concept" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->concept }}</textarea>
                                        </td>
                                        <td>
                                            @if($subtask->subtask_type)
                                                @php $colors = $subtaskTypeColors[$subtask->subtask_type] ?? $subtaskTypeColors['default']; @endphp
                                                <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }};">
                                                    {{ $subtask->subtask_type }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td onclick="event.stopPropagation()">
                                            <textarea class="batch-field rtb-input" data-task-id="{{ $subtask->id }}" data-field="caption" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->caption }}</textarea>
                                        </td>
                                        <td onclick="event.stopPropagation()">
                                            <textarea class="batch-field rtb-input" data-task-id="{{ $subtask->id }}" data-field="post_copy" onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $subtask->post_copy }}</textarea>
                                        </td>
                                        <td>
                                            @if($subtask->reference_file)
                                                <img src="{{ $subtask->reference_file }}" class="rtb-ref-preview">
                                            @elseif($subtask->reference)
                                                <a href="{{ $subtask->reference }}" target="_blank" class="ref-chip" style="padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($subtask->final_designs)
                                                @php $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $subtask->final_designs); @endphp
                                                @if($isImg)
                                                    <img src="{{ $subtask->final_designs }}" class="rtb-ref-preview" style="border-color:#10b981;">
                                                @else
                                                    <a href="{{ $subtask->final_designs }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                                @endif
                                            @elseif($subtask->final_designs_link)
                                                <a href="{{ $subtask->final_designs_link }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php 
                                                $isSubmitted = !in_array($subtask->approval_stage, ['Writer', 'Assignee']);
                                                $readyClass = ($subtask->is_ready || $isSubmitted) ? 'revision-ready' : '';
                                            @endphp
                                            @if($subtask->revisions > 0 || $subtask->is_ready || $isSubmitted)
                                                <span class="revision-badge {{ $readyClass }}">
                                                    @if($subtask->revisions > 0) R{{ $subtask->revisions }} @endif
                                                    @if($subtask->is_ready || $isSubmitted)
                                                        <span style="margin-left:{{ $subtask->revisions > 0 ? '4px' : '0' }}; opacity:0.8; font-size:8px;">{{ $isSubmitted ? 'SUBMITTED' : 'READY' }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-size:10px; font-weight:900; color:#0055D4; text-transform:uppercase; letter-spacing:0.05em;">{{ $subtask->approval_stage }}</div>
                                        </td>
                                        <td style="text-align:center;">
                                            <div class="quick-actions-grid">
                                                @php
                                                    $subStage = $subtask->approval_stage;
                                                    $subNextStage = $subtask->getNextStage();
                                                    $canApproveSub = $isAdmin || (
                                                        ($subStage === 'Writer' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($subStage === 'Assignee' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($subStage === 'Approver' && $userRole === 'approver') ||
                                                        ($subStage === 'Brand Manager' && $userRole === 'brandmanager') ||
                                                        ($subStage === 'AM/BD' && $userRole === 'brandmanager') ||
                                                        ($subStage === 'Final Approval' && $userRole === 'brandmanager') ||
                                                        ($subStage === 'Coordinator' && ($userRole === 'coordinator' || $userRole === 'trafficcoordinator')) ||
                                                        ($subStage === 'Designer' && $userRole === 'designer')
                                                    );
                                                    $canReviseSub = $isAdmin || (
                                                        ($subStage === 'Approver' && $userRole === 'approver') ||
                                                        (in_array($subStage, ['Brand Manager', 'AM/BD', 'Final Approval']) && $userRole === 'brandmanager')
                                                    );
                                                    
                                                    // Contextual label
                                                    $btnLabel = 'Approve';
                                                    if ($subStage === 'Writer' || $subStage === 'Assignee') $btnLabel = 'Submit';
                                                    elseif ($subStage === 'Coordinator') $btnLabel = 'Assign';
                                                    elseif ($subStage === 'Designer') $btnLabel = 'Send';
                                                @endphp

                                                @php 
                                                    $subStakeholders = "{approver: " . ($subtask->approver_id ?? 'null') . ", brand_manager: " . ($subtask->brand_manager_id ?? 'null') . ", coordinator: " . ($subtask->coordinator_id ?? 'null') . ", designer: " . ($subtask->designer_id ?? 'null') . "}";
                                                @endphp
                                                @if($canApproveSub && $subNextStage)
                                                    <button type="button" onclick="openBatchModal(event, {{ $subtask->id }}, '{{ $subNextStage }}', 1, 'submit', {{ $subStakeholders }})" class="quick-action-btn btn-approve-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        {{ ($subStage === 'Writer' || $subStage === 'Assignee') ? 'Submit' : 'Approve' }}
                                                    </button>
                                                @endif

                                                @if($canReviseSub && $subStage !== 'Writer' && $subStage !== 'Assignee')
                                                    <button type="button" onclick="openBatchModal(event, {{ $subtask->id }}, '{{ $subStage }}', 1, 'revision', {{ $subStakeholders }})" class="quick-action-btn btn-revise-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                        Revise
                                                    </button>
                                                @endif

                                                <button type="button" onclick="event.stopPropagation(); openTaskModal({{ $subtask->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history'])->toJson() }})" class="quick-action-btn btn-view-quick">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View
                                                </button>
                                                @if($isAdmin || $userRole === 'brandmanager')
                                                <form action="{{ route('deliverables.destroy', $subtask) }}" method="POST" onsubmit="return confirm('CRITICAL ACTION: Are you sure you want to permanently delete this subtask?')" style="display:contents;" onclick="event.stopPropagation()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="quick-action-btn btn-delete-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        Del
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>

                                    </tr>
                                    @endforeach
                                @else
                                    <!-- Standard Row for Deliverable without Subtasks -->
                                    <tr class="{{ $task->approval_stage === 'Closed' ? 'task-closed' : '' }}" onclick="openTaskModal({{ $task->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history'])->toJson() }})">
                                        <td>
                                            <div class="deliverable-name-cell">
                                                <span style="font-weight:900; color:var(--color-text-primary);">{{ $task->title }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('M d, Y') : '—' }}</td>
                                        <td class="{{ ($isAdmin || $userRole === 'brandmanager') ? 'rtb-editable-cell' : '' }}" onclick="event.stopPropagation()">
                                            @if($isAdmin || $userRole === 'brandmanager')
                                                <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="notes" placeholder="Notes..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $task->notes }}</textarea>
                                            @else
                                                <div style="font-size:11px; color:var(--color-text-secondary); line-height:1.4;">{{ $task->notes ?: '—' }}</div>
                                            @endif
                                        </td>
                                        <td onclick="event.stopPropagation()">
                                            <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="concept" placeholder="Concept..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $task->concept }}</textarea>
                                        </td>
                                        <td>
                                            @if($task->subtask_type)
                                                @php $colors = $subtaskTypeColors[$task->subtask_type] ?? $subtaskTypeColors['default']; @endphp
                                                <span class="subtask-pill" style="background:{{ $colors['bg'] }}; color:{{ $colors['text'] }}; border-color:{{ $colors['border'] }};">
                                                    {{ $task->subtask_type }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td onclick="event.stopPropagation()">
                                            <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="caption" placeholder="Caption..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $task->caption }}</textarea>
                                        </td>
                                        <td onclick="event.stopPropagation()">
                                            <textarea class="batch-field rtb-input" data-task-id="{{ $task->id }}" data-field="post_copy" placeholder="Copy..." onclick="openCellEditor(event)" style="width:100%; min-height:45px; font-size:11px; padding:8px; border:1px solid var(--color-border-primary); border-radius:8px; background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $task->post_copy }}</textarea>
                                        </td>
                                        <td>
                                            @if($task->reference_file)
                                                <img src="{{ $task->reference_file }}" class="rtb-ref-preview">
                                            @elseif($task->reference)
                                                <a href="{{ $task->reference }}" target="_blank" class="ref-chip" style="padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->final_designs)
                                                @php $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)/i', $task->final_designs); @endphp
                                                @if($isImg)
                                                    <img src="{{ $task->final_designs }}" class="rtb-ref-preview" style="border-color:#10b981;">
                                                @else
                                                    <a href="{{ $task->final_designs }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">View</a>
                                                @endif
                                            @elseif($task->final_designs_link)
                                                <a href="{{ $task->final_designs_link }}" target="_blank" class="ref-chip" style="background:rgba(16,185,129,0.1); color:#10b981; border-color:rgba(16,185,129,0.2); padding:4px 8px; font-size:9px;">Link</a>
                                            @else
                                                <span style="color:var(--color-text-secondary); opacity:0.5; font-size:10px;">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php 
                                                $isSubmitted = !in_array($task->approval_stage, ['Writer', 'Assignee']);
                                                $readyClass = ($task->is_ready || $isSubmitted) ? 'revision-ready' : '';
                                            @endphp
                                            @if($task->revisions > 0 || $task->is_ready || $isSubmitted)
                                                <span class="revision-badge {{ $readyClass }}">
                                                    @if($task->revisions > 0) R{{ $task->revisions }} @endif
                                                    @if($task->is_ready || $isSubmitted)
                                                        <span style="margin-left:{{ $task->revisions > 0 ? '4px' : '0' }}; opacity:0.8; font-size:8px;">{{ $isSubmitted ? 'SUBMITTED' : 'READY' }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="font-size:10px; font-weight:900; color:#0055D4; text-transform:uppercase; letter-spacing:0.05em;">{{ $task->approval_stage }}</div>
                                        </td>
                                        <td style="text-align:center; padding: 12px 8px;">
                                            <div class="quick-actions-grid">
                                                @php
                                                    $stage = $task->approval_stage;
                                                    $nextStage = $task->getNextStage();
                                                    $canApproveIndividual = $isAdmin || (
                                                        ($stage === 'Writer' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($stage === 'Assignee' && ($userRole === 'writer' || $userRole === 'assignee')) ||
                                                        ($stage === 'Approver' && $userRole === 'approver') ||
                                                        ($stage === 'Brand Manager' && $userRole === 'brandmanager') ||
                                                        ($stage === 'AM/BD' && $userRole === 'brandmanager') ||
                                                        ($stage === 'Final Approval' && $userRole === 'brandmanager') ||
                                                        ($stage === 'Coordinator' && ($userRole === 'coordinator' || $userRole === 'trafficcoordinator')) ||
                                                        ($stage === 'Designer' && $userRole === 'designer')
                                                    );
                                                    $canReviseIndividual = $isAdmin || (
                                                        ($stage === 'Approver' && $userRole === 'approver') ||
                                                        (in_array($stage, ['Brand Manager', 'AM/BD', 'Final Approval']) && $userRole === 'brandmanager')
                                                    );
                                                    
                                                    // Contextual label
                                                    $btnLabel = 'Approve';
                                                    if ($stage === 'Writer' || $stage === 'Assignee') $btnLabel = 'Submit';
                                                    elseif ($stage === 'Coordinator') $btnLabel = 'Assign';
                                                    elseif ($stage === 'Designer') $btnLabel = 'Send';
                                                @endphp

                                                @php 
                                                    $taskStakeholders = "{approver: " . ($task->approver_id ?? 'null') . ", brand_manager: " . ($task->brand_manager_id ?? 'null') . ", coordinator: " . ($task->coordinator_id ?? 'null') . ", designer: " . ($task->designer_id ?? 'null') . "}";
                                                @endphp
                                                @if($canApproveIndividual && $nextStage)
                                                    <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $nextStage }}', 1, 'submit', {{ $taskStakeholders }})" class="quick-action-btn btn-approve-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        {{ $btnLabel }}
                                                    </button>
                                                @endif

                                                @if($canReviseIndividual && $stage !== 'Writer' && $stage !== 'Assignee')
                                                    <button type="button" onclick="openBatchModal(event, {{ $task->id }}, '{{ $stage }}', 1, 'revision', {{ $taskStakeholders }})" class="quick-action-btn btn-revise-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                        Revise
                                                    </button>
                                                @endif

                                                <button type="button" onclick="event.stopPropagation(); openTaskModal({{ $task->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history'])->toJson() }})" class="quick-action-btn btn-view-quick">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    View
                                                </button>
                                                <a href="{{ route('deliverables.create', ['project_id' => $project->id, 'parent_id' => $task->id]) }}" onclick="event.stopPropagation()" class="quick-action-btn btn-edit-quick" style="text-decoration:none;">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                    Sub
                                                </a>
                                                @if($isAdmin || $userRole === 'brandmanager')
                                                <form action="{{ route('deliverables.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete Deliverable?')" style="display:contents;" onclick="event.stopPropagation()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="quick-action-btn btn-delete-quick">
                                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                        Del
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                            <tr>
                                <td colspan="5" style="padding:60px 24px; text-align:center;">
                                    <div style="display:inline-flex;flex-direction:column;align-items:center;gap:14px;">
                                        <div style="width:56px;height:56px;border-radius:50%;background:var(--color-bg-secondary);border:2px dashed var(--color-border-primary);display:flex;align-items:center;justify-content:center;">
                                            <svg width="22" height="22" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div>
                                            <div style="font-size:14px;font-weight:900;color:var(--color-text-primary);margin-bottom:4px;">No deliverables yet</div>
                                            <div style="font-size:12px;font-weight:600;color:var(--color-text-secondary);">Add your first deliverable to get started</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>
        </div>


        </div>
    </div>

    <!-- Detail Modal -->
    <div id="taskModalOverlay" class="cd-modal-overlay" onclick="closeTaskModal(event)">
        <div class="cd-modal" onclick="event.stopPropagation()">
            <div class="cd-modal-header">
                <div>
                    <div id="modalSubtaskType" class="subtask-pill" style="margin-bottom:12px;"></div>
                    <h2 id="modalTaskTitle" style="font-size:24px; font-weight:900; color:var(--color-text-primary); margin:0;"></h2>
                </div>
                <button onclick="closeTaskModal()" style="background:none; border:none; color:var(--color-text-secondary); cursor:pointer;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="cd-modal-body">
                <!-- Workflow Tracker -->
                <div class="workflow-steps" id="modalWorkflowSteps">
                    @foreach($stages as $index => $stage)
                        <div class="step-item" data-stage="{{ $stage }}">
                            <div class="step-dot">{{ $index + 1 }}</div>
                            <div class="step-label">{{ $stage }}</div>
                        </div>
                    @endforeach
                </div>

                <!-- Revision Alert Banner -->
                <div id="modalRevisionAlert" style="display:none; padding:16px; background:rgba(239, 68, 68, 0.1); border:1px solid rgba(239, 68, 68, 0.2); border-radius:16px; margin-bottom:24px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                        <svg width="20" height="20" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span style="color:#ef4444; font-weight:900; font-size:14px; text-transform:uppercase; letter-spacing:0.05em;">Revision Requested</span>
                    </div>
                    <div id="modalRevisionAlertText" style="color:#ef4444; font-size:13px; font-weight:500; line-height:1.5; background:var(--color-bg-primary); padding:12px; border-radius:10px; border:1px solid rgba(239, 68, 68, 0.3); margin-top:8px;"></div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item full">
                        <label class="detail-label" style="color:#0055D4;">Manager Notes</label>
                        <textarea id="modalNotes" name="notes" form="submitStageForm" class="detail-val-textarea" style="background:rgba(0,85,212,0.03); border-color:rgba(0,85,212,0.1); font-style:italic;" readonly></textarea>
                    </div>
                    <div class="detail-item full">
                        <label class="detail-label">Concept</label>
                        <textarea id="modalConcept" name="concept" form="submitStageForm" class="detail-val-textarea" readonly></textarea>
                    </div>
                    <div class="detail-item full">
                        <label class="detail-label">Caption</label>
                        <textarea id="modalCaption" name="caption" form="submitStageForm" class="detail-val-textarea" readonly></textarea>
                    </div>
                    <div class="detail-item full">
                        <label class="detail-label">Subtask Copy</label>
                        <textarea id="modalSubtaskCopy" name="post_copy" form="submitStageForm" class="detail-val-textarea" style="min-height:180px;" readonly></textarea>
                    </div>
                    <div class="detail-item full">
                        <label class="detail-label">Reference</label>
                        <div id="modalReference" class="detail-val" style="margin-bottom: 12px;"></div>
                        <div id="modalReferenceEditArea" style="display:none; flex-direction:column; gap:12px; padding:16px; background:rgba(0,85,212,0.03); border:1px solid rgba(0,85,212,0.1); border-radius:16px;">
                            <div>
                                <div style="font-size:10px; font-weight:800; color:var(--color-text-secondary); margin-bottom:6px; text-transform:uppercase;">Upload New Reference File</div>
                                <input type="file" name="reference_file" form="submitStageForm" style="width:100%; padding:8px; border:1.5px dashed var(--color-border-primary); border-radius:10px; background:var(--color-bg-primary); font-size:11px;">
                            </div>
                            <div>
                                <div style="font-size:10px; font-weight:800; color:var(--color-text-secondary); margin-bottom:6px; text-transform:uppercase;">Reference URL</div>
                                <input type="url" id="modalReferenceUrl" name="reference" form="submitStageForm" placeholder="https://..." style="width:100%; padding:10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Current Stage</label>
                        <div id="modalStage" class="detail-val" style="font-weight:900; color:#0055D4;">-</div>
                    </div>

                    <div class="detail-item full" style="border-top:1px solid var(--color-border-primary); padding-top:20px; margin-top:10px;">
                        <label class="detail-label" style="margin-bottom:16px; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">Deliverable Team</label>
                        <div id="modalTeamGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px;"></div>
                    </div>
                    <div class="detail-item full" id="modalApprovalsBox" style="display:none; border-top:1px solid var(--color-border-primary); padding-top:20px; margin-top:10px;">
                        <label class="detail-label" style="color:#10b981; margin-bottom:12px;">Approval History</label>
                        <div id="modalApprovalHistory" style="display:flex; flex-direction:column; gap:10px;"></div>
                    </div>

                    <div id="approverSelectionArea" class="detail-item full" style="display:none; margin-top:10px; padding:20px; background:rgba(234,88,12,0.05); border:1px solid rgba(234,88,12,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#ea580c; margin-bottom:12px;">Assign Next Approver</label>
                        <select name="approver_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Approver...</option>
                            @foreach($approvers as $approver)
                                <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#ea580c; margin-top:8px; font-weight:600;">Selection required to advance to Approver stage.</p>
                    </div>

                    <div id="brandManagerSelectionArea" class="detail-item full" style="display:none; margin-top:10px; padding:20px; background:rgba(37,99,235,0.05); border:1px solid rgba(37,99,235,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#3b82f6; margin-bottom:12px;">Assign Next Brand Manager</label>
                        <select name="brand_manager_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Brand Manager...</option>
                            @foreach($brandManagers as $bm)
                                <option value="{{ $bm->id }}">{{ $bm->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#3b82f6; margin-top:8px; font-weight:600;">Selection required to advance to Brand Manager stage.</p>
                    </div>

                    <div id="coordinatorSelectionArea" class="detail-item full" style="display:none; margin-top:10px; padding:20px; background:rgba(14,165,233,0.05); border:1px solid rgba(14,165,233,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#0ea5e9; margin-bottom:12px;">Assign Next Coordinator</label>
                        <select name="coordinator_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Coordinator...</option>
                            @foreach($coordinators as $coord)
                                <option value="{{ $coord->id }}">{{ $coord->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#0ea5e9; margin-top:8px; font-weight:600;">Selection required to advance to Coordinator stage.</p>
                    </div>

                    <div id="designerSelectionArea" class="detail-item full" style="display:none; margin-top:10px; padding:20px; background:rgba(139,92,246,0.05); border:1px solid rgba(139,92,246,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#8b5cf6; margin-bottom:12px;">Assign Next Designer</label>
                        <select name="designer_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Designer...</option>
                            @foreach($designers as $designer)
                                <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#8b5cf6; margin-top:8px; font-weight:600;">Selection required to advance to Designer stage.</p>
                    </div>

                    <div id="designerDeliveryArea" class="detail-item full" style="display:none; margin-top:10px; padding:20px; background:rgba(16,185,129,0.05); border:1px solid rgba(16,185,129,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#10b981; margin-bottom:12px;">Deliver Final Artwork</label>
                        <div style="display:flex; flex-direction:column; gap:16px;">
                            <div>
                                <div style="font-size:11px; font-weight:800; color:#10b981; margin-bottom:6px; text-transform:uppercase;">Upload Artwork Image</div>
                                <input type="file" name="final_designs_file" form="submitStageForm" style="width:100%; padding:10px; border:1.5px dashed var(--color-border-primary); border-radius:12px; background:var(--color-bg-primary); font-size:12px; color:var(--color-text-primary);">
                            </div>
                            <div>
                                <div style="font-size:11px; font-weight:800; color:#10b981; margin-bottom:6px; text-transform:uppercase;">External Link (Optional)</div>
                                <input type="url" name="final_designs_link" form="submitStageForm" placeholder="https://..." style="width:100%; padding:12px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            </div>
                        </div>
                        <p style="font-size:11px; color:#10b981; margin-top:10px; font-weight:600;">Your delivery will be recorded in the final artwork section.</p>
                    </div>

                    <div id="designerSelectionAreaInModal" class="detail-item full" style="display:none; margin-top:10px; padding:20px; background:rgba(0,85,212,0.05); border:1px solid rgba(0,85,212,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#0055D4; margin-bottom:12px;">Assign Next Designer</label>
                        <select name="designer_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Designer...</option>
                            @foreach($designers as $designer)
                                <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#0055D4; margin-top:8px; font-weight:600;">Selection required to advance to Designer stage.</p>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Final Designs</label>
                        <div id="modalFinal" class="detail-val"></div>
                    </div>

                    <div class="detail-item full" id="modalHistoryBox" style="display:none; border-top:1px solid var(--color-border-primary); padding-top:20px; margin-top:10px;">
                        <label class="detail-label" style="margin-bottom:12px;">Revision History</label>
                        <div id="modalRevisionHistory" style="display:flex; flex-direction:column; gap:12px;"></div>
                    </div>
                </div>

                <!-- New Revision Input -->
                <div id="revisionInputArea" style="display:none; margin-top:10px; padding:24px; background:rgba(239, 68, 68, 0.05); border:1px solid rgba(239, 68, 68, 0.1); border-radius:24px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                        <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-xl flex items-center justify-center text-red-600 dark:text-red-400">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:900; color:#ef4444; margin:0;">Request Revisions</h3>
                            <p style="font-size:12px; color:#ef4444; font-weight:600; margin:0; opacity:0.8;">The task will be sent back to the Writer</p>
                        </div>
                    </div>
                    <form id="revisionsForm" method="POST">
                        @csrf
                        <textarea name="revision_instructions" required placeholder="Describe specifically what needs to be fixed..." style="width:100%; height:160px; padding:16px; border-radius:16px; border:1.5px solid rgba(239, 68, 68, 0.2); font-size:14px; resize:none; font-family:inherit; margin-bottom:20px; display:block; outline:none; background:var(--color-bg-primary); color:var(--color-text-primary); transition:all 0.2s;"></textarea>
                        <div style="display:flex; justify-content:flex-end; gap:12px;">
                            <button type="button" class="cd-btn cd-btn-outline" onclick="toggleRevisionInput(false)" style="padding:12px 24px;">Cancel</button>
                            <button type="submit" class="cd-btn" style="background:#ef4444; color:#fff; border:none; padding:12px 32px; font-weight:800; font-size:14px; border-radius:14px; cursor:pointer; shadow:0 10px 20px rgba(239,68,68,0.2);">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="cd-modal-footer">
                <button class="cd-btn cd-btn-outline" onclick="closeTaskModal()">Close</button>
                <button id="markReadyBtn" class="cd-btn cd-btn-outline" style="display:none; transition:all 0.2s;" onclick="toggleReadyStatus()"></button>
                <form id="submitStageForm" method="POST" enctype="multipart/form-data" style="display:none; align-items:center; gap:12px;">
                    @csrf
                    <button type="submit" name="action" value="save_only" class="cd-btn cd-btn-outline" id="saveContentBtn" style="color:#0055D4; border-color:#0055D4; display:none;">Save Content</button>
                    <button type="submit" name="action" value="submit" class="cd-btn cd-btn-primary" id="submitStageBtn">Submit to Next</button>
                </form>
                <button id="showRevisionBtn" type="button" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; display:none;" onclick="toggleRevisionInput(true)">Request Revisions</button>
                <button id="modalDeleteBtn" type="button" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; display:none;" onclick="deleteTaskFromModal()">Delete</button>
                <a id="modalEditBtn" href="#" class="cd-btn cd-btn-outline">Edit Full Details</a>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form for Modal -->
    <form id="deleteTaskForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Centered Error Modal -->
    <div id="taskErrorModalOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999999; align-items:center; justify-content:center; padding:20px; text-align:center; transition:opacity 0.3s ease; opacity:0;">
        <div id="taskErrorModalBox" style="background:var(--color-bg-primary); border-radius:24px; padding:32px; max-width:400px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); transform:scale(0.95); transition:transform 0.3s ease, opacity 0.3s ease; opacity:0;">
            <div style="width:64px; height:64px; border-radius:16px; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h3 style="font-size:20px; font-weight:900; color:var(--color-text-primary); margin-bottom:12px;">Alert</h3>
            <p id="taskErrorModalMessage" style="font-size:14px; font-weight:500; color:var(--color-text-secondary); line-height:1.6; margin-bottom:32px;"></p>
            <button onclick="hideErrorModal()" style="background:#0f172a; color:#fff; border:none; width:100%; padding:14px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:0 10px 20px rgba(15,23,42,0.2);">Got it</button>
        </div>
    </div>

    <script>
        const AUTH_USER_ID = {{ auth()->id() }};
        const AUTH_USER_ROLE = "{{ auth()->user()->role }}";
        const WORKFLOW_STAGES = @json($stages);
        let currentTaskData = null;

        function openTaskModal(task) {
            try {
                console.log('Opening Task Modal for:', task);
                currentTaskData = task;
                const overlay = document.getElementById('taskModalOverlay');
                const modal = overlay.querySelector('.cd-modal');
                
                document.getElementById('modalTaskTitle').textContent = task.title || 'Untitled';
                const ptEl = document.getElementById('modalSubtaskType');
                ptEl.textContent = task.subtask_type || 'Standard';
                
                const colors = task.subtask_type_colors || {bg:'#f1f5f9', text:'#475569', border:'#e2e8f0'};
                ptEl.style.background = colors.bg;
                ptEl.style.color = colors.text;
                ptEl.style.borderColor = colors.border;

                document.getElementById('modalNotes').value = task.notes || '';
                document.getElementById('modalConcept').value = task.concept || '';
                document.getElementById('modalCaption').value = task.caption || '';
                document.getElementById('modalSubtaskCopy').value = task.subtask_copy || task.post_copy || '';
                document.getElementById('modalStage').textContent = task.approval_stage || 'Unknown';

                let refHtml = '';
                if (task.reference_file) {
                    refHtml = `
                        <a href="${task.reference_file}" target="_blank" style="display:inline-block; text-decoration:none;">
                            <img src="${task.reference_file}" style="width:100%; max-width:200px; height:auto; border-radius:12px; border:1px solid var(--color-border-primary); margin-bottom:8px;">
                            <span style="display:block; font-size:10px; font-weight:800; color:#0055D4; text-transform:uppercase;">View Reference Image</span>
                        </a>`;
                } else if (task.reference) {
                    refHtml = `<a href="${task.reference}" target="_blank" class="ref-chip">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Visit Link
                    </a>`;
                } else {
                    refHtml = '<span style="color:#94a3b8; font-size:13px; font-weight:500;">No reference provided</span>';
                }
                document.getElementById('modalReference').innerHTML = refHtml;
                
                document.getElementById('modalEditBtn').href = `/deliverables/${task.id}/edit`;
                document.getElementById('revisionsForm').action = `/deliverables/${task.id}/revisions`;
                document.getElementById('submitStageForm').action = `/deliverables/${task.id}/submit`;

                // Team Grid
                const teamGrid = document.getElementById('modalTeamGrid');
                teamGrid.innerHTML = '';
                if (task.associates) {
                    const roles = [
                        {key: 'writer', label: 'Writer'},
                        {key: 'approver', label: 'Approver'},
                        {key: 'brand_manager', label: 'Brand Manager'},
                        {key: 'coordinator', label: 'Coordinator'},
                        {key: 'designer', label: 'Designer'}
                    ];
                    
                    roles.forEach(role => {
                        const name = task.associates[role.key] || 'None';
                        const item = document.createElement('div');
                        item.style.cssText = 'display:flex; align-items:center; gap:10px; padding:10px; background:#f8fafc; border-radius:12px; border:1px solid #f1f5f9;';
                        item.innerHTML = `
                            <div style="width:30px; height:30px; border-radius:50%; background:#e0e7ff; color:#4338ca; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0; border:1.5px solid #c7d2fe;">
                                ${name !== 'None' ? name.charAt(0) : '?'}
                            </div>
                            <div style="overflow:hidden;">
                                <div style="font-size:9px; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.02em; margin-bottom:1px;">${role.label}</div>
                                <div style="font-size:12px; font-weight:700; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${name}</div>
                            </div>
                        `;
                        teamGrid.appendChild(item);
                    });
                }
                
                let finalHtml = '';
                if (task.final_designs) {
                    const isImage = /\.(jpg|jpeg|png|gif|webp|svg)/i.test(task.final_designs);
                    if (isImage) {
                        finalHtml += `
                            <a href="${task.final_designs}" target="_blank" style="display:inline-block; margin-right:12px; text-decoration:none;">
                                <img src="${task.final_designs}" class="task-thumbnail" alt="Final Design">
                                <span style="display:block; font-size:10px; font-weight:800; color:#10b981; text-transform:uppercase; margin-top:6px; text-align:center;">View Full Size</span>
                            </a>`;
                    } else {
                        finalHtml += `<a href="${task.final_designs}" target="_blank" style="color:#10b981; margin-right:12px; font-weight:700;">View Deliverable</a>`;
                    }
                }
                if (task.final_designs_link) {
                    finalHtml += `
                        <a href="${task.final_designs_link}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; padding:10px 16px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.2); border-radius:10px; color:#10b981; text-decoration:none; vertical-align:top;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span style="font-size:11px; font-weight:800; text-transform:uppercase;">External Link</span>
                        </a>`;
                }
                document.getElementById('modalFinal').innerHTML = finalHtml || '<span style="color:#94a3b8; font-size:13px; font-weight:500;">Pending Delivery</span>';

                // History
                const appBox = document.getElementById('modalApprovalsBox');
                const appHistory = document.getElementById('modalApprovalHistory');
                appHistory.innerHTML = '';
                if (task.approvals_history && task.approvals_history.length > 0) {
                    task.approvals_history.forEach(app => {
                        const dateStr = new Date(app.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const row = document.createElement('div');
                        row.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:rgba(16, 185, 129, 0.05); padding:10px 14px; border-radius:10px; border:1px solid rgba(16, 185, 129, 0.1);';
                        row.innerHTML = `
                            <div style="font-size:12px; font-weight:700; color:var(--color-text-primary);">${app.user ? app.user.name : 'Unknown'}</div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:10px; font-weight:800; color:#10b981; text-transform:uppercase; letter-spacing:0.05em; background:rgba(16, 185, 129, 0.1); padding:2px 8px; border-radius:6px;">${app.stage} Approved</span>
                                <span style="font-size:11px; color:#10b981; opacity:0.8; font-weight:600;">${dateStr}</span>
                            </div>
                        `;
                        appHistory.appendChild(row);
                    });
                    appBox.style.display = 'block';
                } else appBox.style.display = 'none';

                // Revision History
                const revBox = document.getElementById('modalHistoryBox');
                const revHistory = document.getElementById('modalRevisionHistory');
                revHistory.innerHTML = '';
                if (task.revisions_history && task.revisions_history.length > 0) {
                    task.revisions_history.forEach(rev => {
                        const dateStr = new Date(rev.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const row = document.createElement('div');
                        row.style.cssText = 'display:flex; flex-direction:column; gap:8px; background:rgba(239, 68, 68, 0.05); padding:12px; border-radius:12px; border:1px solid rgba(239, 68, 68, 0.1);';
                        
                        let fixedBadge = '';
                        if (rev.fixed_at) {
                            const fixedDate = new Date(rev.fixed_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            fixedBadge = `
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px; padding-top:8px; border-top:1px dashed rgba(239, 68, 68, 0.2);">
                                    <div style="display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:800; color:#10b981; text-transform:uppercase; background:rgba(16, 185, 129, 0.1); padding:4px 8px; border-radius:6px;">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        Fixed by ${rev.fixed_by_user ? rev.fixed_by_user.name : 'Unknown'}
                                    </div>
                                    <div style="font-size:10px; color:#10b981; opacity:0.8; font-weight:600;">${fixedDate}</div>
                                </div>`;
                        } else {
                            fixedBadge = `
                                <div style="margin-top:4px; padding-top:8px; border-top:1px dashed rgba(239, 68, 68, 0.2);">
                                    <div style="display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:800; color:#ef4444; text-transform:uppercase; background:rgba(239, 68, 68, 0.1); padding:4px 8px; border-radius:6px;">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending Fix
                                    </div>
                                </div>`;
                        }

                        row.innerHTML = `
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size:11px; font-weight:800; color:#ef4444; text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                                    <span>Requested by ${rev.user ? rev.user.name : 'Unknown'}</span>
                                    <span style="color:#ef4444; opacity:0.5;">•</span>
                                    <span>${rev.stage_at_revision || 'Unknown Stage'}</span>
                                </div>
                                <div style="font-size:10px; color:#ef4444; opacity:0.8; font-weight:600;">${dateStr}</div>
                            </div>
                            <div style="font-size:13px; font-weight:500; color:var(--color-text-primary); line-height:1.5; padding:10px; background:var(--color-bg-primary); border-radius:8px; border:1px solid rgba(239,68,68,0.1); margin-top:4px;">${(rev.instructions || '').replace(/\\n/g, '<br>')}</div>
                            ${fixedBadge}
                        `;
                        revHistory.appendChild(row);
                    });
                    revBox.style.display = 'block';
                } else revBox.style.display = 'none';

                // Revision Alert
                const revAlert = document.getElementById('modalRevisionAlert');
                if (task.revision_instructions) {
                    document.getElementById('modalRevisionAlertText').textContent = task.revision_instructions;
                    revAlert.style.display = 'block';
                } else revAlert.style.display = 'none';

                // Workflow Tracker dots
                const currentStageIdx = WORKFLOW_STAGES.indexOf(task.approval_stage);
                document.querySelectorAll('.step-item').forEach((item, idx) => {
                    item.classList.remove('active', 'completed');
                    if (idx < currentStageIdx) item.classList.add('completed');
                    else if (idx === currentStageIdx) item.classList.add('active');
                });

                // Action Buttons
                const submitBtnForm = document.getElementById('submitStageForm');
                const showRevisionBtn = document.getElementById('showRevisionBtn');
                const saveContentBtn = document.getElementById('saveContentBtn');
                const apprArea = document.getElementById('approverSelectionArea');
                const bmArea = document.getElementById('brandManagerSelectionArea');
                const coordArea = document.getElementById('coordinatorSelectionArea');
                const dArea = document.getElementById('designerSelectionArea');
                const delArea = document.getElementById('designerDeliveryArea');
                
                submitBtnForm.style.display = 'none';
                submitBtnForm.style.alignItems = 'center';
                submitBtnForm.style.gap = '12px';
                showRevisionBtn.style.display = 'none';
                saveContentBtn.style.display = 'none';
                apprArea.style.display = 'none';
                bmArea.style.display = 'none';
                coordArea.style.display = 'none';
                dArea.style.display = 'none';
                delArea.style.display = 'none';

                // Normalize role for comparison
                const rawRole = AUTH_USER_ROLE;
                const userRole = rawRole.toLowerCase().replace(/\s+/g, '');
                const isAdmin = userRole === 'admin';
                const stage = task.approval_stage;

                const canAct = isAdmin ||
                    (stage === 'Writer' && userRole === 'writer') ||
                    (stage === 'Approver' && userRole === 'approver') ||
                    (stage === 'Brand Manager' && userRole === 'brandmanager') ||
                    (stage === 'Coordinator' && (userRole === 'coordinator' || userRole === 'trafficcoordinator')) ||
                    (stage === 'Designer' && userRole === 'designer') ||
                    (stage === 'Final Approval' && (userRole === 'brandmanager' || userRole === 'admin')) ||
                    (stage === 'AM/BD' && userRole === 'brandmanager') ||
                    (stage === 'Assignee' && (userRole === 'writer' || userRole === 'assignee'));

                if (canAct) {
                    submitBtnForm.style.display = 'flex';
                    const nextBtn = document.getElementById('submitStageBtn');
                    const isLastStage = WORKFLOW_STAGES.indexOf(stage) >= WORKFLOW_STAGES.length - 2;
                    
                    if (stage === 'Designer') nextBtn.textContent = 'Request for Approval';
                    else nextBtn.textContent = isLastStage ? 'Approve & Close' : 'Submit to Next';

                    if (stage === 'Writer' || stage === 'Assignee') apprArea.style.display = 'block';
                    if (stage === 'Approver') bmArea.style.display = 'block';
                    if (stage === 'Brand Manager' || stage === 'AM/BD') coordArea.style.display = 'block';
                    if (stage === 'Coordinator') dArea.style.display = 'block';
                    if (stage === 'Designer') delArea.style.display = 'block';
                }

                // Edit Permissions
                const isAssignedWriter = AUTH_USER_ID == task.writer_id;
                const isAssignedDesigner = AUTH_USER_ID == task.designer_id;
                const hasWriterRole = userRole === 'writer' || userRole === 'assignee';
                const hasDesignerRole = userRole === 'designer';
                
                const writerEditPermission = isAssignedWriter || (hasWriterRole && !task.writer_id);
                const designerEditPermission = isAssignedDesigner || (hasDesignerRole && !task.designer_id);

                document.getElementById('modalNotes').readOnly = !writerEditPermission;
                document.getElementById('modalConcept').readOnly = !writerEditPermission;
                document.getElementById('modalCaption').readOnly = !writerEditPermission;
                document.getElementById('modalSubtaskCopy').readOnly = !writerEditPermission;
                
                // Reference Edit Area
                const refEditArea = document.getElementById('modalReferenceEditArea');
                if (refEditArea) {
                    refEditArea.style.display = writerEditPermission ? 'flex' : 'none';
                    document.getElementById('modalReferenceUrl').value = task.reference || '';
                }

                if ((writerEditPermission && (stage === 'Writer' || stage === 'Assignee')) || 
                    (designerEditPermission && stage === 'Designer') || isAdmin) {
                    submitBtnForm.style.display = 'flex';
                    saveContentBtn.style.display = 'block';
                }

                const isReviewStage = ['Approver', 'Brand Manager', 'Final Approval', 'AM/BD'].includes(stage);
                const isAuthorizedToReview = isAdmin || 
                    (stage === 'Approver' && userRole === 'approver') ||
                    ((stage === 'Brand Manager' || stage === 'Final Approval') && userRole === 'brandmanager');

                if (isReviewStage && isAuthorizedToReview) {
                    showRevisionBtn.style.display = 'block';
                }

                document.getElementById('modalDeleteBtn').style.display = 'block';

                const markReadyBtn = document.getElementById('markReadyBtn');
                markReadyBtn.style.display = 'none';
                if ((stage === 'Writer' || stage === 'Assignee') && (writerEditPermission || isAdmin)) {
                    markReadyBtn.style.display = 'block';
                    updateReadyButtonUI(task.is_ready);
                    markReadyBtn.onclick = () => toggleReadyStatus(task.id);
                }

                overlay.style.display = 'flex';
                setTimeout(() => { overlay.style.opacity = '1'; modal.classList.add('active'); }, 10);
            } catch (e) {
                console.error('Error in openTaskModal:', e);
                alert('Failed to open task details. Check console for details.');
            }
        }

        function updateReadyButtonUI(isReady) {
            const btn = document.getElementById('markReadyBtn');
            if (isReady) {
                btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:6px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg> Ready for Batch';
                btn.style.color = '#10b981';
                btn.style.borderColor = '#10b981';
                btn.style.background = 'rgba(16,185,129,0.05)';
            } else {
                btn.innerHTML = 'Mark as Ready';
                btn.style.color = '#64748b';
                btn.style.borderColor = '#e2e8f0';
                btn.style.background = 'transparent';
            }
        }

        async function toggleReadyStatus(taskId) {
            const btn = document.getElementById('markReadyBtn');
            const isReady = btn.innerText.includes('Ready');
            const newStatus = isReady ? 0 : 1;
            
            btn.disabled = true;
            btn.style.opacity = '0.5';

            try {
                const response = await fetch(`/deliverables/${taskId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        is_ready: newStatus,
                        // Include required fields for validation if any
                        project_id: currentTaskData.project_id,
                        title: currentTaskData.title,
                        status: currentTaskData.status,
                        task_type: currentTaskData.task_type,
                        progress_percent: currentTaskData.progress_percent
                    })
                });

                const data = await response.json();
                if (data.success) {
                    currentTaskData.is_ready = !!newStatus;
                    updateReadyButtonUI(newStatus);
                    // We need a way to update the board without a full reload ideally, 
                    // but for simplicity and layout consistency, a reload or a board update call is fine.
                    // For now, let's just use toast and encourage them to see the header update on reload or when they close modal.
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: `Task marked as ${newStatus ? 'Ready' : 'Not Ready'}`, type: 'success' } }));
                }
            } catch (error) {
                console.error(error);
            } finally {
                btn.disabled = false;
                btn.style.opacity = '1';
            }
        }

        function closeTaskModal(e) {
            if (e && e.target !== document.getElementById('taskModalOverlay')) return;
            const overlay = document.getElementById('taskModalOverlay');
            overlay.style.opacity = '0';
            overlay.querySelector('.cd-modal').classList.remove('active');
            setTimeout(() => { overlay.style.display = 'none'; toggleRevisionInput(false); }, 300);
        }

        function deleteTaskFromModal() {
            if (!currentTaskData) return;
            if (confirm('Are you sure you want to delete this deliverable? This action cannot be undone.')) {
                const form = document.getElementById('deleteTaskForm');
                form.action = `/deliverables/${currentTaskData.id}`;
                form.submit();
            }
        }

        function showConfirm(title, message) {
            return new Promise(resolve => {
                const el = document.createElement('div');
                el.style.cssText = 'position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);';
                el.innerHTML = `
                    <div style="background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:16px;padding:24px;width:100%;max-width:360px;box-shadow:0 25px 50px rgba(0,0,0,0.3);">
                        <div style="width:44px;height:44px;background:rgba(59,130,246,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                            <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 style="font-size:15px;font-weight:700;color:var(--color-text-primary);margin-bottom:6px;">${title}</h3>
                        <p style="font-size:13px;color:var(--color-text-secondary);margin-bottom:20px;">${message}</p>
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <button id="confirm-cancel" style="padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);cursor:pointer;">Cancel</button>
                            <button id="confirm-ok" style="padding:8px 20px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;">Confirm</button>
                        </div>
                    </div>`;
                document.body.appendChild(el);
                el.querySelector('#confirm-ok').onclick = () => { document.body.removeChild(el); resolve(true); };
                el.querySelector('#confirm-cancel').onclick = () => { document.body.removeChild(el); resolve(false); };
            });
        }

        function toggleSubtasks(e, taskId) {
            e.stopPropagation();
            const btn = document.getElementById(`toggle-btn-${taskId}`);
            const rows = document.querySelectorAll(`.subtask-of-${taskId}`);
            if (btn) btn.classList.toggle('active');
            rows.forEach(r => r.classList.toggle('collapsed'));
        }

        function toggleRevisionInput(show) {
            document.getElementById('revisionInputArea').style.display = show ? 'block' : 'none';
            document.querySelector('.detail-grid').style.display = show ? 'none' : 'grid';
            // Hide footer buttons when editing revision
            document.querySelector('.cd-modal-footer').style.display = show ? 'none' : 'flex';
        }

        async function submitBatch(e, taskId, nextStage) {
            e.stopPropagation();
            const confirmed = await showConfirm(`Advance entire batch to <strong>${nextStage}</strong>?`, 'This will move the parent and all subtasks forward in the workflow.');
            if (!confirmed) return;
            
            const btn = e.currentTarget;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            btn.disabled = true;

            // Collect Batch Data
            const batchData = {};
            document.querySelectorAll('.batch-field').forEach(field => {
                const id = field.getAttribute('data-task-id');
                const key = field.getAttribute('data-field');
                if (!batchData[id]) batchData[id] = {};
                batchData[id][key] = field.value;
            });

            try {
                const response = await fetch(`/deliverables/${taskId}/batch-submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ batch_data: batchData })
                });

                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error advancing batch', type: 'error' } }));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'An unexpected error occurred while communicating with the server.', type: 'error' } }));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Handle AJAX submission of the task stage form to prevent modal closing on validation error
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-save selects (like Designer assignment)
            document.querySelectorAll('.batch-field-select').forEach(select => {
                select.addEventListener('change', async function() {
                    const taskId = this.dataset.taskId;
                    const field = this.dataset.field;
                    const value = this.value;

                    this.style.opacity = '0.5';
                    this.disabled = true;

                    try {
                        const response = await fetch(`/deliverables/${taskId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'PUT',
                                [field]: value
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Assignee updated', type: 'success' } }));
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.style.opacity = '1';
                        this.disabled = false;
                    }
                });
            });

            const submitForm = document.getElementById('submitStageForm');
            if (submitForm) {
                submitForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const btn = e.submitter;
                    const actionValue = btn ? btn.value : 'submit';
                    
                    const submitForm = this;
                    const formData = new FormData(submitForm);
                    
                    // Manually append elements and check file sizes
                    let fileTooLarge = false;
                    const MAX_SIZE = 2 * 1024 * 1024; // 2MB default PHP limit

                    document.querySelectorAll(`[form="${submitForm.id}"]`).forEach(el => {
                        if (el.name && !formData.has(el.name)) {
                            if (el.type === 'file') {
                                if (el.files.length > 0) {
                                    for (let i = 0; i < el.files.length; i++) {
                                        if (el.files[i].size > MAX_SIZE) fileTooLarge = true;
                                        formData.append(el.name, el.files[i]);
                                    }
                                }
                            } else if (el.type === 'checkbox' || el.type === 'radio') {
                                if (el.checked) formData.append(el.name, el.value);
                            } else {
                                formData.append(el.name, el.value);
                            }
                        }
                    });

                    if (fileTooLarge) {
                        showErrorModal('The file you are trying to upload is too large. The limit is 2MB. Please compress the image or increase the server limits.');
                        return;
                    }

                    if (btn && btn.name) {
                        formData.set(btn.name, btn.value);
                    }
                    
                    const originalText = btn ? btn.innerHTML : 'Submit';
                    if (btn) {
                        btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:8px;"><svg class="animate-spin" style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...</span>';
                        btn.disabled = true;
                    }

                    const formActionUrl = this.getAttribute('action');

                    try {
                        const response = await fetch(formActionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        
                        const contentType = response.headers.get("content-type");
                        if (contentType && contentType.indexOf("application/json") !== -1) {
                            const result = await response.json();
                            if (!response.ok || !result.success) {
                                showErrorModal(result.message || 'An error occurred.');
                            } else {
                                window.location.reload();
                            }
                        } else {
                            const errorText = await response.text();
                            console.error('Server returned non-JSON response:', errorText);
                            if (response.status === 419) {
                                showErrorModal('Session expired. Please refresh the page and try again.');
                            } else {
                                showErrorModal('Server Error: ' + response.status + '. Check console for details.');
                            }
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        showErrorModal('An unexpected error occurred while communicating with the server.');
                    } finally {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                });
            }
        });

        function showErrorModal(message) {
            // Convert markdown-style **bolding** to <b> or styled span if needed, or just insert
            let formattedMessage = message.replace(/\*\*(.*?)\*\*/g, '<span style="font-weight:900; color:var(--color-text-primary);">$1</span>');
            document.getElementById('taskErrorModalMessage').innerHTML = formattedMessage;
            const overlay = document.getElementById('taskErrorModalOverlay');
            const box = document.getElementById('taskErrorModalBox');
            
            overlay.style.display = 'flex';
            setTimeout(() => {
                overlay.style.opacity = '1';
                box.style.opacity = '1';
                box.style.transform = 'scale(1)';
            }, 10);
        }
        
        function hideErrorModal() {
            const overlay = document.getElementById('taskErrorModalOverlay');
            const box = document.getElementById('taskErrorModalBox');
            
            overlay.style.opacity = '0';
            box.style.opacity = '0';
            box.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 300);
        }

        function deleteTaskFromModal() {
            if (confirm('Delete Deliverable: This action cannot be undone. Proceed?')) {
                const modal = document.getElementById('taskModalOverlay');
                const taskId = document.getElementById('modalEditBtn').href.split('/').slice(-2, -1)[0];
                const form = document.getElementById('deleteTaskForm');
                form.action = `/deliverables/${taskId}`;
                form.submit();
            }
        }
    </script>

    <!-- Batch Action Modal -->
    <div id="batchModalOverlay" class="cd-modal-overlay" onclick="closeBatchModal(event)">
        <div class="cd-modal" style="max-width: 450px;" onclick="event.stopPropagation()">
            <div class="cd-modal-header" style="background: var(--color-bg-secondary); padding: 24px 32px;">
                <div>
                    <h2 id="batchModalTitle" style="font-size: 18px; font-weight: 900; color: var(--color-text-primary);">Submit Batch</h2>
                    <p id="batchModalSubtitle" style="font-size: 11px; color: var(--color-text-secondary); margin: 4px 0 0; font-weight: 600;"></p>
                </div>
                <button onclick="closeBatchModal()" style="background:none; border:none; color:var(--color-text-secondary); cursor:pointer;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="cd-modal-body" style="padding: 32px;">
                <div id="batchStakeholderGroup" style="display: none;">
                    <label id="batchStakeholderLabel" class="detail-label">Assign Next Stakeholder</label>
                    <div style="position: relative;">
                        <select id="batchStakeholderSelect" class="batch-select">
                            <!-- Options populated via JS -->
                        </select>
                        <div style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--color-text-secondary);">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <p id="batchStickyHint" style="font-size: 10px; color: #10b981; font-weight: 700; margin-top: 8px; display: none;">
                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 2px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        This person will handle all remaining approval steps for this batch.
                    </p>
                </div>
                <!-- Revision Group -->
                <div id="batchRevisionGroup" style="display: none;">
                    <label class="detail-label" style="color:#ef4444;">Revision Instructions</label>
                    <textarea id="batchRevisionInstructions" placeholder="Describe what needs to be fixed in this entire batch..." style="width:100%; height:120px; padding:12px; border-radius:12px; border:1.5px solid rgba(239, 68, 68, 0.2); font-size:13px; outline:none; background:var(--color-bg-primary); color:var(--color-text-primary); resize:none;"></textarea>
                    <p style="font-size: 10px; color: #ef4444; font-weight: 700; margin-top: 8px;">
                        The entire batch will be sent back to the Writer.
                    </p>
                </div>
                <div id="batchGenericConfirm" style="font-size:13px; font-weight:600; color:var(--color-text-secondary); line-height:1.6;">
                    Are you sure you want to advance this entire batch?
                </div>
            </div>
            <div class="cd-modal-footer">
                <button onclick="closeBatchModal()" class="cd-btn cd-btn-outline">Cancel</button>
                <button id="batchConfirmBtn" onclick="executeBatchAction()" class="cd-btn cd-btn-primary">Confirm & Submit</button>
            </div>
        </div>
    </div>

    <!-- Hidden data for JS lookup -->
    <script id="approvers-data" type="application/json">{!! json_encode($approvers) !!}</script>
    <script id="managers-data" type="application/json">{!! json_encode($brandManagers) !!}</script>
    <script id="coordinators-data" type="application/json">{!! json_encode($coordinators) !!}</script>
    <script id="designers-data" type="application/json">{!! json_encode($designers) !!}</script>

    <script>
        let currentBatchTask = null;
        let currentBatchNextStage = null;
        let currentBatchType = 'submit'; 
        let currentBatchStakeholders = {};

        function openBatchModal(e, taskId, nextStage, count, type = 'submit', stakeholders = {}) {
            e.stopPropagation();
            currentBatchTask = taskId;
            currentBatchNextStage = nextStage;
            currentBatchType = type;
            currentBatchStakeholders = stakeholders;

            const isIndividual = (count === 1);
            const itemType = isIndividual ? 'Deliverable' : 'Batch';

            const modal = document.getElementById('batchModalOverlay');
            const title = document.getElementById('batchModalTitle');
            const subtitle = document.getElementById('batchModalSubtitle');
            const stakeholderGroup = document.getElementById('batchStakeholderGroup');
            const stakeholderSelect = document.getElementById('batchStakeholderSelect');
            const stakeholderLabel = document.getElementById('batchStakeholderLabel');
            const revisionGroup = document.getElementById('batchRevisionGroup');
            const genericConfirm = document.getElementById('batchGenericConfirm');
            const stickyHint = document.getElementById('batchStickyHint');
            const confirmBtn = document.getElementById('batchConfirmBtn');

            if (type === 'revision') {
                title.textContent = `${itemType} Revision Request`;
                subtitle.textContent = isIndividual ? `Moving this task back for revisions.` : `Moving ${count} tasks back for revisions.`;
                stakeholderGroup.style.display = 'none';
                revisionGroup.style.display = 'block';
                genericConfirm.style.display = 'none';
                confirmBtn.textContent = 'Request Revisions';
                confirmBtn.style.background = '#ef4444';
                
                // Update specific instruction text
                document.querySelector('#batchRevisionGroup textarea').placeholder = isIndividual ? "Describe what needs to be fixed..." : "Describe what needs to be fixed in this entire batch...";
                document.querySelector('#batchRevisionGroup p').textContent = isIndividual ? "The task will be sent back to the Writer." : "The entire batch will be sent back to the Writer.";
            } else {
                title.textContent = `${itemType} Submission`;
                subtitle.textContent = isIndividual ? `Submitting this task to the ${nextStage} stage.` : `Preparing to submit ${count} tasks to ${nextStage} stage.`;
                stakeholderGroup.style.display = 'none';
                revisionGroup.style.display = 'none';
                genericConfirm.style.display = 'block';
                genericConfirm.textContent = isIndividual ? `Are you sure you want to advance this task?` : `Are you sure you want to advance this entire batch?`;
                confirmBtn.textContent = 'Confirm & Submit';
                confirmBtn.style.background = ''; // default
            }

            stickyHint.style.display = 'none';

            // Role assignment logic
            let roleToFill = null;
            let roleUsers = [];
            let label = "";

            if (nextStage === 'Approver') {
                roleToFill = 'approver_id';
                roleUsers = JSON.parse(document.getElementById('approvers-data').textContent);
                label = "Select Approver";
                stickyHint.style.display = 'block'; // As per user request "till its end"
            } else if (nextStage === 'Brand Manager' || nextStage === 'Final Approval') {
                roleToFill = 'brand_manager_id';
                roleUsers = JSON.parse(document.getElementById('managers-data').textContent);
                label = "Select Brand Manager";
            } else if (nextStage === 'Coordinator' || nextStage === 'Traffic Coordinator') {
                roleToFill = 'coordinator_id';
                roleUsers = JSON.parse(document.getElementById('coordinators-data').textContent);
                label = "Select Coordinator";
            } else if (nextStage === 'Designer') {
                // If moving from Coordinator to Designer, we don't need a single stakeholder selection 
                // IF we are using individual assignments.
                title.textContent = `Batch Designer Assignment`;
                subtitle.textContent = `Advancing ${count} tasks to individually assigned designers.`;
                label = "Individual Assignments Confirmed";
                // We'll show a message instead of a select in this special case
                stakeholderGroup.style.display = 'block';
                stakeholderLabel.textContent = "Workflow Notice";
                stakeholderSelect.style.display = 'none';
                genericConfirm.innerHTML = `This will advance all ${count} tasks to the <strong>Designer stage</strong> using their specific assignments.`;
                genericConfirm.style.display = 'block';
                roleToFill = 'advance_individually'; // Signal for execute
            }

            if (roleToFill && type === 'submit') {
                stakeholderGroup.style.display = 'block';
                genericConfirm.style.display = 'none';
                stakeholderLabel.textContent = label;
                
                stakeholderSelect.innerHTML = '<option value="">-- Choose User --</option>';
                
                // Get pre-selection ID (task level first, then fallback to project)
                let preSelectId = null;
                if (nextStage === 'Approver') {
                    preSelectId = stakeholders.approver || {!! json_encode($project->approver_id) !!};
                } else if (nextStage === 'Brand Manager' || nextStage === 'Final Approval') {
                    preSelectId = stakeholders.brand_manager || {!! json_encode($project->brand_manager_id) !!} || {!! json_encode($project->lead_id) !!};
                } else if (nextStage === 'Coordinator') {
                    preSelectId = stakeholders.coordinator || {!! json_encode($project->coordinator_id) !!};
                } else if (nextStage === 'Designer') {
                    preSelectId = stakeholders.designer || {!! json_encode($project->designer_id) !!};
                }

                if (stakeholderSelect.style.display !== 'none') {
                    roleUsers.forEach(user => {
                        const opt = document.createElement('option');
                        opt.value = user.id;
                        opt.textContent = user.name;
                        if (user.id == preSelectId) opt.selected = true;
                        stakeholderSelect.appendChild(opt);
                    });
                }
            }

            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.cd-modal').classList.add('active');
            }, 10);
        }

        function closeBatchModal(e) {
            if (e && e.target !== document.getElementById('batchModalOverlay')) return;
            const modal = document.getElementById('batchModalOverlay');
            modal.style.opacity = '0';
            modal.querySelector('.cd-modal').classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        async function executeBatchAction() {
            if (currentBatchType === 'revision') {
                await executeBatchRevision();
            } else {
                await executeBatchSubmit();
            }
        }

        async function executeBatchRevision() {
            const btn = document.getElementById('batchConfirmBtn');
            const originalText = btn.innerHTML;
            const instructions = document.getElementById('batchRevisionInstructions').value;

            if (!instructions) {
                alert('Please provide revision instructions.');
                return;
            }

            btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...';
            btn.disabled = true;

            try {
                const response = await fetch(`/deliverables/${currentBatchTask}/batch-revisions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ revision_instructions: instructions })
                });

                const contentType = response.headers.get("content-type");
                let data;
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.error("Non-JSON response:", text);
                    data = { success: false, message: "Server returned an invalid response. Please check the logs." };
                }

                if (data.success) {
                    window.location.reload();
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error requesting revisions', type: 'error' } }));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error("Batch revision error:", error);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'An unexpected error occurred while communicating with the server.', type: 'error' } }));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        async function executeBatchSubmit() {
            const btn = document.getElementById('batchConfirmBtn');
            const originalText = btn.innerHTML;
            const stakeholderSelect = document.getElementById('batchStakeholderSelect');
            
            const stakeholderRole = document.getElementById('batchStakeholderGroup').style.display !== 'none';
            let assigneeId = null;
            let roleField = null;

            if (stakeholderRole && stakeholderSelect.style.display !== 'none') {
                assigneeId = stakeholderSelect.value;
                if (!assigneeId) {
                    alert('Please select a stakeholder for the next stage.');
                    return;
                }
                // Determine which role field to send
                if (currentBatchNextStage === 'Approver') roleField = 'approver_id';
                else if (currentBatchNextStage === 'Brand Manager' || currentBatchNextStage === 'Final Approval') roleField = 'brand_manager_id';
                else if (currentBatchNextStage === 'Coordinator' || currentBatchNextStage === 'Traffic Coordinator') roleField = 'coordinator_id';
                else if (currentBatchNextStage === 'Designer') roleField = 'designer_id';
            }

            btn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Submitting...';
            btn.disabled = true;

            // Collect In-line Data
            const batchData = {};
            document.querySelectorAll('.batch-field').forEach(field => {
                const id = field.getAttribute('data-task-id');
                const key = field.getAttribute('data-field');
                if (!batchData[id]) batchData[id] = {};
                batchData[id][key] = field.value;
            });

            const payload = { 
                batch_data: batchData 
            };
            if (roleField && assigneeId) {
                payload[roleField] = assigneeId;
            }

            try {
                const response = await fetch(`/deliverables/${currentBatchTask}/batch-submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const contentType = response.headers.get("content-type");
                let data;
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    console.error("Non-JSON response:", text);
                    data = { success: false, message: "Server returned an invalid response. Please check the logs." };
                }

                if (data.success) {
                    window.location.reload();
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error advancing batch', type: 'error' } }));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error("Batch submit error:", error);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'An unexpected error occurred while communicating with the server.', type: 'error' } }));
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    </script>

    <!-- Cell Editor Modal -->
    <div id="cellEditorOverlay" class="cd-modal-overlay" onclick="closeCellEditor(event)" style="z-index: 1000;">
        <div class="cd-modal" style="max-width: 600px;" onclick="event.stopPropagation()">
            <div class="cd-modal-header" style="padding: 20px 32px; background: var(--color-bg-secondary);">
                <div>
                    <h2 id="cellEditorTitle" style="font-size: 16px; font-weight: 900; color: var(--color-text-primary); margin: 0;">Edit Field</h2>
                    <p id="cellEditorSubtitle" style="font-size: 10px; color: var(--color-text-secondary); margin: 4px 0 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;"></p>
                </div>
                <button onclick="closeCellEditor()" style="background:none; border:none; color:var(--color-text-secondary); cursor:pointer;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="cd-modal-body" style="padding: 24px;">
                <textarea id="cellEditorTextarea" style="width: 100%; min-height: 300px; padding: 20px; border-radius: 16px; border: 1.5px solid var(--color-border-primary); background: var(--color-bg-primary); color: var(--color-text-primary); font-size: 14px; line-height: 1.6; font-family: inherit; outline: none; transition: border-color 0.2s; resize: vertical;"></textarea>
            </div>
            <div class="cd-modal-footer" style="padding: 16px 24px;">
                <button onclick="closeCellEditor()" class="cd-btn cd-btn-outline">Cancel</button>
                <button onclick="saveCellEditor()" class="cd-btn cd-btn-primary" style="padding: 12px 32px;">Apply Changes</button>
            </div>
        </div>
    </div>

    <script>
        let activeTextarea = null;

        function openCellEditor(e) {
            e.stopPropagation();
            activeTextarea = e.target;
            
            const fieldName = activeTextarea.getAttribute('data-field');
            const titleEl = activeTextarea.closest('tr').querySelector('.deliverable-name-cell span');
            const taskTitle = titleEl ? titleEl.textContent : 'Deliverable Field';
            
            document.getElementById('cellEditorTitle').textContent = `Edit ${fieldName.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`;
            document.getElementById('cellEditorSubtitle').textContent = taskTitle;
            document.getElementById('cellEditorTextarea').value = activeTextarea.value;
            
            const overlay = document.getElementById('cellEditorOverlay');
            overlay.style.display = 'flex';
            setTimeout(() => {
                overlay.style.opacity = '1';
                overlay.querySelector('.cd-modal').classList.add('active');
                document.getElementById('cellEditorTextarea').focus();
            }, 10);
        }

        function saveCellEditor() {
            if (activeTextarea) {
                activeTextarea.value = document.getElementById('cellEditorTextarea').value;
                // Trigger input event to update auto-height if applicable
                activeTextarea.dispatchEvent(new Event('input'));
                
                // Visual feedback on the original cell
                activeTextarea.style.background = 'rgba(16, 185, 129, 0.05)';
                setTimeout(() => {
                    activeTextarea.style.background = '';
                }, 1000);
            }
            closeCellEditor();
        }

        function closeCellEditor(e) {
            if (e && e.target !== document.getElementById('cellEditorOverlay')) return;
            const overlay = document.getElementById('cellEditorOverlay');
            overlay.style.opacity = '0';
            overlay.querySelector('.cd-modal').classList.remove('active');
            setTimeout(() => {
                overlay.style.display = 'none';
                activeTextarea = null;
            }, 300);
        }
    </script>
</x-layout>
