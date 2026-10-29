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
        .subtask-pill { display:inline-flex; align-items:center; justify-content:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:9.5px; font-weight:800; text-transform:uppercase; letter-spacing:0.04em; border:1px solid; white-space:nowrap; max-width:100%; box-sizing:border-box; }
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
        .dark .ql-editor,
        .dark .ql-editor *,
        .dark .ql-container.ql-snow.ql-disabled .ql-editor,
        .dark .ql-container.ql-snow.ql-disabled .ql-editor * {
            color: var(--color-text-primary) !important;
        }
        .ql-container.ql-snow.ql-disabled .ql-editor {
            color: var(--color-text-primary) !important;
            cursor: default;
        }
        
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
        #taskModalOverlay.cd-modal-overlay, #taskModalOverlay { 
            position: static !important; 
            inset: auto !important;
            top: auto !important;
            bottom: auto !important;
            left: auto !important;
            right: auto !important;
            display: block !important; 
            opacity: 1 !important; 
            z-index: 1 !important; 
            background: transparent !important; 
            backdrop-filter: none !important; 
            padding: 0 !important; 
            margin: 20px 0 0 0 !important;
            clear: both !important;
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            max-height: none !important;
        }
        #taskModalOverlay .cd-modal { 
            position: static !important;
            inset: auto !important;
            top: auto !important;
            bottom: auto !important;
            left: auto !important;
            right: auto !important;
            max-width: 100% !important; 
            width: 100% !important; 
            height: auto !important;
            max-height: none !important;
            margin: 0 !important; 
            transform: none !important; 
            box-shadow: none !important; 
            background: transparent !important; 
            border-radius: 0 !important; 
            border: none !important; 
            overflow: visible !important; 
            display: block !important;
        }
        #taskModalOverlay .cd-modal-header { 
            position: static !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 0 16px 0 !important; 
            border-bottom: none !important; 
            height: auto !important;
            max-height: none !important;
        }
        #taskModalOverlay .cd-modal-body { 
            position: static !important;
            padding: 0 !important; 
            overflow: visible !important; 
            height: auto !important; 
            max-height: none !important;
            flex: none !important; 
            display: block !important;
        }
        #modalTaskTitle { border: 1px solid var(--color-border-primary) !important; background: var(--color-bg-secondary) !important; }

        #reassignDesignerModalOverlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 10001 !important;
            background: rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(4px) !important;
            display: none;
            justify-content: center !important;
            align-items: center !important;
            padding: 20px !important;
            box-sizing: border-box !important;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        #reassignDesignerModalOverlay .cd-modal {
            position: relative !important;
            inset: auto !important;
            margin: auto !important;
            width: 92% !important;
            max-width: 480px !important;
            height: auto !important;
            max-height: 90vh !important;
            border-radius: 20px !important;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5) !important;
            border: 1px solid rgba(236,72,153,0.3) !important;
            overflow: hidden !important;
            transform: scale(0.96) !important;
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease !important;
            background: var(--color-bg-primary) !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #reassignDesignerModalOverlay .cd-modal.active {
            transform: scale(1) !important;
        }

        /* Direct Design 2-Column Responsive Layout */
        .direct-design-modal {
            position: relative !important;
            inset: auto !important;
            margin: auto !important;
            width: 95vw !important;
            max-width: 1400px !important;
            height: auto !important;
            max-height: 94vh !important;
            border-radius: 20px !important;
            border: 1px solid var(--color-border-primary) !important;
            box-shadow: 0 25px 70px -15px rgba(0,0,0,0.6) !important;
        }
        #taskModalOverlay:has(.direct-design-modal) {
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            padding: 20px !important;
            box-sizing: border-box !important;
            overflow-y: auto !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        #taskModalOverlay:has(.direct-design-modal)::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .direct-design-modal,
        .direct-design-modal * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .direct-design-modal::-webkit-scrollbar,
        .direct-design-modal *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .direct-design-modal .cd-modal-body {
            padding: 24px 28px !important;
            padding-bottom: 36px !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .direct-design-modal .cd-modal-body::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .direct-design-modal .dd-two-col-layout {
            display: grid !important;
            grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr) !important;
            gap: 28px !important;
            align-items: start !important;
            width: 100% !important;
        }
        @media (max-width: 1024px) {
            .direct-design-modal .dd-two-col-layout {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }
            .direct-design-modal .dd-col-sidebar {
                position: static !important;
                max-height: none !important;
            }
        }
        .direct-design-modal .dd-col-main {
            display: flex !important;
            flex-direction: column !important;
            gap: 20px !important;
            min-width: 0 !important;
        }
        .direct-design-modal .dd-col-sidebar {
            position: sticky !important;
            top: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 16px !important;
            min-width: 0 !important;
            overflow: visible !important;
            padding-bottom: 24px !important;
        }
        .direct-design-modal .dd-card-section {
            background: var(--color-bg-secondary) !important;
            border: 1px solid var(--color-border-primary) !important;
            border-radius: 16px !important;
            padding: 20px !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;
            margin: 0 !important;
        }
        .dark .direct-design-modal .dd-card-section {
            background: rgba(22, 19, 31, 0.75) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.25) !important;
        }
        .direct-design-modal .dd-section-label,
        .direct-design-modal .detail-label {
            font-size: 11px !important;
            font-weight: 800 !important;
            letter-spacing: 0.07em !important;
            text-transform: uppercase !important;
            color: var(--color-text-secondary) !important;
        }
        .dark .direct-design-modal .dd-section-label,
        .dark .direct-design-modal .detail-label {
            color: #cbd5e1 !important;
        }
        .direct-design-modal .workflow-steps {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 6px !important;
            padding: 12px 14px !important;
            background: var(--color-bg-secondary) !important;
            border: 1px solid var(--color-border-primary) !important;
            border-radius: 14px !important;
            margin-bottom: 0 !important;
            position: relative !important;
        }
        .dark .direct-design-modal .workflow-steps {
            background: rgba(22, 19, 31, 0.75) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .direct-design-modal .workflow-steps::before {
            display: none !important;
        }
        .direct-design-modal .step-item {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 5px !important;
            flex: 1 !important;
            min-width: 0 !important;
            padding: 4px 2px !important;
        }
        .direct-design-modal .step-dot {
            width: 24px !important;
            height: 24px !important;
            border-radius: 50% !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1.5px solid var(--color-border-primary) !important;
            background: var(--color-bg-primary) !important;
            color: var(--color-text-secondary) !important;
            transition: all 0.2s !important;
        }
        .direct-design-modal .step-label {
            font-size: 9.5px !important;
            font-weight: 700 !important;
            letter-spacing: 0.03em !important;
            text-transform: uppercase !important;
            text-align: center !important;
            color: var(--color-text-secondary) !important;
            white-space: normal !important;
            word-break: normal !important;
            line-height: 1.2 !important;
            overflow: visible !important;
            width: 100% !important;
        }
        .direct-design-modal .step-item.completed .step-dot {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3) !important;
        }
        .direct-design-modal .step-item.completed .step-label {
            color: #10b981 !important;
            font-weight: 800 !important;
        }
        .direct-design-modal .step-item.active .step-dot {
            background: #ea580c !important;
            border-color: #ea580c !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.2), 0 2px 8px rgba(234, 88, 12, 0.35) !important;
        }
        .direct-design-modal .step-item.active .step-label {
            color: #ea580c !important;
            font-weight: 900 !important;
        }
        .direct-design-modal .step-item:not(.completed):not(.active) .step-dot {
            opacity: 0.45 !important;
            background: transparent !important;
        }
        .direct-design-modal .step-item:not(.completed):not(.active) .step-label {
            opacity: 0.55 !important;
        }
        .direct-design-modal #modalMetaRow {
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            margin-bottom: 0 !important;
            padding: 16px !important;
            background: var(--color-bg-secondary) !important;
            border: 1px solid var(--color-border-primary) !important;
            border-radius: 14px !important;
        }
        .dark .direct-design-modal #modalMetaRow {
            background: rgba(22, 19, 31, 0.75) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .direct-design-modal #modalDeadlineRow {
            display: flex !important;
            flex-direction: column !important;
            gap: 12px !important;
            margin-bottom: 0 !important;
            padding-top: 12px !important;
            border-top: 1px solid var(--color-border-primary) !important;
            width: 100% !important;
        }
        .direct-design-modal #modalDeliverableDeadlineBox,
        .direct-design-modal #modalRevisionDeadlineBox {
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            width: 100% !important;
            min-width: 0 !important;
            margin: 0 !important;
        }
        .direct-design-modal #modalDeliverableDeadlineBox label,
        .direct-design-modal #modalRevisionDeadlineBox label {
            font-size: 11px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 0 !important;
            display: block !important;
        }
        .direct-design-modal #modalDeliverableDeadlineBox > div,
        .direct-design-modal #modalRevisionDeadlineBox > div {
            display: flex !important;
            gap: 8px !important;
            width: 100% !important;
            align-items: center !important;
        }
        .direct-design-modal #modalDeliverableDeadlineInput,
        .direct-design-modal #modalRevisionDeadlineInputBox {
            flex: 1 !important;
            width: 100% !important;
            min-width: 0 !important;
            padding: 9px 12px !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            box-sizing: border-box !important;
        }
        .direct-design-modal #ddSidebarActions {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            width: 100% !important;
            margin-top: 4px !important;
            padding-top: 14px !important;
            padding-bottom: 16px !important;
            border-top: 1px solid var(--color-border-primary) !important;
        }
        .direct-design-modal #submitStageForm {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            width: 100% !important;
        }
        .direct-design-modal #submitStageBtn {
            width: 100% !important;
            padding: 12px 18px !important;
            border-radius: 10px !important;
            font-weight: 800 !important;
            font-size: 13px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
            background: #ea580c !important;
            color: #ffffff !important;
            border: none !important;
            cursor: pointer !important;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.25) !important;
            transition: all 0.15s !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
        }
        .direct-design-modal #submitStageBtn:hover {
            background: #c2410c !important;
            transform: translateY(-1px) !important;
        }
        .direct-design-modal #saveContentBtn {
            width: 100% !important;
            padding: 10px 18px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            background: var(--color-bg-primary) !important;
            color: var(--color-text-primary) !important;
            border: 1.5px solid var(--color-border-primary) !important;
            cursor: pointer !important;
            transition: all 0.15s !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .direct-design-modal #saveContentBtn:hover {
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
        }
        .direct-design-modal #showRevisionBtn {
            width: 100% !important;
            padding: 10px 18px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            text-align: center !important;
        }
        .direct-design-modal #modalDeleteBtn {
            width: 100% !important;
            padding: 8px 14px !important;
            border-radius: 8px !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            text-align: center !important;
        }
    </style>

    <div class="container mx-auto px-4">
        <div class="mb-6" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; position:relative; z-index:10; margin-bottom:24px; width:100%; border-bottom:1px solid var(--color-border-primary); padding-bottom:16px;">
            <nav style="display:flex; gap:6px; align-items:center; font-size:12px; font-weight:600; color:var(--color-text-secondary); flex-wrap:wrap;">
                <a href="{{ route('brands.index') }}" style="text-decoration:none; color:inherit; transition:color 0.15s;">Brands</a>
                @if($deliverable->project && $deliverable->project->brand)
                    <span style="opacity:0.35;">/</span>
                    <a href="{{ route('brands.show', $deliverable->project->brand) }}" style="text-decoration:none; color:inherit; transition:color 0.15s;">{{ $deliverable->project->brand->name }}</a>
                @endif
                @if($deliverable->project)
                    <span style="opacity:0.35;">/</span>
                    <a href="{{ route('projects.show', $deliverable->project) }}" style="text-decoration:none; color:inherit; transition:color 0.15s;">{{ $deliverable->project->name }}</a>
                @endif
                @if($deliverable->parent)
                    <span style="opacity:0.35;">/</span>
                    <a href="{{ route('deliverables.show', $deliverable->parent) }}" style="text-decoration:none; color:inherit; transition:color 0.15s;">{{ $deliverable->parent->title }}</a>
                @endif
                <span style="opacity:0.35;">/</span>
                <span style="color:var(--color-text-primary); font-weight:700;">{{ $deliverable->title }}</span>
            </nav>
            <div style="display:flex; align-items:center; gap:10px;">
                <a href="{{ route('projects.show', $deliverable->project_id) }}" class="cd-btn" style="display:inline-flex; align-items:center; gap:8px; background:rgba(37,99,235,0.1); color:#2563eb; border:1px solid rgba(37,99,235,0.2); padding:8px 16px; font-size:12px; font-weight:800; border-radius:8px; transition:all 0.2s;" onmouseover="this.style.background='#2563eb'; this.style.color='#fff';" onmouseout="this.style.background='rgba(37,99,235,0.1)'; this.style.color='#2563eb';">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Project
                </a>
            </div>
        </div>

        <!-- Detail Modal -->
    <div id="taskModalOverlay" class="cd-modal-overlay" onclick="closeTaskModal(event)">
        <div class="cd-modal" >
            <div class="cd-modal-header" style="padding:0; margin-bottom:16px; align-items:center; gap:16px; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0;">
                    <div id="modalSubtaskType" class="subtask-pill" style="margin-bottom:0; flex-shrink:0;"></div>
                    <div id="modalTopDeadlines" style="display:flex; align-items:center; gap:8px; flex-shrink:0;"></div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    @if($isAdmin || in_array($userRole, ['brandmanager', 'coordinator', 'approvercoordinator']))
                    <button type="button" id="btnOpenReassignDesignerModal" onclick="openReassignDesignerModal()" style="display:none; padding:8px 14px; border-radius:8px; font-size:12px; font-weight:800; background:rgba(236,72,153,0.1); color:#ec4899; border:1px solid rgba(236,72,153,0.3); align-items:center; gap:6px; cursor:pointer; transition:all 0.15s; white-space:nowrap;" onmouseover="this.style.background='rgba(236,72,153,0.2)'" onmouseout="this.style.background='rgba(236,72,153,0.1)'" title="Reassign Designer">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Reassign Designer
                    </button>
                    @endif
                    <a id="btnExportPpt" href="{{ route('deliverables.export.ppt', $deliverable) }}" class="cd-btn" style="padding:8px 14px; font-size:12px; background:#ea580c; color:#fff; border:none; box-shadow:0 4px 12px rgba(234,88,12,0.25); font-weight:800; border-radius:8px; transition:transform 0.15s;" onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='none'" title="Download PPT">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PPT
                    </a>
                    <button type="button" onclick="closeTaskModal()" class="cd-btn cd-btn-outline" style="padding:8px; border-radius:8px; color:var(--color-text-secondary); cursor:pointer;" title="Close Modal">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="cd-modal-body">
                <div id="standardTopContainer">
                <!-- 1. Workflow Progress Stepper (TOP) -->
                @php $stages = $stages ?? ($deliverable->getStages() ?? []); @endphp
                <div class="workflow-steps" id="modalWorkflowSteps" style="margin-top: 4px; margin-bottom: 20px;">
                    @foreach(collect($stages)->filter(fn($s) => !in_array($s, ['Closed', 'Close']))->values() as $index => $stage)
                        <div class="step-item" data-stage="{{ $stage }}">
                            <div class="step-dot">{{ $index + 1 }}</div>
                            <div class="step-label">{{ $stage }}</div>
                        </div>
                    @endforeach
                </div>

                <!-- 2. Compact Metadata Bar -->
                <div id="modalMetaBar" style="display:flex; flex-direction:column; gap:12px; margin-bottom:16px; background:var(--color-bg-secondary); padding:12px 16px; border-radius:12px; border:1px solid var(--color-border-primary);">
                    <!-- Row 1: Core Stage, Priority & Client Status -->
                    <div id="modalMetaRow" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
                        <div class="detail-item" style="display:flex; align-items:center; gap:8px; margin:0;">
                            <label class="detail-label" style="margin:0; flex-shrink:0;">Current Stage:</label>
                            <div id="modalStage" class="detail-val" style="font-weight:900; color:#0055D4; font-size:13px;">-</div>
                        </div>
                        <div class="detail-item" style="display:flex; align-items:center; gap:8px; margin:0;">
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
                        <div class="detail-item" style="display:flex; align-items:center; gap:8px; margin:0;">
                            <label class="detail-label" style="margin:0; flex-shrink:0;">Client Status:</label>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div id="modalClientStatusDisplay" class="detail-val" style="font-weight:800; font-size:12px; color:var(--color-text-primary);">Not Sent</div>
                                <a id="modalClientReviewLink" href="#" target="_blank" style="display:none; align-items:center; gap:3px; font-size:11px; font-weight:800; color:#3b82f6; text-decoration:none;" title="Open Client Review Page">
                                    🔗 Review Link
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Deadlines (with dedicated breathing room and clean alignment) -->
                    <div id="modalDeadlineRow" style="display:flex; flex-wrap:wrap; align-items:center; gap:20px; padding-top:10px; border-top:1px solid var(--color-border-primary);">
                        <div class="detail-item" id="modalDeliverableDeadlineBox" style="display:flex; align-items:center; gap:8px; margin:0;">
                            <label class="detail-label" style="color:#3b82f6; margin:0; flex-shrink:0;">Deliverable Due:</label>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <input type="date" id="modalDeliverableDeadlineInput" name="deadline" form="submitStageForm" min="{{ date('Y-m-d') }}"
                                    style="width:130px; padding:4px 8px; border:1.5px solid rgba(59,130,246,0.25); border-radius:6px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; transition:border-color 0.15s; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(59,130,246,0.25)'">
                                <button type="button" id="saveDeadlineBtn" style="display:none; padding:4px 8px; background:#3b82f6; color:#fff; border:none; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;" onclick="saveDeliverableDeadline(this)">Save</button>
                            </div>
                        </div>
                        <div class="detail-item" id="modalDesignerDeadlineBox" style="display:flex; align-items:center; gap:8px; margin:0;">
                            <label class="detail-label" style="color:#ec4899; margin:0; flex-shrink:0;">Designer Due:</label>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <input type="date" id="modalDesignerDeadlineInput" name="designer_deadline" form="submitStageForm"
                                    style="width:130px; padding:4px 8px; border:1.5px solid rgba(236,72,153,0.25); border-radius:6px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; transition:border-color 0.15s; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#ec4899'" onblur="this.style.borderColor='rgba(236,72,153,0.25)'"
                                    onchange="showSaveDesignerDeadlineBtn()">
                                <button type="button" id="saveDesignerDeadlineBtn" style="display:none; padding:4px 8px; background:#ec4899; color:#fff; border:none; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;" onclick="saveDesignerDeadline(this)">Save</button>
                            </div>
                        </div>
                        <div class="detail-item" id="modalRevisionDeadlineBox" style="display:none; align-items:center; gap:8px; margin:0;">
                            <label class="detail-label" style="color:#ef4444; margin:0; flex-shrink:0;">Revision Due:</label>
                            <div style="display:flex; gap:6px; align-items:center;">
                                <input type="date" id="modalRevisionDeadlineInputBox" name="revision_deadline" form="submitStageForm"
                                    style="width:130px; padding:4px 8px; border:1.5px solid rgba(239,68,68,0.25); border-radius:6px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; transition:border-color 0.15s; box-sizing:border-box;"
                                    onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='rgba(239,68,68,0.25)'"
                                    onchange="showSaveRevisionDeadlineBtn()">
                                <button type="button" id="saveRevisionDeadlineBtn" style="display:none; padding:4px 8px; background:#ef4444; color:#fff; border:none; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;" onclick="saveRevisionDeadline(this)">Save</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Deliverable Name Field (Below Current Stage / Metadata Bar) -->
                <div id="modalTaskTitleRow" style="display:flex; align-items:center; gap:12px; margin-bottom:20px; background:var(--color-bg-secondary); padding:10px 16px; border-radius:12px; border:1px solid var(--color-border-primary);">
                    <label class="detail-label" style="margin:0; flex-shrink:0; font-size:11px; font-weight:900; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.1em;">Deliverable Name:</label>
                    <input type="text" id="modalTaskTitle" name="title" form="submitStageForm" class="batch-field" data-field="title" value="{{ $deliverable->title ?? '' }}" style="font-size:15px; font-weight:800; color:var(--color-text-primary); margin:0; border:1.5px solid var(--color-border-primary); background:var(--color-bg-primary); width:100%; flex:1; outline:none; border-radius:8px; padding:6px 12px; transition:all 0.2s;" onfocus="this.style.borderColor='#0055D4'; this.style.background='var(--color-bg-primary)'" onblur="this.style.borderColor='var(--color-border-primary)'; this.style.background='var(--color-bg-primary)'">
                </div>


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
                        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; margin-bottom:6px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <svg width="18" height="18" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span style="color:#ef4444; font-weight:900; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Revision Requested</span>
                                <span id="modalRevisionAlertTargetBadge" style="display:none; align-items:center; gap:3px; background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25); padding:2px 7px; border-radius:5px; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.04em;"></span>
                            </div>
                            <div id="modalRevisionDeadlineBadge" style="display:none; align-items:center; gap:6px; background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25); padding:3px 8px; border-radius:6px; font-size:11px; font-weight:800; letter-spacing:0.02em;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span id="modalRevisionDeadlineText"></span>
                            </div>
                        </div>
                        <div style="display:flex; gap:12px; align-items:flex-start;">
                            <div id="modalRevisionAlertText" style="flex:1; color:#ef4444; font-size:13px; font-weight:500; line-height:1.5; background:var(--color-bg-primary); padding:10px 12px; border-radius:8px; border:1px solid rgba(239, 68, 68, 0.3);"></div>
                            <img id="modalRevisionAlertImage" src="" style="display:none; max-width:120px; max-height:80px; border-radius:8px; border:1px solid rgba(239, 68, 68, 0.3); object-fit:cover;">
                        </div>
                    </div>
                </div>
            </div> <!-- /#standardTopContainer -->

            <!-- 2-Column Responsive Layout Container for Direct Design -->
            <div id="ddTwoColLayout" class="dd-two-col-layout" style="display:none;">
                <div id="ddColMain" class="dd-col-main"></div>
                <div id="ddColSidebar" class="dd-col-sidebar">
                    <div id="ddSidebarMetaCard"></div>
                    <div id="ddSidebarActions"></div>
                </div>
            </div>

            <div class="detail-grid" id="standardDetailGrid">
                    <div class="detail-item full dd-card-section" id="modalConceptGroup" style="margin-bottom: 24px !important; padding-top: 16px;">
                        @php
                            $isOtherDeliv = ($deliverable->project?->workflow_type ?? '') !== 'retainer' && !in_array(strtolower(trim($deliverable->post_type ?? '')), ['outlines', 'outline']);
                            $isBriefOnlyDeliverable = $isOtherDeliv || $deliverable->isDirectDesign();
                            $initialBriefOrConcept = $deliverable->concept ?: ($deliverable->notes ?: '');
                        @endphp
                        <label class="detail-label dd-section-label" style="font-size:12px; font-weight:900; color:#fff; background:{{ $deliverable->isDirectDesign() ? '#6366f1' : '#3b82f6' }}; padding:6px 12px; border-radius:6px; margin-bottom:12px; display:inline-flex; align-items:center; text-transform:uppercase; letter-spacing:0.1em; box-shadow:0 2px 10px rgba(59,130,246,0.3);">{{ $isBriefOnlyDeliverable ? 'Brief' : 'Concept' }}</label>
                        <input type="hidden" id="modalConcept" name="concept" value="{{ $initialBriefOrConcept }}" form="submitStageForm">
                        <div id="quillConcept" class="detail-val-textarea" style="padding:0; min-height:80px; border-radius:10px;">{!! $initialBriefOrConcept !!}</div>
                    </div>
                    <div class="detail-item full dd-card-section" id="modalCopyGroup" style="{{ $isBriefOnlyDeliverable ? 'display:none;' : '' }} margin-bottom: 24px !important; padding-top: 24px;">
                        <label class="detail-label dd-section-label" style="font-size:12px; font-weight:900; color:#fff; background:#3b82f6; padding:6px 12px; border-radius:6px; margin-bottom:12px; display:inline-flex; align-items:center; text-transform:uppercase; letter-spacing:0.1em; box-shadow:0 2px 10px rgba(59,130,246,0.3);">Copy</label>
                        <input type="hidden" id="modalSubtaskCopy" name="post_copy" form="submitStageForm">
                        <input type="hidden" id="deleteReferenceFile" name="delete_reference_file" value="0" form="submitStageForm">
                        <div id="quillCopy" class="detail-val-textarea" style="padding:0; min-height:140px; background:var(--color-bg-primary); border-color:var(--color-border-primary); border-radius:10px;"></div>
                    </div>
                    <div class="detail-item full dd-card-section" id="modalCaptionGroup" style="{{ $isBriefOnlyDeliverable ? 'display:none;' : '' }} margin-bottom: 24px !important; padding-top: 24px;">
                        <label class="detail-label dd-section-label" style="font-size:12px; font-weight:900; color:#fff; background:#3b82f6; padding:6px 12px; border-radius:6px; margin-bottom:12px; display:inline-flex; align-items:center; text-transform:uppercase; letter-spacing:0.1em; box-shadow:0 2px 10px rgba(59,130,246,0.3);">Caption</label>
                        <input type="hidden" id="modalCaption" name="caption" form="submitStageForm">
                        <div id="quillCaption" class="detail-val-textarea" style="padding:0; min-height:80px; border-radius:10px;"></div>
                    </div>
                    <div class="detail-item full dd-card-section" id="modalReferenceGroup" style="clear:both; position:relative; z-index:10; margin-top:32px !important; padding-top:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
                            <div id="modalReferenceLabel" class="detail-label dd-section-label" style="font-size:12px; font-weight:900; color:#fff; background:#0ea5e9; padding:6px 12px; border-radius:6px; display:inline-flex; align-items:center; text-transform:uppercase; letter-spacing:0.1em; box-shadow:0 2px 10px rgba(14,165,233,0.3); position:relative; z-index:20;">Reference</div>
                            <button type="button" id="toggleRefEditBtn" onclick="
                                const el = document.getElementById('modalReferenceEditArea');
                                if (el) {
                                    const isHidden = el.style.display === 'none' || !el.style.display;
                                    el.style.display = isHidden ? 'grid' : 'none';
                                    this.textContent = isHidden ? '✕ Close' : '+ Add / Edit References';
                                }
                            " style="display:none; background:rgba(14,165,233,0.1); color:#0ea5e9; border:1px solid rgba(14,165,233,0.25); border-radius:8px; padding:4px 10px; font-size:11px; font-weight:700; cursor:pointer;">+ Add / Edit References</button>
                        </div>
                        <div id="modalReference" class="detail-val" style="margin-bottom: 24px; display: block; overflow: hidden; margin-top: 8px;"></div>
                        <div id="modalReferenceEditArea" style="display:none; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top: 16px;">
                            <!-- Section 1: Reference Files & Images -->
                            <div style="padding:16px; background:rgba(0,85,212,0.03); border:1px solid rgba(0,85,212,0.12); border-radius:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span style="font-size:11px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em;">📁 Reference Images & Files</span>
                                        <div id="modalReferenceUploadStatus" style="display:none; align-items:center; gap:6px; font-size:10px; font-weight:800;"></div>
                                    </div>
                                    <button type="button" onclick="addReferenceInputRow('file')" style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:rgba(0,85,212,0.1); color:#0055D4; border:1px solid rgba(0,85,212,0.2); border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; transition:all 0.15s;">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        Add Image / File
                                    </button>
                                </div>
                                <div id="modalReferenceFilesContainer" style="display:flex; flex-direction:column; gap:10px;">
                                    <div class="ref-input-row">
                                        <input type="file" id="modalReferenceFileInput" name="reference_files[]" form="submitStageForm" accept="image/*,video/*,application/pdf,.pdf" multiple style="width:100%; padding:8px; border:1.5px dashed var(--color-border-primary); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="autoUploadModalReference(this)">
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


                    <div class="detail-item full dd-card-section" id="modalTeamGroup" style="border-top:1px solid var(--color-border-primary); padding-top:24px; margin-top:0px;">
                        <label class="detail-label dd-section-label" style="margin-bottom:16px; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.05em; font-size:11px;">Deliverable Team</label>
                        <div id="modalTeamGrid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px;"></div>
                    </div>
                    <div class="detail-item full hist-box dd-card-section" id="modalApprovalsBox" style="display:none; border-top:1px solid var(--color-border-primary); margin-top:10px;">
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

                    {{-- Direct Design Review Route (Send to Approver or Writer) --}}
                    <div id="directDesignRouteArea" class="detail-item full dd-card-section" style="display:none; margin-top:10px; padding:16px; background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); border-radius:16px;">
                        <label class="detail-label dd-section-label" style="color:var(--color-text-primary); margin-bottom:10px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; display:flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                            Next Review Step
                        </label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <label id="directRouteApproverLabel" style="display:flex; flex-direction:column; gap:4px; padding:12px; border:1.5px solid rgba(234,88,12,0.4); background:rgba(234,88,12,0.05); border-radius:12px; cursor:pointer; transition:all 0.15s;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <input type="radio" name="direct_design_route" id="directRouteApproverRadio" value="approver" checked onchange="handleDirectDesignRouteChange('approver')" style="accent-color:#ea580c; cursor:pointer;">
                                    <span style="font-weight:700; font-size:12px; color:var(--color-text-primary);">Send to Approver</span>
                                </div>
                                <span style="font-size:10px; color:var(--color-text-secondary); padding-left:22px; font-weight:500;">Mandatory approval · skips writer</span>
                            </label>
                            <label id="directRouteWriterLabel" style="display:flex; flex-direction:column; gap:4px; padding:12px; border:1.5px solid var(--color-border-primary); background:transparent; border-radius:12px; cursor:pointer; transition:all 0.15s;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <input type="radio" name="direct_design_route" id="directRouteWriterRadio" value="writer" onchange="handleDirectDesignRouteChange('writer')" style="accent-color:#0ea5e9; cursor:pointer;">
                                    <span style="font-weight:700; font-size:12px; color:var(--color-text-primary);">Send to Writer</span>
                                </div>
                                <span style="font-size:10px; color:var(--color-text-secondary); padding-left:22px; font-weight:500;">Optional · writer reviews first</span>
                            </label>
                        </div>
                        <input type="hidden" name="send_to" id="directDesignSendTo" value="approver" form="submitStageForm">
                    </div>

                    <div id="writerSelectionArea" class="detail-item full dd-card-section" style="display:none; margin-top:10px; padding:18px; background:rgba(14,165,233,0.05); border:1px solid rgba(14,165,233,0.15); border-radius:16px;">
                        <label class="detail-label dd-section-label" style="color:#0ea5e9; margin-bottom:10px;">Assign Writer for Review</label>
                        <select name="writer_id" form="submitStageForm" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Writer...</option>
                            @if(isset($writers))
                                @foreach($writers as $writer)
                                    <option value="{{ $writer->id }}">{{ $writer->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <p style="font-size:11px; color:#0ea5e9; margin-top:8px; font-weight:600;">Selection required to advance to Writer Review stage.</p>
                    </div>

                    <div id="approverSelectionArea" class="detail-item full dd-card-section" style="display:none; margin-top:10px; padding:18px; background:rgba(234,88,12,0.05); border:1px solid rgba(234,88,12,0.15); border-radius:16px;">
                        <label class="detail-label dd-section-label" style="color:#ea580c; margin-bottom:10px;">Assign Next Approver</label>
                        <select name="approver_id" form="submitStageForm" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Approver...</option>
                            @foreach($approvers as $approver)
                                <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#ea580c; margin-top:8px; font-weight:600;">Selection required to advance to Approver stage.</p>
                    </div>

                    <div id="brandManagerSelectionArea" class="detail-item full dd-card-section" style="display:none; margin-top:10px; padding:18px; background:rgba(37,99,235,0.05); border:1px solid rgba(37,99,235,0.15); border-radius:16px;">
                        <label class="detail-label dd-section-label" style="color:#3b82f6; margin-bottom:10px;">Assign Next Brand Manager</label>
                        <select name="brand_manager_id" form="submitStageForm" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                            <option value="">Select Brand Manager...</option>
                            @foreach($brandManagers as $bm)
                                <option value="{{ $bm->id }}">{{ $bm->name }}</option>
                            @endforeach
                        </select>
                        <p style="font-size:11px; color:#3b82f6; margin-top:8px; font-weight:600;">Selection required to advance to Brand Manager stage.</p>
                    </div>

                    <div id="modalFurtherApproverGroup" class="detail-item full dd-card-section" style="display:none; margin-top:10px; padding:18px; background:rgba(124,58,237,0.05); border:1px solid rgba(124,58,237,0.15); border-radius:16px;">
                        <label class="detail-label dd-section-label" style="color:#7c3aed; margin-bottom:10px; text-transform:uppercase; font-size:10px; letter-spacing:0.08em; font-weight:700;">
                            Further Approver <span style="text-transform:none; letter-spacing:0; font-weight:500; opacity:0.8;">(optional — adds another approval step before Brand Manager)</span>
                        </label>
                        <select name="further_approver_id" form="submitStageForm" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid rgba(124,58,237,0.2); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
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

                    <!-- Deliver Final Artwork (Tabbed Attachment Block) -->
                    <div id="designerDeliveryArea" class="detail-item full dd-card-section" style="display:none; margin-top:20px; padding:20px; background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); border-radius:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                            <label class="detail-label dd-section-label" style="color:#10b981; margin:0; font-size:11px; font-weight:800; letter-spacing:0.06em; text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Deliver Final Artwork
                            </label>
                            <!-- Tab Switcher -->
                            <div class="dd-tab-switcher" style="display:flex; background:var(--color-bg-primary); padding:3px; border-radius:8px; border:1px solid var(--color-border-primary); gap:4px;">
                                <button type="button" id="tabArtworkUploadBtn" onclick="switchArtworkDeliveryTab('upload')" class="dd-tab-btn active" style="padding:5px 12px; border-radius:6px; font-size:11px; font-weight:700; border:none; cursor:pointer; background:rgba(16,185,129,0.15); color:#10b981; transition:all 0.15s; display:inline-flex; align-items:center; gap:6px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    Upload File(s)
                                </button>
                                <button type="button" id="tabArtworkLinkBtn" onclick="switchArtworkDeliveryTab('link')" class="dd-tab-btn" style="padding:5px 12px; border-radius:6px; font-size:11px; font-weight:700; border:none; cursor:pointer; background:transparent; color:var(--color-text-secondary); transition:all 0.15s; display:inline-flex; align-items:center; gap:6px;">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    External Link
                                </button>
                            </div>
                        </div>

                        {{-- Self-contained form --}}
                        <form id="artworkDeliveryForm" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitArtworkDeliveryForm(this);">
                            @csrf
                            <input type="hidden" name="action" value="save_only">
                            
                            <!-- Pane 1: File Upload Dropzone -->
                            <div id="paneArtworkUpload" style="display:block;">
                                <label for="modalArtworkFile" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:22px 16px; border:1.5px dashed var(--color-border-primary); border-radius:12px; cursor:pointer; background:var(--color-bg-primary); transition:all 0.2s;" onmouseenter="this.style.borderColor='#10b981'; this.style.background='rgba(16,185,129,0.03)';" onmouseleave="this.style.borderColor='var(--color-border-primary)'; this.style.background='var(--color-bg-primary)';">
                                    <div style="width:40px; height:40px; border-radius:10px; background:rgba(16,185,129,0.1); display:flex; align-items:center; justify-content:center; margin-bottom:8px; color:#10b981;">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    </div>
                                    <div style="font-size:13px; font-weight:700; color:var(--color-text-primary);" id="artworkFileLabel">Click to browse or drop artwork files here</div>
                                    <div style="font-size:11px; color:var(--color-text-secondary); margin-top:4px;">Supports PNG, JPG, GIF, WebP, MP4, WebM, PDF (auto-saves on selection)</div>
                                    <input id="modalArtworkFile" type="file" name="final_designs_file" accept="image/*,video/*,application/pdf,.pdf" multiple style="display:none;" onchange="autoUploadModalArtwork(this)">
                                </label>
                                <div id="modalArtworkPdfPreview" style="display:none; align-items:center; gap:8px; padding:10px 14px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:10px; max-width:260px; margin-top:10px;">
                                    <svg width="22" height="22" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                                    <span id="modalArtworkPdfName" style="font-size:11px; font-weight:700; color:#ef4444; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                                </div>
                                <img id="modalArtworkPreview" src="" style="display:none; margin-top:10px; max-width:100%; max-height:160px; border-radius:10px; border:1px solid var(--color-border-primary); object-fit:contain;">
                                <video id="modalArtworkVideoPreview" controls style="display:none; margin-top:10px; max-width:100%; max-height:160px; border-radius:10px; border:1px solid var(--color-border-primary);"></video>
                            </div>

                            <!-- Pane 2: External Link Input -->
                            <div id="paneArtworkLink" style="display:none;">
                                <div style="font-size:11px; font-weight:700; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">External Cloud / Figma Link</div>
                                <div style="display:flex; gap:8px;">
                                    <input type="url" name="final_designs_link" placeholder="https://drive.google.com/… or Figma / Dropbox URL" style="flex:1; padding:10px 14px; border:1.5px solid var(--color-border-primary); border-radius:10px; font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; transition:border-color 0.15s;" onfocus="this.style.borderColor='#10b981'" onblur="this.style.borderColor='var(--color-border-primary)'">
                                    <button type="submit" style="padding:10px 18px; background:#10b981; color:#fff; border:none; border-radius:10px; font-size:12px; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; box-shadow:0 2px 8px rgba(16,185,129,0.25);">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Save Link
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="submitNotesArea" class="detail-item full dd-card-section" style="margin-top:20px; padding:20px; background:var(--color-bg-secondary); border:1px solid var(--color-border-primary); border-radius:16px;">
                        <label class="detail-label dd-section-label" style="margin-bottom:10px; color:var(--color-text-secondary); font-size:11px; font-weight:800; letter-spacing:0.06em; text-transform:uppercase; display:flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            Submission Comments (Optional)
                        </label>
                        <textarea name="submit_notes" form="submitStageForm" placeholder="Add an optional comment when submitting..." style="width:100%; height:80px; padding:12px; border-radius:10px; border:1px solid var(--color-border-primary); font-size:13px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); resize:vertical; outline:none; transition:border-color 0.15s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='var(--color-border-primary)'"></textarea>
                    </div>

                    <div class="detail-item full dd-card-section" id="modalFinalGroup">
                        <label class="detail-label dd-section-label" style="font-size:12px; font-weight:900; color:#fff; background:#10b981; padding:6px 12px; border-radius:6px; margin-bottom:12px; display:inline-flex; align-items:center; text-transform:uppercase; letter-spacing:0.1em; box-shadow:0 2px 10px rgba(16,185,129,0.3);">Final Designs Preview</label>
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


                    <div class="detail-item full hist-box dd-card-section" id="modalHistoryBox" style="display:none; border-top:1px solid var(--color-border-primary); margin-top:10px;">
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
                    <div class="detail-item full hist-box dd-card-section" id="modalReassignmentHistoryBox" style="display:none; border-top:1px solid var(--color-border-primary); margin-top:10px;">
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
                        <input type="hidden" name="revision_target" id="modalRevisionTarget" value="designer">
                        <!-- Post-designer revision target toggle (shown only for Approver Review / Further Approver / Final Approval) -->
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
                        <!-- Optional revision deadline -->
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#ef4444;margin-bottom:8px;">
                                Revision Deadline <span style="font-weight:500;opacity:0.6;text-transform:none;letter-spacing:0;">(optional)</span>
                            </label>
                            <input type="date" id="modalRevisionDeadlineInput" name="revision_deadline" min="{{ date('Y-m-d') }}" style="width:100%; max-width:260px; padding:9px 14px; border-radius:10px; border:1.5px solid rgba(239,68,68,0.25); font-size:13px; font-weight:600; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary); outline:none; box-sizing:border-box;">
                        </div>
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
                <div id="standardModalFooterInner" style="display:flex; align-items:center; gap:12px;">
                    <form id="submitStageForm" method="POST" enctype="multipart/form-data" style="display:none; align-items:center; gap:12px;">
                        @csrf
                        <button type="submit" name="action" value="save_only" class="cd-btn" id="saveContentBtn" style="background:rgba(59,130,246,0.1); color:#3b82f6; border:1.5px solid rgba(59,130,246,0.4); display:none; padding:10px 20px; font-size:12px; transition:all 0.2s;" onmouseover="this.style.background='rgba(59,130,246,0.2)'" onmouseout="this.style.background='rgba(59,130,246,0.1)'">Save Content</button>
                        <button type="submit" name="action" value="submit" class="cd-btn cd-btn-primary" id="submitStageBtn" style="display:none; padding:10px 20px; font-size:12px; box-shadow:0 4px 12px rgba(0,85,212,0.4);">Submit to Next</button>
                    </form>
                    <button type="button" class="cd-btn cd-btn-outline" id="showRevisionBtn" style="color:#ef4444; border-color:rgba(239,68,68,0.4); background:rgba(239,68,68,0.1); display:none; padding:10px 20px; font-size:12px; font-weight:700;" onclick="toggleRevisionInput(true)">Request Revisions</button>
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
                    ],
                    clipboard: {
                        matchers: [
                            [Node.ELEMENT_NODE, function(node, delta) {
                                delta.ops.forEach(op => {
                                    if (op.attributes) {
                                        delete op.attributes.color;
                                        delete op.attributes.background;
                                    }
                                });
                                return delta;
                            }]
                        ]
                    }
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
        const AUTH_USER_NAME = @json(auth()->user()->name);
        const AUTH_USER_ROLE = "{{ auth()->user()->role }}";
        const WORKFLOW_STAGES = @json($stages);
        let currentTaskData = null;

        function formatLocalDateStr(dateVal) {
            if (!dateVal) return '';
            if (typeof dateVal === 'string' && /^\d{4}-\d{2}-\d{2}/.test(dateVal)) {
                const parts = dateVal.substring(0, 10).split('-');
                const d = new Date(parts[0], parts[1] - 1, parts[2]);
                return d.toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'});
            }
            const d = new Date(dateVal);
            return isNaN(d) ? '' : d.toLocaleDateString(undefined, {month:'short', day:'numeric', year:'numeric'});
        }

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

        function showSaveDesignerDeadlineBtn() {
            const btn = document.getElementById('saveDesignerDeadlineBtn');
            if (btn) btn.style.display = 'inline-block';
        }

        function saveDesignerDeadline(btn) {
            const box = document.getElementById('modalDesignerDeadlineBox');
            const taskId = currentTaskData ? currentTaskData.id : (box ? box.getAttribute('data-task-id') : null);
            const input = document.getElementById('modalDesignerDeadlineInput');
            if (!taskId || !input) return;

            const deadlineVal = input.value;
            const originalText = btn.textContent;
            btn.textContent = 'Saving...';
            btn.style.opacity = '0.7';
            fetch(`/deliverables/${taskId}/designer-deadline`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ designer_deadline: deadlineVal })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    btn.textContent = 'Saved!';
                    btn.style.background = '#10b981';
                    btn.style.opacity = '1';
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert(data.message || 'Error updating designer deadline');
                    btn.textContent = 'Save';
                    btn.style.opacity = '1';
                }
            }).catch(err => {
                console.error(err);
                btn.textContent = 'Error';
                btn.style.background = '#ef4444';
                setTimeout(() => {
                    btn.textContent = 'Save';
                    btn.style.background = '#ec4899';
                    btn.style.opacity = '1';
                }, 2000);
            });
        }

        function showSaveRevisionDeadlineBtn() {
            const btn = document.getElementById('saveRevisionDeadlineBtn');
            if (btn) btn.style.display = 'inline-block';
        }

        function saveRevisionDeadline(btn) {
            const taskId = currentTaskData ? currentTaskData.id : {{ $deliverable->id ?? 'null' }};
            const input = document.getElementById('modalRevisionDeadlineInputBox');
            if (!taskId || !input) return;

            const deadlineVal = input.value;
            const originalText = btn.textContent;
            btn.textContent = 'Saving...';
            btn.style.opacity = '0.7';
            fetch(`/deliverables/${taskId}/revision-deadline`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ revision_deadline: deadlineVal })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    btn.textContent = 'Saved!';
                    btn.style.background = '#10b981';
                    btn.style.opacity = '1';
                    if (currentTaskData) currentTaskData.revision_deadline = deadlineVal;
                    const revDeadlineBadge = document.getElementById('modalRevisionDeadlineBadge');
                    const revDeadlineText = document.getElementById('modalRevisionDeadlineText');
                    if (revDeadlineBadge && revDeadlineText) {
                        if (deadlineVal) {
                            revDeadlineText.textContent = `Due: ${formatLocalDateStr(deadlineVal)}`;
                            revDeadlineBadge.style.display = 'inline-flex';
                        } else {
                            revDeadlineBadge.style.display = 'none';
                        }
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert(data.message || 'Error updating revision deadline');
                    btn.textContent = originalText;
                    btn.style.opacity = '1';
                }
            }).catch(err => {
                console.error(err);
                btn.textContent = 'Error';
                btn.style.background = '#ef4444';
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '#ef4444';
                    btn.style.opacity = '1';
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
                        <input type="file" name="reference_files[]" form="submitStageForm" accept="image/*,video/*,application/pdf,.pdf" multiple style="flex:1; padding:8px; border:1.5px dashed var(--color-border-primary); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="autoUploadModalReference(this)">
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

        window.removeArtworkImmediately = async function(deliverableId, targetItem, boxElement, isLink = false, index = null, triggerBtn = null) {
            const confirmTitle = isLink ? 'Remove External Link' : 'Remove Artwork File';
            const confirmMsg = isLink
                ? 'Are you sure you want to remove this external link?'
                : 'Are you sure you want to remove this artwork file?';

            let confirmed = false;
            if (typeof window.customConfirm === 'function') {
                confirmed = await window.customConfirm({
                    title: confirmTitle,
                    message: confirmMsg,
                    confirmText: 'Remove',
                    cancelText: 'Cancel',
                    isDanger: true
                });
            } else {
                confirmed = confirm(confirmMsg);
            }

            if (!confirmed) {
                return;
            }

            const activeBtn = triggerBtn || (boxElement ? boxElement.querySelector('button') : null);
            let originalHtml = '';
            if (activeBtn) {
                originalHtml = activeBtn.innerHTML;
                activeBtn.innerHTML = '<span style="font-size:10px;">Removing...</span>';
                activeBtn.disabled = true;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const fd = new FormData();
            if (csrfToken) fd.append('_token', csrfToken);

            if (isLink) {
                fd.append('delete_final_designs_link', '1');
                if (targetItem) {
                    fd.append('target_artwork_url', targetItem);
                }
                if (index !== null && index !== undefined) {
                    fd.append('delete_final_designs_url_indices[]', index);
                }
            } else {
                fd.append('delete_final_designs', '1');
                if (targetItem) {
                    fd.append('target_artwork_file', targetItem);
                    fd.append('target_artwork_files[]', targetItem);
                }
                if (index !== null && index !== undefined) {
                    fd.append('delete_final_designs_file_indices[]', index);
                }
            }

            try {
                const res = await fetch(`/deliverables/${deliverableId}/submit`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json'
                    },
                    body: fd
                });

                if (!res.ok) {
                    const text = await res.text();
                    let errMsg = 'Failed to remove';
                    try {
                        const parsed = JSON.parse(text);
                        if (parsed.message) errMsg = parsed.message;
                    } catch(e) {
                        if (text && text.length < 100) errMsg = text;
                    }
                    throw new Error(errMsg);
                }

                const data = await res.json().catch(() => ({ success: true }));

                window._deliverableModalModified = true;

                if (boxElement) {
                    boxElement.style.transition = 'opacity 0.2s, transform 0.2s';
                    boxElement.style.opacity = '0';
                    boxElement.style.transform = 'scale(0.9)';
                    setTimeout(() => boxElement.remove(), 200);
                }

                const modalFinal = document.getElementById('modalFinal');
                if (modalFinal) {
                    setTimeout(() => {
                        const remaining = modalFinal.querySelectorAll('.art-box-item');
                        if (remaining.length === 0) {
                            modalFinal.innerHTML = '<span style="color:#94a3b8; font-size:13px; font-weight:500;">Pending Delivery</span>';
                        }
                    }, 220);
                }

                const imagePreviewOverlay = document.getElementById('imagePreviewOverlay');
                if (imagePreviewOverlay && (imagePreviewOverlay.style.display === 'flex' || imagePreviewOverlay.style.display === 'block')) {
                    if (typeof closeImagePreview === 'function') {
                        closeImagePreview();
                    }
                }

                window.dispatchEvent(new CustomEvent('toast', { detail: { message: isLink ? 'External link removed' : 'Artwork removed', type: 'success' } }));
                if (typeof showToast === 'function') {
                    showToast(isLink ? 'External link removed' : 'Artwork removed', 'success');
                }
            } catch (err) {
                console.error('Error removing artwork/link:', err);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to remove: ' + (err.message || 'Please try again'), type: 'error' } }));
                alert('Failed to remove: ' + (err.message || 'Please try again'));
                if (activeBtn) {
                    activeBtn.innerHTML = originalHtml;
                    activeBtn.disabled = false;
                }
            }
        };

        function removeSpecificArtFile(boxId, index, fileUrl = '') {
            const deliverableId = {{ $deliverable->id ?? 'null' }} || (window.currentTaskData ? window.currentTaskData.id : null);
            removeArtworkImmediately(deliverableId, fileUrl, document.getElementById(boxId), false, index);
        }

        function removeSpecificArtUrl(boxId, index, url = '') {
            const deliverableId = {{ $deliverable->id ?? 'null' }} || (window.currentTaskData ? window.currentTaskData.id : null);
            removeArtworkImmediately(deliverableId, url, document.getElementById(boxId), true, index);
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
                        <input type="file" name="final_designs_files[]" form="artworkDeliveryForm" accept="image/*,video/*,application/pdf,.pdf" multiple style="flex:1; padding:8px; border:1.5px dashed rgba(16,185,129,0.35); border-radius:10px; background:var(--color-bg-primary); font-size:11px;" onchange="autoUploadModalArtwork(this)">
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

        window.autoUploadModalReference = async function(input) {
            if (!input || !input.files || input.files.length === 0) return;
            const taskId = (currentTaskData ? currentTaskData.id : null) || {{ $deliverable->id ?? 'null' }};
            if (!taskId) return;

            const statusEl = document.getElementById('modalReferenceUploadStatus');
            if (statusEl) {
                statusEl.style.display = 'inline-flex';
                statusEl.style.color = '#0ea5e9';
                statusEl.innerHTML = `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity:0.75;"></path></svg> Saving reference...`;
            }

            const fd = new FormData();
            fd.append('action', 'save_only');
            const files = Array.from(input.files);
            files.forEach(f => {
                fd.append('reference_files[]', f);
            });
            if (files.length === 1) {
                fd.append('reference_file', files[0]);
            }

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
                if (data.success) {
                    window._deliverableModalModified = true;
                    if (statusEl) {
                        statusEl.style.color = '#10b981';
                        statusEl.innerHTML = `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Saved!`;
                        setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 2500);
                    }
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Reference uploaded and saved automatically!', type: 'success' } }));

                    if (data.deliverable) {
                        if (window.projectDeliverables) window.projectDeliverables[taskId] = data.deliverable;
                        currentTaskData = data.deliverable;
                        openTaskModal(data.deliverable);
                    } else {
                        const freshTaskRes = await fetch(`/deliverables/${taskId}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (freshTaskRes.ok) {
                            const freshTask = await freshTaskRes.json();
                            if (window.projectDeliverables) window.projectDeliverables[taskId] = freshTask;
                            currentTaskData = freshTask;
                            openTaskModal(freshTask);
                        }
                    }
                } else {
                    if (statusEl) statusEl.style.display = 'none';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error saving reference', type: 'error' } }));
                }
            } catch (err) {
                console.error('Auto upload reference error:', err);
                if (statusEl) statusEl.style.display = 'none';
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to upload reference file.', type: 'error' } }));
            } finally {
                input.value = '';
            }
        };

        window.autoUploadModalArtwork = async function(input) {
            if (!input || !input.files || input.files.length === 0) return;
            const taskId = (currentTaskData ? currentTaskData.id : null) || {{ $deliverable->id ?? 'null' }};
            if (!taskId) return;

            const statusEl = document.getElementById('modalArtworkUploadStatus');
            if (statusEl) {
                statusEl.style.display = 'inline-flex';
                statusEl.style.color = '#10b981';
                statusEl.innerHTML = `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity:0.75;"></path></svg> Uploading artwork...`;
            }

            const fd = new FormData();
            fd.append('action', 'save_only');
            const files = Array.from(input.files);
            files.forEach(f => {
                fd.append('final_designs_files[]', f);
            });
            if (files.length === 1) {
                fd.append('final_designs_file', files[0]);
            }

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
                if (data.success) {
                    window._deliverableModalModified = true;
                    if (statusEl) {
                        statusEl.innerHTML = `<span style="color:#10b981;display:inline-flex;align-items:center;gap:4px;"><svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Artwork saved!</span>`;
                        setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 2500);
                    }
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Artwork uploaded and saved automatically!', type: 'success' } }));

                    if (data.deliverable) {
                        if (window.projectDeliverables) window.projectDeliverables[taskId] = data.deliverable;
                        currentTaskData = data.deliverable;
                        openTaskModal(data.deliverable);
                    } else {
                        const freshTaskRes = await fetch(`/deliverables/${taskId}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (freshTaskRes.ok) {
                            const freshTask = await freshTaskRes.json();
                            if (window.projectDeliverables) window.projectDeliverables[taskId] = freshTask;
                            currentTaskData = freshTask;
                            openTaskModal(freshTask);
                        }
                    }
                } else {
                    if (statusEl) statusEl.style.display = 'none';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error saving artwork', type: 'error' } }));
                }
            } catch (err) {
                console.error('Auto upload artwork error:', err);
                if (statusEl) statusEl.style.display = 'none';
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to upload artwork file.', type: 'error' } }));
            } finally {
                input.value = '';
            }
        };

        window.submitArtworkDeliveryForm = async function(form) {
            const taskId = (currentTaskData ? currentTaskData.id : null) || {{ $deliverable->id ?? 'null' }};
            if (!taskId) return;
            const btn = form.querySelector('button[type="submit"]');
            const origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity:0.75;"></path></svg> Saving...`;
            }

            const fd = new FormData(form);
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
                if (data.success) {
                    window._deliverableModalModified = true;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Artwork saved successfully!', type: 'success' } }));
                    form.reset();
                    if (data.deliverable) {
                        if (window.projectDeliverables) window.projectDeliverables[taskId] = data.deliverable;
                        currentTaskData = data.deliverable;
                        openTaskModal(data.deliverable);
                    } else {
                        const freshTaskRes = await fetch(`/deliverables/${taskId}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        if (freshTaskRes.ok) {
                            const freshTask = await freshTaskRes.json();
                            if (window.projectDeliverables) window.projectDeliverables[taskId] = freshTask;
                            currentTaskData = freshTask;
                            openTaskModal(freshTask);
                        }
                    }
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error saving artwork', type: 'error' } }));
                }
            } catch (err) {
                console.error('Artwork form submit error:', err);
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Failed to save artwork.', type: 'error' } }));
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
            }
        };

        window.handleDirectDesignRouteChange = function(choice) {
            const sendToInput = document.getElementById('directDesignSendTo');
            const writerArea = document.getElementById('writerSelectionArea');
            const apprArea = document.getElementById('approverSelectionArea');
            const nextBtn = document.getElementById('submitStageBtn');
            const apprCard = document.getElementById('directRouteApproverLabel');
            const writerCard = document.getElementById('directRouteWriterLabel');
            const radioApprover = document.getElementById('directRouteApproverRadio');
            const radioWriter = document.getElementById('directRouteWriterRadio');

            if (sendToInput) sendToInput.value = choice;

            const wSel = writerArea ? writerArea.querySelector('select') : null;
            const aSel = apprArea ? apprArea.querySelector('select') : null;

            if (choice === 'approver') {
                if (radioApprover) radioApprover.checked = true;
                if (radioWriter) radioWriter.checked = false;
                if (apprCard) {
                    apprCard.style.borderColor = 'rgba(234,88,12,0.5)';
                    apprCard.style.background = 'rgba(234,88,12,0.08)';
                }
                if (writerCard) {
                    writerCard.style.borderColor = 'var(--color-border-primary)';
                    writerCard.style.background = 'transparent';
                }
                if (apprArea) {
                    apprArea.style.display = 'block';
                    if (aSel) aSel.disabled = false;
                }
                if (writerArea) {
                    writerArea.style.display = 'none';
                    if (wSel) wSel.disabled = true;
                }
                if (nextBtn) {
                    nextBtn.textContent = 'Submit to Approver';
                    nextBtn.style.background = '#ea580c';
                }
            } else {
                if (radioWriter) radioWriter.checked = true;
                if (radioApprover) radioApprover.checked = false;
                if (writerCard) {
                    writerCard.style.borderColor = 'rgba(14,165,233,0.5)';
                    writerCard.style.background = 'rgba(14,165,233,0.08)';
                }
                if (apprCard) {
                    apprCard.style.borderColor = 'var(--color-border-primary)';
                    apprCard.style.background = 'transparent';
                }
                if (writerArea) {
                    writerArea.style.display = 'block';
                    if (wSel) wSel.disabled = false;
                }
                if (apprArea) {
                    apprArea.style.display = 'none';
                    if (aSel) aSel.disabled = true;
                }
                if (nextBtn) {
                    nextBtn.textContent = 'Submit for Writer Review';
                    nextBtn.style.background = '#0ea5e9';
                }
            }
        };

        window.switchArtworkDeliveryTab = function(tab) {
            const uploadBtn = document.getElementById('tabArtworkUploadBtn');
            const linkBtn = document.getElementById('tabArtworkLinkBtn');
            const uploadPane = document.getElementById('paneArtworkUpload');
            const linkPane = document.getElementById('paneArtworkLink');
            if (!uploadBtn || !linkBtn || !uploadPane || !linkPane) return;

            if (tab === 'upload') {
                uploadBtn.style.background = 'rgba(16,185,129,0.15)';
                uploadBtn.style.color = '#10b981';
                linkBtn.style.background = 'transparent';
                linkBtn.style.color = 'var(--color-text-secondary)';
                uploadPane.style.display = 'block';
                linkPane.style.display = 'none';
            } else {
                linkBtn.style.background = 'rgba(16,185,129,0.15)';
                linkBtn.style.color = '#10b981';
                uploadBtn.style.background = 'transparent';
                uploadBtn.style.color = 'var(--color-text-secondary)';
                uploadPane.style.display = 'none';
                linkPane.style.display = 'block';
            }
        };

        window.setupModalLayout = function(isDirectDesign) {
            const overlay = document.getElementById('taskModalOverlay');
            if (!overlay) return;
            const modal = overlay.querySelector('.cd-modal');
            const ddLayout = document.getElementById('ddTwoColLayout');
            const ddMain = document.getElementById('ddColMain');
            const ddSidebar = document.getElementById('ddColSidebar');
            const ddSidebarMeta = document.getElementById('ddSidebarMetaCard');
            const ddActions = document.getElementById('ddSidebarActions');

            const stdTop = document.getElementById('standardTopContainer');
            const stdGrid = document.getElementById('standardDetailGrid');
            const stdFooterInner = document.getElementById('standardModalFooterInner');
            const modalFooter = modal ? modal.querySelector('.cd-modal-footer') : null;

            if (!modal || !ddLayout || !ddMain || !ddSidebar || !stdTop || !stdGrid) return;

            if (isDirectDesign) {
                overlay.style.display = 'flex';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';
                overlay.style.padding = '20px';
                modal.classList.add('direct-design-modal');
                ddLayout.style.display = 'grid';
                stdTop.style.display = 'none';
                stdGrid.style.display = 'none';
                if (modalFooter) modalFooter.style.display = 'none';

                // Hide top header deadlines in Direct Design (they belong cleanly in the sidebar)
                const topDeadlines = document.getElementById('modalTopDeadlines');
                if (topDeadlines) {
                    topDeadlines.style.display = 'none';
                    topDeadlines.innerHTML = '';
                }

                // Sidebar items:
                // 1. Stepper
                const stepper = document.getElementById('modalWorkflowSteps');
                if (stepper) ddSidebar.insertBefore(stepper, ddSidebarMeta);

                // 2. Metadata into ddSidebarMetaCard
                const metaRow = document.getElementById('modalMetaRow');
                const deadlineRow = document.getElementById('modalDeadlineRow');
                if (metaRow) ddSidebarMeta.appendChild(metaRow);
                if (deadlineRow) ddSidebarMeta.appendChild(deadlineRow);

                // 3. Routing & Assignments into ddSidebar (above ddActions)
                const routeArea = document.getElementById('directDesignRouteArea');
                const writerArea = document.getElementById('writerSelectionArea');
                const apprArea = document.getElementById('approverSelectionArea');
                const bmArea = document.getElementById('brandManagerSelectionArea');
                const faArea = document.getElementById('modalFurtherApproverGroup');
                if (routeArea) ddSidebar.insertBefore(routeArea, ddActions);
                if (writerArea) ddSidebar.insertBefore(writerArea, ddActions);
                if (apprArea) ddSidebar.insertBefore(apprArea, ddActions);
                if (bmArea) ddSidebar.insertBefore(bmArea, ddActions);
                if (faArea) ddSidebar.insertBefore(faArea, ddActions);

                // 4. Action buttons into ddActions
                const submitForm = document.getElementById('submitStageForm');
                const revBtn = document.getElementById('showRevisionBtn');
                const delBtn = document.getElementById('modalDeleteBtn');
                if (submitForm) ddActions.appendChild(submitForm);
                if (revBtn) ddActions.appendChild(revBtn);
                if (delBtn) ddActions.appendChild(delBtn);

                // Main Column items:
                // Alerts
                const commentAlert = document.getElementById('modalLatestCommentAlert');
                const revAlert = document.getElementById('modalRevisionAlert');
                if (commentAlert) ddMain.appendChild(commentAlert);
                if (revAlert) ddMain.appendChild(revAlert);

                // Creative Brief / Description
                const conceptItem = document.getElementById('modalConceptGroup');
                if (conceptItem) ddMain.appendChild(conceptItem);

                // Deliverable Team
                const teamItem = document.getElementById('modalTeamGroup');
                if (teamItem) ddMain.appendChild(teamItem);

                // Reference Files & Links
                const refItem = document.getElementById('modalReferenceGroup');
                if (refItem) ddMain.appendChild(refItem);

                // Deliver Final Artwork (Tabbed block)
                const deliveryArea = document.getElementById('designerDeliveryArea');
                if (deliveryArea) ddMain.appendChild(deliveryArea);

                // Final Artwork Preview
                const finalItem = document.getElementById('modalFinalGroup');
                if (finalItem) ddMain.appendChild(finalItem);

                // Submission Comments
                const notesArea = document.getElementById('submitNotesArea');
                if (notesArea) ddMain.appendChild(notesArea);

                // History accordions
                const appBox = document.getElementById('modalApprovalsBox');
                const revBox = document.getElementById('modalHistoryBox');
                const reassBox = document.getElementById('modalReassignmentHistoryBox');
                if (appBox) ddMain.appendChild(appBox);
                if (revBox) ddMain.appendChild(revBox);
                if (reassBox) ddMain.appendChild(reassBox);

                // Revision Form
                const revInputArea = document.getElementById('revisionInputArea');
                if (revInputArea) ddMain.appendChild(revInputArea);

            } else {
                // Restore standard Retainer / standard deliverable layout
                overlay.style.display = '';
                overlay.style.justifyContent = '';
                overlay.style.alignItems = '';
                overlay.style.padding = '';
                modal.classList.remove('direct-design-modal');
                ddLayout.style.display = 'none';
                stdTop.style.display = '';
                stdGrid.style.display = '';
                if (modalFooter) modalFooter.style.display = '';

                // Restore top deadlines
                const topDeadlines = document.getElementById('modalTopDeadlines');
                if (topDeadlines) topDeadlines.style.display = 'flex';

                // Restore top container
                const metaRow = document.getElementById('modalMetaRow');
                const stepper = document.getElementById('modalWorkflowSteps');
                const commentAlert = document.getElementById('modalLatestCommentAlert');
                const deadlineRow = document.getElementById('modalDeadlineRow');
                const revAlert = document.getElementById('modalRevisionAlert');

                if (metaRow) stdTop.appendChild(metaRow);
                if (stepper) stdTop.appendChild(stepper);
                if (commentAlert) stdTop.appendChild(commentAlert);
                if (deadlineRow) stdTop.appendChild(deadlineRow);
                if (revAlert) stdTop.appendChild(revAlert);

                // Restore detail grid items in original order
                const conceptItem = document.getElementById('modalConceptGroup');
                const copyItem = document.getElementById('modalCopyGroup');
                const captionItem = document.getElementById('modalCaptionGroup');
                const refItem = document.getElementById('modalReferenceGroup');
                const teamItem = document.getElementById('modalTeamGroup');
                const appBox = document.getElementById('modalApprovalsBox');
                const routeArea = document.getElementById('directDesignRouteArea');
                const writerArea = document.getElementById('writerSelectionArea');
                const apprArea = document.getElementById('approverSelectionArea');
                const bmArea = document.getElementById('brandManagerSelectionArea');
                const faArea = document.getElementById('modalFurtherApproverGroup');
                const coordArea = document.getElementById('coordinatorSelectionArea');
                const dArea = document.getElementById('designerSelectionArea');
                const deliveryArea = document.getElementById('designerDeliveryArea');
                const notesArea = document.getElementById('submitNotesArea');
                const finalItem = document.getElementById('modalFinalGroup');
                const revBox = document.getElementById('modalHistoryBox');
                const reassBox = document.getElementById('modalReassignmentHistoryBox');

                [conceptItem, copyItem, captionItem, refItem, teamItem, appBox, routeArea, writerArea, apprArea, bmArea, faArea, coordArea, dArea, deliveryArea, notesArea, finalItem, revBox, reassBox].forEach(el => {
                    if (el) stdGrid.appendChild(el);
                });

                // Restore revision input area
                const revInputArea = document.getElementById('revisionInputArea');
                if (revInputArea) {
                    const modalBodyInner = modal.querySelector('.cd-modal-body .cd-modal-inner') || stdGrid.parentElement;
                    modalBodyInner.appendChild(revInputArea);
                }

                // Restore footer buttons
                const submitForm = document.getElementById('submitStageForm');
                const revBtn = document.getElementById('showRevisionBtn');
                const delBtn = document.getElementById('modalDeleteBtn');
                if (stdFooterInner) {
                    if (submitForm) stdFooterInner.appendChild(submitForm);
                    if (revBtn) stdFooterInner.appendChild(revBtn);
                    if (delBtn) stdFooterInner.appendChild(delBtn);
                }
            }
        };

        async function openTaskModal(task) {
            try {
                if (typeof task === 'number' || typeof task === 'string' || (task && typeof task === 'object' && !task.title && !task.approval_stage && task.id)) {
                    const taskId = typeof task === 'object' ? task.id : task;
                    if (window.projectDeliverables && window.projectDeliverables[taskId]) {
                        task = window.projectDeliverables[taskId];
                    } else {
                        try {
                            const res = await fetch(`/deliverables/${taskId}`, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            if (res.ok) {
                                task = await res.json();
                            }
                        } catch (err) {
                            console.error('Failed to fetch deliverable for modal:', err);
                        }
                    }
                }

                if (!task || typeof task !== 'object') {
                    console.error('Invalid task passed to openTaskModal:', task);
                    return;
                }

                console.log('Opening Task Modal for:', task);
                currentTaskData = task;

                const rawRole = AUTH_USER_ROLE;
                const userRole = rawRole.toLowerCase().replace(/\s+/g, '');
                const isAdmin = userRole === 'admin';
                let stage = task.approval_stage;
                const projectWorkflowType = task.project?.workflow_type || '{{ $deliverable->project?->workflow_type ?? "" }}';
                const postTypeNorm = (task.post_type || '').toLowerCase().trim();
                const isDirectDesign = (task.flow_type === 'direct_design' || task.is_direct_design);
                setupModalLayout(Boolean(isDirectDesign));
                switchArtworkDeliveryTab('upload');
                const isOtherDeliverable = !isDirectDesign && projectWorkflowType !== 'retainer' && (task.workflow_stages?.length === 3 || (!['outlines', 'outline'].includes(postTypeNorm)));
                const isAssignedWriter      = AUTH_USER_ID == task.writer_id;
                const isAssignedApprover    = AUTH_USER_ID == task.approver_id;
                const isAssignedBrandMgr    = AUTH_USER_ID == task.brand_manager_id;
                const isAssignedCoordinator = AUTH_USER_ID == task.coordinator_id;
                const isAssignedDesigner    = AUTH_USER_ID == task.designer_id;
                const isAssignedFurtherApprover = AUTH_USER_ID == task.further_approver_id;
                const hasWriterRole = userRole === 'writer' || userRole === 'assignee';
                const hasDesignerRole = userRole === 'designer';
                const isWriterStage = ['Writer', 'Assignee', 'Writer Review', 'Assign'].includes(task.approval_stage || 'Writer');
                const isOtherAssignStage = isOtherDeliverable && (['Assign', 'Assignee', 'Writer'].includes(task.approval_stage) || !task.approval_stage);
                const isOtherAssignedPerson = (task.writer_id && AUTH_USER_ID == task.writer_id) || 
                                              (task.designer_id && AUTH_USER_ID == task.designer_id) || 
                                              (task.associates && task.associates.assignee && task.associates.assignee === AUTH_USER_NAME) ||
                                              (task.assignee_name && task.assignee_name === AUTH_USER_NAME);
                const writerEditPermission = isOtherDeliverable
                    ? (isOtherAssignStage && isOtherAssignedPerson)
                    : (isAdmin || (hasWriterRole && (!task.writer_id || AUTH_USER_ID == task.writer_id) && isWriterStage));
                // Allow any designer to upload when no designer is assigned (matches PHP logic)
                const designerEditPermission = isAssignedDesigner || (hasDesignerRole && !task.designer_id);
                const canDesignerEdit = isOtherDeliverable
                    ? (isOtherAssignStage && isOtherAssignedPerson)
                    : ((designerEditPermission && stage === 'Designer') || isAdmin);

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

                const conceptOrBrief = task.concept || task.notes || task.description || '';
                document.getElementById('modalConcept').value = conceptOrBrief;
                if (typeof quillConcept !== 'undefined' && quillConcept) {
                    if (conceptOrBrief) {
                        quillConcept.clipboard.dangerouslyPasteHTML(conceptOrBrief);
                        if (!quillConcept.getText().trim() && conceptOrBrief.trim()) {
                            quillConcept.root.innerHTML = conceptOrBrief;
                        }
                    } else {
                        quillConcept.setText('');
                    }
                }
                
                document.getElementById('modalCaption').value = task.caption || '';
                quillCaption.clipboard.dangerouslyPasteHTML(task.caption || '');
                
                document.getElementById('modalSubtaskCopy').value = task.subtask_copy || task.post_copy || '';
                quillCopy.clipboard.dangerouslyPasteHTML(task.subtask_copy || task.post_copy || '');

                if (document.getElementById('modalStage')) {
                    const isCampaignOrPitch = (projectWorkflowType === 'campaign' || projectWorkflowType === 'pitch');
                    let stage = task.approval_stage || ((isOtherDeliverable || isCampaignOrPitch) ? 'Assignee' : 'Writer');
                    if (isOtherDeliverable || isCampaignOrPitch) {
                        if (['writer', 'assign', 'assignee'].includes(String(stage || '').toLowerCase()) || !stage) stage = 'Assignee';
                        else if (['close', 'closed'].includes(String(stage || '').toLowerCase())) stage = 'Closed';
                    }
                    let stageText = stage;
                    
                    let assignedUser = null;
                    if (stage === 'Writer' || stage === 'Writer Review' || stage === 'Assign' || stage === 'Assignee') assignedUser = task.writer?.name || task.assignee_name;
                    else if (stage === 'Designer') assignedUser = task.designer?.name;
                    else if (stage === 'Brand Manager' || stage === 'AM/BD' || stage === 'Final Approval' || stage === 'Approve') assignedUser = task.brandManager?.name;
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

                // Reassign Designer Button and Modal Setup
                const reassignBtn = document.getElementById('btnOpenReassignDesignerModal');
                const reassignModalOverlay = document.getElementById('reassignDesignerModalOverlay');
                if (reassignBtn) {
                    if (!isOtherDeliverable && task.approval_stage === 'Designer') {
                        reassignBtn.style.display = 'inline-flex';
                        reassignBtn.setAttribute('data-task-id', task.id);
                        if (reassignModalOverlay) {
                            reassignModalOverlay.setAttribute('data-task-id', task.id);
                            const currentDesignerName = task.designer?.name || 'Unassigned';
                            const currentNameEl = document.getElementById('reassignDesignerCurrentName');
                            if (currentNameEl) currentNameEl.textContent = `Current designer: ${currentDesignerName}`;
                            const sel = document.getElementById('reassignDesignerSelect');
                            if (sel) sel.value = '';
                            const reasonEl = document.getElementById('reassignDesignerReason');
                            if (reasonEl) reasonEl.value = '';
                            const deadlineEl = document.getElementById('reassignDesignerDeadline');
                            if (deadlineEl) {
                                deadlineEl.value = task.designer_deadline ? task.designer_deadline.split('T')[0] : '';
                            }
                        }
                    } else {
                        reassignBtn.style.display = 'none';
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
                    if (isDirectDesign) {
                        topDeadlinesEl.style.display = 'none';
                        topDeadlinesEl.innerHTML = '';
                    } else {
                        topDeadlinesEl.style.display = 'flex';
                        topDeadlinesEl.innerHTML = '';
                        
                        // 1. Deliverable Due Date
                        const displayDeadline = task.deadline || (task.parent ? task.parent.deadline : null);
                        if (displayDeadline) {
                            const dateStr = formatLocalDateStr(displayDeadline);
                            const dueBadge = document.createElement('div');
                            dueBadge.style.cssText = 'background: rgba(59, 130, 246, 0.08); color: #3b82f6; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(59, 130, 246, 0.15); font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.02em;';
                            dueBadge.textContent = `Del: ${dateStr}`;
                            topDeadlinesEl.appendChild(dueBadge);
                        }

                        // 2. Designer Due Date
                        if (task.designer_deadline && !isOtherDeliverable) {
                            const desDateStr = formatLocalDateStr(task.designer_deadline);
                            const desBadge = document.createElement('div');
                            desBadge.style.cssText = 'background: rgba(236, 72, 153, 0.08); color: #ec4899; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(236, 72, 153, 0.15); font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.02em;';
                            desBadge.textContent = `Designer Due: ${desDateStr}`;
                            topDeadlinesEl.appendChild(desBadge);
                        }

                        // 3. Revision Due Date
                        if (task.revision_deadline && (task.revision_instructions || (task.revisions && task.revisions > 0))) {
                            const revDateStr = formatLocalDateStr(task.revision_deadline);
                            const revBadge = document.createElement('div');
                            revBadge.style.cssText = 'background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(239, 68, 68, 0.25); font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.02em; display:flex; align-items:center; gap:4px;';
                            revBadge.innerHTML = `<svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Revision Due: ${revDateStr}`;
                            topDeadlinesEl.appendChild(revBadge);
                        }
                    }
                }

                // Hide designer deadline box for Other Deliverables & Fast Track (where deliverable deadline is used directly)
                const desDeadlineBox = document.getElementById('modalDesignerDeadlineBox');
                if (desDeadlineBox) {
                    desDeadlineBox.style.display = (isOtherDeliverable || isDirectDesign) ? 'none' : 'flex';
                }

                // Deliverable Deadline Input Population
                const delDeadlineInput = document.getElementById('modalDeliverableDeadlineInput');
                if (delDeadlineInput) {
                    let dDateStr = '';
                    const displayDeadline = task.deadline || task.designer_deadline || (task.parent ? (task.parent.deadline || task.parent.designer_deadline) : null);
                    if (displayDeadline) {
                        if (typeof displayDeadline === 'string' && /^\d{4}-\d{2}-\d{2}/.test(displayDeadline)) {
                            dDateStr = displayDeadline.substring(0, 10);
                        } else {
                            const dDate = new Date(displayDeadline);
                            const pad = n => String(n).padStart(2,'0');
                            dDateStr = `${dDate.getFullYear()}-${pad(dDate.getMonth()+1)}-${pad(dDate.getDate())}`;
                        }
                    }
                    delDeadlineInput.value = dDateStr;
                    const isBrandManagerOrAdmin = isAdmin || userRole === 'brandmanager' || userRole === 'operationsmanager';
                    delDeadlineInput.readOnly = !isBrandManagerOrAdmin;
                    const saveBtn = document.getElementById('saveDeadlineBtn');
                    if (!isBrandManagerOrAdmin) {
                        delDeadlineInput.style.opacity = '0.7';
                        delDeadlineInput.style.pointerEvents = 'none';
                        if (saveBtn) saveBtn.style.display = 'none';
                    } else {
                        delDeadlineInput.style.opacity = '1';
                        delDeadlineInput.style.pointerEvents = 'auto';
                    }
                }

                // Designer Deadline Input Population
                const desDeadlineInput = document.getElementById('modalDesignerDeadlineInput');
                if (desDeadlineInput) {
                    let desDateStr = '';
                    if (task.designer_deadline) {
                        const desDate = new Date(task.designer_deadline);
                        const pad = n => String(n).padStart(2,'0');
                        desDateStr = `${desDate.getFullYear()}-${pad(desDate.getMonth()+1)}-${pad(desDate.getDate())}`;
                    }
                    desDeadlineInput.value = desDateStr;
                    const canEditDesDeadline = isAdmin || ['brandmanager', 'coordinator', 'approvercoordinator', 'operationsmanager'].includes(userRole);
                    desDeadlineInput.readOnly = !canEditDesDeadline;
                    const saveDesBtn = document.getElementById('saveDesignerDeadlineBtn');
                    if (saveDesBtn) saveDesBtn.style.display = 'none';
                    if (!canEditDesDeadline) {
                        desDeadlineInput.style.opacity = '0.7';
                        desDeadlineInput.style.pointerEvents = 'none';
                    } else {
                        desDeadlineInput.style.opacity = '1';
                        desDeadlineInput.style.pointerEvents = 'auto';
                    }
                }

                // Revision Deadline Input Population in Metadata Area
                const revDeadlineBox = document.getElementById('modalRevisionDeadlineBox');
                const revDeadlineInputBox = document.getElementById('modalRevisionDeadlineInputBox');
                if (revDeadlineBox && revDeadlineInputBox) {
                    const hasRevision = Boolean(task.revision_instructions || task.revision_deadline);
                    revDeadlineBox.style.display = hasRevision ? 'flex' : 'none';
                    let rDateStr = '';
                    if (task.revision_deadline) {
                        if (typeof task.revision_deadline === 'string' && /^\d{4}-\d{2}-\d{2}/.test(task.revision_deadline)) {
                            rDateStr = task.revision_deadline.substring(0, 10);
                        } else {
                            const rDate = new Date(task.revision_deadline);
                            const pad = n => String(n).padStart(2,'0');
                            rDateStr = `${rDate.getFullYear()}-${pad(rDate.getMonth()+1)}-${pad(rDate.getDate())}`;
                        }
                    }
                    revDeadlineInputBox.value = rDateStr;
                    const canEditRevDeadline = isAdmin || ['brandmanager', 'coordinator', 'approvercoordinator', 'operationsmanager', 'writer'].includes(userRole);
                    revDeadlineInputBox.readOnly = !canEditRevDeadline;
                    const saveRevBtn = document.getElementById('saveRevisionDeadlineBtn');
                    if (saveRevBtn) saveRevBtn.style.display = 'none';
                    if (!canEditRevDeadline) {
                        revDeadlineInputBox.style.opacity = '0.7';
                        revDeadlineInputBox.style.pointerEvents = 'none';
                    } else {
                        revDeadlineInputBox.style.opacity = '1';
                        revDeadlineInputBox.style.pointerEvents = 'auto';
                    }
                }

                // Reset revision deadline in revision request form
                const reqRevDeadlineInput = document.getElementById('modalRevisionDeadlineInput');
                if (reqRevDeadlineInput) reqRevDeadlineInput.value = '';

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
                    const isPdf = /\.pdf(?:$|\?)/i.test(fileUrl);
                    const boxId = `modalRefFileBox_${index}`;
                    const labelText = refFiles.length > 1 ? `Reference File #${index + 1}` : 'Reference File';
                    const removeBtnHtml = task.can_remove_reference ? `
                        <button type="button" onclick="removeSpecificRefFile('${boxId}', ${index})" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; padding:6px 12px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer; margin-bottom:8px;">Remove</button>
                    ` : '';
                    if (isPdf) {
                        refParts.push(`
                        <div id="${boxId}" style="display:flex; align-items:flex-end; gap:12px; margin-bottom:8px; margin-top:8px;">
                            <div onclick="window.open('${fileUrl}', '_blank')" style="display:inline-block; cursor:pointer;">
                                <div style="display:flex; align-items:center; gap:8px; padding:12px 16px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:12px; margin-bottom:4px;">
                                    <svg width="24" height="24" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                                    <span style="font-size:12px; font-weight:800; color:#ef4444;">VIEW PDF</span>
                                </div>
                                <span style="display:block; font-size:10px; font-weight:800; color:#0055D4; text-transform:uppercase;">${labelText}</span>
                            </div>
                            ${removeBtnHtml}
                        </div>`);
                    } else if (isVideo) {
                        refParts.push(`
                        <div id="${boxId}" style="display:flex; align-items:flex-end; gap:12px; margin-bottom:8px; margin-top:8px;">
                            <div style="display:inline-block;">
                                <video controls src="${fileUrl}" style="width:100%; max-width:260px; max-height:160px; border-radius:12px; border:1px solid var(--color-border-primary); margin-bottom:4px;"></video>
                                <span style="display:block; font-size:10px; font-weight:800; color:#0055D4; text-transform:uppercase;">${labelText}</span>
                            </div>
                            ${removeBtnHtml}
                        </div>`);
                    } else {
                        refParts.push(`
                        <div id="${boxId}" style="display:flex; align-items:flex-end; gap:12px; margin-bottom:8px; margin-top:8px;">
                            <div onclick="openImagePreview('${fileUrl}', false)" style="display:inline-block; cursor:pointer;">
                                <img src="${fileUrl}" style="width:100%; max-width:180px; max-height:140px; height:auto; border-radius:12px; border:1px solid var(--color-border-primary); margin-bottom:4px; object-fit:cover;">
                                <span style="display:block; font-size:10px; font-weight:800; color:#0055D4; text-transform:uppercase;">${labelText}</span>
                            </div>
                            ${removeBtnHtml}
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

                // Deduplicate reference URLs against reference files (prevents uploaded files from showing twice as links)
                if (refUrls && refUrls.length > 0 && refFiles && refFiles.length > 0) {
                    refUrls = refUrls.filter(u => {
                        if (!u || typeof u !== 'string') return false;
                        const trimmed = u.trim();
                        return !refFiles.includes(trimmed) && !refFiles.some(f => f.includes(trimmed) || trimmed.includes(f));
                    });
                }

                refUrls.forEach((url, index) => {
                    if (!url) return;
                    const label = refUrls.length > 1 ? `Visit Link #${index + 1}` : 'Visit Reference Link';
                    const linkBoxId = `modalRefUrlBox_${index}`;
                    const removeLinkBtnHtml = task.can_remove_reference ? `
                        <button type="button" onclick="removeSpecificRefUrl('${linkBoxId}', ${index})" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; padding:4px 8px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;" title="Remove link">Remove</button>
                    ` : '';
                    refParts.push(`
                    <div id="${linkBoxId}" style="display:inline-flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <a href="${url}" target="_blank" class="ref-chip" style="display:inline-flex; align-items:center; gap:6px;">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            ${label}
                        </a>
                        ${removeLinkBtnHtml}
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
                const associates = task.associates || {};
                let roles = [];
                if (isOtherDeliverable || task.is_other_deliverable) {
                    // Other Deliverables in Campaign / Pitch: Assign -> Approve -> Close
                    roles = [
                        {key: 'assignee', label: 'Assignee'},
                        {key: 'brand_manager', label: 'Brand Manager'},
                    ];
                } else if (task.flow_type === 'direct_design' || task.is_direct_design) {
                    // Direct Design: Designer -> Writer Review -> Approver Review -> Further Approver -> Final Approval -> Closed
                    roles = [
                        {key: 'designer', label: 'Designer'},
                        {key: 'writer', label: 'Writer'},
                        {key: 'approver', label: 'Approver'},
                    ];
                    if (associates.further_approver && associates.further_approver !== 'None') {
                        roles.push({key: 'further_approver', label: 'Further Approver'});
                    }
                    roles.push({key: 'brand_manager', label: 'Brand Manager'});
                } else if (projectWorkflowType === 'campaign' || projectWorkflowType === 'pitch') {
                    // Campaign / Pitch Outlines
                    roles = [
                        {key: 'assignee', label: 'Assignee'},
                        {key: 'brand_manager', label: 'Brand Manager'},
                    ];
                    if (associates.approver && associates.approver !== 'None') {
                        roles.push({key: 'approver', label: 'Approver'});
                    }
                    if (associates.further_approver && associates.further_approver !== 'None') {
                        roles.push({key: 'further_approver', label: 'Further Approver'});
                    }
                    if (associates.coordinator && associates.coordinator !== 'None') {
                        roles.push({key: 'coordinator', label: 'Coordinator'});
                    }
                    if (associates.designer && associates.designer !== 'None') {
                        roles.push({key: 'designer', label: 'Designer'});
                    }
                } else {
                    // Standard Retainer Workflow
                    roles = [
                        {key: 'writer', label: 'Writer'},
                        {key: 'approver', label: 'Approver'},
                    ];
                    if (associates.further_approver && associates.further_approver !== 'None') {
                        roles.push({key: 'further_approver', label: 'Further Approver'});
                    }
                    roles.push(
                        {key: 'brand_manager', label: 'Brand Manager'},
                        {key: 'coordinator', label: 'Coordinator'},
                        {key: 'designer', label: 'Designer'}
                    );
                }
                
                roles.forEach(role => {
                    const name = associates[role.key]
                        || (role.key === 'assignee' ? (task.assignee_name || task.writer?.name || associates.writer) : null)
                        || (role.key === 'designer' ? (task.designer?.name || task.designer_name) : null)
                        || (role.key === 'writer' ? (task.writer?.name || task.writer_name) : null)
                        || (role.key === 'approver' ? (task.approver?.name || task.approver_name) : null)
                        || (role.key === 'brand_manager' ? (task.brand_manager?.name || task.brand_manager_name) : null)
                        || (role.key === 'coordinator' ? (task.coordinator?.name || task.coordinator_name) : null)
                        || 'None';
                    
                    let rCol = '100,116,139';
                    if (role.key === 'writer' || role.key === 'assignee') rCol = '59,130,246';
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

                if (teamGrid.children.length === 0) {
                    teamGrid.innerHTML = `
                        <div style="grid-column: 1 / -1; display:flex; align-items:center; gap:10px; padding:12px 14px; background:rgba(255,255,255,0.03); border:1px dashed var(--color-border-primary); border-radius:12px; color:var(--color-text-secondary); font-size:12px; font-weight:600;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Team members will be populated as deliverable advances through review.</span>
                        </div>
                    `;
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
                const canRemoveArt = Boolean(task.can_remove_artwork);
                artFiles.forEach((fileUrl, index) => {
                    if (!fileUrl) return;
                    const isVideo = /\.(mp4|webm|ogg|mov)(?:$|\?)/i.test(fileUrl);
                    const isPdf = /\.pdf(?:$|\?)/i.test(fileUrl);
                    const boxId = `modalArtFileBox_${index}`;
                    const labelText = artFiles.length > 1 ? `Artwork File #${index + 1}` : 'Preview Artwork';
                    let thumbHtml = '';
                    if (isPdf) {
                        thumbHtml = `<div class="task-thumbnail" style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25);">
                            <svg width="28" height="28" fill="none" stroke="#ef4444" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                            <span style="font-size:10px; font-weight:800; color:#ef4444;">PDF</span>
                        </div>`;
                    } else if (isVideo) {
                        thumbHtml = `<video src="${fileUrl}" class="task-thumbnail" preload="metadata"></video>`;
                    } else {
                        thumbHtml = `<img src="${fileUrl}" class="task-thumbnail" alt="Final Design">`;
                    }
                    finalParts.push(`
                        <div id="${boxId}" class="art-box-item" style="display:inline-block; margin-right:12px; margin-bottom:8px; vertical-align:top; text-align:center;">
                            <div onclick="${isPdf ? `window.open('${fileUrl}', '_blank')` : `openImagePreview('${fileUrl}', ${canRemoveArt}, ${task.id})`}" style="text-decoration:none; cursor:pointer;">
                                ${thumbHtml}
                                <span style="display:block; font-size:10px; font-weight:800; color:#10b981; text-transform:uppercase; margin-top:6px; text-align:center;">${labelText}</span>
                            </div>
                            ${canRemoveArt ? `
                                <button type="button" onclick="removeSpecificArtFile('${boxId}', ${index}, '${fileUrl}')" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; padding:4px 8px; font-size:10px; margin-top:8px; width:100%; height:auto; line-height:1; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-weight:700;">
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
                        <div id="${linkBoxId}" class="art-box-item" style="display:inline-block; vertical-align:top; text-align:center; margin-right:12px; margin-bottom:8px;">
                            <a href="${url}" target="_blank" style="display:inline-flex; align-items:center; gap:6px; padding:10px 16px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.2); border-radius:10px; color:#10b981; text-decoration:none; vertical-align:top;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                <span style="font-size:11px; font-weight:800; text-transform:uppercase;">${label}</span>
                            </a>
                            ${canRemoveArt ? `
                                <button type="button" onclick="removeSpecificArtUrl('${linkBoxId}', ${index}, '${url}')" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; padding:4px 8px; font-size:10px; margin-top:8px; width:100%; height:auto; line-height:1; display:inline-flex; align-items:center; justify-content:center; gap:4px; font-weight:700;">
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
                            const removeBtnHtml = task.can_remove_artwork ? `
                                <button type="button" onclick="this.closest('.art-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove link input">✕ Remove</button>
                            ` : '';
                            row.innerHTML = `
                                <input type="url" name="final_designs_urls[]" value="${url}" form="artworkDeliveryForm" ${task.can_remove_artwork ? '' : 'readonly'} placeholder="https://drive.google.com/…" style="flex:1; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                ${removeBtnHtml}`;
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
                    task.revisions_history.forEach((rev, rIdx) => {
                        const dateStr = new Date(rev.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                        const row = document.createElement('div');
                        row.style.cssText = 'display:flex; flex-direction:column; gap:8px; background:rgba(239, 68, 68, 0.05); padding:12px; border-radius:12px; border:1px solid rgba(239, 68, 68, 0.1); flex: 1 1 calc(25% - 12px); min-width: 240px; box-sizing: border-box;';
                        const revLabel = `R${task.revisions_history.length - rIdx}`;
                        
                        let fixedBadge = '';
                        if (rev.fixed_at) {
                            const fixedDate = new Date(rev.fixed_at).toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
                            
                            // Find the corresponding approval record where the writer wrote what they changed.
                            const fixTime = new Date(rev.fixed_at).getTime();
                            const matchingApproval = task.approvals_history ? task.approvals_history.find(app => {
                                if (rev.fixed_by_user_id && app.user_id !== rev.fixed_by_user_id) return false;
                                const appTime = new Date(app.created_at).getTime();
                                return Math.abs(appTime - fixTime) < 10000;
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

                            let fixedByName = null;
                            if (rev.fixed_by_user && rev.fixed_by_user.name) {
                                fixedByName = rev.fixed_by_user.name;
                            } else if (rev.fixedByUser && rev.fixedByUser.name) {
                                fixedByName = rev.fixedByUser.name;
                            } else if (matchingApproval && matchingApproval.user && matchingApproval.user.name) {
                                fixedByName = matchingApproval.user.name;
                            } else if (rev.fixed_by_user_id) {
                                const anyApprovalByUser = task.approvals_history ? task.approvals_history.find(app => app.user_id === rev.fixed_by_user_id && app.user && app.user.name) : null;
                                if (anyApprovalByUser) {
                                    fixedByName = anyApprovalByUser.user.name;
                                } else if (task.writer_id === rev.fixed_by_user_id && (task.writer?.name || task.assignee_name)) {
                                    fixedByName = task.writer?.name || task.assignee_name;
                                } else if (task.designer_id === rev.fixed_by_user_id && task.designer?.name) {
                                    fixedByName = task.designer.name;
                                } else if (task.associates && task.associates.writer && task.writer_id === rev.fixed_by_user_id) {
                                    fixedByName = task.associates.writer;
                                }
                            }
                            if (!fixedByName && (task.writer || task.assignee_name || (task.associates && task.associates.writer))) {
                                if (['Approver', 'Writer', 'Writer Review'].includes(rev.stage_at_revision)) {
                                    fixedByName = task.writer?.name || task.assignee_name || (task.associates && task.associates.writer);
                                }
                            }

                            let targetRole = (rev.target || rev.target_role || '').toLowerCase();
                            if (!targetRole) {
                                if (['Writer', 'Approver', 'Further Approver', 'Brand Manager', 'Coordinator', 'Assignee', 'Assign'].includes(rev.stage_at_revision)) {
                                    targetRole = 'writer';
                                } else if (rev.stage_at_revision === 'Writer Review') {
                                    targetRole = 'designer';
                                } else if (task.approval_stage === 'Designer') {
                                    targetRole = 'designer';
                                } else {
                                    targetRole = 'writer';
                                }
                            }
                            const targetLabel = targetRole === 'designer' ? 'Designer' : (targetRole === 'assignee' ? 'Assignee' : 'Writer');

                            fixedBadge = `
                                <div style="display:flex; flex-direction:column; margin-top:4px; padding-top:8px; border-top:1px dashed rgba(239, 68, 68, 0.2);">
                                    <div style="display:flex; justify-content:space-between; align-items:center;">
                                        <div style="display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:800; color:#10b981; text-transform:uppercase; background:rgba(16, 185, 129, 0.1); padding:4px 8px; border-radius:6px;">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Fixed by ${fixedByName || 'Unknown'} (${targetLabel})
                                        </div>
                                        <div style="font-size:10px; color:#10b981; opacity:0.8; font-weight:600;">${fixedDate}</div>
                                    </div>
                                    ${noteHtml}
                                </div>`;
                        } else {
                            let targetRole = (rev.target || rev.target_role || '').toLowerCase();
                            if (!targetRole) {
                                if (['Writer', 'Approver', 'Further Approver', 'Brand Manager', 'Coordinator', 'Assignee', 'Assign'].includes(rev.stage_at_revision)) {
                                    targetRole = 'writer';
                                } else if (rev.stage_at_revision === 'Writer Review') {
                                    targetRole = 'designer';
                                } else if (task.approval_stage === 'Designer') {
                                    targetRole = 'designer';
                                } else {
                                    targetRole = 'writer';
                                }
                            }
                            const targetLabel = targetRole === 'designer' ? 'Designer' : (targetRole === 'assignee' ? 'Assignee' : 'Writer');

                            fixedBadge = `
                                <div style="margin-top:4px; padding-top:8px; border-top:1px dashed rgba(239, 68, 68, 0.2);">
                                    <div style="display:inline-flex; align-items:center; gap:4px; font-size:9px; font-weight:800; color:#ef4444; text-transform:uppercase; background:rgba(239, 68, 68, 0.1); padding:4px 8px; border-radius:6px;">
                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Pending Fix (${targetLabel})
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

                        let revDeadHtml = '';
                        if (rev.revision_deadline) {
                            const revDeadDate = new Date(rev.revision_deadline);
                            const revDeadStr = isNaN(revDeadDate.getTime()) ? rev.revision_deadline : revDeadDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                            revDeadHtml = `
                                <div style="display:inline-flex; align-items:center; gap:5px; margin-top:6px; font-size:10px; font-weight:700; color:#ef4444; background:rgba(239, 68, 68, 0.08); padding:3px 8px; border-radius:6px; border:1px solid rgba(239, 68, 68, 0.2);">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Revision Deadline: ${revDeadStr}
                                </div>`;
                        }

                        let targetRole = (rev.target || rev.target_role || '').toLowerCase();
                        if (!targetRole) {
                            if (['Writer', 'Approver', 'Further Approver', 'Brand Manager', 'Coordinator', 'Assignee', 'Assign'].includes(rev.stage_at_revision)) {
                                targetRole = 'writer';
                            } else if (rev.stage_at_revision === 'Writer Review') {
                                targetRole = 'designer';
                            } else if (task.approval_stage === 'Designer') {
                                targetRole = 'designer';
                            } else {
                                targetRole = 'writer';
                            }
                        }
                        const targetLabel = targetRole === 'designer' ? 'Designer' : (targetRole === 'assignee' ? 'Assignee' : 'Writer');

                        row.innerHTML = `
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                                <div style="font-size:11px; font-weight:800; color:#ef4444; text-transform:uppercase; display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <span style="display:inline-flex; align-items:center; justify-content:center; background:#ef4444; color:#fff; padding:1px 6px; border-radius:4px; font-size:9.5px; font-weight:900;">${revLabel}</span>
                                    <span>Requested by ${rev.user ? rev.user.name : 'Unknown'}</span>
                                    <span style="color:#ef4444; opacity:0.5;">•</span>
                                    <span>${rev.stage_at_revision || 'Unknown Stage'}</span>
                                    <span style="color:#ef4444; opacity:0.5;">•</span>
                                    <span style="display:inline-flex; align-items:center; gap:3px; background:rgba(239,68,68,0.12); color:#ef4444; border:1px solid rgba(239,68,68,0.25); padding:1px 6px; border-radius:4px; font-size:9.5px; font-weight:800; letter-spacing:0.04em;">
                                        FOR ${targetLabel.toUpperCase()}
                                    </span>
                                </div>
                                <div style="font-size:10px; color:#ef4444; opacity:0.8; font-weight:600; white-space:nowrap;">${dateStr}</div>
                            </div>
                            ${contentHtml}
                            ${revDeadHtml}
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
                    const activeTarget = (task.revision_target || task.active_revision_target || (task.approval_stage === 'Designer' ? 'designer' : 'writer')).toLowerCase();
                    const activeTargetLabel = activeTarget === 'designer' ? 'Designer' : (activeTarget === 'assignee' ? 'Assignee' : 'Writer');
                    const targetBadge = document.getElementById('modalRevisionAlertTargetBadge');
                    if (targetBadge) {
                        targetBadge.textContent = `For: ${activeTargetLabel}`;
                        targetBadge.style.display = 'inline-flex';
                    }
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

                    const revDeadlineBadge = document.getElementById('modalRevisionDeadlineBadge');
                    const revDeadlineText = document.getElementById('modalRevisionDeadlineText');
                    if (revDeadlineBadge && revDeadlineText) {
                        if (task.revision_deadline) {
                            const rStr = formatLocalDateStr(task.revision_deadline);
                            revDeadlineText.textContent = `Revision Due: ${rStr}`;
                            revDeadlineBadge.style.display = 'inline-flex';
                        } else {
                            revDeadlineBadge.style.display = 'none';
                        }
                    }

                    revAlert.style.display = 'block';
                } else revAlert.style.display = 'none';

                // Workflow Tracker dots
                let taskStages;
                if (isDirectDesign || task.is_direct_design) {
                    const stageHistory = task.approvals_history || task.approvalsHistory || [];
                    const isInHistory = (stg) => {
                        if (Array.isArray(stageHistory)) {
                            return stageHistory.some(h => (h.stage || h.approval_stage) === stg);
                        }
                        if (typeof stageHistory === 'object' && stageHistory !== null) {
                            return Object.values(stageHistory).some(h => (h.stage || h.approval_stage) === stg);
                        }
                        return false;
                    };

                    const hasWriterReview = Boolean(
                        task.writer_id ||
                        task.approval_stage === 'Writer Review' ||
                        isInHistory('Writer Review')
                    );
                    const hasFurtherApprover = Boolean(
                        task.further_approver_id ||
                        task.approval_stage === 'Further Approver' ||
                        isInHistory('Further Approver')
                    );

                    taskStages = ['Designer'];
                    if (hasWriterReview) {
                        taskStages.push('Writer Review');
                    }
                    taskStages.push('Approver Review');
                    if (hasFurtherApprover) {
                        taskStages.push('Further Approver');
                    }
                    taskStages.push('Final Approval');
                } else if (isOtherDeliverable) {
                    taskStages = ['Assign', 'Approve'];
                } else if (projectWorkflowType === 'campaign' || projectWorkflowType === 'pitch') {
                    taskStages = ['Writer', 'Approver', 'Further Approver', 'Brand Manager', 'Coordinator', 'Designer', 'Writer Review', 'Approver Review', 'AM/BD', 'Final Approval'];
                } else {
                    taskStages = (task.workflow_stages || WORKFLOW_STAGES).filter(s => !['Closed', 'Close'].includes(s));
                }

                let currentStageIdx = taskStages.indexOf(task.approval_stage);
                if (currentStageIdx === -1) {
                    if (task.approval_stage === 'Manager Review') currentStageIdx = taskStages.indexOf('Final Approval');
                    if (task.approval_stage === 'Assignee') currentStageIdx = taskStages.indexOf('Writer') !== -1 ? taskStages.indexOf('Writer') : taskStages.indexOf('Assign');
                    if (task.approval_stage === 'Assign') currentStageIdx = taskStages.indexOf('Assignee');
                    if (task.approval_stage === 'Closed' || task.approval_stage === 'Close') currentStageIdx = taskStages.length;
                }

                const modalStepsEl = document.getElementById('modalWorkflowSteps');
                if (modalStepsEl) {
                    modalStepsEl.innerHTML = taskStages.map((stg, idx) => {
                        let cls = 'step-item';
                        const isCompleted = idx < currentStageIdx;
                        const isActive = idx === currentStageIdx;
                        if (isCompleted) cls += ' completed';
                        else if (isActive) cls += ' active';

                        let dotContent = `${idx + 1}`;
                        if (isCompleted && isDirectDesign) {
                            dotContent = `<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
                        }
                        return `<div class="${cls}" data-stage="${stg}"><div class="step-dot">${dotContent}</div><div class="step-label">${stg}</div></div>`;
                    }).join('');
                }

                // Adjust fields for Other Deliverables & Fast Track (hide caption/copy, show brief)
                const captionItem = document.getElementById('quillCaption')?.closest('.detail-item');
                const copyItem = document.getElementById('quillCopy')?.closest('.detail-item');
                const conceptLabel = document.getElementById('quillConcept')?.closest('.detail-item')?.querySelector('.detail-label');
                const refGroup = document.getElementById('modalReferenceGroup') || document.getElementById('modalReference')?.closest('.detail-item');
                const submitNotesArea = document.getElementById('submitNotesArea');
                const isBriefOnly = isOtherDeliverable || isDirectDesign || task.is_direct_design;
                if (isBriefOnly) {
                    if (captionItem) captionItem.style.display = 'none';
                    if (copyItem) copyItem.style.display = 'none';
                    if (conceptLabel) {
                        conceptLabel.textContent = 'Brief';
                        if (isDirectDesign || task.is_direct_design) {
                            conceptLabel.style.background = '#6366f1';
                        }
                    }
                    if (refGroup) {
                        refGroup.style.marginTop = '36px';
                        refGroup.style.paddingTop = '20px';
                    }
                    if (submitNotesArea && isOtherDeliverable) submitNotesArea.style.display = 'none';
                } else {
                    if (captionItem) captionItem.style.display = '';
                    if (copyItem) copyItem.style.display = '';
                    if (conceptLabel) {
                        conceptLabel.textContent = 'Concept';
                        conceptLabel.style.background = '#3b82f6';
                    }
                    if (refGroup) {
                        refGroup.style.marginTop = '24px';
                        refGroup.style.paddingTop = '12px';
                    }
                    if (submitNotesArea) submitNotesArea.style.display = '';
                }

                // Action Buttons
                const submitBtnForm = document.getElementById('submitStageForm');
                const showRevisionBtn = document.getElementById('showRevisionBtn');
                const saveContentBtn = document.getElementById('saveContentBtn');
                const writerArea = document.getElementById('writerSelectionArea');
                const apprArea = document.getElementById('approverSelectionArea');
                const bmArea = document.getElementById('brandManagerSelectionArea');
                const coordArea = document.getElementById('coordinatorSelectionArea');
                const dArea = document.getElementById('designerSelectionArea');
                const delArea = document.getElementById('designerDeliveryArea');
                
                const nextBtn = document.getElementById('submitStageBtn');
                submitBtnForm.style.display = 'none';
                submitBtnForm.style.alignItems = 'center';
                submitBtnForm.style.gap = '12px';
                if (nextBtn) nextBtn.style.display = 'none';
                if (showRevisionBtn) showRevisionBtn.style.display = 'none';
                saveContentBtn.style.display = 'none';
                const routeArea = document.getElementById('directDesignRouteArea');
                if (routeArea) routeArea.style.display = 'none';
                if (writerArea) {
                    writerArea.style.display = 'none';
                    const wSel = writerArea.querySelector('select');
                    if (wSel) {
                        wSel.disabled = true;
                        wSel.value = '';
                    }
                }
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

                if (isOtherDeliverable) {
                    if (stage === 'Assignee' || stage === 'Writer' || !stage) stage = 'Assign';
                    else if (stage === 'Closed' || stage === 'Close') stage = 'Close';
                }

                // Normalize role for comparison (already computed above)

                const isAssignedPerson = (task.writer_id && AUTH_USER_ID == task.writer_id) || 
                                          (task.designer_id && AUTH_USER_ID == task.designer_id) || 
                                          (task.associates && task.associates.assignee && task.associates.assignee === AUTH_USER_NAME) ||
                                          (task.assignee_name && task.assignee_name === AUTH_USER_NAME);
                const isManager = isAdmin || userRole === 'brandmanager' || userRole === 'operationsmanager';

                const canAct = (isDirectDesign || task.is_direct_design)
                    ? (
                        isAdmin ||
                        userRole === 'operationsmanager' ||
                        (stage === 'Designer' && (isAssignedDesigner || (!task.designer_id && hasDesignerRole) || userRole === 'brandmanager')) ||
                        (stage === 'Writer Review' && (isAssignedWriter || (!task.writer_id && hasWriterRole))) ||
                        (stage === 'Approver Review' && (isAssignedApprover || (!task.approver_id && (userRole === 'approver' || userRole === 'approvercoordinator')))) ||
                        (stage === 'Further Approver' && (isAssignedFurtherApprover || (!task.further_approver_id && (userRole === 'approver' || userRole === 'approvercoordinator')))) ||
                        ((stage === 'Final Approval' || stage === 'Manager Review') && (isAssignedBrandMgr || (!task.brand_manager_id && (userRole === 'brandmanager' || isManager))))
                    )
                    : (isOtherDeliverable && (stage === 'Assign' || stage === 'Assignee' || stage === 'Writer'))
                    ? isAssignedPerson
                    : (
                        isAdmin ||
                        (isOtherDeliverable && stage === 'Approve' && isManager) ||
                        (!isOtherDeliverable && (
                            (stage === 'Writer'          && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                            (stage === 'Assignee'        && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                            (stage === 'Assign'          && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                            (stage === 'Writer Review'   && hasWriterRole             && (!task.writer_id         || isAssignedWriter)) ||
                            (stage === 'Approver'          && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager')   && (!task.approver_id       || isAssignedApprover)) ||
                            (stage === 'Approver Review'   && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager')   && (!task.approver_id       || isAssignedApprover)) ||
                            (stage === 'Further Approver'  && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager')   && (!task.approver_id       || isAssignedApprover)) ||
                            (stage === 'Brand Manager'   && userRole === 'brandmanager' && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                            (stage === 'AM/BD'           && userRole === 'brandmanager' && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                            (stage === 'Final Approval'  && userRole === 'brandmanager' && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                            (stage === 'Approve'         && isManager && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                            (stage === 'Manager Review'  && isManager && (!task.brand_manager_id || isAssignedBrandMgr)) ||
                            (stage === 'Coordinator'     && (userRole === 'coordinator' || userRole === 'approvercoordinator')  && (!task.coordinator_id  || isAssignedCoordinator)) ||
                            (stage === 'Designer'        && hasDesignerRole             && (!task.designer_id      || isAssignedDesigner))
                        ))
                    );

                // Check if current user was the last person who submitted/approved this deliverable
                const approvals = task.approvals_history || task.approvalsHistory || [];
                const latestApproval = (approvals && approvals.length > 0) ? approvals[0] : null;
                const isLastSubmitter = latestApproval && (latestApproval.user_id == AUTH_USER_ID);

                // Check if the current user is specifically assigned to handle this current stage
                const isCurrentStageAssignee = (isDirectDesign || task.is_direct_design) ? (
                    (stage === 'Designer') ? (isAssignedDesigner || (!task.designer_id && hasDesignerRole) || userRole === 'brandmanager') :
                    (stage === 'Writer Review') ? (isAssignedWriter || (!task.writer_id && hasWriterRole)) :
                    (stage === 'Approver Review') ? (isAssignedApprover || (!task.approver_id && (userRole === 'approver' || userRole === 'approvercoordinator'))) :
                    (stage === 'Further Approver') ? (isAssignedFurtherApprover || (!task.further_approver_id && (userRole === 'approver' || userRole === 'approvercoordinator'))) :
                    (stage === 'Final Approval' || stage === 'Manager Review') ? (isAssignedBrandMgr || (!task.brand_manager_id && (userRole === 'brandmanager' || isManager))) : false
                ) : isOtherDeliverable ? (
                    (stage === 'Assign' || stage === 'Assignee') ? isAssignedPerson :
                    (stage === 'Approve') ? isManager : false
                ) : (
                    (stage === 'Writer' || stage === 'Assignee' || stage === 'Assign' || stage === 'Writer Review') ? isAssignedWriter :
                    (stage === 'Approver' || stage === 'Approver Review' || stage === 'Further Approver') ? isAssignedApprover :
                    (stage === 'Brand Manager' || stage === 'AM/BD' || stage === 'Final Approval' || stage === 'Approve' || stage === 'Manager Review') ? isAssignedBrandMgr :
                    (stage === 'Coordinator') ? isAssignedCoordinator :
                    (stage === 'Designer') ? isAssignedDesigner : false
                );

                // If the user was the one who submitted the previous stage, and is not assigned to this next stage, disable the button
                const isWaitingForDifferentPerson = isLastSubmitter && !isCurrentStageAssignee && !isAdmin;

                if (canAct && submitBtnForm) {
                    submitBtnForm.style.display = 'flex';
                    const nextBtn = document.getElementById('submitStageBtn');
                    const isLastStage = (taskStages.indexOf(stage) >= taskStages.length - 1) || stage === 'Final Approval' || stage === 'Approve';

                    if (nextBtn) {
                        nextBtn.style.display = 'block';
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
                            if (isDirectDesign || task.is_direct_design) {
                                if (stage === 'Designer') {
                                    nextBtn.textContent = 'Submit for Writer Review';
                                    nextBtn.style.background = '#0055D4';
                                } else if (stage === 'Writer Review') {
                                    nextBtn.textContent = 'Submit to Approver';
                                    nextBtn.style.background = '#0055D4';
                                } else if (stage === 'Approver Review') {
                                    nextBtn.textContent = 'Approve & Submit';
                                    nextBtn.style.background = '#0055D4';
                                } else if (stage === 'Further Approver') {
                                    nextBtn.textContent = 'Submit to Brand Manager';
                                    nextBtn.style.background = '#0055D4';
                                } else if (stage === 'Final Approval' || stage === 'Manager Review') {
                                    nextBtn.textContent = 'Approve & Close';
                                    nextBtn.style.background = '#10b981';
                                } else {
                                    nextBtn.textContent = 'Submit';
                                }
                            } else if (isOtherDeliverable) {
                                if (stage === 'Assign' || stage === 'Assignee') {
                                    nextBtn.textContent = 'Submit';
                                    nextBtn.style.background = '#0055D4';
                                } else if (stage === 'Approve') {
                                    nextBtn.textContent = 'Approve';
                                    nextBtn.style.background = '#10b981';
                                } else {
                                    nextBtn.textContent = 'Submit';
                                }
                            } else {
                                if (stage === 'Designer') nextBtn.textContent = 'Request for Approval';
                                else nextBtn.textContent = isLastStage ? 'Approve & Close' : 'Submit to Next';
                            }
                            nextBtn.title = '';
                        }
                    }

                    if (isDirectDesign || task.is_direct_design) {
                        if (stage === 'Designer') {
                            if (routeArea) {
                                routeArea.style.display = 'block';

                                // Populate Approver Select
                                if (apprArea) {
                                    const aSel = apprArea.querySelector('select');
                                    if (aSel) {
                                        const projectApproverId = {!! json_encode($deliverable->project?->approver_id ?? null) !!};
                                        if (task.approver_id) {
                                            aSel.value = task.approver_id;
                                        } else if (projectApproverId && aSel.querySelector(`option[value="${projectApproverId}"]`)) {
                                            aSel.value = projectApproverId;
                                        } else if (aSel.options.length > 1 && !aSel.value) {
                                            aSel.value = aSel.options[1].value;
                                        }
                                    }
                                }

                                // Populate Writer Select
                                if (writerArea) {
                                    const wSel = writerArea.querySelector('select');
                                    if (wSel) {
                                        const projectWriterId = {!! json_encode($deliverable->project?->writer_id ?? null) !!};
                                        if (task.writer_id) {
                                            wSel.value = task.writer_id;
                                        } else if (projectWriterId && wSel.querySelector(`option[value="${projectWriterId}"]`)) {
                                            wSel.value = projectWriterId;
                                        } else if (wSel.options.length > 1 && !wSel.value) {
                                            wSel.value = wSel.options[1].value;
                                        }
                                    }
                                }

                                // Route selection: if task already has writer_id and no approver_id, select writer; otherwise default to approver (direct)
                                if (task.writer_id && !task.approver_id) {
                                    handleDirectDesignRouteChange('writer');
                                } else {
                                    handleDirectDesignRouteChange('approver');
                                }
                            }
                        } else if (stage === 'Writer Review') {
                            if (routeArea) routeArea.style.display = 'none';
                            if (apprArea) {
                                apprArea.style.display = 'block';
                                const sel = apprArea.querySelector('select');
                                if (sel) {
                                    sel.disabled = false;
                                    const projectApproverId = {!! json_encode($deliverable->project?->approver_id ?? null) !!};
                                    if (task.approver_id) {
                                        sel.value = task.approver_id;
                                    } else if (projectApproverId && sel.querySelector(`option[value="${projectApproverId}"]`)) {
                                        sel.value = projectApproverId;
                                    }
                                }
                            }
                        } else if (stage === 'Approver Review') {
                            const faArea = document.getElementById('modalFurtherApproverGroup');
                            if (faArea) {
                                faArea.style.display = 'block';
                                const sel = faArea.querySelector('select');
                                if (sel) sel.disabled = false;
                            }
                            if (!task.brand_manager_id && bmArea) {
                                bmArea.style.display = 'block';
                                const sel = bmArea.querySelector('select');
                                if (sel) sel.disabled = false;
                            }
                        } else if (stage === 'Further Approver') {
                            if (!task.brand_manager_id && bmArea) {
                                bmArea.style.display = 'block';
                                const sel = bmArea.querySelector('select');
                                if (sel) sel.disabled = false;
                            }
                        }
                    } else if (isOtherDeliverable && (stage === 'Assign' || stage === 'Assignee')) {
                        if (!task.brand_manager_id && bmArea) {
                            bmArea.style.display = 'block';
                            const sel = bmArea.querySelector('select');
                            if (sel) sel.disabled = false;
                        }
                    } else if (stage === 'Writer' || stage === 'Assignee' || stage === 'Writer Review') {
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
                    if (!isDirectDesign && !task.is_direct_design && stage === 'Approver') {
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
                    if (stage === 'Brand Manager') {
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
                    const canShowArtworkUpload = (isDirectDesign || task.is_direct_design)
                        ? (stage === 'Designer')
                        : (isOtherDeliverable
                            ? (isOtherAssignStage && isOtherAssignedPerson)
                            : (stage === 'Designer'));
                    if (canShowArtworkUpload && delArea) {
                        delArea.style.display = 'block';
                        const titleEl = delArea.querySelector('.detail-label');
                        if (titleEl) {
                            titleEl.textContent = isOtherDeliverable ? 'Upload Final Artwork & Media' : 'Deliver Final Artwork';
                        }
                    }
                }

                // Edit Permissions (already computed above)
                const canEditBriefAndRef = (isDirectDesign || task.is_direct_design)
                    ? (isAdmin || isManager || (task.brand_manager_id && AUTH_USER_ID == task.brand_manager_id))
                    : (isOtherDeliverable
                        ? (isAdmin || Boolean(task.can_remove_reference))
                        : writerEditPermission);

                const mTitle = document.getElementById('modalTaskTitle');
                const canEditTitle = isOtherDeliverable
                    ? (isOtherAssignStage && isOtherAssignedPerson)
                    : (writerEditPermission || isAdmin || ['writer','assignee','brandmanager','operationsmanager'].includes(userRole));
                if (mTitle) mTitle.readOnly = !canEditTitle;
                if (typeof quillConcept !== 'undefined' && quillConcept) quillConcept.enable(canEditBriefAndRef);
                if (typeof quillCaption !== 'undefined' && quillCaption) quillCaption.enable(writerEditPermission);
                if (typeof quillCopy !== 'undefined' && quillCopy) quillCopy.enable(writerEditPermission);
                
                // Reference Edit Area
                const refEditArea = document.getElementById('modalReferenceEditArea');
                const canEditReference = canEditBriefAndRef;
                if (refEditArea) {
                    const toggleBtn = document.getElementById('toggleRefEditBtn');
                    const hasExistingRefs = refParts && refParts.length > 0;
                    if ((isDirectDesign || task.is_direct_design) && hasExistingRefs) {
                        refEditArea.style.display = 'none';
                        if (toggleBtn) {
                            toggleBtn.style.display = canEditReference ? 'inline-flex' : 'none';
                            toggleBtn.textContent = '+ Add / Edit References';
                        }
                    } else {
                        refEditArea.style.display = canEditReference ? 'grid' : 'none';
                        if (toggleBtn) toggleBtn.style.display = 'none';
                    }
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
                                const removeBtnHtml = task.can_remove_reference ? `
                                    <button type="button" onclick="this.closest('.ref-input-row').remove()" style="background:rgba(239,68,68,0.1); color:#ef4444; border:none; border-radius:8px; padding:8px 12px; font-weight:bold; cursor:pointer; font-size:11px;" title="Remove link input">✕ Remove</button>
                                ` : '';
                                row.innerHTML = `
                                    <input type="url" name="reference_urls[]" value="${url}" form="submitStageForm" ${task.can_remove_reference ? '' : 'readonly'} placeholder="https://..." style="flex:1; padding:8px 10px; border:1px solid var(--color-border-primary); border-radius:10px; font-size:12px; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-primary);">
                                    ${removeBtnHtml}`;
                                linksContainer.appendChild(row);
                            });
                        }
                    }
                }

                const canSaveContent = isOtherDeliverable
                    ? (isOtherAssignedPerson || canEditBriefAndRef || isManager)
                    : (canEditReference || (designerEditPermission && stage === 'Designer') || isAdmin || userRole === 'brandmanager');

                if (canSaveContent) {
                    submitBtnForm.style.display = 'flex';
                    saveContentBtn.style.display = 'block';
                }

                const isReviewStage = ['Approver', 'Brand Manager', 'Final Approval', 'AM/BD', 'Writer Review', 'Approver Review', 'Further Approver', 'Approve', 'Manager Review'].includes(stage);
                const isAuthorizedToReview = isAdmin ||
                    (isDirectDesign || task.is_direct_design ? (
                        (stage === 'Writer Review' && (hasWriterRole || isAssignedWriter || userRole === 'operationsmanager')) ||
                        (stage === 'Approver Review' && (userRole === 'approver' || userRole === 'approvercoordinator' || isAssignedApprover || userRole === 'operationsmanager')) ||
                        (stage === 'Further Approver' && (userRole === 'approver' || userRole === 'approvercoordinator' || isAssignedFurtherApprover || userRole === 'operationsmanager')) ||
                        ((stage === 'Final Approval' || stage === 'Manager Review') && (userRole === 'brandmanager' || userRole === 'operationsmanager' || isAssignedBrandMgr))
                    ) : (
                        (stage === 'Approver' && (userRole === 'approver' || userRole === 'operationsmanager')) ||
                        ((stage === 'Brand Manager' || stage === 'Final Approval') && (userRole === 'brandmanager' || userRole === 'operationsmanager')) ||
                        (stage === 'Approve' && (userRole === 'brandmanager' || userRole === 'operationsmanager' || isManager || canEditBriefAndRef)) ||
                        (stage === 'Writer Review' && (userRole === 'writer' || userRole === 'assignee')) ||
                        (stage === 'Approver Review' && (userRole === 'approver' || userRole === 'operationsmanager')) ||
                        (stage === 'Further Approver' && (userRole === 'approver' || userRole === 'approvercoordinator' || userRole === 'operationsmanager'))
                    ));

                if (isReviewStage && isAuthorizedToReview && showRevisionBtn) {
                    showRevisionBtn.style.display = 'block';
                }

                // Show/reset revision target toggle for post-designer stages where a choice between Designer and Writer makes sense
                const postDesignerStagesWithChoice = ['Approver Review', 'Further Approver', 'Final Approval', 'Manager Review'];
                const modalRevisionTargetGroup = document.getElementById('modalRevisionTargetGroup');
                const modalRevisionTarget = document.getElementById('modalRevisionTarget');
                if (modalRevisionTargetGroup) {
                    if (isOtherDeliverable) {
                        modalRevisionTargetGroup.style.display = 'none';
                        modalRevisionTarget.value = 'writer';
                        document.getElementById('modalRevisionTargetNote').textContent = 'The task will be sent back to the assigned person.';
                    } else if (isDirectDesign || task.is_direct_design || stage === 'Writer Review') {
                        // At Writer Review, the reviewer is the Writer, so revisions always route back to the Designer
                        modalRevisionTargetGroup.style.display = 'none';
                        modalRevisionTarget.value = 'designer';
                        document.getElementById('modalRevisionTargetNote').textContent = 'The task will be sent back to the Designer.';
                    } else if (postDesignerStagesWithChoice.includes(stage)) {
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
            setTimeout(() => { 
                overlay.style.display = 'none'; 
                toggleRevisionInput(false); 
                setupModalLayout(false);
                if (window._deliverableModalModified) {
                    window._deliverableModalModified = false;
                    window.location.reload();
                }
            }, 300);
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
                const dInp = document.getElementById('modalRevisionDeadlineInput');
                if (dInp) dInp.value = '';
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

                if (window.HighlightedUpload) {
                    const fileNames = Object.values(sbRefFiles).map(f => f.name).join(', ');
                    window.HighlightedUpload.show({
                        title: 'Uploading Batch References…',
                        filename: fileNames,
                        percent: 45,
                        status: `Uploading reference files for ${Object.keys(sbRefFiles).length} task(s)…`,
                        type: 'reference'
                    });
                }
            } else {
                sbFetchOpts = { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': sbCsrf, 'Accept': 'application/json' }, body: JSON.stringify({ batch_data: batchData }) };
            }

            try {
                const response = await fetch(`/deliverables/${taskId}/batch-submit`, sbFetchOpts);

                const data = await response.json();
                if (data.success) {
                    if (window.HighlightedUpload) {
                        window.HighlightedUpload.update(100, 'Batch updated successfully! Refreshing…');
                    }
                    clearBatchDrafts();
                    window.location.reload();
                } else {
                    if (window.HighlightedUpload) window.HighlightedUpload.hide();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Error advancing batch', type: 'error' } }));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                if (window.HighlightedUpload) window.HighlightedUpload.hide();
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
                    
                    // Check if any files are attached
                    let hasFilesToUpload = false;
                    let fileNamesList = [];
                    for (let [k, v] of formData.entries()) {
                        if (v instanceof File && v.name && v.size > 0) {
                            hasFilesToUpload = true;
                            fileNamesList.push(v.name);
                        }
                    }

                    if (hasFilesToUpload && window.HighlightedUpload) {
                        const hasArtwork = fileNamesList.some(name => !name.toLowerCase().includes('ref'));
                        window.HighlightedUpload.show({
                            title: hasArtwork ? 'Uploading Deliverable Artwork…' : 'Uploading Reference Files…',
                            filename: fileNamesList.join(', '),
                            percent: 50,
                            status: `Uploading ${fileNamesList.length} file(s) to server…`,
                            type: hasArtwork ? 'artwork' : 'reference'
                        });
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
                                if (window.HighlightedUpload) window.HighlightedUpload.hide();
                                showErrorModal(result.message || 'An error occurred.');
                            } else {
                                if (window.HighlightedUpload) {
                                    window.HighlightedUpload.update(100, 'Saved successfully! Refreshing…');
                                }
                                window.location.reload();
                            }
                        } else {
                            if (window.HighlightedUpload) window.HighlightedUpload.hide();
                            const errorText = await response.text();
                            console.error('Server returned non-JSON response:', errorText);
                            if (response.status === 419) {
                                showErrorModal('Session expired. Please refresh the page and try again.');
                            } else {
                                showErrorModal('Server Error: ' + response.status + '. Check console for details.');
                            }
                        }
                    } catch (error) {
                        if (window.HighlightedUpload) window.HighlightedUpload.hide();
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

function openReassignDesignerModal() {
    const overlay = document.getElementById('reassignDesignerModalOverlay');
    if (!overlay) return;
    overlay.style.display = 'flex';
    setTimeout(() => {
        overlay.style.opacity = '1';
        const modal = overlay.querySelector('.cd-modal');
        if (modal) modal.classList.add('active');
    }, 10);
}

function closeReassignDesignerModal(e) {
    if (e && e.target !== document.getElementById('reassignDesignerModalOverlay')) return;
    const overlay = document.getElementById('reassignDesignerModalOverlay');
    if (!overlay) return;
    overlay.style.opacity = '0';
    const modal = overlay.querySelector('.cd-modal');
    if (modal) modal.classList.remove('active');
    setTimeout(() => {
        overlay.style.display = 'none';
    }, 200);
}

async function reassignDesigner(btn) {
    const overlay = document.getElementById('reassignDesignerModalOverlay');
    const taskId = overlay?.getAttribute('data-task-id') || document.getElementById('btnOpenReassignDesignerModal')?.getAttribute('data-task-id');
    const designerId = document.getElementById('reassignDesignerSelect').value;
    const designerDeadline = document.getElementById('reassignDesignerDeadline')?.value || null;
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
            body: JSON.stringify({ designer_id: designerId, designer_deadline: designerDeadline, reason: reason })
        });

        const result = await response.json();
        if (response.ok && result.success) {
            alert(result.message || 'Designer reassigned successfully.');
            closeReassignDesignerModal();
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
    if (!url) return;
    if (typeof url === 'string' && url.trim().startsWith('[')) {
        try {
            const parsed = JSON.parse(url.trim());
            if (Array.isArray(parsed) && parsed.length > 0) {
                if (parsed.length > 1 && typeof openMediaGallery === 'function') {
                    return openMediaGallery('Artwork Media', parsed, [], canRemove, deliverableId);
                }
                url = parsed[0];
            }
        } catch(e) {}
    }
    if (!url || typeof url !== 'string') return;

    if (url.match(/\.pdf(?:$|\?)/i)) {
        window.open(url, '_blank');
        return;
    }
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
            removeBtn.onclick = function() {
                const targetBox = document.querySelector(`.art-box-item img[src="${url}"], .art-box-item video[src="${url}"]`)?.closest('.art-box-item');
                removeArtworkImmediately(deliverableId, url, targetBox, false);
            };
        } else {
            removeBtn.style.display = 'none';
            removeBtn.onclick = null;
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

    <!-- Reassign Designer Modal Popup -->
    @if($isAdmin || in_array($userRole, ['brandmanager', 'coordinator', 'approvercoordinator']))
    <div id="reassignDesignerModalOverlay" class="cd-modal-overlay" onclick="closeReassignDesignerModal(event)" style="z-index: 10001; justify-content: center; align-items: center;">
        <div class="cd-modal" style="max-width: 480px; width: 92%; position: relative !important; inset: auto !important; margin: auto !important; height: auto !important; max-height: 90vh !important; border-radius: 20px !important; overflow: hidden !important; border: 1px solid rgba(236,72,153,0.3) !important; box-shadow: 0 25px 60px -15px rgba(0,0,0,0.5) !important;" onclick="event.stopPropagation()">
            <div class="cd-modal-header" style="padding: 18px 24px; background: var(--color-bg-secondary); align-items: center; border-bottom: 1px solid var(--color-border-primary); display:flex; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:32px; height:32px; border-radius:8px; background:rgba(236,72,153,0.1); border:1px solid rgba(236,72,153,0.25); display:flex; align-items:center; justify-content:center;">
                        <svg width="18" height="18" fill="none" stroke="#ec4899" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 style="font-size: 15px; font-weight: 900; color: #ec4899; margin: 0; text-transform:uppercase; letter-spacing:0.04em;">Reassign Designer</h2>
                        <div id="reassignDesignerCurrentName" style="font-size: 11px; color: var(--color-text-secondary); font-weight: 600; margin-top:2px;">Current designer: -</div>
                    </div>
                </div>
                <button type="button" onclick="closeReassignDesignerModal()" style="background:none; border:none; color:var(--color-text-secondary); cursor:pointer; padding:4px;" title="Close">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="cd-modal-body" style="padding: 24px; display:flex; flex-direction:column; gap:16px;">
                <div>
                    <label class="detail-label" style="font-size:10px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">New Designer</label>
                    <select id="reassignDesignerSelect" required style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid rgba(236,72,153,0.25); font-size:13px; font-weight:600; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-secondary); outline:none;">
                        <option value="" style="background:var(--color-bg-primary); color:var(--color-text-primary);">Select Designer...</option>
                        @foreach($designers as $designer)
                            <option value="{{ $designer->id }}" style="background:var(--color-bg-primary); color:var(--color-text-primary);">{{ $designer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="detail-label" style="font-size:10px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">Designer Due Date</label>
                    <input type="date" id="reassignDesignerDeadline" style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid rgba(236,72,153,0.25); font-size:13px; font-weight:600; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-secondary); outline:none; box-sizing:border-box;">
                </div>
                <div>
                    <label class="detail-label" style="font-size:10px; font-weight:800; color:var(--color-text-secondary); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px;">Reason (Optional)</label>
                    <input type="text" id="reassignDesignerReason" placeholder="e.g. Designer unavailable" style="width:100%; padding:10px 14px; border-radius:10px; border:1.5px solid rgba(236,72,153,0.25); font-size:13px; font-weight:500; font-family:inherit; color:var(--color-text-primary); background:var(--color-bg-secondary); outline:none; box-sizing:border-box;">
                </div>
            </div>
            <div class="cd-modal-footer" style="padding: 16px 24px; background: var(--color-bg-secondary); border-top: 1px solid var(--color-border-primary); display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeReassignDesignerModal()" class="cd-btn cd-btn-outline" style="padding: 8px 18px; font-size: 12px; font-weight: 700;">Cancel</button>
                <button type="button" onclick="reassignDesigner(this)" class="cd-btn" style="padding: 8px 20px; background:#ec4899; color:#fff; border:none; border-radius:8px; font-size:12px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(236,72,153,0.35);">Reassign</button>
            </div>
        </div>
    </div>
    @endif

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
            <button type="button" id="imagePreviewRemoveBtn" class="cd-btn cd-btn-outline" style="color:#ef4444; border-color:#fee2e2; padding: 10px 24px; font-size: 13px; font-weight: 700; display: none; align-items: center; gap: 8px; justify-content: center; background: rgba(239, 68, 68, 0.1); backdrop-filter: blur(4px);">
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
