<x-layout title="{{ $deliverable->title }}">
    @php
        $isAdmin = auth()->user()->isAdmin();
        $userRole = strtolower(str_replace(' ', '', auth()->user()->role));
        $currentUserId = auth()->id();
    @endphp

    <style>
        /* Content Deliverables Table Styles */
        .cd-table-wrap { background:var(--color-bg-primary); border-radius:16px; border:1px solid var(--color-border-primary); box-shadow:0 8px 30px rgba(0,0,0,0.04); overflow:hidden; transition: background 0.3s, border-color 0.3s; }
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
        .cd-table td { padding:14px 10px; vertical-align:middle; font-size:12px; color:var(--color-text-primary); }
        .subtask-pill { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:8px; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.15em; border:1px solid; }
        .subtask-copy-box { background:var(--color-bg-secondary); border-radius:12px; padding:10px 14px; font-size:12px; color:var(--color-text-secondary); font-weight:500; line-height:1.5; border:1px solid var(--color-border-primary); max-height:70px; overflow:hidden; }
        .ref-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; background:rgba(37,99,235,0.1); border:1px solid rgba(37,99,235,0.2); border-radius:10px; font-size:11px; font-weight:700; color:#2563eb; text-decoration:none; transition:all 0.15s; }
        .ref-chip:hover { background:rgba(37,99,235,0.15); }

        /* Modal Styles */
        .cd-modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(8px); display:none; justify-content:center; align-items:center; z-index:9999; opacity:0; transition:all 0.3s ease; }
        .cd-modal { background:var(--color-bg-primary); width:90%; max-width:800px; max-height:92vh; border-radius:32px; box-shadow:0 40px 100px rgba(0,0,0,0.2); overflow:hidden; transform:scale(0.95); transition:all 0.3s ease; position:relative; display:flex; flex-direction:column; }
        .cd-modal.active { transform:scale(1); }
        .cd-modal-header { padding:32px; border-bottom:1px solid var(--color-border-primary); display:flex; justify-content:space-between; align-items:flex-start; flex-shrink:0; }
        .cd-modal-body { padding:32px; overflow-y:auto; flex:1; min-height:0; }
        .cd-modal-footer { padding:24px 32px; background:var(--color-bg-secondary); border-top:1px solid var(--color-border-primary); display:flex; justify-content:flex-end; gap:12px; flex-shrink:0; }
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
        .cd-table tbody tr:hover { background:var(--color-bg-secondary); }
        .subtask-row { background:var(--color-bg-secondary); }
        .subtask-row td:first-child { padding-left:44px; position:relative; }
        .subtask-row td:first-child::before { content:''; position:absolute; left:26px; top:50%; width:10px; height:10px; border-left:2px solid var(--color-border-primary); border-bottom:2px solid var(--color-border-primary); transform:translateY(-50%); border-radius:0 0 0 3px; }
        .subtask-row.collapsed { display: none; }
        .subtask-toggle { 
            display: inline-flex; align-items: center; justify-content: center;
            padding: 4px 8px; border-radius: 6px; border: 1.5px solid rgba(0, 85, 212, 0.25);
            background: rgba(0, 85, 212, 0.05); color: #0055D4; cursor: pointer; margin-right: 8px;
            font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
            transition: all 0.15s;
        }
        .subtask-toggle:hover { background: rgba(0, 85, 212, 0.12); color: #0044aa; border-color: rgba(0, 85, 212, 0.45); }
        .subtask-toggle:not(.active) {
            background: rgba(16, 185, 129, 0.08); color: #10b981; border-color: rgba(16, 185, 129, 0.25);
        }
        .subtask-toggle:not(.active):hover {
            background: rgba(16, 185, 129, 0.14); color: #059669; border-color: rgba(16, 185, 129, 0.45);
        }
        .subtask-toggle::after { content: 'Collapse'; }
        .subtask-toggle:not(.active)::after { content: 'Expand'; }
        .deliverable-name-cell { display: flex; align-items: center; }

        /* Workflow Steps Tracker */
        .workflow-steps { display: flex; justify-content: space-between; position: relative; margin-bottom: 24px; margin-top: 16px; padding: 0 10px; gap: 4px; }
        .workflow-steps::before { content: ''; position: absolute; top: 15px; left: 40px; right: 40px; height: 2px; background: rgba(100, 116, 139, 0.25); z-index: 0; }
        .step-item { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; flex: 1; min-width: 0; }
        .step-dot { width: 30px; height: 30px; border-radius: 50%; background: var(--color-bg-primary); border: 2px solid rgba(100, 116, 139, 0.3); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; color: rgba(100, 116, 139, 0.8); transition: all 0.3s; flex-shrink: 0; }
        .step-label { font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.03em; color: var(--color-text-secondary); text-align: center; line-height: 1.3; word-break: break-word; width: 100%; }
        
        .step-item.active .step-dot { border-color: #0055D4; color: #0055D4; background: rgba(0,85,212,0.1); box-shadow: 0 0 0 4px rgba(0,85,212,0.1); }
        .step-item.active .step-label { color: var(--color-text-primary); }
        .step-item.completed .step-dot { background: #10b981; border-color: #10b981; color: #fff; }
        .step-item.completed .step-label { color: var(--color-text-primary); }

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
        .rtb-ref-preview { display: block; width: 80px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--color-border-primary); cursor: pointer; transition: all 0.2s; }
        .rtb-ref-preview:hover { transform: scale(1.05); border-color: #0055D4; box-shadow: 0 4px 10px rgba(0,85,212,0.15); }

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
            white-space: normal;
            text-align: center;
            line-height: 1.2;
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
        .stage-pill { display:inline-flex; align-items:center; justify-content:center; padding:3px 8px; border-radius:6px; font-size:10px; font-weight:700; white-space:normal; text-align:center; line-height:1.2; }
        .stage-writer    { background:rgba(14,165,233,0.1);  color:#0ea5e9;  border:1px solid rgba(14,165,233,0.2); }
        .stage-approver  { background:rgba(245,158,11,0.1);  color:#d97706;  border:1px solid rgba(245,158,11,0.2); }
        .stage-manager   { background:rgba(59,130,246,0.1);  color:#3b82f6;  border:1px solid rgba(59,130,246,0.2); }
        .stage-coordinator { background:rgba(99,102,241,0.1); color:#6366f1; border:1px solid rgba(99,102,241,0.2); }
        .stage-designer  { background:rgba(236,72,153,0.1);  color:#ec4899;  border:1px solid rgba(236,72,153,0.2); }
        .stage-final     { background:rgba(16,185,129,0.1);  color:#10b981;  border:1px solid rgba(16,185,129,0.2); }
        .stage-closed    { background:rgba(100,116,139,0.08); color:#64748b; border:1px solid rgba(100,116,139,0.15); }
        .stage-default   { background:rgba(59,130,246,0.08); color:#3b82f6;  border:1px solid rgba(59,130,246,0.15); }

        /* Quill Dark Mode Overrides & UI Integration */
        .ql-toolbar.ql-snow {
            border: 1px solid var(--color-border-primary) !important;
            border-bottom: 1px solid rgba(100, 116, 139, 0.15) !important;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            background: var(--color-bg-primary) !important;
            padding: 10px 12px;
        }
        .ql-container.ql-snow, 
        .detail-val-textarea.ql-container {
            border: 1px solid var(--color-border-primary) !important;
            border-top: none !important;
            border-top-left-radius: 0 !important;
            border-top-right-radius: 0 !important;
            border-bottom-left-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
            background: var(--color-bg-primary) !important;
            color: var(--color-text-primary);
            font-family: inherit;
            font-size: 13px;
            margin-top: 0 !important;
            height: auto !important; /* Fix CSS grid collapse overlap */
        }
        .ql-snow .ql-stroke { stroke: var(--color-text-secondary); }
        .ql-snow .ql-fill, .ql-snow .ql-stroke.ql-fill { fill: var(--color-text-secondary); }
        .ql-snow .ql-picker { color: var(--color-text-secondary); }
        .ql-snow .ql-picker-options { background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary); }
        .ql-snow .ql-picker-item:hover, .ql-snow .ql-picker-label:hover { color: var(--color-text-primary); }
        .ql-snow .ql-picker-label:hover .ql-stroke { stroke: var(--color-text-primary); }
        .ql-editor { padding: 12px; }
        .ql-editor.ql-blank::before { color: rgba(255, 255, 255, 0.3) !important; font-style: normal; }
        
        button.ql-active .ql-stroke { stroke: #0ea5e9 !important; }
        button.ql-active .ql-fill { fill: #0ea5e9 !important; }
        .ql-picker-label.ql-active { color: #0ea5e9 !important; }
        .ql-picker-label.ql-active .ql-stroke { stroke: #0ea5e9 !important; }

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
            white-space: normal;
            text-align: center;
            cursor: pointer;
            background: none;
            line-height: 1.2;
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
        /* History accordion */
        .hist-box { padding-top:0 !important; }
        .hist-toggle { width:100%; display:flex; justify-content:space-between; align-items:center; padding:12px 0; background:none; border:none; cursor:pointer; text-align:left; gap:8px; }
        .hist-chevron { color:var(--color-text-secondary); transition:transform 0.2s; flex-shrink:0; }
        .hist-toggle:hover .hist-chevron { color:var(--color-text-primary); }
        .hist-toggle.open .hist-chevron { transform:rotate(180deg); }
        .hist-count { display:inline-flex; align-items:center; justify-content:center; min-width:18px; height:18px; padding:0 5px; border-radius:20px; font-size:10px; font-weight:700; }
        .hist-count-green { background:rgba(16,185,129,0.1); color:#10b981; }
        .hist-count-red   { background:rgba(239,68,68,0.1);   color:#ef4444; }
        .hist-body { padding-bottom:8px; }

        /* Compact read-only text cell — click to expand */
        .cell-text {
            font-size: 11px;
            color: var(--color-text-secondary);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            cursor: pointer;
            max-height: 32px;
            transition: color 0.12s;
        }
        .cell-text:hover { color: var(--color-text-primary); }

        /* Text preview popup */
        #text-preview-modal { position:fixed; inset:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(6px); display:none; justify-content:center; align-items:center; z-index:10000; }
        #text-preview-inner { background:var(--color-bg-primary); width:90%; max-width:560px; border-radius:16px; border:1px solid var(--color-border-primary); box-shadow:0 24px 60px rgba(0,0,0,0.2); overflow:hidden; }
        #text-preview-inner .tp-header { padding:16px 20px; border-bottom:1px solid var(--color-border-primary); display:flex; justify-content:space-between; align-items:center; }
        #text-preview-inner .tp-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--color-text-secondary); }
        #text-preview-inner .tp-close { width:28px; height:28px; border-radius:7px; border:1px solid var(--color-border-primary); background:var(--color-bg-secondary); color:var(--color-text-secondary); cursor:pointer; display:flex; align-items:center; justify-content:center; }
        #text-preview-inner .tp-close:hover { color:var(--color-text-primary); background:var(--color-border-primary); }
        #text-preview-inner .tp-body { padding:20px; font-size:13px; font-weight:500; color:var(--color-text-primary); line-height:1.7; white-space:pre-wrap; max-height:60vh; overflow-y:auto; word-break:break-word; }

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

    <style>
        #taskModalOverlay.cd-modal-overlay, #taskModalOverlay { position: relative !important; display: block !important; opacity: 1 !important; z-index: 1 !important; background: transparent !important; backdrop-filter: none !important; padding: 0; }
        #taskModalOverlay .cd-modal { max-width: 100% !important; width: 100% !important; margin: 0; transform: none !important; box-shadow: none !important; background: transparent !important; border-radius: 0 !important; border: none !important; overflow: visible !important; }
        #taskModalOverlay .cd-modal-header { padding: 0 0 24px 0 !important; border-bottom: none !important; }
        #taskModalOverlay .cd-modal-body { padding: 0 !important; overflow: visible !important; height: auto !important; flex: none !important; }
        #modalTaskTitle { border: 1px solid var(--color-border-primary) !important; background: var(--color-bg-secondary) !important; }
    </style>

    <div class="container mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('projects.show', $deliverable->project_id) }}" class="cd-btn" style="display:inline-flex; align-items:center; gap:8px; background:rgba(37,99,235,0.1); color:#2563eb; border:1px solid rgba(37,99,235,0.2); padding:10px 18px; font-size:13px; font-weight:800; border-radius:10px; transition:all 0.2s;" onmouseover="this.style.background='#2563eb'; this.style.color='#fff';" onmouseout="this.style.background='rgba(37,99,235,0.1)'; this.style.color='#2563eb';">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Project
            </a>
        </div>

        <!-- Detail Modal -->
    <div id="taskModalOverlay" class="cd-modal-overlay" >
        <div class="cd-modal" >
            <div class="cd-modal-header" style="padding: 16px 32px; align-items: center; gap: 24px; justify-content: space-between;">
                <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0;">
                    <div id="modalSubtaskType" class="subtask-pill" style="margin-bottom:0; flex-shrink:0;"></div>
                    <div id="modalTopDeadlines" style="display:flex; align-items:center; gap:8px; flex-shrink:0;"></div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <a id="btnExportPpt" href="#" class="cd-btn" style="padding:8px 14px; font-size:12px; background:#ea580c; color:#fff; border:none; box-shadow:0 4px 12px rgba(234,88,12,0.25); font-weight:800; border-radius:8px; transition:transform 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'" title="Download PPT">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PPT
                    </a>
                </div>
            </div>
            <div class="cd-modal-body">
                <!-- 1. Workflow Progress Stepper (TOP) -->
                <div class="workflow-steps" id="modalWorkflowSteps" style="margin-top: 4px; margin-bottom: 20px;">
                    @foreach($stages as $index => $stage)
                        <div class="step-item" data-stage="{{ $stage }}">
                            <div class="step-dot">{{ $index + 1 }}</div>
                            <div class="step-label">{{ $stage }}</div>
                        </div>
                    @endforeach
                </div>

                <!-- 2. Compact Metadata Bar -->
                <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:16px; background:var(--color-bg-secondary); padding:10px 16px; border-radius:12px; border:1px solid var(--color-border-primary);">
                    <div class="detail-item" style="display:flex; align-items:center; gap:8px; flex:1; min-width:130px; margin:0;">
                        <label class="detail-label" style="margin:0; flex-shrink:0;">Current Stage:</label>
                        <div id="modalStage" class="detail-val" style="font-weight:900; color:#0055D4; font-size:13px;">-</div>
                    </div>
                    <div class="detail-item" style="display:flex; align-items:center; gap:8px; flex:1; min-width:140px; margin:0;">
                        <label class="detail-label" style="margin:0; flex-shrink:0;">Priority:</label>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="hidden" id="modalPriorityTaskId" value="">
                            @if($isAdmin || in_array($userRole, ['brandmanager', 'writer', 'approver', 'approvercoordinator', 'coordinator']))
                                <select id="prioritySelect" onchange="updatePriorityInlineModal(this, document.getElementById('modalPriorityTaskId').value)" class="cd-btn cd-btn-outline" style="padding:3px 6px; border-radius:6px; font-size:12px; font-weight:800; background:transparent; border:none; color:var(--color-text-primary); cursor:pointer;">
                                    <option value="High Priority" style="background:var(--color-bg-primary); color:var(--color-text-primary);">High Priority (Urgent)</option>
                                    <option value="Medium" style="background:var(--color-bg-primary); color:var(--color-text-primary);">Medium (Stable)</option>
                                    <option value="Low Priority" style="background:var(--color-bg-primary); color:var(--color-text-primary);">Low Priority (Paused)</option>
                                </select>
                            @else
                                <div id="modalPriorityDisplay" class="detail-val" style="font-weight:800; font-size:12px; color:var(--color-text-primary);"></div>
                            @endif
                        </div>
                    </div>
                    <div class="detail-item" style="display:flex; align-items:center; gap:8px; flex:1; min-width:150px; margin:0;">
                        <label class="detail-label" style="margin:0; flex-shrink:0;">Client Status:</label>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div id="modalClientStatusDisplay" class="detail-val" style="font-weight:800; font-size:12px; color:var(--color-text-primary);">Not Sent</div>
                            <a id="modalClientReviewLink" href="#" target="_blank" style="display:none; align-items:center; gap:3px; font-size:11px; font-weight:800; color:#3b82f6; text-decoration:none;" title="Open Client Review Page">
                                🔗 Review Link
                            </a>
                        </div>
                    </div>
                    <div class="detail-item" id="modalDeliverableDeadlineBox" style="display:flex; align-items:center; gap:8px; flex:1; min-width:180px; margin:0;">
                        <label class="detail-label" style="color:#3b82f6; margin:0; flex-shrink:0;">Deadline:</label>
                        <div style="display:flex; gap:6px; align-items:center; flex:1;">
                            <input type="date" id="modalDeliverableDeadlineInput" name="deadline" form="submitStageForm" min="{{ date('Y-m-d') }}"
                                style="flex:1; padding:4px 8px; border:1.5px solid rgba(59,130,246,0.25); border-radius:6px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; transition:border-color 0.15s; box-sizing:border-box;"
                                onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(59,130,246,0.25)'">
                            <button type="button" id="saveDeadlineBtn" style="display:none; padding:4px 8px; background:#3b82f6; color:#fff; border:none; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;" onclick="saveDeliverableDeadline(this)">Save</button>
                        </div>
                    </div>
                </div>

                <!-- 3. Deliverable Name Field (Below Current Stage / Metadata Bar) -->
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; background:var(--color-bg-secondary); padding:10px 16px; border-radius:12px; border:1px solid var(--color-border-primary);">
                    <label class="detail-label" style="margin:0; flex-shrink:0; font-size:11px; font-weight:900; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.1em;">Deliverable Name:</label>
                    <input type="text" id="modalTaskTitle" name="title" form="submitStageForm" class="batch-field" data-field="title" value="{{ $deliverable->title ?? '' }}" style="font-size:15px; font-weight:800; color:var(--color-text-primary); margin:0; border:1.5px solid var(--color-border-primary); background:var(--color-bg-primary); width:100%; flex:1; outline:none; border-radius:8px; padding:6px 12px; transition:all 0.2s;" onfocus="this.style.borderColor='#0055D4'; this.style.background='var(--color-bg-primary)'" onblur="this.style.borderColor='var(--color-border-primary)'; this.style.background='var(--color-bg-primary)'">
                </div>

                <!-- Reassign Designer Area -->
                @if($isAdmin || in_array($userRole, ['brandmanager', 'coordinator', 'approvercoordinator']))
                <div id="reassignDesignerArea" style="display:none; margin-bottom:24px; padding:16px; background:rgba(236,72,153,0.05); border:1px solid rgba(236,72,153,0.15); border-radius:12px;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                        <svg width="18" height="18" fill="none" stroke="#ec4899" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span style="font-size:12px; font-weight:900; color:#ec4899; text-transform:uppercase; letter-spacing:0.05em;">Reassign Designer</span>
                    </div>
                    <div style="display:flex; gap:10px; align-items:flex-end;">
                        <div style="flex:1;">
                            <label style="font-size:10px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.08em; display:block; margin-bottom:4px;">New Designer</label>
                            <select id="reassignDesignerSelect" style="width:100%; padding:8px 12px; border-radius:8px; border:1.5px solid rgba(236,72,153,0.25); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none;">
                                <option value="" style="background:var(--color-bg-primary); color:var(--color-text-primary);">Select Designer...</option>
                                @foreach($designers as $designer)
                                    <option value="{{ $designer->id }}" style="background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $designer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label style="font-size:10px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.08em; display:block; margin-bottom:4px;">Reason (Optional)</label>
                            <input type="text" id="reassignDesignerReason" placeholder="e.g. Designer unavailable" style="width:100%; padding:8px 12px; border-radius:8px; border:1.5px solid rgba(236,72,153,0.25); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; box-sizing:border-box;">
                        </div>
                        <button type="button" onclick="reassignDesigner(this)" style="padding:8px 16px; background:#ec4899; color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; flex-shrink:0;">Reassign</button>
                    </div>
                    <div id="reassignDesignerCurrentName" style="margin-top:8px; font-size:11px; color:var(--color-text-secondary); font-weight:600;"></div>
                </div>
                @endif

                <div style="display:flex; gap:24px; flex-wrap:wrap;">
                    <!-- Latest Comment Banner -->
                    <div id="modalLatestCommentAlert" style="display:none; flex:1; min-width:300px; padding:12px; background:rgba(16, 185, 129, 0.05); border:1px solid rgba(16, 185, 129, 0.2); border-radius:12px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <svg width="18" height="18" fill="none" stroke="#10b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span style="color:#10b981; font-weight:900; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;" id="modalLatestCommentTitle">Latest Comment</span>
                            </div>
                            <span id="modalLatestCommentDate" style="font-size:10px; color:#10b981; font-weight:600; opacity:0.8;"></span>
                        </div>
                        <div id="modalLatestCommentText" style="color:var(--color-text-primary); font-size:13px; font-weight:500; font-style:italic; line-height:1.5; background:var(--color-bg-primary); padding:10px 12px; border-radius:8px; border:1px solid rgba(16, 185, 129, 0.15); white-space:pre-wrap;"></div>
                    </div>
    
                    <!-- Revision Alert Banner -->
                    <div id="modalRevisionAlert" style="display:none; flex:1; min-width:300px; padding:12px; background:rgba(239, 68, 68, 0.05); border:1px solid rgba(239, 68, 68, 0.2); border-radius:12px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                            <svg width="18" height="18" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span style="color:#ef4444; font-weight:900; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Revision Requested</span>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <div id="modalRevisionAlertText" style="flex:1; color:#ef4444; font-size:13px; font-weight:500; line-height:1.5; background:var(--color-bg-primary); padding:10px 12px; border-radius:8px; border:1px solid rgba(239, 68, 68, 0.3);"></div>
                            <img id="modalRevisionAlertImage" src="" style="display:none; max-width:120px; max-height:80px; border-radius:8px; border:1px solid rgba(239, 68, 68, 0.3); object-fit:cover;">
                        </div>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <label class="detail-label">Concept</label>
                        <input type="hidden" id="modalConcept" name="concept" form="submitStageForm">
                        <div id="quillConcept" class="detail-val-textarea" style="padding:0; min-height:80px;"></div>
                    </div>
                    <div class="detail-item">
                        <label class="detail-label">Caption</label>
                        <input type="hidden" id="modalCaption" name="caption" form="submitStageForm">
                        <div id="quillCaption" class="detail-val-textarea" style="padding:0; min-height:80px;"></div>
                    </div>
                    <div class="detail-item full">
                        <label class="detail-label">Copy</label>
                        <input type="hidden" id="modalSubtaskCopy" name="post_copy" form="submitStageForm">
                        <input type="hidden" id="deleteReferenceFile" name="delete_reference_file" value="0" form="submitStageForm">
                        <div id="quillCopy" class="detail-val-textarea" style="padding:0; min-height:140px; background:var(--color-bg-primary); border-color:var(--color-border-primary);"></div>
                    </div>
                    <div class="detail-item full" style="clear:both; position:relative; z-index:10;">
                        <label class="detail-label">Reference</label>
                        <div id="modalReference" class="detail-val" style="margin-bottom: 24px; display: block; overflow: hidden;"></div>
                        <div id="modalReferenceEditArea" style="display:none; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top: 16px;">
                            <!-- Section 1: Reference Files & Images -->
                            <div style="padding:16px; background:rgba(0,85,212,0.03); border:1px solid rgba(0,85,212,0.12); border-radius:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                                    <span style="font-size:11px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em;">📁 Reference Images & Files</span>
                                    <button type="button" onclick="addReferenceInputRow('file')" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.2); border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.15s;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        Add Image / File
                                    </button>
                                </div>
                                <div id="modalReferenceFilesContainer" style="display:flex; flex-direction:column; gap:10px;">
                                    <div class="ref-input-row">
                                        <input type="file" id="modalReferenceFileInput" name="reference_files[]" form="submitStageForm" accept="image/*,video/*" multiple style="width:100%; padding:8px; border:1.5px dashed var(--color-border-primary); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="
                                            const previewContainer = document.getElementById('modalReferenceMultiPreviews');
                                            if (previewContainer) {
                                                previewContainer.innerHTML = '';
                                                Array.from(this.files).forEach((file, idx) => {
                                                    const wrapper = document.createElement('div');
                                                    wrapper.style.cssText = 'position:relative; display:inline-block; margin-right:8px; margin-top:8px;';
                                                    if (file.type.startsWith('image/')) {
                                                        const r = new FileReader();
                                                        r.onload = e => {
                                                            wrapper.innerHTML = `<img src='${e.target.result}' style='max-width:100px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary); object-fit:cover;'>`;
                                                        };
                                                        r.readAsDataURL(file);
                                                    } else if (file.type.startsWith('video/')) {
                                                        const url = (window.URL || window.webkitURL).createObjectURL(file);
                                                        wrapper.innerHTML = `<video src='${url}' controls style='max-width:120px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary);'></video>`;
                                                    }
                                                    previewContainer.appendChild(wrapper);
                                                });
                                            }
                                        ">
                                        <div id="modalReferenceMultiPreviews" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Reference Links & URLs -->
                            <div style="padding:16px; background:rgba(59,130,246,0.03); border:1px solid rgba(59,130,246,0.12); border-radius:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                                    <span style="font-size:11px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em;">🔗 Reference Links & URLs</span>
                                    <button type="button" onclick="addReferenceInputRow('link')" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:rgba(59,130,246,0.1); color:#2563eb; border:1px solid rgba(59,130,246,0.2); border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.15s;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        Add Link
                                    </button>
                                </div>
                                <div id="modalReferenceLinksContainer" style="display:flex; flex-direction:column; gap:10px;">
                                    <div class="ref-input-row">
                                        <input type="url" id="modalReferenceUrl" name="reference_urls[]" form="submitStageForm" placeholder="https://..." style="width:100%; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="detail-item full" style="border-top:1px solid var(--color-border-primary); padding-top:24px; margin-top:0px;">
                        <label class="detail-label" style="margin-bottom:16px; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">Deliverable Team</label>
                        <div id="modalTeamGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px;"></div>
                    </div>
                    <div class="detail-item full hist-box" id="modalApprovalsBox" style="display:none; border-top:1px solid var(--color-border-primary); margin-top:10px;">
                        <button class="hist-toggle" onclick="toggleHistory('modalApprovalHistory', this)">
                            <span style="display:flex; align-items:center; gap:8px; flex:1;">
                                <span style="width:7px;height:7px;border-radius:50%;background:#10b981;flex-shrink:0;"></span>
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#10b981;">Submission History</span>
                                <span id="modalApprovalsCount" class="hist-count hist-count-green"></span>
                            </span>
                            <svg class="hist-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="modalApprovalHistory" class="hist-body" style="display:none; flex-direction:column; gap:10px;"></div>
                    </div>

                    <div id="approverSelectionArea" class="detail-item full" style="display:none; margin-top:0px; padding:20px; background:rgba(234,88,12,0.05); border:1px solid rgba(234,88,12,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#ea580c; margin-bottom:12px;">Assign Next Approver</label>
                        <select name="approver_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Approver...</option>
                            @foreach($approvers as $approver)
                                <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#ea580c; margin-top:8px; font-weight:600;">Selection required to advance to Approver stage.</p>
                    </div>

                    <div id="brandManagerSelectionArea" class="detail-item full" style="display:none; margin-top:0px; padding:20px; background:rgba(37,99,235,0.05); border:1px solid rgba(37,99,235,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#3b82f6; margin-bottom:12px;">Assign Next Brand Manager</label>
                        <select name="brand_manager_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Brand Manager...</option>
                            @foreach($brandManagers as $bm)
                                <option value="{{ $bm->id }}">{{ $bm->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#3b82f6; margin-top:8px; font-weight:600;">Selection required to advance to Brand Manager stage.</p>
                    </div>

                    <div id="modalFurtherApproverGroup" style="display:none; margin-top:10px; padding:20px; background:rgba(124,58,237,0.05); border:1px solid rgba(124,58,237,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#7c3aed; margin-bottom:12px; text-transform:uppercase; font-size:10px; letter-spacing:0.08em; font-weight:700;">
                            Further Approver <span style="text-transform:none; letter-spacing:0; font-weight:500; opacity:0.8;">(optional — adds another approval step before Brand Manager)</span>
                        </label>
                        <select name="further_approver_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid rgba(124,58,237,0.2); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">— Skip, go directly to Brand Manager —</option>
                            @foreach($approvers as $approver)
                                <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="coordinatorSelectionArea" class="detail-item full" style="display:none; margin-top:0px; padding:20px; background:rgba(14,165,233,0.05); border:1px solid rgba(14,165,233,0.1); border-radius:16px;">
                        <label class="detail-label" style="color:#0ea5e9; margin-bottom:12px;">Assign Next Coordinator</label>
                        <select name="coordinator_id" form="submitStageForm" style="width:100%; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Coordinator...</option>
                            @foreach($coordinators as $coord)
                                <option value="{{ $coord->id }}">{{ $coord->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#0ea5e9; margin-top:8px; font-weight:600;">Selection required to advance to Coordinator stage.</p>
                    </div>

                    <div id="designerSelectionArea" class="detail-item full" style="display:none; margin-top:0px; padding:20px; background:rgba(139,92,246,0.05); border:1px solid rgba(139,92,246,0.1); border-radius:16px;">
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
                        <label class="detail-label" style="color:#10b981; margin-bottom:14px;">Deliver Final Artwork</label>
                        {{-- Self-contained form — avoids cross-form file input bugs --}}
                        <form id="artworkDeliveryForm" method="POST" enctype="multipart/form-data" onsubmit="
                            const btn = this.querySelector('button[type=submit]');
                            if (btn) {
                                btn.disabled = true;
                                btn.style.opacity = '0.75';
                                btn.style.cursor = 'not-allowed';
                                btn.innerHTML = `<svg width='14' height='14' fill='none' stroke='currentColor' viewBox='0 0 24 24' style='animation: spin 1s linear infinite; margin-right: 6px;'><circle cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4' style='opacity:0.25;'></circle><path fill='currentColor' d='M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z' style='opacity:0.75;'></path></svg> Uploading Artwork...`;
                            }
                        ">
                            @csrf
                            <input type="hidden" name="action" value="save_only">
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                                {{-- Section 1: Artwork Files --}}
                                <div style="display:flex; flex-direction:column; gap:10px; padding:14px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:12px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between;">
                                        <div style="font-size:11px; font-weight:800; color:#10b981; text-transform:uppercase; letter-spacing:0.08em; display:flex; align-items:center; gap:6px;">
                                            <span>🎨</span> Final Artwork Files
                                        </div>
                                        <button type="button" onclick="addArtworkInputRow('file')" style="background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.2); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                                            + Add Artwork File
                                        </button>
                                    </div>
                                    <div id="modalArtworkFilesContainer" style="display:flex; flex-direction:column; gap:10px;">
                                        <div class="art-input-row" style="display:flex; flex-direction:column; gap:6px;">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <input type="file" id="modalArtworkFile" name="final_designs_files[]" form="artworkDeliveryForm" accept="image/*,video/*" multiple style="width:100%; padding:8px; border:1.5px dashed rgba(16,185,129,0.35); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="
                                                    const prevDiv = document.getElementById('modalArtworkInitialPreview');
                                                    if (prevDiv) {
                                                        prevDiv.innerHTML = '';
                                                        Array.from(this.files).forEach(file => {
                                                            const wrapper = document.createElement('div');
                                                            if (file.type.startsWith('image/')) {
                                                                const r = new FileReader();
                                                                r.onload = (e) => {
                                                                    wrapper.innerHTML = `<img src='${e.target.result}' style='max-width:100px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary); object-fit:cover;'>`;
                                                                };
                                                                r.readAsDataURL(file);
                                                            } else if (file.type.startsWith('video/')) {
                                                                const url = (window.URL || window.webkitURL).createObjectURL(file);
                                                                wrapper.innerHTML = `<video src='${url}' controls style='max-width:120px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary);'></video>`;
                                                            }
                                                            prevDiv.appendChild(wrapper);
                                                        });
                                                    }
                                                ">
                                            </div>
                                            <div id="modalArtworkInitialPreview" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Section 2: Artwork Links --}}
                                <div style="display:flex; flex-direction:column; gap:10px; padding:14px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:12px;">
                                    <div style="display:flex; align-items:center; justify-content:space-between;">
                                        <div style="font-size:11px; font-weight:800; color:#10b981; text-transform:uppercase; letter-spacing:0.08em; display:flex; align-items:center; gap:6px;">
                                            <span>🔗</span> Artwork Links & URLs
                                        </div>
                                        <button type="button" onclick="addArtworkInputRow('link')" style="background:rgba(16,185,129,0.1); color:#10b981; border:1px solid rgba(16,185,129,0.2); border-radius:6px; padding:4px 8px; font-size:10px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                                            + Add Link
                                        </button>
                                    </div>
                                    <div id="modalArtworkLinksContainer" style="display:flex; flex-direction:column; gap:10px;">
                                        <div class="art-input-row">
                                            <input type="url" id="modalArtworkUrl" name="final_designs_urls[]" form="artworkDeliveryForm" placeholder="https://drive.google.com/…" style="width:100%; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" style="margin-top:14px;width:100%;padding:10px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;display:flex;justify-content:center;align-items:center;gap:8px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Save Artwork
                            </button>
                        </form>
                    </div>

                    <div id="submitNotesArea" class="detail-item full" style="margin-top:0px; padding:20px; background:rgba(0,0,0,0.02); border:1px solid rgba(0,0,0,0.05); border-radius:16px;">
                        <label class="detail-label" style="margin-bottom:12px;">Submission Comment (Optional)</label>
                        <textarea name="submit_notes" form="submitStageForm" placeholder="Add an optional comment when submitting..." style="width:100%; height:80px; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); resize:vertical; outline:none; transition:border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='var(--color-border-primary)'"></textarea>
                    </div>

                    <div class="detail-item">
                        <label class="detail-label">Final Designs</label>
                        <div id="modalFinal" class="detail-val"></div>
                    </div>

                    {{-- ── Send Artwork to Client ────────────────────────────────────── --}}
                    @if(\App\Services\FeatureManager::isClientReviewEnabled())
                    <div class="detail-item full" id="sendToClientSection">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                            <label class="detail-label" style="margin:0;">Client Artwork Review</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <a id="viewAnnotationsLink" href="#" target="_blank"
                                   style="display:none; align-items:center; gap:5px; padding:5px 12px; background:rgba(139,92,246,0.1); color:#8b5cf6; border:1px solid rgba(139,92,246,0.2); border-radius:8px; font-size:10px; font-weight:700; text-decoration:none; transition:all 0.15s;">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View Annotations
                                </a>
                                <button onclick="openSendArtworkModal()" id="sendArtworkBtn"
                                        style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(16,185,129,0.08)); color:#10b981; border:1px solid rgba(16,185,129,0.25); border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.15s;"
                                        onmouseover="this.style.background='rgba(16,185,129,0.2)'"
                                        onmouseout="this.style.background='linear-gradient(135deg,rgba(16,185,129,0.15),rgba(16,185,129,0.08))'">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                    Send to Client
                                </button>
                            </div>
                        </div>

                        {{-- Existing review links summary --}}
                        <div id="reviewLinksSummary" style="display:none; padding:10px 14px; background:rgba(16,185,129,0.05); border:1px solid rgba(16,185,129,0.15); border-radius:12px; font-size:12px; color:var(--color-text-secondary);">
                            <div id="reviewLinksContent"></div>
                        </div>
                    </div>
                    @endif


                    <div class="detail-item full hist-box" id="modalHistoryBox" style="display:none; border-top:1px solid var(--color-border-primary); margin-top:10px;">
                        <button class="hist-toggle" onclick="toggleHistory('modalRevisionHistory', this)">
                            <span style="display:flex; align-items:center; gap:8px; flex:1;">
                                <span style="width:7px;height:7px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#ef4444;">Revision History</span>
                                <span id="modalRevisionsCount" class="hist-count hist-count-red"></span>
                            </span>
                            <svg class="hist-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="modalRevisionHistory" class="hist-body" style="display:none; flex-wrap:wrap; flex-direction:row; gap:12px; align-items:stretch;"></div>
                    </div>

                    <!-- Reassignment History -->
                    <div class="detail-item full hist-box" id="modalReassignmentHistoryBox" style="display:none; border-top:1px solid var(--color-border-primary); margin-top:10px;">
                        <button class="hist-toggle" onclick="toggleHistory('modalReassignmentHistory', this)">
                            <span style="display:flex; align-items:center; gap:8px; flex:1;">
                                <span style="width:7px;height:7px;border-radius:50%;background:#ec4899;flex-shrink:0;"></span>
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#ec4899;">Reassignment History</span>
                                <span id="modalReassignmentsCount" class="hist-count" style="background:rgba(236,72,153,0.1); color:#ec4899; border:1px solid rgba(236,72,153,0.2);"></span>
                            </span>
                            <svg class="hist-chevron" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="modalReassignmentHistory" class="hist-body" style="display:none; flex-direction:column; gap:12px;"></div>
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
                            <p id="modalRevisionTargetNote" style="font-size:12px; color:#ef4444; font-weight:600; margin:0; opacity:0.8;">The task will be sent back to the Writer</p>
                        </div>
                    </div>
                    <form id="revisionsForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="revision_target" id="modalRevisionTarget" value="writer">
                        <!-- Post-designer revision target toggle (shown only for Writer Review / Approver Review / Final Approval) -->
                        <div id="modalRevisionTargetGroup" style="display:none; margin-bottom:16px; padding:12px 14px; border-radius:10px; border:1.5px solid rgba(239,68,68,0.15); background:rgba(239,68,68,0.04);">
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#ef4444;display:block;margin-bottom:8px;">Send back to</span>
                            <div style="display:flex;gap:16px;">
                                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;font-weight:600;color:var(--color-text-primary);">
                                    <input type="radio" name="modal_revision_target_radio" value="designer" id="modalReviseTargetDesigner" checked style="accent-color:#ef4444;" onchange="document.getElementById('modalRevisionTarget').value=this.value; document.getElementById('modalRevisionTargetNote').textContent='The task will be sent back to the Designer.'">
                                    Designer
                                </label>
                                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:13px;font-weight:600;color:var(--color-text-primary);">
                                    <input type="radio" name="modal_revision_target_radio" value="writer" id="modalReviseTargetWriter" style="accent-color:#ef4444;" onchange="document.getElementById('modalRevisionTarget').value=this.value; document.getElementById('modalRevisionTargetNote').textContent='The task will be sent back to the Writer.'">
                                    Writer
                                </label>
                            </div>
                        </div>
                        <textarea name="revision_instructions" required placeholder="Describe specifically what needs to be fixed..." style="width:100%; height:160px; padding:16px; border-radius:16px; border:1.5px solid rgba(239, 68, 68, 0.2); font-size:14px; resize:none; font-family:inherit; margin-bottom:12px; display:block; outline:none; background:var(--color-bg-primary); color:var(--color-text-primary); transition:all 0.2s;"></textarea>
                        <!-- Optional revision image -->
                        <div style="margin-bottom:20px;">
                            <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#ef4444;margin-bottom:8px;">Attach Image <span style="font-weight:500;opacity:0.6;text-transform:none;letter-spacing:0;">(optional)</span></label>
                            <label for="revisionImageInput" style="display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:10px;border:1.5px dashed rgba(239,68,68,0.3);background:rgba(239,68,68,0.04);cursor:pointer;font-size:13px;font-weight:600;color:#ef4444;transition:all 0.15s;" onmouseover="this.style.background='rgba(239,68,68,0.08)'" onmouseout="this.style.background='rgba(239,68,68,0.04)'">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span id="revisionImageLabel">Choose image…</span>
                            </label>
                            <input type="file" id="revisionImageInput" name="revision_image" accept="image/*,video/*" style="display:none;" onchange="
                                const f = this.files[0];
                                document.getElementById('revisionImageLabel').textContent = f ? f.name : 'Choose image…';
                                const prev = document.getElementById('revisionImagePreview');
                                if (f) { const r = new FileReader(); r.onload = e => { prev.src = e.target.result; prev.style.display='block'; }; r.readAsDataURL(f); }
                                else { prev.style.display='none'; prev.src=''; }
                            ">
                            <img id="revisionImagePreview" src="" alt="" style="display:none;margin-top:10px;max-width:100%;max-height:200px;border-radius:10px;border:1px solid rgba(239,68,68,0.2);object-fit:contain;">
                        </div>
                        <div style="display:flex; justify-content:flex-end; gap:12px;">
                            <button type="button" class="cd-btn cd-btn-outline" onclick="toggleRevisionInput(false)" style="padding:12px 24px;">Cancel</button>
                            <button type="submit" class="cd-btn" style="background:#ef4444; color:#fff; border:none; padding:12px 32px; font-weight:800; font-size:14px; border-radius:14px; cursor:pointer; shadow:0 10px 20px rgba(239,68,68,0.2);">Send Request</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="cd-modal-footer" style="flex-direction: column; align-items: stretch; gap: 12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <form id="submitStageForm" method="POST" enctype="multipart/form-data" style="display:none; align-items:center; gap:12px;">
                        @csrf
                        <button type="submit" name="action" value="save_only" class="cd-btn" id="saveContentBtn" style="background:rgba(59,130,246,0.1); color:#3b82f6; border:1.5px solid rgba(59,130,246,0.4); display:none; padding:10px 20px; font-size:12px; transition:all 0.2s;" onmouseover="this.style.background='rgba(59,130,246,0.2)'" onmouseout="this.style.background='rgba(59,130,246,0.1)'">Save Content</button>
                        <button type="submit" name="action" value="submit" class="cd-btn cd-btn-primary" id="submitStageBtn" style="padding:10px 20px; font-size:12px; box-shadow:0 4px 12px rgba(0,85,212,0.4);">Submit to Next</button>
                    </form>
                    <button id="modalDeleteBtn" type="button" class="cd-btn" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1.5px solid rgba(239,68,68,0.4); display:none; padding:10px 20px; font-size:12px; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.2)'" onmouseout="this.style.background='rgba(239,68,68,0.1)'" onclick="deleteTaskFromModal()">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Text Preview Popup -->
    <div id="text-preview-modal" onclick="if(event.target===this)closeTextPreview()">
        <div id="text-preview-inner">
            <div class="tp-header">
                <span id="tp-label" class="tp-label"></span>
                <button class="tp-close" onclick="closeTextPreview()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="tp-body" class="tp-body"></div>
        </div>
    </div>

    
    </div>

    <script id="approvers-data" type="application/json">{!! json_encode($approvers) !!}</script>      
    <script id="managers-data" type="application/json">{!! json_encode($brandManagers) !!}</script>   
    <script id="coordinators-data" type="application/json">{!! json_encode($coordinators) !!}</script>
    <script id="designers-data" type="application/json">{!! json_encode($designers) !!}</script>
    <script>
        function openPriorityInlineEditor(e, taskId, currentPrio) {
            e.stopPropagation();
            @if($isAdmin || $userRole === 'brandmanager' || $userRole === 'writer')
                const overlay = document.getElementById('priorityInlineOverlay');
                const popup = document.getElementById('priorityInlinePopup');
                document.getElementById('priorityInlineTaskId').value = taskId;
                document.getElementById('priorityInlineSelect').value = currentPrio || 'Medium';
                
                const rect = e.currentTarget.getBoundingClientRect();
                popup.style.top = (rect.bottom + 6) + 'px';
                popup.style.left = rect.left + 'px';
                
                overlay.style.display = 'block';
            @endif
        }

        async function submitInlinePriority() {
            const taskId = document.getElementById('priorityInlineTaskId').value;
            const priority = document.getElementById('priorityInlineSelect').value;
            const selectEl = document.getElementById('priorityInlineSelect');
            selectEl.disabled = true;
            
            try {
                const response = await fetch(`/deliverables/${taskId}/priority`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ priority: priority })
                });
                
                const result = await response.json();
                if (response.ok && result.success) {
                    window.location.reload();
                } else {
                    showErrorModal(result.message || 'Error updating priority');
                    selectEl.disabled = false;
                }
            } catch (err) {
                console.error(err);
                showErrorModal('Network error');
                selectEl.disabled = false;
            }
        }
        let quillConcept, quillCaption, quillCopy;

        document.addEventListener('DOMContentLoaded', function () {
            const quillOptions = {
                theme: 'snow',
                placeholder: 'Start typing...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                }
            };
            
            quillConcept = new Quill('#quillConcept', quillOptions);
            quillCaption = new Quill('#quillCaption', quillOptions);
            quillCopy = new Quill('#quillCopy', quillOptions);
            
            quillConcept.on('text-change', () => { document.getElementById('modalConcept').value = quillConcept.root.innerHTML === '<p><br></p>' ? '' : quillConcept.root.innerHTML; });
            quillCaption.on('text-change', () => { document.getElementById('modalCaption').value = quillCaption.root.innerHTML === '<p><br></p>' ? '' : quillCaption.root.innerHTML; });
            quillCopy.on('text-change', () => { document.getElementById('modalSubtaskCopy').value = quillCopy.root.innerHTML === '<p><br></p>' ? '' : quillCopy.root.innerHTML; });
        });

        /* Work hours — auto-save on blur */
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.hrs-input').forEach(function (input) {
                input.addEventListener('change', function () {
                    const taskId = this.dataset.taskId;
                    const hours  = this.value;
                    const el     = this;
                    el.style.opacity = '0.5';
                    fetch(`/deliverables/${taskId}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ action: 'save_only', work_hours: hours })
                    }).then(r => r.json()).then(function (data) {
                        el.style.opacity = '1';
                        if (data.success) {
                            el.style.borderColor = '#10b981';
                            setTimeout(() => { el.style.borderColor = ''; }, 1200);
                        }
                    }).catch(() => { el.style.opacity = '1'; });
                });
            });
        });

        /* Artwork picker — fixed-position dropdown, unclipped by overflow:hidden */
        function toggleArtworkPicker(id, btn) {
            const picker = document.getElementById(id);
            const isOpen = picker.style.display !== 'none';

            // Close all pickers first
            document.querySelectorAll('.aw-picker').forEach(p => p.style.display = 'none');

            if (!isOpen) {
                const r = btn.getBoundingClientRect();
                picker.style.top  = (r.bottom + 4) + 'px';
                picker.style.left = r.left + 'px';
                picker.style.display = 'block';

                // Prevent right-edge overflow
                const pr = picker.getBoundingClientRect();
                if (pr.right > window.innerWidth - 8) {
                    picker.style.left = (r.right - picker.offsetWidth) + 'px';
                }
            }
        }
        document.addEventListener('click', () => {
            document.querySelectorAll('.aw-picker').forEach(p => p.style.display = 'none');
        });

        function toggleHistory(contentId, btn) {
            const content = document.getElementById(contentId);
            const isOpen = content.style.display !== 'none';
            content.style.display = isOpen ? 'none' : 'flex';
            btn.classList.toggle('open', !isOpen);
        }

        function openTextPreview(label, text, imagePath) {
            document.getElementById('tp-label').textContent = label;
            const body = document.getElementById('tp-body');
            body.innerHTML = '';
            const textNode = document.createElement('div');
            textNode.className = 'ql-editor';
            textNode.style.cssText = 'word-break:break-word; padding:0;';
            textNode.innerHTML = text;
            body.appendChild(textNode);
            if (imagePath) {
                const img = document.createElement('img');
                img.src = imagePath;
                img.alt = 'Revision reference';
                img.style.cssText = 'display:block;margin-top:14px;max-width:100%;max-height:300px;border-radius:8px;border:1px solid rgba(239,68,68,0.2);object-fit:contain;cursor:pointer;';
                img.onclick = () => window.open(imagePath, '_blank');
                body.appendChild(img);
            }
            document.getElementById('text-preview-modal').style.display = 'flex';
        }
        function closeTextPreview() {
            document.getElementById('text-preview-modal').style.display = 'none';
            document.getElementById('tp-body').innerHTML = '';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeTextPreview();
        });

        const AUTH_USER_ID = {{ auth()->id() }};
        const AUTH_USER_ROLE = "{{ auth()->user()->role }}";
        const WORKFLOW_STAGES = @json($stages);
        let currentTaskData = null;

        function saveDeliverableDeadline(btn) {
            if (!currentTaskData) return;
            const input = document.getElementById('modalDeliverableDeadlineInput');
            if (!input) return;
            const deadlineVal = input.value;
            const originalText = btn.textContent;
            btn.textContent = 'Saving...';
            btn.style.opacity = '0.7';
            fetch(`/deliverables/${currentTaskData.id}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: 'save_only', deadline: deadlineVal })
            }).then(r => r.json()).then(data => {
                btn.textContent = 'Saved!';
                btn.style.background = '#10b981';
                btn.style.opacity = '1';
                setTimeout(() => {
                    window.location.reload();
                }, 500);

            }).catch(() => {
                btn.textContent = 'Error';
                btn.style.background = '#ef4444';
                setTimeout(() => {
                    btn.textContent = 'Save';
                    btn.style.background = '#3b82f6';
                }, 2000);
            });
        }

        function addReferenceInputRow(type) {
            if (type === 'file') {
                const container = document.getElementById('modalReferenceFilesContainer') || document.getElementById('modalReferenceFilesContainerPrj');
                if (!container) return;
                const row = document.createElement('div');
                row.className = 'ref-input-row';
                row.style.cssText = 'margin-top:10px; padding-top:10px; border-top:1px dashed var(--color-border-primary);';
                
                const rowId = 'row_prev_' + Date.now() + Math.random().toString(36).substr(2, 4);
                row.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="file" name="reference_files[]" form="submitStageForm" accept="image/*,video/*" multiple style="flex:1; padding:8px; border:1.5px dashed var(--color-border-primary); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="
                            const prevDiv = document.getElementById('${rowId}');
                            if (prevDiv) {
                                prevDiv.innerHTML = '';
                                Array.from(this.files).forEach(file => {
                                    const wrapper = document.createElement('div');
                                    wrapper.style.cssText = 'position:relative; display:inline-block; margin-right:8px; margin-top:8px;';
                                    if (file.type.startsWith('image/')) {
                                        const r = new FileReader();
                                        r.onload = e => {
                                            wrapper.innerHTML = \`<img src='\${e.target.result}' style='max-width:100px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary); object-fit:cover;'>\`;
                                        };
                                        r.readAsDataURL(file);
                                    } else if (file.type.startsWith('video/')) {
                                        const url = (window.URL || window.webkitURL).createObjectURL(file);
                                        wrapper.innerHTML = \`<video src='\${url}' controls style='max-width:120px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary);'></video>\`;
                                    }
                                    prevDiv.appendChild(wrapper);
                                });
                            }
                        ">
                        <button type="button" onclick="this.closest('.ref-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove file input">✕ Remove</button>
                    </div>
                    <div id="${rowId}" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;"></div>
                `;
                container.appendChild(row);
            } else {
                const container = document.getElementById('modalReferenceLinksContainer') || document.getElementById('modalReferenceLinksContainerPrj');
                if (!container) return;
                const row = document.createElement('div');
                row.className = 'ref-input-row';
                row.style.cssText = 'display:flex; align-items:center; gap:10px; margin-top:8px;';
                row.innerHTML = `
                    <input type="url" name="reference_urls[]" form="submitStageForm" placeholder="https://..." style="flex:1; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                    <button type="button" onclick="this.closest('.ref-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove link input">✕ Remove</button>
                `;
                container.appendChild(row);
            }
        }

        function removeSpecificRefFile(boxId, index) {
            const box = document.getElementById(boxId);
            if (box) box.style.display = 'none';
            const form = document.getElementById('submitStageForm');
            if (form) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_reference_file_indices[]';
                input.value = index;
                form.appendChild(input);
            }
        }

        function removeSpecificRefUrl(boxId, index) {
            const box = document.getElementById(boxId);
            if (box) box.style.display = 'none';
            const form = document.getElementById('submitStageForm');
            if (form) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_reference_url_indices[]';
                input.value = index;
                form.appendChild(input);
            }
        }

        function removeSpecificArtFile(boxId, index) {
            const box = document.getElementById(boxId);
            if (box) box.style.display = 'none';
            const form = document.getElementById('artworkDeliveryForm') || document.getElementById('submitStageForm');
            if (form) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_final_designs_file_indices[]';
                input.value = index;
                form.appendChild(input);
            }
        }

        function removeSpecificArtUrl(boxId, index) {
            const box = document.getElementById(boxId);
            if (box) box.style.display = 'none';
            const form = document.getElementById('artworkDeliveryForm') || document.getElementById('submitStageForm');
            if (form) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_final_designs_url_indices[]';
                input.value = index;
                form.appendChild(input);
            }
        }

        function addArtworkInputRow(type) {
            if (type === 'file') {
                const container = document.getElementById('modalArtworkFilesContainer') || document.getElementById('modalArtworkFilesContainerPrj');
                if (!container) return;
                const rowId = `artRowPrev_${Date.now()}_${Math.floor(Math.random()*1000)}`;
                const row = document.createElement('div');
                row.className = 'art-input-row';
                row.style.cssText = 'display:flex; flex-direction:column; gap:6px; margin-top:8px;';
                row.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="file" name="final_designs_files[]" form="artworkDeliveryForm" accept="image/*,video/*" multiple style="flex:1; padding:8px; border:1.5px dashed rgba(16,185,129,0.35); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="
                            const prevDiv = document.getElementById('${rowId}');
                            if (prevDiv) {
                                prevDiv.innerHTML = '';
                                Array.from(this.files).forEach(file => {
                                    const wrapper = document.createElement('div');
                                    if (file.type.startsWith('image/')) {
                                        const r = new FileReader();
                                        r.onload = (e) => {
                                            wrapper.innerHTML = \`<img src='\${e.target.result}' style='max-width:100px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary); object-fit:cover;'>\`;
                                        };
                                        r.readAsDataURL(file);
                                    } else if (file.type.startsWith('video/')) {
                                        const url = (window.URL || window.webkitURL).createObjectURL(file);
                                        wrapper.innerHTML = \`<video src='\${url}' controls style='max-width:120px; max-height:100px; border-radius:8px; border:1px solid var(--color-border-primary);'></video>\`;
                                    }
                                    prevDiv.appendChild(wrapper);
                                });
                            }
                        ">
                        <button type="button" onclick="this.closest('.art-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove artwork input">✕ Remove</button>
                    </div>
                    <div id="${rowId}" style="display:flex; flex-wrap:wrap; gap:8px; margin-top:4px;"></div>
                `;
                container.appendChild(row);
            } else {
                const container = document.getElementById('modalArtworkLinksContainer') || document.getElementById('modalArtworkLinksContainerPrj');
                if (!container) return;
                const row = document.createElement('div');
                row.className = 'art-input-row';
                row.style.cssText = 'display:flex; align-items:center; gap:10px; margin-top:8px;';
                row.innerHTML = `
                    <input type="url" name="final_designs_urls[]" form="artworkDeliveryForm" placeholder="https://drive.google.com/…" style="flex:1; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                    <button type="button" onclick="this.closest('.art-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove link input">✕ Remove</button>
                `;
                container.appendChild(row);
            }
        }

        function openTaskModal(task) {
            try {
                console.log('Opening Task Modal for:', task);
                currentTaskData = task;

                const rawRole = AUTH_USER_ROLE;
                const userRole = rawRole.toLowerCase().replace(/\s+/g, '');
                const isAdmin = userRole === 'admin';
                const stage = task.approval_stage;
                const isAssignedWriter      = AUTH_USER_ID == task.writer_id;
                const isAssignedApprover    = AUTH_USER_ID == task.approver_id;
                const isAssignedBrandMgr    = AUTH_USER_ID == task.brand_manager_id;
                const isAssignedCoordinator = AUTH_USER_ID == task.coordinator_id;
                const isAssignedDesigner    = AUTH_USER_ID == task.designer_id;
                const hasWriterRole = userRole === 'writer' || userRole === 'assignee';
                const hasDesignerRole = userRole === 'designer';
                const isWriterStage = ['Writer', 'Assignee', 'Writer Review'].includes(task.approval_stage || 'Writer');
                const writerEditPermission = isAdmin || (hasWriterRole && (!task.writer_id || AUTH_USER_ID == task.writer_id) && isWriterStage);
                // Allow any designer to upload when no designer is assigned (matches PHP logic)
                const designerEditPermission = isAssignedDesigner || (hasDesignerRole && !task.designer_id);
                const canDesignerEdit = (designerEditPermission && stage === 'Designer') || isAdmin;

                const overlay = document.getElementById('taskModalOverlay');
                const modal = overlay.querySelector('.cd-modal');
                
                const titleEl = document.getElementById('modalTaskTitle');
                titleEl.value = task.title || '';
                titleEl.setAttribute('data-task-id', task.id);
                const ptEl = document.getElementById('modalSubtaskType');
                ptEl.textContent = task.subtask_type || 'Standard';
                
                const colors = task.subtask_type_colors || {bg:'#f1f5f9', text:'#475569', border:'#e2e8f0'};
                ptEl.style.background = colors.bg;
                ptEl.style.color = colors.text;
                ptEl.style.borderColor = colors.border;

                document.getElementById('modalConcept').value = task.concept || '';
                quillConcept.clipboard.dangerouslyPasteHTML(task.concept || '');
                
                document.getElementById('modalCaption').value = task.caption || '';
                quillCaption.clipboard.dangerouslyPasteHTML(task.caption || '');
                
                document.getElementById('modalSubtaskCopy').value = task.subtask_copy || task.post_copy || '';
                quillCopy.clipboard.dangerouslyPasteHTML(task.subtask_copy || task.post_copy || '');
                if (document.getElementById('modalStage')) {
                    const stage = task.approval_stage || 'Writer';
                    let stageText = stage;
                    
                    let assignedUser = null;
                    if (stage === 'Writer' || stage === 'Writer Review') assignedUser = task.writer?.name;
                    else if (stage === 'Designer') assignedUser = task.designer?.name;
                    else if (stage === 'Brand Manager' || stage === 'AM/BD' || stage === 'Final Approval') assignedUser = task.brandManager?.name;
                    else if (stage === 'Coordinator') assignedUser = task.coordinator?.name;
                    else if (stage === 'Approver' || stage === 'Approver Review') assignedUser = task.approver?.name;
                    else if (stage === 'Further Approver') assignedUser = task.furtherApprover?.name;
                    
                    if (assignedUser) {
                        stageText += ` (${assignedUser})`;
                    }
                    
                    document.getElementById('modalStage').textContent = stageText;
                }
                
                if (document.getElementById('modalPriorityDisplay')) {
                    let displayPrio = task.priority || 'Medium';
                    if (displayPrio === 'High Priority') displayPrio = 'High Priority (Urgent)';
                    if (displayPrio === 'Medium') displayPrio = 'Medium (Stable)';
                    if (displayPrio === 'Low Priority') displayPrio = 'Low Priority (Paused)';
                    document.getElementById('modalPriorityDisplay').textContent = displayPrio;
                }
                if (document.getElementById('modalPriorityTaskId')) document.getElementById('modalPriorityTaskId').value = task.id;
                if (document.getElementById('prioritySelect')) document.getElementById('prioritySelect').value = task.priority || 'Medium';
                
                if (document.getElementById('modalPriorityEdit')) document.getElementById('modalPriorityEdit').style.display = 'none';
                
                if (document.getElementById('modalClientStatusDisplay')) {
                    const cStatus = task.client_status || 'Not Sent';
                    const disp = document.getElementById('modalClientStatusDisplay');
                    disp.textContent = cStatus;
                    disp.style.color = cStatus === 'Client Approved' ? '#10b981' : (cStatus === 'Client Revisions' ? '#ef4444' : (cStatus === 'Sent to Client' || cStatus === 'Waiting for Feedback' ? '#3b82f6' : 'var(--color-text-secondary)'));
                }
                const reviewLinkEl = document.getElementById('modalClientReviewLink');
                if (reviewLinkEl) {
                    if (task.artwork_reviews && task.artwork_reviews.length > 0) {
                        const activeRev = task.artwork_reviews.find(r => r.is_active);
                        if (activeRev) {
                            reviewLinkEl.href = `/artwork-review/${activeRev.token}`;
                            reviewLinkEl.style.display = 'inline-flex';
                        } else {
                            reviewLinkEl.style.display = 'none';
                        }
                    } else {
                        reviewLinkEl.style.display = 'none';
                    }
                }
                
                if (document.getElementById('modalClientStatusEdit')) document.getElementById('modalClientStatusEdit').style.display = 'none';

                // Reassign Designer Area visibility
                const reassignArea = document.getElementById('reassignDesignerArea');
                if (reassignArea) {
                    if (task.approval_stage === 'Designer') {
                        reassignArea.style.display = 'block';
                        const currentDesignerName = task.designer?.name || 'Unassigned';
                        document.getElementById('reassignDesignerCurrentName').textContent = `Current designer: ${currentDesignerName}`;
                        document.getElementById('reassignDesignerSelect').value = '';
                        document.getElementById('reassignDesignerReason').value = '';
                        reassignArea.setAttribute('data-task-id', task.id);
                    } else {
                        reassignArea.style.display = 'none';
                    }
                }

                // Reassignment History
                const reassignHistoryBox = document.getElementById('modalReassignmentHistoryBox');
                const reassignHistoryEl = document.getElementById('modalReassignmentHistory');
                const reassignCountEl = document.getElementById('modalReassignmentsCount');
                if (reassignHistoryBox && reassignHistoryEl) {
                    const reassignments = task.reassignments_history || [];
                    if (reassignments.length > 0) {
                        reassignHistoryBox.style.display = 'block';
                        reassignCountEl.textContent = reassignments.length;
                        reassignHistoryEl.innerHTML = '';
                        reassignments.forEach(r => {
                            const date = new Date(r.created_at);
                            const dateStr = date.toLocaleString(undefined, {month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit'});
                            const fromName = r.from_user?.name || 'Unassigned';
                            const toName = r.to_user?.name || 'Unknown';
                            const byName = r.reassigned_by?.name || 'Unknown';
                            const reasonHtml = r.reason ? `<div style="font-size:11px; color:var(--color-text-secondary); margin-top:4px; font-style:italic;">Reason: ${r.reason}</div>` : '';
                            reassignHistoryEl.innerHTML += `
                                <div style="padding:10px 14px; background:rgba(236,72,153,0.04); border:1px solid rgba(236,72,153,0.1); border-radius:10px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                        <span style="font-size:12px; font-weight:700; color:#ec4899;">${fromName} → ${toName}</span>
                                        <span style="font-size:10px; color:var(--color-text-secondary); font-weight:600;">${dateStr}</span>
                                    </div>
                                    <div style="font-size:11px; color:var(--color-text-secondary);">Reassigned by <strong>${byName}</strong></div>
                                    ${reasonHtml}
                                </div>
                            `;
                        });
                    } else {
                        reassignHistoryBox.style.display = 'none';
                    }
                }

                // Populate top deadlines list
                const topDeadlinesEl = document.getElementById('modalTopDeadlines');
                if (topDeadlinesEl) {
                    topDeadlinesEl.innerHTML = '';
                    
                    // 1. Deliverable Due Date
                    const displayDeadline = task.deadline || (task.parent ? task.parent.deadline : null);
                    if (displayDeadline) {
                        const dDate = new Date(displayDeadline);
                        const dateStr = dDate.toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'});
                        const dueBadge = document.createElement('div');
                        dueBadge.style.cssText = 'background: rgba(59, 130, 246, 0.08); color: #3b82f6; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.15); font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.02em;';
                        dueBadge.textContent = `Due: ${dateStr}`;
                        topDeadlinesEl.appendChild(dueBadge);
                    }
                }

                // Deliverable Deadline Input Population
                const delDeadlineInput = document.getElementById('modalDeliverableDeadlineInput');
                if (delDeadlineInput) {
                    let dDateStr = '';
                    const displayDeadline = task.deadline || (task.parent ? task.parent.deadline : null);
                    if (displayDeadline) {
                        const dDate = new Date(displayDeadline);
                        const pad = n => String(n).padStart(2,'0');
                        dDateStr = `${dDate.getFullYear()}-${pad(dDate.getMonth()+1)}-${pad(dDate.getDate())}`;
                    }
                    delDeadlineInput.value = dDateStr;
                    const isBrandManagerOrAdmin = isAdmin || userRole === 'brandmanager';
                    delDeadlineInput.readOnly = !isBrandManagerOrAdmin;
                    const saveBtn = document.getElementById('saveDeadlineBtn');
                    if (!isBrandManagerOrAdmin) {
                        delDeadlineInput.style.opacity = '0.7';
                        delDeadlineInput.style.pointerEvents = 'none';
                        if (saveBtn) saveBtn.style.display = 'none';
                    } else {
                        delDeadlineInput.style.opacity = '1';
                        delDeadlineInput.style.pointerEvents = 'auto';
                        if (saveBtn) saveBtn.style.display = 'block';
                    }
                }

                const delRefFile = document.getElementById('deleteReferenceFile');
                if (delRefFile) delRefFile.value = '0';
                let refParts = [];

                let refFiles = [];
                if (task.reference_files_list && Array.isArray(task.reference_files_list)) {
                    refFiles = task.reference_files_list;
                } else if (task.reference_file) {
                    if (typeof task.reference_file === 'string' && task.reference_file.startsWith('[')) {
                        try { refFiles = JSON.parse(task.reference_file); } catch(e) { refFiles = [task.reference_file]; }
                    } else if (Array.isArray(task.reference_file)) {
                        refFiles = task.reference_file;
                    } else {
                        refFiles = [task.reference_file];
                    }
                }

                refFiles.forEach((fileUrl, index) => {
                    if (!fileUrl) return;
                    const isVideo = fileUrl.match(/\.(mp4|webm|ogg|mov)(?:$|\?)/i);
                    const boxId = `modalRefFileBox_${index}`;
                    const labelText = refFiles.length > 1 ? `Reference File #${index + 1}` : 'Reference File';
                    if (isVideo) {
                        refParts.push(`
                        <div id="${boxId}" style="display:flex; align-items:flex-end; gap:12px; margin-bottom:8px;">
                            <div style="display:inline-block;">
                                <video controls src="${fileUrl}" style="width:100%; max-width:260px; max-height:160px; border-radius:12px; border:1px solid var(--color-border-primary); margin-bottom:4px;"></video>
                                <span style="display:block; font-size:10px; font-weight:800; color:#0055D4; text-transform:uppercase;">${labelText}</span>
                            </div>
                            <button type="button" onclick="removeSpecificRefFile('${boxId}', ${index})" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; margin-bottom:8px;">Remove</button>
                        </div>`);
                    } else {
                        refParts.push(`
                        <div id="${boxId}" style="display:flex; align-items:flex-end; gap:12px; margin-bottom:8px;">
                            <div onclick="openImagePreview('${fileUrl}', false)" style="display:inline-block; cursor:pointer;">
                                <img src="${fileUrl}" style="width:100%; max-width:180px; max-height:140px; height:auto; border-radius:12px; border:1px solid var(--color-border-primary); margin-bottom:4px; object-fit:cover;">
                                <span style="display:block; font-size:10px; font-weight:800; color:#0055D4; text-transform:uppercase;">${labelText}</span>
                            </div>
                            <button type="button" onclick="removeSpecificRefFile('${boxId}', ${index})" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; margin-bottom:8px;">Remove</button>
                        </div>`);
                    }
                });

                let refUrls = [];
                if (task.reference_urls_list && Array.isArray(task.reference_urls_list)) {
                    refUrls = task.reference_urls_list;
                } else if (task.reference) {
                    if (typeof task.reference === 'string' && task.reference.startsWith('[')) {
                        try { refUrls = JSON.parse(task.reference); } catch(e) { refUrls = [task.reference]; }
                    } else if (Array.isArray(task.reference)) {
                        refUrls = task.reference;
                    } else {
                        refUrls = task.reference.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean);
                    }
                }

                refUrls.forEach((url, index) => {
                    if (!url) return;
                    const label = refUrls.length > 1 ? `Visit Link #${index + 1}` : 'Visit Reference Link';
                    const linkBoxId = `modalRefUrlBox_${index}`;
                    refParts.push(`
                    <div id="${linkBoxId}" style="display:inline-flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <a href="${url}" target="_blank" class="ref-chip" style="display:inline-flex; align-items:center; gap:6px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            ${label}
                        </a>
                        <button type="button" onclick="removeSpecificRefUrl('${linkBoxId}', ${index})" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;" title="Remove link">Remove</button>
                    </div>`);
                });

                if (refParts.length > 0) {
                    refHtml = `<div style="display:flex; flex-wrap:wrap; align-items:center; gap:16px;">${refParts.join('')}</div>`;
                } else {
                    refHtml = '<span style="color:#94a3b8; font-size:13px; font-weight:500;">No reference provided</span>';
                }
                const modRef = document.getElementById('modalReference');
                if (modRef) modRef.innerHTML = refHtml;
                
                const revForm = document.getElementById('revisionsForm');
                if (revForm) revForm.action = `/deliverables/${task.id}/revisions`;
                const subForm = document.getElementById('submitStageForm');
                if (subForm) subForm.action = `/deliverables/${task.id}/submit`;
                const artForm = document.getElementById('artworkDeliveryForm');
                if (artForm) artForm.action = `/deliverables/${task.id}/submit`;
                const artLabel = document.getElementById('artworkFileLabel');
                if (artLabel) artLabel.textContent = 'Choose image file…';

                // Update export links
                const btnExportDocx = document.getElementById('btnExportDocx');
                if (btnExportDocx) btnExportDocx.href = `/deliverables/${task.id}/export/docx`;
                const btnExportPpt = document.getElementById('btnExportPpt');
                if (btnExportPpt) btnExportPpt.href = `/deliverables/${task.id}/export/ppt`;

                // Team Grid
                const teamGrid = document.getElementById('modalTeamGrid');
                teamGrid.innerHTML = '';
                if (task.associates) {
                    const roles = [
                        {key: 'writer', label: 'Writer'},
                        {key: 'approver', label: 'Approver'},
                    ];
                    if (task.associates.further_approver && task.associates.further_approver !== 'None') {
                        roles.push({key: 'further_approver', label: 'Further Approver'});
                    }
                    roles.push(
                        {key: 'brand_manager', label: 'Brand Manager'},
                        {key: 'coordinator', label: 'Coordinator'},
                        {key: 'designer', label: 'Designer'}
                    );
                    
                    roles.forEach(role => {
                        const name = task.associates[role.key] || 'None';
                        
                        let rCol = '100,116,139';
                        if (role.key === 'writer') rCol = '59,130,246';
                        else if (role.key === 'approver' || role.key === 'further_approver') rCol = '234,88,12';
                        else if (role.key === 'brand_manager') rCol = '37,99,235';
                        else if (role.key === 'coordinator') rCol = '14,165,233';
                        else if (role.key === 'designer') rCol = '139,92,246';

                        const item = document.createElement('div');
                        item.style.cssText = `display:flex; align-items:center; gap:10px; padding:10px; background:rgba(${rCol},0.05); border-radius:12px; border:1px solid rgba(${rCol},0.2);`;
                        item.innerHTML = `
                            <div style="width:30px; height:30px; border-radius:50%; background:rgba(${rCol},0.1); color:rgb(${rCol}); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0; border:1.5px solid rgba(${rCol},0.25);">
                                ${name !== 'None' ? name.charAt(0) : '?'}
                            </div>
                            <div style="overflow:hidden;">
                                <div style="font-size:9px; font-weight:800; color:rgb(${rCol}); text-transform:uppercase; letter-spacing:0.02em; margin-bottom:1px; opacity:0.9;">${role.label}</div>
                                <div style="font-size:12px; font-weight:700; color:var(--color-text-primary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${name}</div>
                            </div>
                        `;
                        teamGrid.appendChild(item);
                    });
                }
                
                let artFiles = [];
                if (task.final_designs_list && Array.isArray(task.final_designs_list)) {
                    artFiles = task.final_designs_list;
                } else if (task.final_designs) {
                    if (typeof task.final_designs === 'string' && task.final_designs.startsWith('[')) {
                        try { artFiles = JSON.parse(task.final_designs); } catch(e) { artFiles = [task.final_designs]; }
                    } else if (Array.isArray(task.final_designs)) {
                        artFiles = task.final_designs;
                    } else {
                        artFiles = [task.final_designs];
                    }
                }

                let artUrls = [];
                if (task.final_designs_urls_list && Array.isArray(task.final_designs_urls_list)) {
                    artUrls = task.final_designs_urls_list;
                } else if (task.final_designs_link) {
                    if (typeof task.final_designs_link === 'string' && task.final_designs_link.startsWith('[')) {
                        try { artUrls = JSON.parse(task.final_designs_link); } catch(e) { artUrls = [task.final_designs_link]; }
                    } else if (Array.isArray(task.final_designs_link)) {
                        artUrls = task.final_designs_link;
                    } else {
                        artUrls = task.final_designs_link.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean);
                    }
                }

                let finalParts = [];
                artFiles.forEach((fileUrl, index) => {
                    if (!fileUrl) return;
                    const isVideo = /\.(mp4|webm|ogg|mov)(?:$|\?)/i.test(fileUrl);
                    const boxId = `modalArtFileBox_${index}`;
                    const labelText = artFiles.length > 1 ? `Artwork File #${index + 1}` : 'Preview Artwork';
                    finalParts.push(`
                        <div id="${boxId}" style="display:inline-block; margin-right:12px; margin-bottom:8px; vertical-align:top; text-align:center;">
                            <div onclick="openImagePreview('${fileUrl}', ${canDesignerEdit}, ${task.id})" style="text-decoration:none; cursor:pointer;">
                                ${isVideo ? `<video src="${fileUrl}" class="task-thumbnail" preload="metadata"></video>` : `<img src="${fileUrl}" class="task-thumbnail" alt="Final Design">`}
                                <span style="display:block; font-size:10px; font-weight:800; color:#10b981; text-transform:uppercase; margin-top:6px; text-align:center;">${labelText}</span>
                            </div>
                            ${canDesignerEdit ? `
                                <button type="button" onclick="removeSpecificArtFile('${boxId}', ${index})" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; padding:4px 8px; font-size:10px; margin-top:8px; width:100%; height:auto; line-height:1; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-weight:700;">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove
                                </button>
                            ` : ''}
                        </div>`);
                });

                artUrls.forEach((url, index) => {
                    if (!url) return;
                    const label = artUrls.length > 1 ? `External Link #${index + 1}` : 'External Link';
                    const linkBoxId = `modalArtUrlBox_${index}`;
                    finalParts.push(`
                        <div id="${linkBoxId}" style="display:inline-block; vertical-align:top; text-align:center; margin-right:12px; margin-bottom:8px;">
                            <a href="${url}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; padding:10px 16px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.2); border-radius:10px; color:#10b981; text-decoration:none; vertical-align:top;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span style="font-size:11px; font-weight:800; text-transform:uppercase;">${label}</span>
                            </a>
                            ${canDesignerEdit ? `
                                <button type="button" onclick="removeSpecificArtUrl('${linkBoxId}', ${index})" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; padding:4px 8px; font-size:10px; margin-top:8px; width:100%; height:auto; line-height:1; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-weight:700;">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove Link
                                </button>
                            ` : ''}
                        </div>`);
                });

                document.getElementById('modalFinal').innerHTML = finalParts.join('') || '<span style="color:#94a3b8; font-size:13px; font-weight:500;">Pending Delivery</span>';

                const artLinksContainer = document.getElementById('modalArtworkLinksContainer') || document.getElementById('modalArtworkLinksContainerPrj');
                if (artLinksContainer) {
                    artLinksContainer.innerHTML = '';
                    if (artUrls.length === 0) {
                        artLinksContainer.innerHTML = `
                            <div class="art-input-row">
                                <input type="url" id="modalArtworkUrl" name="final_designs_urls[]" form="artworkDeliveryForm" placeholder="https://drive.google.com/…" style="width:100%; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            </div>`;
                    } else {
                        artUrls.forEach((url, idx) => {
                            const row = document.createElement('div');
                            row.className = 'art-input-row';
                            row.style.cssText = 'display:flex; align-items:center; gap:10px; margin-top:8px;';
                            row.innerHTML = `
                                <input type="url" name="final_designs_urls[]" value="${url}" form="artworkDeliveryForm" placeholder="https://drive.google.com/…" style="flex:1; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                <button type="button" onclick="this.closest('.art-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove link input">✕ Remove</button>`;
                            artLinksContainer.appendChild(row);
                        });
                    }
                }

                // History
                const appBox = document.getElementById('modalApprovalsBox');
                const appHistory = document.getElementById('modalApprovalHistory');
                appHistory.innerHTML = '';
                if (task.approvals_history && task.approvals_history.length > 0) {
                    task.approvals_history.forEach(app => {
                        const dateStr = new Date(app.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                        const row = document.createElement('div');
                        row.style.cssText = 'display:flex; flex-direction:column; gap:8px; background:rgba(16, 185, 129, 0.05); padding:10px 14px; border-radius:10px; border:1px solid rgba(16, 185, 129, 0.1);';
                        
                        let notesHtml = '';
                        if (app.notes) {
                            notesHtml = `<div style="margin-top:4px; font-size:13px; color:var(--color-text-primary); font-weight:500; background:rgba(16, 185, 129, 0.05); padding:10px 12px; border-radius:8px; border:1px solid rgba(16, 185, 129, 0.15); font-style:italic; white-space:pre-wrap;">"${app.notes}"</div>`;
                        }

                        row.innerHTML = `
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size:12px; font-weight:700; color:var(--color-text-primary);">${app.user ? app.user.name : 'Unknown'}</div>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:10px; font-weight:800; color:#10b981; text-transform:uppercase; letter-spacing:0.05em; background:rgba(16, 185, 129, 0.1); padding:2px 8px; border-radius:6px;">${app.stage} Submitted</span>
                                    <span style="font-size:11px; color:#10b981; opacity:0.8; font-weight:600;">${dateStr}</span>
                                </div>
                            </div>
                            ${notesHtml}
                        `;
                        appHistory.appendChild(row);
                    });
                    appBox.style.display = 'block';
                    document.getElementById('modalApprovalsCount').textContent = task.approvals_history.length;
                    appHistory.style.display = 'none';
                    appBox.querySelector('.hist-toggle').classList.remove('open');
                    
                    const commentsArr = task.approvals_history.filter(app => app.notes && app.notes.trim() !== '');
                    const latestCommentAlert = document.getElementById('modalLatestCommentAlert');
                    if (latestCommentAlert) {
                        if (commentsArr.length > 0) {
                            const latestApp = commentsArr[commentsArr.length - 1]; // Assume last is newest
                            const cDateStr = new Date(latestApp.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                            document.getElementById('modalLatestCommentTitle').textContent = `${latestApp.stage} submission comment by ${latestApp.user ? latestApp.user.name : 'Unknown'}`;
                            document.getElementById('modalLatestCommentDate').textContent = cDateStr;
                            document.getElementById('modalLatestCommentText').textContent = `"${latestApp.notes}"`;
                            latestCommentAlert.style.display = 'block';
                        } else {
                            latestCommentAlert.style.display = 'none';
                        }
                    }
                } else {
                    appBox.style.display = 'none';
                    const latestCommentAlert = document.getElementById('modalLatestCommentAlert');
                    if (latestCommentAlert) latestCommentAlert.style.display = 'none';
                }

                // Revision History
                const revBox = document.getElementById('modalHistoryBox');
                const revHistory = document.getElementById('modalRevisionHistory');
                revHistory.innerHTML = '';
                if (task.revisions_history && task.revisions_history.length > 0) {
                    task.revisions_history.forEach(rev => {
                        const dateStr = new Date(rev.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                        const row = document.createElement('div');
                        row.style.cssText = 'display:flex; flex-direction:column; gap:8px; background:rgba(239, 68, 68, 0.05); padding:12px; border-radius:12px; border:1px solid rgba(239, 68, 68, 0.1); flex: 1 1 calc(25% - 12px); min-width: 240px; box-sizing: border-box;';
                        
                        let fixedBadge = '';
                        if (rev.fixed_at) {
                            const fixedDate = new Date(rev.fixed_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                            
                            // Find the corresponding approval record where the writer wrote what they changed.
                            const fixTime = new Date(rev.fixed_at).getTime();
                            const matchingApproval = task.approvals_history ? task.approvals_history.find(app => {
                                if (app.user_id !== rev.fixed_by_user_id) return false;
                                const appTime = new Date(app.created_at).getTime();
                                return Math.abs(appTime - fixTime) < 5000;
                            }) : null;

                            let noteHtml = '';
                            if (matchingApproval && matchingApproval.notes) {
                                noteHtml = `
                                    <div style="margin-top: 8px; padding: 10px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 8px; font-size: 12px; color: var(--color-text-primary); line-height: 1.4;">
                                        <div style="font-weight: 800; font-size: 9px; color: #10b981; text-transform: uppercase; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            What was changed:
                                        </div>
                                        <div>${matchingApproval.notes.replace(/\n/g, '<br>')}</div>
                                    </div>
                                `;
                            }

                            fixedBadge = `
                                <div style="display:flex; flex-direction:column; margin-top:4px; padding-top:8px; border-top:1px dashed rgba(239, 68, 68, 0.2);">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div style="display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:800; color:#10b981; text-transform:uppercase; background:rgba(16, 185, 129, 0.1); padding:4px 8px; border-radius:6px;">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Fixed by ${rev.fixed_by_user ? rev.fixed_by_user.name : 'Unknown'}
                                        </div>
                                        <div style="font-size:10px; color:#10b981; opacity:0.8; font-weight:600;">${fixedDate}</div>
                                    </div>
                                    ${noteHtml}
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

                        let contentHtml = `
                            <div style="font-size:13px; font-weight:500; color:var(--color-text-primary); line-height:1.5; padding:12px; background:var(--color-bg-primary); border-radius:10px; border:1px solid rgba(239,68,68,0.1); margin-top:8px;">
                                ${(rev.instructions || '').replace(/\n/g, '<br>')}
                        `;
                        if (rev.image_path) {
                            contentHtml += `
                                <div style="margin-top:12px;">
                                    <div onclick="openImagePreview('${rev.image_path}')" style="display:inline-flex; align-items:center; gap:12px; padding:6px; background:var(--color-bg-secondary); border:1px solid rgba(239,68,68,0.15); border-radius:8px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.05)'" onmouseout="this.style.background='var(--color-bg-secondary)'" title="Click to enlarge">
                                        <img src="${rev.image_path}" alt="Revision reference" style="height:48px; width:48px; object-fit:cover; border-radius:4px; border:1px solid rgba(0,0,0,0.05);">
                                        <div style="padding-right:8px;">
                                            <div style="font-size:11px; font-weight:700; color:#ef4444; margin-bottom:2px;">Attached Image</div>
                                            <div style="font-size:10px; font-weight:500; color:var(--color-text-secondary);">Click to enlarge</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        contentHtml += `</div>`;

                        row.innerHTML = `
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <div style="font-size:11px; font-weight:800; color:#ef4444; text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                                    <span>Requested by ${rev.user ? rev.user.name : 'Unknown'}</span>
                                    <span style="color:#ef4444; opacity:0.5;">•</span>
                                    <span>${rev.stage_at_revision || 'Unknown Stage'}</span>
                                </div>
                                <div style="font-size:10px; color:#ef4444; opacity:0.8; font-weight:600;">${dateStr}</div>
                            </div>
                            ${contentHtml}
                            ${fixedBadge}
                        `;
                        revHistory.appendChild(row);
                    });
                    revBox.style.display = 'block';
                    document.getElementById('modalRevisionsCount').textContent = task.revisions_history.length;
                    revHistory.style.display = 'none';
                    revBox.querySelector('.hist-toggle').classList.remove('open');
                } else revBox.style.display = 'none';

                // Revision Alert
                const revAlert = document.getElementById('modalRevisionAlert');
                if (task.revision_instructions) {
                    const alertTextEl = document.getElementById('modalRevisionAlertText');
                    alertTextEl.innerHTML = '';
                    
                    const flexContainer = document.createElement('div');
                    flexContainer.style.cssText = 'display:flex; gap:16px; align-items:flex-start;';
                    
                    const textNode = document.createElement('div');
                    textNode.style.cssText = 'flex:1; margin-top:2px; white-space:pre-wrap;';
                    textNode.textContent = task.revision_instructions;
                    flexContainer.appendChild(textNode);
                    
                    // Show image from the most recent revision entry if present
                    const latestRev = task.revisions_history && task.revisions_history.length
                        ? task.revisions_history[0]
                        : null;
                    if (latestRev && latestRev.image_path) {
                        const img = document.createElement('img');
                        img.src = latestRev.image_path;
                        img.alt = 'Revision reference';
                        img.style.cssText = 'display:block; max-width:180px; max-height:100px; border-radius:6px; border:1px solid rgba(239,68,68,0.2); object-fit:contain; cursor:pointer; background:rgba(0,0,0,0.02);';
                        img.onclick = () => openImagePreview(latestRev.image_path);
                        flexContainer.appendChild(img);
                    }
                    alertTextEl.appendChild(flexContainer);
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
                if (showRevisionBtn) showRevisionBtn.style.display = 'none';
                saveContentBtn.style.display = 'none';
                apprArea.style.display = 'none';
                apprArea.querySelector('select').disabled = true;
                bmArea.style.display = 'none';
                bmArea.querySelector('select').disabled = true;
                document.getElementById('modalFurtherApproverGroup').style.display = 'none';
                document.getElementById('modalFurtherApproverGroup').querySelector('select').disabled = true;
                document.getElementById('modalFurtherApproverGroup').querySelector('select').value = '';
                
                coordArea.style.display = 'none';
                coordArea.querySelector('select').disabled = true;
                dArea.style.display = 'none';
                dArea.querySelector('select').disabled = true;
                delArea.style.display = 'none';

                // Normalize role for comparison (already computed above)

                const canAct = isAdmin ||
                    (stage === 'Writer'          && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                    (stage === 'Assignee'        && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                    (stage === 'Writer Review'   && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                    (stage === 'Approver'          && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager')   && (!task.approver_id       || isAssignedApprover)) ||
                    (stage === 'Approver Review'   && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager')   && (!task.approver_id       || isAssignedApprover)) ||
                    (stage === 'Further Approver'  && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager')   && (!task.approver_id       || isAssignedApprover)) ||
                    (stage === 'Brand Manager'   && userRole === 'brandmanager' && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                    (stage === 'AM/BD'           && userRole === 'brandmanager' && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                    (stage === 'Final Approval'  && userRole === 'brandmanager' && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                    (stage === 'Coordinator'     && (userRole === 'coordinator' || userRole === 'approvercoordinator')  && (!task.coordinator_id  || isAssignedCoordinator)) ||
                    (stage === 'Designer'        && hasDesignerRole             && (!task.designer_id      || isAssignedDesigner));

                // Check if current user was the last person who submitted/approved this deliverable
                const approvals = task.approvals_history || task.approvalsHistory || [];
                const latestApproval = (approvals && approvals.length > 0) ? approvals[0] : null;
                const isLastSubmitter = latestApproval && (latestApproval.user_id == AUTH_USER_ID);

                // Check if the current user is specifically assigned to handle this current stage
                const isCurrentStageAssignee = 
                    (stage === 'Writer' || stage === 'Assignee' || stage === 'Writer Review') ? isAssignedWriter :
                    (stage === 'Approver' || stage === 'Approver Review' || stage === 'Further Approver') ? isAssignedApprover :
                    (stage === 'Brand Manager' || stage === 'AM/BD' || stage === 'Final Approval') ? isAssignedBrandMgr :
                    (stage === 'Coordinator') ? isAssignedCoordinator :
                    (stage === 'Designer') ? isAssignedDesigner : false;

                // If the user was the one who submitted the previous stage, and is not assigned to this next stage, disable the button
                const isWaitingForDifferentPerson = isLastSubmitter && !isCurrentStageAssignee;

                if (canAct && submitBtnForm) {
                    submitBtnForm.style.display = 'flex';
                    const nextBtn = document.getElementById('submitStageBtn');
                    const isLastStage = WORKFLOW_STAGES.indexOf(stage) >= WORKFLOW_STAGES.length - 2;

                    if (nextBtn) {
                        if (isWaitingForDifferentPerson) {
                            nextBtn.disabled = true;
                            nextBtn.style.opacity = '0.5';
                            nextBtn.style.cursor = 'not-allowed';
                            nextBtn.style.boxShadow = 'none';
                            nextBtn.textContent = 'Submitted · Waiting for ' + stage;
                            nextBtn.title = 'You have already submitted this deliverable. It is currently with the ' + stage + '.';
                        } else {
                            nextBtn.disabled = false;
                            nextBtn.style.opacity = '1';
                            nextBtn.style.cursor = 'pointer';
                            nextBtn.style.boxShadow = '0 4px 12px rgba(0,85,212,0.4)';
                            if (stage === 'Designer') nextBtn.textContent = 'Request for Approval';
                            else nextBtn.textContent = isLastStage ? 'Approve & Close' : 'Submit to Next';
                            nextBtn.title = '';
                        }
                    }

                    if (stage === 'Writer' || stage === 'Assignee') {
                        if (task.approver_id) {
                            if (apprArea) {
                                apprArea.style.display = 'none';
                                const sel = apprArea.querySelector('select');
                                if (sel) sel.disabled = true;
                            }
                        } else {
                            if (apprArea) {
                                apprArea.style.display = 'block';
                                const sel = apprArea.querySelector('select');
                                if (sel) sel.disabled = false;
                            }
                        }
                    }
                    if (stage === 'Approver') {
                        if (bmArea) {
                            bmArea.style.display = 'block';
                            const sel = bmArea.querySelector('select');
                            if (sel) sel.disabled = false;
                        }
                        
                        const faArea = document.getElementById('modalFurtherApproverGroup');
                        if (faArea) {
                            faArea.style.display = 'block';
                            const sel = faArea.querySelector('select');
                            if (sel) sel.disabled = false;
                        }
                    }
                    if (stage === 'Brand Manager' || stage === 'AM/BD') {
                        if (coordArea) {
                            coordArea.style.display = 'block';
                            const sel = coordArea.querySelector('select');
                            if (sel) sel.disabled = false;
                        }
                    }
                    if (stage === 'Coordinator') {
                        if (dArea) {
                            dArea.style.display = 'block';
                            const sel = dArea.querySelector('select');
                            if (sel) sel.disabled = false;
                        }
                    }
                    if (stage === 'Designer' && delArea) delArea.style.display = 'block';
                }

                // Edit Permissions (already computed above)
                const mTitle = document.getElementById('modalTaskTitle');
                const canEditTitle = writerEditPermission || isAdmin || ['writer','assignee','brandmanager','operationsmanager'].includes(userRole);
                if (mTitle) mTitle.readOnly = !canEditTitle;
                if (typeof quillConcept !== 'undefined' && quillConcept) quillConcept.enable(writerEditPermission);
                if (typeof quillCaption !== 'undefined' && quillCaption) quillCaption.enable(writerEditPermission);
                if (typeof quillCopy !== 'undefined' && quillCopy) quillCopy.enable(writerEditPermission);
                
                // Reference Edit Area
                const refEditArea = document.getElementById('modalReferenceEditArea');
                const canEditReference = writerEditPermission || isAdmin || hasWriterRole || ['writer','assignee','brandmanager','operationsmanager'].includes(userRole);
                if (refEditArea) {
                    refEditArea.style.display = canEditReference ? 'grid' : 'none';
                    const linksContainer = document.getElementById('modalReferenceLinksContainer') || document.getElementById('modalReferenceLinksContainerPrj');
                    if (linksContainer) {
                        linksContainer.innerHTML = '';
                        if (refUrls.length === 0) {
                            linksContainer.innerHTML = `
                                <div class="ref-input-row">
                                    <input type="url" id="modalReferenceUrl" name="reference_urls[]" form="submitStageForm" placeholder="https://..." style="width:100%; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                </div>`;
                        } else {
                            refUrls.forEach((url, idx) => {
                                const row = document.createElement('div');
                                row.className = 'ref-input-row';
                                row.style.cssText = 'display:flex; align-items:center; gap:10px; margin-top:8px;';
                                row.innerHTML = `
                                    <input type="url" name="reference_urls[]" value="${url}" form="submitStageForm" placeholder="https://..." style="flex:1; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                    <button type="button" onclick="this.closest('.ref-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove link input">✕ Remove</button>`;
                                linksContainer.appendChild(row);
                            });
                        }
                    }
                }

                if (canEditReference || (designerEditPermission && stage === 'Designer') || isAdmin || userRole === 'brandmanager') {
                    submitBtnForm.style.display = 'flex';
                    saveContentBtn.style.display = 'block';
                }

                const isReviewStage = ['Approver', 'Brand Manager', 'Final Approval', 'AM/BD', 'Writer Review', 'Approver Review'].includes(stage);
                const isAuthorizedToReview = isAdmin ||
                    (stage === 'Approver' && (userRole === 'approver' || userRole === 'operationsmanager')) ||
                    ((stage === 'Brand Manager' || stage === 'Final Approval') && userRole === 'brandmanager') ||
                    (stage === 'Writer Review' && (userRole === 'writer' || userRole === 'assignee')) ||
                    (stage === 'Approver Review' && (userRole === 'approver' || userRole === 'operationsmanager'));

                if (isReviewStage && isAuthorizedToReview && showRevisionBtn) {
                    showRevisionBtn.style.display = 'block';
                }

                // Show/reset revision target toggle for post-designer stages
                const postDesignerStages = ['Writer Review', 'Approver Review', 'Final Approval'];
                const modalRevisionTargetGroup = document.getElementById('modalRevisionTargetGroup');
                const modalRevisionTarget = document.getElementById('modalRevisionTarget');
                if (modalRevisionTargetGroup) {
                    if (postDesignerStages.includes(stage)) {
                        modalRevisionTargetGroup.style.display = 'block';
                        document.getElementById('modalReviseTargetDesigner').checked = true;
                        modalRevisionTarget.value = 'designer';
                        document.getElementById('modalRevisionTargetNote').textContent = 'The task will be sent back to the Designer.';
                    } else {
                        modalRevisionTargetGroup.style.display = 'none';
                        modalRevisionTarget.value = 'writer';
                        document.getElementById('modalRevisionTargetNote').textContent = 'The task will be sent back to the Writer.';
                    }
                }

                const isAuthorizedToDelete = isAdmin || userRole === 'brandmanager' || (hasWriterRole && task.writer_id == {{ $currentUserId }});
                const modalDeleteBtn = document.getElementById('modalDeleteBtn');
                if (modalDeleteBtn) {
                    modalDeleteBtn.style.display = isAuthorizedToDelete ? 'block' : 'none';
                }

                // Mark as ready button removed

                // Load client review summary and annotations count when the modal opens
                if (typeof updateReviewSummary === 'function') {
                    updateReviewSummary(task.id);
                }

                overlay.style.display = 'flex';
                setTimeout(() => { overlay.style.opacity = '1'; modal.classList.add('active'); }, 10);
            } catch (e) {
                console.error('Error in openTaskModal:', e);
                alert('Failed to open task details. Check console for details.');
            }
        }

        // Ready button UI updates and toggle functions removed

        function closeTaskModal(e) {
            if (e && e.target !== document.getElementById('taskModalOverlay')) return;
            const overlay = document.getElementById('taskModalOverlay');
            overlay.style.opacity = '0';
            overlay.querySelector('.cd-modal').classList.remove('active');
            setTimeout(() => { overlay.style.display = 'none'; toggleRevisionInput(false); }, 300);
        }

        async function deleteTaskFromModal() {
            if (!currentTaskData) return;
            const confirmed = await showConfirm('Delete Deliverable?', 'This cannot be undone. The deliverable and all its subtasks will be permanently removed.');
            if (!confirmed) return;

            const btn = document.getElementById('modalDeleteBtn');
            const origText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Deleting…';

            try {
                const res = await fetch(`/deliverables/${currentTaskData.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new URLSearchParams({ _method: 'DELETE' }),
                });

                if (res.ok) {
                    window.location.reload();
                } else {
                    const data = await res.json().catch(() => ({}));
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'You do not have permission to delete this deliverable.', type: 'error' } }));
                    btn.disabled = false;
                    btn.textContent = origText;
                }
            } catch (e) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Network error — please try again.', type: 'error' } }));
                btn.disabled = false;
                btn.textContent = origText;
            }
        }

        function showConfirm(title, message) {
            return window.customConfirm({ title: title, message: message, isDanger: true });
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
            if (!show) {
                // Reset file input and preview when closing
                const inp = document.getElementById('revisionImageInput');
                if (inp) inp.value = '';
                document.getElementById('revisionImageLabel').textContent = 'Choose image…';
                const prev = document.getElementById('revisionImagePreview');
                prev.src = ''; prev.style.display = 'none';
            }
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

            // Collect reference files — use FormData if any are attached
            const sbRefFiles = {};
            document.querySelectorAll('.batch-ref-file').forEach(input => {
                if (input.files && input.files.length > 0) {
                    sbRefFiles[input.getAttribute('data-task-id')] = input.files[0];
                }
            });
            const sbCsrf = document.querySelector('meta[name="csrf-token"]').content;
            let sbFetchOpts;
            if (Object.keys(sbRefFiles).length > 0) {
                const fd = new FormData();
                fd.append('batch_data', JSON.stringify(batchData));
                Object.entries(sbRefFiles).forEach(([id, file]) => fd.append('reference_files[' + id + ']', file));
                sbFetchOpts = { method: 'POST', headers: { 'X-CSRF-TOKEN': sbCsrf, 'Accept': 'application/json' }, body: fd };
            } else {
                sbFetchOpts = { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': sbCsrf, 'Accept': 'application/json' }, body: JSON.stringify({ batch_data: batchData }) };
            }

            try {
                const response = await fetch(`/deliverables/${taskId}/batch-submit`, sbFetchOpts);

                const data = await response.json();
                if (data.success) {
                    clearBatchDrafts();
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
            // Mutual exclusion for Further Approver and Brand Manager
            const furtherApproverSelect = document.getElementById('batchFurtherApproverSelect');
            const stakeholderSelect = document.getElementById('batchStakeholderSelect');

            if (furtherApproverSelect && stakeholderSelect) {
                furtherApproverSelect.addEventListener('change', function() {
                    if (this.value) {
                        stakeholderSelect.value = '';
                        stakeholderSelect.disabled = true;
                    } else {
                        stakeholderSelect.disabled = false;
                    }
                });

                stakeholderSelect.addEventListener('change', function() {
                    // Only apply mutual exclusion if both are visible (i.e. at Approver stage)
                    const furtherApproverGroup = document.getElementById('batchFurtherApproverGroup');
                    if (furtherApproverGroup && furtherApproverGroup.style.display !== 'none') {
                        if (this.value) {
                            furtherApproverSelect.value = '';
                            furtherApproverSelect.disabled = true;
                        } else {
                            furtherApproverSelect.disabled = false;
                        }
                    }
                });
            }

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
                        if (el.type === 'submit' || el.type === 'button' || el.tagName === 'BUTTON' || el.disabled) return;
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

                    // Client-side validation: if submitting to next stage, ensure required assignee is selected
                    const isSubmittingNext = !btn || btn.value !== 'save_only';
                    if (isSubmittingNext) {
                        const coordArea = document.getElementById('coordinatorSelectionArea');
                        if (coordArea && coordArea.style.display !== 'none') {
                            const coordSel = coordArea.querySelector('select');
                            if (coordSel && !coordSel.value) {
                                showErrorModal('Please select a **Coordinator** before submitting to the next stage.');
                                return;
                            }
                        }
                        const dArea = document.getElementById('designerSelectionArea');
                        if (dArea && dArea.style.display !== 'none') {
                            const dSel = dArea.querySelector('select');
                            if (dSel && !dSel.value) {
                                showErrorModal('Please select a **Designer** before submitting to the next stage.');
                                return;
                            }
                        }
                        const apprArea = document.getElementById('approverSelectionArea');
                        if (apprArea && apprArea.style.display !== 'none') {
                            const apprSel = apprArea.querySelector('select');
                            if (apprSel && !apprSel.value) {
                                showErrorModal('Please select an **Approver** before submitting to the next stage.');
                                return;
                            }
                        }
                        const bmArea = document.getElementById('brandManagerSelectionArea');
                        if (bmArea && bmArea.style.display !== 'none') {
                            const bmSel = bmArea.querySelector('select');
                            if (bmSel && !bmSel.value) {
                                showErrorModal('Please select a **Brand Manager** before submitting to the next stage.');
                                return;
                            }
                        }
                    }
                    
                    const originalText = btn ? btn.innerHTML : 'Submit';
                    if (btn) {
                        btn.innerHTML = '<span style="display:inline-flex;align-items:center;gap:8px;"><svg class="animate-spin" style="width:16px;height:16px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...</span>';
                        btn.disabled = true;
                    }

                    const formActionUrl = this.getAttribute('action');
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';

                    try {
                        const response = await fetch(formActionUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
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
            if (!message) return;
            // Convert markdown-style **bolding** to <b> or styled span
            let formattedMessage = message.replace(/\*\*(.*?)\*\*/g, '<span style="font-weight:900; color:var(--color-text-primary);">$1</span>');
            const msgEl = document.getElementById('taskErrorModalMessage');
            const overlay = document.getElementById('taskErrorModalOverlay');
            const box = document.getElementById('taskErrorModalBox');
            
            if (msgEl && overlay && box) {
                msgEl.innerHTML = formattedMessage;
                overlay.style.display = 'flex';
                setTimeout(() => {
                    overlay.style.opacity = '1';
                    box.style.opacity = '1';
                    box.style.transform = 'scale(1)';
                }, 10);
            } else {
                alert(message.replace(/\*\*/g, ''));
            }
        }
        
        function hideErrorModal() {
            const overlay = document.getElementById('taskErrorModalOverlay');
            const box = document.getElementById('taskErrorModalBox');
            
            if (overlay && box) {
                overlay.style.opacity = '0';
                box.style.opacity = '0';
                box.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 300);
            }
        }

        // deleteTaskFromModal uses currentTaskData.id and is defined above

async function reassignDesigner(btn) {
    const area = document.getElementById('reassignDesignerArea');
    const taskId = area?.getAttribute('data-task-id');
    const designerId = document.getElementById('reassignDesignerSelect').value;
    const reason = document.getElementById('reassignDesignerReason').value;

    if (!designerId) {
        alert('Please select a designer.');
        return;
    }

    if (!await window.customConfirm({ title: 'Reassign Designer', message: 'Are you sure you want to reassign this deliverable to a different designer?', isDanger: false })) return;

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'Reassigning...';
    }

    try {
        const response = await fetch(`/deliverables/${taskId}/reassign-designer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ designer_id: designerId, reason: reason })
        });

        const result = await response.json();
        if (response.ok && result.success) {
            alert(result.message || 'Designer reassigned successfully.');
            window.location.reload();
        } else {
            alert(result.message || 'Error reassigning designer.');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'Reassign';
            }
        }
    } catch (e) {
        console.error(e);
        alert('Network error.');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'Reassign';
        }
    }
}
    </script>
    <script>
        async function updateClientStatusInlineModal(selectEl, taskId) {
            const originalValue = selectEl.getAttribute('data-original') || selectEl.value;
            const newValue = selectEl.value;
            selectEl.disabled = true;

            try {
                const response = await fetch(`/deliverables/${taskId}/client-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ client_status: newValue })
                });
                
                const result = await response.json();
                if (response.ok && result.success) {
                    selectEl.setAttribute('data-original', newValue);
                    selectEl.disabled = false;
                    window.location.reload();
                } else {
                    alert(result.message || 'Error updating client status');
                    selectEl.value = originalValue;
                    selectEl.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Network error');
                selectEl.value = originalValue;
                selectEl.disabled = false;
            }
        }

        async function updatePriorityInlineModal(selectEl, taskId) {
            const originalValue = selectEl.getAttribute('data-original') || selectEl.value;
            const newValue = selectEl.value;
            selectEl.disabled = true;

            try {
                const response = await fetch(`/deliverables/${taskId}/priority`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ priority: newValue })
                });
                
                const result = await response.json();
                if (response.ok && result.success) {
                    selectEl.setAttribute('data-original', newValue);
                    selectEl.disabled = false;
                    window.location.reload();
                } else {
                    alert(result.message || 'Error updating priority');
                    selectEl.value = originalValue;
                    selectEl.disabled = false;
                }
            } catch (err) {
                console.error(err);
                alert('Network error');
                selectEl.value = originalValue;
                selectEl.disabled = false;
            }
        }
    </script>

{{-- ──────────────────────────────────────────────────────────────────────────
     Send Artwork to Client Modal
──────────────────────────────────────────────────────────────────────────── --}}
<div id="sendArtworkModalOverlay"
     style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.7); backdrop-filter:blur(8px); z-index:9999; justify-content:center; align-items:center; opacity:0; transition:opacity 0.25s;">
    <div style="background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:24px; padding:32px; width:480px; max-width:94vw; box-shadow:0 30px 80px rgba(0,0,0,0.25); transform:scale(0.96); transition:transform 0.25s;" id="sendArtworkModalBox">

        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:rgba(16,185,129,0.15); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" fill="none" stroke="#10b981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:16px; font-weight:800; color:var(--color-text-primary);">Send Artwork to Client</div>
                        <div style="font-size:11px; color:var(--color-text-secondary); margin-top:1px;">Single permanent shareable link for client proofing</div>
                    </div>
                </div>
            </div>
            <button onclick="closeSendArtworkModal()"
                    style="width:32px; height:32px; border-radius:9px; border:1px solid var(--color-border-primary); background:var(--color-bg-secondary); color:var(--color-text-secondary); cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; transition:all 0.15s;"
                    onmouseover="this.style.background='var(--color-border-primary)'"
                    onmouseout="this.style.background='var(--color-bg-secondary)'">✕</button>
        </div>

        {{-- Single Shareable Link Display Area --}}
        <div id="singleLinkSection" style="margin-bottom:20px; padding:16px; background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.25); border-radius:14px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div style="font-size:11px; font-weight:800; color:#10b981; text-transform:uppercase; letter-spacing:0.08em; display:flex; align-items:center; gap:6px;">
                    <span style="width:7px; height:7px; border-radius:50%; background:#10b981; display:inline-block;"></span>
                    Permanent Client Review Link
                </div>
                <div id="currentRoundBadge" style="font-size:10px; font-weight:700; background:#10b981; color:#fff; padding:2px 8px; border-radius:12px;">
                    ● Active Link
                </div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <input type="text" id="generatedLinkInput" readonly
                       style="flex:1; padding:10px 12px; border:1px solid rgba(16,185,129,0.25); border-radius:8px; font-size:11px; font-family:monospace; color:var(--color-text-primary); background:var(--color-bg-secondary); outline:none;">
                <button id="copyLinkBtn" onclick="copyGeneratedLink()"
                        style="padding:10px 16px; background:#10b981; color:#fff; border:none; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; white-space:nowrap; transition:all 0.15s; flex-shrink:0;"
                        onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    📋 Copy Link
                </button>
            </div>
            <p style="font-size:11px; color:var(--color-text-secondary); margin-top:8px; font-weight:500; line-height:1.4;">
                Share this link with your client. They can review artwork, leave comments, and approve designs without logging in. When revised artwork is uploaded, this link updates automatically.
            </p>
        </div>

        {{-- Footer Actions --}}
        <div style="display:flex; gap:10px; justify-content:space-between; align-items:center;">
            <a id="viewAllAnnotationsBtn" href="{{ route('artwork.dashboard', $deliverable) }}"
               style="display:inline-flex; align-items:center; gap:6px; padding:10px 14px; background:rgba(139,92,246,0.1); color:#8b5cf6; border:1px solid rgba(139,92,246,0.2); border-radius:10px; font-size:11px; font-weight:700; text-decoration:none; transition:all 0.15s;"
               target="_blank">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                View Feedback Dashboard
            </a>
            <button onclick="closeSendArtworkModal()"
                    style="display:inline-flex; align-items:center; gap:6px; padding:10px 18px; background:var(--color-bg-secondary); color:var(--color-text-primary); border:1px solid var(--color-border-primary); border-radius:10px; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.15s;">
                Done
            </button>
        </div>
    </div>
</div>

<script>
// ── Send Artwork Modal ────────────────────────────────────────────────────────

let currentDeliverableIdForArtwork = null;

async function openSendArtworkModal() {
    const overlay = document.getElementById('sendArtworkModalOverlay');
    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
        overlay.style.opacity = '1';
        document.getElementById('sendArtworkModalBox').style.transform = 'scale(1)';
    });

    const taskData = @json($deliverable);
    currentDeliverableIdForArtwork = taskData.id;

    // Load or create single review link
    await getOrCreateSingleLink();
    await loadExistingReviews(currentDeliverableIdForArtwork);
}

function closeSendArtworkModal() {
    const overlay = document.getElementById('sendArtworkModalOverlay');
    overlay.style.opacity = '0';
    document.getElementById('sendArtworkModalBox').style.transform = 'scale(0.96)';
    setTimeout(() => { overlay.style.display = 'none'; }, 250);
}

async function getOrCreateSingleLink() {
    try {
        const resp = await fetch(`/deliverables/${currentDeliverableIdForArtwork}/send-artwork`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({}),
        });

        const data = await resp.json();
        if (resp.ok && data.success) {
            document.getElementById('generatedLinkInput').value = data.url;
            document.getElementById('currentRoundBadge').textContent = '● Active Link';
        }
    } catch(e) {
        console.error('Error fetching review link:', e);
    }
}

function copyGeneratedLink() {
    const input = document.getElementById('generatedLinkInput');
    const btn = document.getElementById('copyLinkBtn');
    if (!input.value) return;

    input.select();
    navigator.clipboard.writeText(input.value);

    const origText = btn.innerHTML;
    btn.innerHTML = '✓ Copied!';
    btn.style.background = '#059669';
    setTimeout(() => {
        btn.innerHTML = origText;
        btn.style.background = '#10b981';
    }, 2000);
}

async function loadExistingReviews(deliverableId) {
    try {
        const resp = await fetch(`/deliverables/${deliverableId}/artwork-reviews-json`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const reviews = await resp.json();

        // Update the summary badge in the deliverable modal
        const summaryEl = document.getElementById('reviewLinksSummary');
        const contentEl = document.getElementById('reviewLinksContent');
        const viewLink  = document.getElementById('viewAnnotationsLink');
        if (summaryEl && contentEl) {
            const totalAnnot = reviews.reduce((s, r) => s + (r.annotations_count || 0), 0);
            const openAnnot  = reviews.reduce((s, r) => s + (r.unresolved_count || 0), 0);
            summaryEl.style.display = 'block';
            contentEl.innerHTML = `
                <div style="display:flex; gap:16px; align-items:center;">
                    <span><strong style="color:#10b981;">● Active Review Link</strong></span>
                    <span><strong style="color:var(--color-text-primary);">${totalAnnot}</strong> total note(s)</span>
                    ${openAnnot > 0 ? `<span style="color:#f59e0b;"><strong>${openAnnot}</strong> open</span>` : '<span style="color:#10b981;">All notes resolved ✓</span>'}
                </div>`;
        }
        if (viewLink && reviews.length > 0) {
            viewLink.style.display = 'inline-flex';
            viewLink.href = `/deliverables/${deliverableId}/artwork-review`;
        }
    } catch(e) { console.error(e); }
}

async function updateReviewSummary(deliverableId) {
    loadExistingReviews(deliverableId); // re-uses the same logic
}

function copyGeneratedLink() {
    const val = document.getElementById('generatedLinkInput').value;
    copyToClipboard(val, document.getElementById('copyLinkBtn'));
}

function copyToClipboard(text, btn) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.textContent;
            btn.textContent = '✓ Copied!';
            setTimeout(() => { btn.textContent = orig; }, 2000);
        }).catch(() => {
            fallbackCopyToClipboard(text, btn);
        });
    } else {
        fallbackCopyToClipboard(text, btn);
    }
}

function fallbackCopyToClipboard(text, btn) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed'; // Avoid scrolling to bottom
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        const orig = btn.textContent;
        btn.textContent = '✓ Copied!';
        setTimeout(() => { btn.textContent = orig; }, 2000);
    } catch (err) {
        console.error('Fallback copy failed', err);
    }
    document.body.removeChild(ta);
}

// Close overlay when clicking outside box
document.getElementById('sendArtworkModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeSendArtworkModal();
});

// Image Preview Functions
function openImagePreview(url, canRemove = false, deliverableId = null) {
    const isVideo = url.match(/\.(mp4|webm|ogg|mov)(?:$|\?)/i);
    if (isVideo) {
        document.getElementById('imagePreviewSrc').style.display = 'none';
        document.getElementById('imagePreviewSrc').src = '';
        document.getElementById('videoPreviewSrc').style.display = 'block';
        document.getElementById('videoPreviewSrc').src = url;
    } else {
        document.getElementById('videoPreviewSrc').style.display = 'none';
        document.getElementById('videoPreviewSrc').src = '';
        document.getElementById('imagePreviewSrc').style.display = 'block';
        document.getElementById('imagePreviewSrc').src = url;
    }
    document.getElementById('imagePreviewDownload').href = url;
    
    const removeBtn = document.getElementById('imagePreviewRemoveBtn');
    if (removeBtn) {
        if (canRemove) {
            removeBtn.style.display = 'inline-flex';
            if (deliverableId) {
                document.getElementById('submitStageForm').action = `/deliverables/${deliverableId}/submit`;
            }
        } else {
            removeBtn.style.display = 'none';
        }
    }
    
    const overlay = document.getElementById('imagePreviewOverlay');
    overlay.style.display = 'flex';
    setTimeout(() => {
        overlay.style.opacity = '1';
        overlay.querySelector('.cd-modal').classList.add('active');
    }, 10);
}

function downloadMedia(event, url) {
    event.preventDefault();
    const btn = event.currentTarget;
    const originalContent = btn.innerHTML;
    btn.innerHTML = 'Downloading...';
    btn.style.pointerEvents = 'none';
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.blob();
        })
        .then(blob => {
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = blobUrl;
            const filename = url.substring(url.lastIndexOf('/') + 1).split('?')[0] || 'artwork';
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(blobUrl);
            a.remove();
        })
        .catch(err => {
            console.error('Download via fetch failed, opening in new tab', err);
            window.open(url, '_blank');
        })
        .finally(() => {
            btn.innerHTML = originalContent;
            btn.style.pointerEvents = 'auto';
        });
}

function closeImagePreview(e) {
    if (e && e.target !== document.getElementById('imagePreviewOverlay')) return;
    const overlay = document.getElementById('imagePreviewOverlay');
    overlay.style.opacity = '0';
    overlay.querySelector('.cd-modal').classList.remove('active');
    setTimeout(() => {
        overlay.style.display = 'none';
        document.getElementById('imagePreviewSrc').src = '';
    }, 300);
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof openTaskModal === 'function') {
        const deliverableData = @json($deliverable);
        if (deliverableData && deliverableData.id) {
            openTaskModal(deliverableData);
        }
    }
});
</script>

<!-- Image Preview Modal -->
<div id="imagePreviewOverlay" class="cd-modal-overlay" onclick="closeImagePreview(event)" style="z-index: 999999; justify-content: center; align-items: center;">
    <div class="cd-modal" style="max-width: 90%; max-height: 90vh; background: transparent; border: none; box-shadow: none; display: flex; flex-direction: column; align-items: center; justify-content: center;" onclick="event.stopPropagation()">
        <div style="position: absolute; top: 16px; right: 16px; z-index: 10;">
            <button onclick="closeImagePreview()" style="background: rgba(15, 23, 42, 0.6); border: none; color: #fff; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(4px);">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <img id="imagePreviewSrc" src="" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 12px; border: 4px solid var(--color-bg-primary); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <video id="videoPreviewSrc" src="" controls style="display:none; max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 12px; border: 4px solid var(--color-bg-primary); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);"></video>
        <div style="margin-top: 12px; display: flex; gap: 12px; justify-content: center; align-items: center;">
            <a id="imagePreviewDownload" href="" download target="_blank" class="cd-btn cd-btn-primary" style="padding: 10px 24px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 10px 20px rgba(0, 85, 212, 0.2);" onclick="downloadMedia(event, this.href)">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Original
            </a>
            <button type="submit" name="delete_final_designs" value="1" form="submitStageForm" id="imagePreviewRemoveBtn" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; padding: 10px 24px; font-size: 13px; font-weight: 700; display: none; align-items: center; gap: 8px; justify-content: center; background: rgba(239, 68, 68, 0.1); backdrop-filter: blur(4px);">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Remove Artwork
            </button>
        </div>
    </div>
</div>

<!-- Centered Error Modal -->
<div id="taskErrorModalOverlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:9999999; align-items:center; justify-content:center; padding:20px; text-align:center; transition:opacity 0.3s ease; opacity:0;">
    <div id="taskErrorModalBox" style="background:var(--color-bg-primary); border-radius:24px; padding:32px; max-width:400px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); transform:scale(0.95); transition:transform 0.3s ease, opacity 0.3s ease; opacity:0;">
        <div style="width:64px; height:64px; border-radius:16px; background:#fef2f2; color:#ef4444; display:flex; align-items:center; justify-content:center; margin:0 auto 24px;">
            <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <h3 style="font-size:20px; font-weight:900; color:var(--color-text-primary); margin-bottom:12px;">Alert</h3>
        <p id="taskErrorModalMessage" style="font-size:14px; font-weight:500; color:var(--color-text-secondary); line-height:1.6; margin-bottom:32px;"></p>
        <button onclick="hideErrorModal()" style="background:#0f172a; color:#fff; border:none; width:100%; padding:14px; border-radius:14px; font-weight:800; font-size:14px; cursor:pointer; box-shadow:0 10px 20px rgba(15,23,42,0.2);">Got it</button>
    </div>
</div>
</x-layout>
