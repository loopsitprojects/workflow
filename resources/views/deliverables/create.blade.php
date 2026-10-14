<x-layout title="New Deliverable">
    <style>
        .create-page-wrapper {
            max-width: 820px;
            margin: 24px auto 48px;
            padding: 0 16px;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        .create-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: var(--color-text-secondary);
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .create-bc-link {
            text-decoration: none;
            color: inherit;
            transition: color 0.15s;
        }
        .create-bc-link:hover {
            color: var(--color-text-primary);
        }
        .create-bc-sep {
            opacity: 0.35;
        }
        .create-bc-current {
            color: var(--color-text-primary);
        }
        
        /* Top Navigation & Switch Header */
        .top-ctrl-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* Mode Switcher Pill Toggle */
        .mode-toggle-group {
            display: inline-flex;
            padding: 3px;
            background: var(--color-bg-secondary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 10px;
            gap: 4px;
        }
        .mode-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 16px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            color: var(--color-text-secondary);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .mode-toggle-btn:hover {
            color: var(--color-text-primary);
        }
        .mode-toggle-btn.active {
            background: #0055D4;
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,85,212,0.35);
        }

        /* Distinct Separated Form Cards */
        .section-card {
            width: 100%;
            background: var(--color-bg-primary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 14px;
            overflow: visible;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 22px;
            transition: border-color 0.2s;
        }
        .section-card.batch-meta-card {
            border-color: rgba(0, 85, 212, 0.25);
        }
        .section-card.deliverables-card {
            border-color: rgba(0, 85, 212, 0.35);
        }
        .section-header-bar {
            padding: 14px 22px;
            background: var(--color-bg-secondary);
            border-bottom: 1px solid var(--color-border-primary);
            border-top-left-radius: 13px;
            border-top-right-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .section-title {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: var(--color-text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.01em;
        }
        .section-subtitle {
            font-size: 11px;
            color: var(--color-text-secondary);
            margin: 3px 0 0;
        }
        .section-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
            background: rgba(0,85,212,0.12);
            color: #3b82f6;
            border: 1px solid rgba(0,85,212,0.25);
        }

        .form-section-body {
            padding: 20px 22px;
        }
        .form-field-group {
            margin-bottom: 18px;
        }
        .form-field-group:last-child {
            margin-bottom: 0;
        }
        
        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--color-text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 7px;
        }
        .field-label.blue {
            color: #3b82f6;
        }
        
        .massive-input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            font-size: 18px;
            font-weight: 800;
            color: var(--color-text-primary);
            letter-spacing: -0.01em;
        }
        .massive-input::placeholder {
            opacity: 0.3;
            color: var(--color-text-primary);
        }
        
        .styled-input {
            width: 100%;
            background: var(--color-bg-secondary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 9px;
            padding: 9px 13px;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-primary);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
            appearance: none;
            box-sizing: border-box;
        }
        .styled-input:focus {
            border-color: #0055D4;
            box-shadow: 0 0 0 3px rgba(0,85,212,0.15);
        }
        select.styled-input {
            cursor: pointer;
        }
        .styled-textarea {
            width: 100%;
            background: var(--color-bg-secondary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 9px;
            padding: 10px 13px;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-primary);
            outline: none;
            resize: vertical;
            min-height: 70px;
            transition: border-color 0.15s, box-shadow 0.15s;
            box-sizing: border-box;
            line-height: 1.5;
        }
        .styled-textarea:focus {
            border-color: #0055D4;
            box-shadow: 0 0 0 3px rgba(0,85,212,0.15);
        }
        
        /* Grid layouts */
        .form-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        @media(min-width: 640px) {
            .form-grid-3 {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        @media(min-width: 640px) {
            .form-grid-2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Individual Deliverable Card (Inside Batch) */
        .deliverable-item-card {
            background: var(--color-bg-secondary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 12px;
            margin-bottom: 16px;
            overflow: visible;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .deliverable-item-card:hover {
            border-color: rgba(0,85,212,0.4);
        }
        .deliverable-item-header {
            padding: 10px 16px;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--color-border-primary);
            border-top-left-radius: 11px;
            border-top-right-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .deliverable-item-num {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .deliverable-item-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #0055D4;
        }
        .deliverable-type-tag {
            font-size: 10px;
            font-weight: 700;
            color: var(--color-text-secondary);
            background: var(--color-bg-primary);
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid var(--color-border-primary);
        }
        .deliverable-remove-btn {
            background: transparent;
            border: none;
            color: var(--color-text-secondary);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            transition: all 0.15s;
        }
        .deliverable-remove-btn:hover {
            color: #ef4444;
            background: rgba(239,68,68,0.1);
        }

        .deliverable-item-body {
            padding: 14px 16px;
        }

        .add-deliverable-btn {
            width: 100%;
            padding: 13px 18px;
            background: transparent;
            border: 1.5px dashed rgba(0,85,212,0.4);
            border-radius: 10px;
            color: #3b82f6;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.18s;
            margin-top: 10px;
        }
        .add-deliverable-btn:hover {
            border-color: #0055D4;
            background: rgba(0,85,212,0.06);
            color: #0055D4;
        }

        /* Footer */
        .form-footer-card {
            background: var(--color-bg-primary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        .btn {
            padding: 9px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }
        .btn-primary-blue {
            background: #0055D4;
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(0,85,212,0.3);
        }
        .btn-primary-blue:hover {
            background: #0044aa;
            transform: translateY(-1px);
        }
        .btn-primary-blue:disabled {
            opacity: 0.85;
            cursor: not-allowed;
            transform: none !important;
        }
        @keyframes loopSpin { to { transform: rotate(360deg); } }
        .btn-cancel {
            background: transparent;
            color: var(--color-text-secondary);
            border: 1.5px solid var(--color-border-primary);
        }
        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--color-text-primary);
        }

        /* Full-page loading overlay */
        .full-page-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            background: rgba(11, 17, 32, 0.82);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        .loading-spinner-ring {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            border: 3.5px solid rgba(255, 255, 255, 0.15);
            border-top-color: #3b82f6;
            border-right-color: #3b82f6;
            animation: loopSpin 0.75s linear infinite;
        }

        /* Quill Rich Text Editor Dark Mode & Theme */
        .ql-toolbar.ql-snow {
            border: 1.5px solid var(--color-border-primary) !important;
            border-top-left-radius: 9px;
            border-top-right-radius: 9px;
            background: var(--color-bg-secondary);
            padding: 6px 10px;
        }
        .ql-container.ql-snow {
            border: 1.5px solid var(--color-border-primary) !important;
            border-top: none !important;
            border-bottom-left-radius: 9px;
            border-bottom-right-radius: 9px;
            background: var(--color-bg-primary);
            color: var(--color-text-primary);
            font-family: inherit;
            font-size: 13px;
        }
        .ql-snow .ql-stroke { stroke: var(--color-text-secondary); }
        .ql-snow .ql-fill, .ql-snow .ql-stroke.ql-fill { fill: var(--color-text-secondary); }
        .ql-snow .ql-picker { color: var(--color-text-secondary); }
        .ql-snow .ql-picker-options { background: var(--color-bg-secondary); border: 1px solid var(--color-border-primary); color: var(--color-text-primary); }
        .ql-snow .ql-picker-item:hover, .ql-snow .ql-picker-label:hover { color: var(--color-text-primary); }
        .ql-snow .ql-picker-label:hover .ql-stroke { stroke: var(--color-text-primary); }
        .ql-editor { padding: 10px 14px; min-height: 85px; font-family: inherit; font-size: 13px; line-height: 1.6; }
        .ql-editor.ql-blank::before { color: var(--color-text-secondary) !important; opacity: 0.4; font-style: normal; }
        .dark .ql-editor,
        .dark .ql-editor * {
            color: var(--color-text-primary) !important;
        }
        button.ql-active .ql-stroke { stroke: #0055D4 !important; }
        button.ql-active .ql-fill { fill: #0055D4 !important; }
        .ql-picker-label.ql-active { color: #0055D4 !important; }
        .ql-picker-label.ql-active .ql-stroke { stroke: #0055D4 !important; }

        /* Searchable User Combobox Component */
        .searchable-user-wrapper {
            position: relative;
            width: 100%;
        }
        .searchable-select-trigger {
            width: 100%;
            background: var(--color-bg-secondary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 9px;
            padding: 7px 12px;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-primary);
            outline: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 40px;
            box-sizing: border-box;
            transition: border-color 0.15s, box-shadow 0.15s;
            user-select: none;
        }
        .searchable-select-trigger:focus,
        .searchable-select-trigger.is-open {
            border-color: #0055D4;
            box-shadow: 0 0 0 3px rgba(0, 85, 212, 0.15);
        }
        .searchable-select-trigger.has-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
        .searchable-chevron {
            color: var(--color-text-secondary);
            transition: transform 0.2s ease;
        }
        .searchable-chevron.is-open {
            transform: rotate(180deg);
        }
        .searchable-clear-btn {
            background: none;
            border: none;
            color: var(--color-text-secondary);
            font-size: 16px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .searchable-clear-btn:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        .user-avatar-pill {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            flex-shrink: 0;
            letter-spacing: -0.02em;
        }
        .user-name-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: rgba(0, 85, 212, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(0, 85, 212, 0.2);
            text-transform: capitalize;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .user-role-badge.role-writer {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            border-color: rgba(59, 130, 246, 0.25);
        }
        .user-role-badge.role-designer {
            background: rgba(168, 85, 247, 0.12);
            color: #a855f7;
            border-color: rgba(168, 85, 247, 0.25);
        }
        .user-role-badge.role-admin,
        .user-role-badge.role-operations-manager {
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.25);
        }
        .user-role-badge.role-brand-manager {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.25);
        }

        /* Floating Dropdown Panel */
        .searchable-dropdown-panel {
            position: absolute;
            top: calc(100% + 5px);
            left: 0;
            right: 0;
            z-index: 9999;
            background: var(--color-bg-primary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 10px;
            box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.35);
            padding: 6px;
            min-width: 260px;
        }
        .searchable-input-box {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }
        .searchable-input-box .search-icon {
            position: absolute;
            left: 10px;
            color: var(--color-text-secondary);
            pointer-events: none;
            display: flex;
            align-items: center;
        }
        .searchable-input {
            width: 100%;
            padding: 8px 30px 8px 32px;
            font-size: 12px;
            font-weight: 600;
            background: var(--color-bg-secondary);
            border: 1.5px solid #0055D4;
            border-radius: 7px;
            color: var(--color-text-primary);
            outline: none;
            box-sizing: border-box;
        }
        .searchable-input::placeholder {
            color: var(--color-text-secondary);
            opacity: 0.6;
            font-weight: 500;
        }
        .search-clear-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: var(--color-text-secondary);
            font-size: 15px;
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .search-clear-btn:hover {
            color: var(--color-text-primary);
        }
        .searchable-count-bar {
            padding: 2px 6px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--color-text-secondary);
            border-bottom: 1px solid var(--color-border-primary);
            margin-bottom: 4px;
        }
        .searchable-list {
            max-height: 220px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .searchable-list::-webkit-scrollbar {
            width: 5px;
        }
        .searchable-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .searchable-list::-webkit-scrollbar-thumb {
            background: var(--color-border-primary);
            border-radius: 4px;
        }
        .searchable-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
            border-radius: 7px;
            cursor: pointer;
            transition: background 0.12s;
            user-select: none;
            gap: 8px;
        }
        .searchable-item:hover,
        .searchable-item.is-highlighted {
            background: rgba(0, 85, 212, 0.08);
        }
        .searchable-item.is-selected {
            background: rgba(0, 85, 212, 0.12);
        }
        .searchable-empty {
            padding: 20px 12px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="create-page-wrapper" x-data="deliverableForm()">
        {{-- Breadcrumbs --}}
        <nav class="create-breadcrumb">
            <a href="{{ route('brands.index') }}" class="create-bc-link">Brands</a>
            @if(isset($project) && $project && $project->brand)
                <span class="create-bc-sep">/</span>
                <a href="{{ route('brands.show', $project->brand->slug) }}" class="create-bc-link">{{ $project->brand->name }}</a>
            @endif
            @if(isset($project) && $project)
                <span class="create-bc-sep">/</span>
                <a href="{{ route('projects.show', $project->id) }}" class="create-bc-link">{{ $project->name }}</a>
            @endif
            @if(isset($parentTask) && $parentTask)
                <span class="create-bc-sep">/</span>
                <a href="{{ route('deliverables.show', $parentTask->id) }}" class="create-bc-link">{{ $parentTask->title }}</a>
            @endif
            <span class="create-bc-sep">/</span>
            <span class="create-bc-current">{{ isset($parentId) ? 'Add Subtasks' : 'New Deliverable' }}</span>
        </nav>

        {{-- Top Navigation & Mode Switch --}}
        <div class="top-ctrl-bar">
            <div>
                <h2 style="font-size:18px;font-weight:800;color:var(--color-text-primary);margin:0;">
                    {{ isset($parentId) ? 'Add Deliverables to Batch' : 'Create Deliverable' }}
                </h2>
                <p style="font-size:12px;color:var(--color-text-secondary);margin:3px 0 0;">
                    {{ $project ? ($project->brand->name ?? '') . ' — ' . $project->name : 'Select project and configure deliverable' }}
                </p>
            </div>

            {{-- Mode Switcher: Single vs Batch --}}
            @if(!isset($parentId))
            <div class="mode-toggle-group">
                <button type="button" @click="setMode('single')" :class="{'active': mode === 'single'}" class="mode-toggle-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Single Deliverable
                </button>
                <button type="button" @click="setMode('batch')" :class="{'active': mode === 'batch'}" class="mode-toggle-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Batch of Deliverables
                </button>
            </div>
            @endif
        </div>

        <form action="{{ route('deliverables.store') }}" method="POST" enctype="multipart/form-data" @submit="handleSubmit($event)">
            @csrf
            <input type="hidden" name="status" value="To Do">
            <input type="hidden" name="task_type" value="Deliverable">
            <input type="hidden" name="progress_percent" value="{{ $progressPercent ?? 0 }}">
            <input type="hidden" name="approver_id" value="">
            <input type="hidden" name="approval_stage" :value="(workflowType === 'campaign' || workflowType === 'pitch') ? 'Assignee' : 'Writer'" value="{{ (isset($workflowType) && in_array($workflowType, ['campaign', 'pitch'])) ? 'Assignee' : 'Writer' }}">

            @if(isset($parentId))
                <input type="hidden" name="parent_deliverable_id" value="{{ $parentId }}">
            @endif

            @if ($errors->any())
                <div style="margin-bottom:20px;padding:14px 18px;background:#fff1f2;border:1.5px solid #fecaca;border-radius:12px;color:#be123c;font-size:13px;font-weight:600;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Target Project Select (if not preselected) --}}
            @if($selectedProjectId)
                <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
            @else
                <div class="section-card" style="padding:16px 20px;margin-bottom:18px;">
                    <label class="field-label">Target Project</label>
                    <select name="project_id" class="styled-input" required @change="updateProjectWorkflow($event.target.value)">
                        <option value="">Select Project...</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->brand->name ?? 'Unassigned' }} - {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- ========================================================================= --}}
            {{-- OPTION 1: SINGLE DELIVERABLE                                              --}}
            {{-- ========================================================================= --}}
            <div x-show="mode === 'single'" x-cloak>
                <div class="section-card">
                    <div class="section-header-bar">
                        <div>
                            <h3 class="section-title">
                                <svg width="16" height="16" fill="none" stroke="#0055D4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Deliverable Details
                            </h3>
                            <p class="section-subtitle">Create a single standalone deliverable</p>
                        </div>
                        <span class="section-badge">Single Deliverable</span>
                    </div>

                    <div class="form-section-body">
                        {{-- Deliverable Title --}}
                        <div class="form-field-group">
                            <label class="field-label blue">Deliverable Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" :disabled="mode !== 'single'" :required="mode === 'single'" placeholder="e.g. Ramadan Special Promo Post / Product Video..." class="massive-input"
                                value="{{ old('title', $parentTask->title ?? '') }}">
                            @error('title') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:5px;">{{ $message }}</p> @enderror
                        </div>

                        {{-- Details Grid --}}
                        <div class="form-grid-3 form-field-group">
                            <div>
                                <label class="field-label blue">Assigned Person <span style="color:#ef4444;">*</span></label>
                                @if(auth()->user()->role === 'Writer')
                                    <input type="hidden" name="writer_id" value="{{ auth()->id() }}" :disabled="mode !== 'single'">
                                    <div class="styled-input" style="cursor:default;opacity:0.8;">{{ auth()->user()->name }}</div>
                                @else
                                    <div x-data="searchableUserSelect({
                                        getUsers: () => availableUsers,
                                        selectedId: singleWriterId,
                                        onSelect: (val) => { singleWriterId = val }
                                    })" 
                                         class="searchable-user-wrapper" 
                                         @click.outside="open = false"
                                         @single-writer-missing.window="showError = true; open = true; $nextTick(() => { if ($refs.searchInput) $refs.searchInput.focus(); })">
                                        
                                        <input type="hidden" name="writer_id" :value="selectedId" :disabled="mode !== 'single'">

                                        {{-- Trigger Box --}}
                                        <div class="searchable-select-trigger"
                                             :class="{ 'is-open': open, 'has-error': showError }"
                                             @click="toggle()"
                                             tabindex="0"
                                             @keydown="handleTriggerKeyDown($event)">
                                            <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;overflow:hidden;">
                                                <template x-if="selectedUser">
                                                    <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                                                        <span class="user-avatar-pill" :style="getAvatarStyle(selectedUser.id, selectedUser.name)" x-text="getInitials(selectedUser.name)"></span>
                                                        <span class="user-name-text" x-text="selectedUser.name"></span>
                                                        <span class="user-role-badge" :class="'role-' + (selectedUser.role || '').toLowerCase().replace(/\s+/g, '-')" x-text="selectedUser.role" x-show="selectedUser.role"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!selectedUser">
                                                    <div style="display:flex;align-items:center;gap:8px;color:var(--color-text-secondary);font-size:13px;opacity:0.7;">
                                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                        </svg>
                                                        <span>Select Assignee / Writer...</span>
                                                    </div>
                                                </template>
                                            </div>

                                            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                                <button type="button" 
                                                        x-show="selectedUser" 
                                                        @click.stop="clear($event)" 
                                                        class="searchable-clear-btn" 
                                                        title="Clear selection">
                                                    &times;
                                                </button>
                                                <svg class="searchable-chevron" :class="{ 'is-open': open }" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>

                                        {{-- Dropdown Floating Panel --}}
                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-150"
                                             x-transition:enter-start="opacity-0 transform -translate-y-1 scale-98"
                                             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                             x-transition:leave="transition ease-in duration-100"
                                             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                             x-transition:leave-end="opacity-0 transform -translate-y-1 scale-98"
                                             class="searchable-dropdown-panel"
                                             style="display:none;"
                                             @keydown.escape.prevent.stop="open = false">

                                            {{-- Search Input Box --}}
                                            <div class="searchable-input-box">
                                                <span class="search-icon">
                                                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="11" cy="11" r="8" stroke-width="2.5"/>
                                                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2.5" stroke-linecap="round"/>
                                                    </svg>
                                                </span>
                                                <input type="text"
                                                       x-ref="searchInput"
                                                       x-model="search"
                                                       @input="highlightedIndex = 0"
                                                       @keydown="handleSearchKeyDown($event)"
                                                       placeholder="Type name or role to find..."
                                                       class="searchable-input"
                                                       autocomplete="off"
                                                       spellcheck="false">
                                                <button type="button" 
                                                        x-show="search.length > 0" 
                                                        @click="search = ''; $refs.searchInput.focus()" 
                                                        class="search-clear-btn">
                                                    &times;
                                                </button>
                                            </div>

                                            {{-- Quick Count / Help Bar --}}
                                            <div class="searchable-count-bar">
                                                <span x-text="filteredUsers.length + (filteredUsers.length === 1 ? ' person' : ' people')"></span>
                                                <span style="font-size:10px;opacity:0.6;">&uarr;&darr; navigate &bull; &crarr; select</span>
                                            </div>

                                            {{-- Users List --}}
                                            <div class="searchable-list" x-ref="list">
                                                <template x-for="(user, idx) in filteredUsers" :key="user.id">
                                                    <div class="searchable-item"
                                                         :class="{
                                                             'is-highlighted': idx === highlightedIndex,
                                                             'is-selected': String(user.id) === String(selectedId)
                                                         }"
                                                         @mouseenter="highlightedIndex = idx"
                                                         @click="select(user)">
                                                        <div style="display:flex;align-items:center;gap:9px;min-width:0;flex:1;">
                                                            <span class="user-avatar-pill" :style="getAvatarStyle(user.id, user.name)" x-text="getInitials(user.name)"></span>
                                                            <span class="user-name-text" x-html="highlightMatch(user.name, search)"></span>
                                                            <span class="user-role-badge" :class="'role-' + (user.role || '').toLowerCase().replace(/\s+/g, '-')" x-text="user.role" x-show="user.role"></span>
                                                        </div>
                                                        <template x-if="String(user.id) === String(selectedId)">
                                                            <svg width="15" height="15" fill="none" stroke="#0055D4" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                        </template>
                                                    </div>
                                                </template>

                                                {{-- Empty State --}}
                                                <div x-show="filteredUsers.length === 0" class="searchable-empty">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:6px;opacity:0.4;">
                                                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                                                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                    <div style="font-weight:700;font-size:12px;color:var(--color-text-primary);">No person found</div>
                                                    <div style="font-size:11px;color:var(--color-text-secondary);margin-top:2px;">No match for "<span x-text="search"></span>"</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="field-label">Due Date</label>
                                <input type="date" name="deadline" class="styled-input" :disabled="mode !== 'single'"
                                    value="{{ old('deadline', isset($parentTask->deadline) ? \Carbon\Carbon::parse($parentTask->deadline)->format('Y-m-d') : '') }}">
                            </div>

                            <div>
                                <label class="field-label">Priority</label>
                                <select name="priority" class="styled-input" :disabled="mode !== 'single'">
                                    <option value="High Priority" {{ old('priority', $parentTask->priority ?? '') == 'High Priority' ? 'selected' : '' }}>High Priority</option>
                                    <option value="Medium" {{ old('priority', $parentTask->priority ?? 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="Low Priority" {{ old('priority', $parentTask->priority ?? '') == 'Low Priority' ? 'selected' : '' }}>Low Priority</option>
                                </select>
                            </div>
                        </div>

                        {{-- Format / Post Type --}}
                        <div class="form-field-group">
                            <label class="field-label blue">Deliverable Type <span style="color:#ef4444;">*</span></label>
                            <select name="post_type" x-model="singlePostType" class="styled-input" :disabled="mode !== 'single'" :required="mode === 'single'">
                                <option value="">Select Type...</option>
                                <template x-for="t in currentSubtaskTypes" :key="t">
                                    <option :value="t" x-text="t"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Dynamic Fields based on Outlines vs Brief --}}
                        <div x-show="isOutlinesType(singlePostType)">
                            <div class="form-field-group">
                                <label class="field-label">Concept</label>
                                <div x-data="{ conceptVal: {{ json_encode(old('concept', $parentTask->concept ?? '')) }} }"
                                     x-init="initQuill($refs.singleConcept, conceptVal, (v) => conceptVal = v, 'Visual concept, storyline, or angle...')">
                                    <div x-ref="singleConcept"></div>
                                    <input type="hidden" name="concept" :value="conceptVal" :disabled="mode !== 'single'">
                                </div>
                            </div>
                            <div class="form-field-group">
                                <label class="field-label">Caption</label>
                                <div x-data="{ captionVal: {{ json_encode(old('caption', $parentTask->caption ?? '')) }} }"
                                     x-init="initQuill($refs.singleCaption, captionVal, (v) => captionVal = v, 'Caption or social headline text...')">
                                    <div x-ref="singleCaption"></div>
                                    <input type="hidden" name="caption" :value="captionVal" :disabled="mode !== 'single'">
                                </div>
                            </div>
                            <div class="form-field-group">
                                <label class="field-label">Post Copy</label>
                                <div x-data="{ copyVal: {{ json_encode(old('post_copy', $parentTask->post_copy ?? '')) }} }"
                                     x-init="initQuill($refs.singleCopy, copyVal, (v) => copyVal = v, 'Complete post copy or script lines...')">
                                    <div x-ref="singleCopy"></div>
                                    <input type="hidden" name="post_copy" :value="copyVal" :disabled="mode !== 'single'">
                                </div>
                            </div>
                        </div>

                        <div x-show="!isOutlinesType(singlePostType)">
                            <div class="form-field-group">
                                <label class="field-label">Creative Brief / Description</label>
                                <div x-data="{ notesVal: {{ json_encode(old('notes', $parentTask->notes ?? '')) }} }"
                                     x-init="initQuill($refs.singleNotes, notesVal, (v) => notesVal = v, 'Enter details, requirements, or instructions for this deliverable...')">
                                    <div x-ref="singleNotes"></div>
                                    <input type="hidden" name="notes" :value="notesVal" :disabled="mode !== 'single'">
                                </div>
                            </div>
                        </div>

                        {{-- References --}}
                        <div class="form-grid-2 form-field-group">
                            <div>
                                <label class="field-label">Reference URL (Drive, Figma, Links...)</label>
                                <input type="url" name="reference" class="styled-input" :disabled="mode !== 'single'" placeholder="https://..." value="{{ old('reference', $parentTask->reference ?? '') }}">
                            </div>
                            <div>
                                <label class="field-label">Attach Reference File</label>
                                <input type="file" name="reference_file" class="styled-input" :disabled="mode !== 'single'" style="padding:6px 10px;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Single Deliverable Action Footer --}}
                <div class="form-footer-card">
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel" :style="isSubmitting ? 'pointer-events:none;opacity:0.6;' : ''">Cancel</a>
                    <button type="submit" class="btn btn-primary-blue" :disabled="isSubmitting" :style="isSubmitting ? 'pointer-events:none;opacity:0.85;' : ''">
                        <template x-if="!isSubmitting">
                            <span style="display:inline-flex;align-items:center;gap:6px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                Create Deliverable
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span style="display:inline-flex;align-items:center;gap:8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:loopSpin 0.75s linear infinite;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating Deliverable…
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- OPTION 2: BATCH OF DELIVERABLES (SINGLE PAGE, BATCH = NAME, DATE, PRIORITY) --}}
            {{-- ========================================================================= --}}
            <div x-show="mode === 'batch'" x-cloak>
                
                {{-- CARD 1: BATCH DETAILS (ONLY Batch Name, Due Date, and Priority) --}}
                <div class="section-card batch-meta-card">
                    <div class="section-header-bar">
                        <div>
                            <h3 class="section-title">
                                <svg width="16" height="16" fill="none" stroke="#0055D4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Batch Details
                            </h3>
                            <p class="section-subtitle">Define the batch campaign title, default due date, and priority</p>
                        </div>
                        <span class="section-badge">Batch Settings</span>
                    </div>

                    <div class="form-section-body">
                        {{-- Batch Name --}}
                        <div class="form-field-group">
                            <label class="field-label blue">Batch Name / Campaign Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" :disabled="mode !== 'batch'" :required="mode === 'batch'" placeholder="e.g. Ramadan Campaign Posts / Q4 Social Set..." class="massive-input"
                                value="{{ old('title', $parentTask->title ?? '') }}" {{ isset($parentId) ? 'readonly' : '' }}>
                            @error('title') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:5px;">{{ $message }}</p> @enderror
                        </div>

                        {{-- Batch Date & Priority (Clean 2-Column Row) --}}
                        <div class="form-grid-2 form-field-group">
                            <div>
                                <label class="field-label">Batch Due Date</label>
                                <input type="date" name="deadline" class="styled-input" :disabled="mode !== 'batch'"
                                    value="{{ old('deadline', isset($parentTask->deadline) ? \Carbon\Carbon::parse($parentTask->deadline)->format('Y-m-d') : '') }}">
                            </div>

                            <div>
                                <label class="field-label">Batch Priority</label>
                                <select name="priority" class="styled-input" :disabled="mode !== 'batch'">
                                    <option value="High Priority" {{ old('priority', $parentTask->priority ?? '') == 'High Priority' ? 'selected' : '' }}>High Priority</option>
                                    <option value="Medium" {{ old('priority', $parentTask->priority ?? 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="Low Priority" {{ old('priority', $parentTask->priority ?? '') == 'Low Priority' ? 'selected' : '' }}>Low Priority</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 2: DELIVERABLES IN THIS BATCH (EACH DELIVERABLE IS SEPARATE) --}}
                <div class="section-card deliverables-card">
                    <div class="section-header-bar">
                        <div>
                            <h3 class="section-title">
                                <svg width="16" height="16" fill="none" stroke="#0055D4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Deliverables in this Batch
                            </h3>
                            <p class="section-subtitle">Add each deliverable item. Each has its own title, format, assignee, deadline, and specs.</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="section-badge" x-text="subtasks.length + ' Deliverable(s)'"></span>
                            <button type="button" @click="addSubtask()" class="btn btn-primary-blue" style="padding:5px 12px;font-size:11px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                Add Deliverable
                            </button>
                        </div>
                    </div>

                    <div class="form-section-body">
                        <div id="subtasks-container">
                            <template x-for="(subtask, index) in subtasks" :key="subtask.id">
                                <div class="deliverable-item-card">
                                    {{-- Deliverable Item Header --}}
                                    <div class="deliverable-item-header">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span class="deliverable-item-num">
                                                <span class="deliverable-item-dot"></span>
                                                Deliverable #<span x-text="index + 1"></span>
                                            </span>
                                            <span class="deliverable-type-tag" x-show="subtask.post_type" x-text="subtask.post_type"></span>
                                        </div>
                                        <button type="button" @click="removeSubtask(index)" class="deliverable-remove-btn" title="Remove this deliverable" x-show="subtasks.length > 1">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Remove
                                        </button>
                                    </div>

                                    {{-- Deliverable Item Fields --}}
                                    <div class="deliverable-item-body">
                                        {{-- Row 1: Title and Format --}}
                                        <div class="form-grid-2" style="margin-bottom:12px;">
                                            <div>
                                                <label class="field-label blue">Deliverable Name / Title <span style="color:#ef4444;">*</span></label>
                                                <input type="text" :name="`subtasks[${index}][title]`" x-model="subtask.title" placeholder="e.g. Carousel Post 1 / Reel / Radio Script" class="styled-input" :disabled="mode !== 'batch'" :required="mode === 'batch'">
                                            </div>
                                            <div>
                                                <label class="field-label">Deliverable Type <span style="color:#ef4444;">*</span></label>
                                                <select :name="`subtasks[${index}][post_type]`" x-model="subtask.post_type" class="styled-input" :disabled="mode !== 'batch'" :required="mode === 'batch'">
                                                    <option value="">Select Type...</option>
                                                    <template x-for="t in currentSubtaskTypes" :key="t">
                                                        <option :value="t" x-text="t"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Row 2: Assigned Person & Custom Due Date --}}
                                        <div class="form-grid-2" style="margin-bottom:12px;">
                                            <div>
                                                <label class="field-label blue">Assigned Person <span style="color:#ef4444;">*</span></label>
                                                <div x-data="searchableUserSelect({
                                                    getUsers: () => availableUsers,
                                                    selectedId: subtask.writer_id,
                                                    onSelect: (val) => { subtask.writer_id = val; subtask.hasWriterError = false; }
                                                })" 
                                                     class="searchable-user-wrapper" 
                                                     @click.outside="open = false">
                                                    
                                                    <input type="hidden" :name="`subtasks[${index}][writer_id]`" :value="subtask.writer_id" :disabled="mode !== 'batch'">

                                                    {{-- Trigger Box --}}
                                                    <div class="searchable-select-trigger"
                                                         :class="{ 'is-open': open, 'has-error': subtask.hasWriterError }"
                                                         @click="toggle()"
                                                         tabindex="0"
                                                         @keydown="handleTriggerKeyDown($event)">
                                                        <div style="display:flex;align-items:center;gap:8px;min-width:0;flex:1;overflow:hidden;">
                                                            <template x-if="selectedUser">
                                                                <div style="display:flex;align-items:center;gap:8px;min-width:0;">
                                                                    <span class="user-avatar-pill" :style="getAvatarStyle(selectedUser.id, selectedUser.name)" x-text="getInitials(selectedUser.name)"></span>
                                                                    <span class="user-name-text" x-text="selectedUser.name"></span>
                                                                    <span class="user-role-badge" :class="'role-' + (selectedUser.role || '').toLowerCase().replace(/\s+/g, '-')" x-text="selectedUser.role" x-show="selectedUser.role"></span>
                                                                </div>
                                                            </template>
                                                            <template x-if="!selectedUser">
                                                                <div style="display:flex;align-items:center;gap:8px;color:var(--color-text-secondary);font-size:13px;opacity:0.7;">
                                                                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                                    </svg>
                                                                    <span>Select Assignee / Writer...</span>
                                                                </div>
                                                            </template>
                                                        </div>

                                                        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                                            <button type="button" 
                                                                    x-show="selectedUser" 
                                                                    @click.stop="clear($event)" 
                                                                    class="searchable-clear-btn" 
                                                                    title="Clear selection">
                                                                &times;
                                                            </button>
                                                            <svg class="searchable-chevron" :class="{ 'is-open': open }" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    {{-- Dropdown Floating Panel --}}
                                                    <div x-show="open"
                                                         x-transition:enter="transition ease-out duration-150"
                                                         x-transition:enter-start="opacity-0 transform -translate-y-1 scale-98"
                                                         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                                         x-transition:leave="transition ease-in duration-100"
                                                         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                                         x-transition:leave-end="opacity-0 transform -translate-y-1 scale-98"
                                                         class="searchable-dropdown-panel"
                                                         style="display:none;"
                                                         @keydown.escape.prevent.stop="open = false">

                                                        {{-- Search Input Box --}}
                                                        <div class="searchable-input-box">
                                                            <span class="search-icon">
                                                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <circle cx="11" cy="11" r="8" stroke-width="2.5"/>
                                                                    <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2.5" stroke-linecap="round"/>
                                                                </svg>
                                                            </span>
                                                            <input type="text"
                                                                   x-ref="searchInput"
                                                                   x-model="search"
                                                                   @input="highlightedIndex = 0"
                                                                   @keydown="handleSearchKeyDown($event)"
                                                                   placeholder="Type name or role to find..."
                                                                   class="searchable-input"
                                                                   autocomplete="off"
                                                                   spellcheck="false">
                                                            <button type="button" 
                                                                    x-show="search.length > 0" 
                                                                    @click="search = ''; $refs.searchInput.focus()" 
                                                                    class="search-clear-btn">
                                                                &times;
                                                            </button>
                                                        </div>

                                                        {{-- Quick Count / Help Bar --}}
                                                        <div class="searchable-count-bar">
                                                            <span x-text="filteredUsers.length + (filteredUsers.length === 1 ? ' person' : ' people')"></span>
                                                            <span style="font-size:10px;opacity:0.6;">&uarr;&darr; navigate &bull; &crarr; select</span>
                                                        </div>

                                                        {{-- Users List --}}
                                                        <div class="searchable-list" x-ref="list">
                                                            <template x-for="(user, idx) in filteredUsers" :key="user.id">
                                                                <div class="searchable-item"
                                                                     :class="{
                                                                         'is-highlighted': idx === highlightedIndex,
                                                                         'is-selected': String(user.id) === String(selectedId)
                                                                     }"
                                                                     @mouseenter="highlightedIndex = idx"
                                                                     @click="select(user)">
                                                                    <div style="display:flex;align-items:center;gap:9px;min-width:0;flex:1;">
                                                                        <span class="user-avatar-pill" :style="getAvatarStyle(user.id, user.name)" x-text="getInitials(user.name)"></span>
                                                                        <span class="user-name-text" x-html="highlightMatch(user.name, search)"></span>
                                                                        <span class="user-role-badge" :class="'role-' + (user.role || '').toLowerCase().replace(/\s+/g, '-')" x-text="user.role" x-show="user.role"></span>
                                                                    </div>
                                                                    <template x-if="String(user.id) === String(selectedId)">
                                                                        <svg width="15" height="15" fill="none" stroke="#0055D4" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </template>
                                                                </div>
                                                            </template>

                                                            {{-- Empty State --}}
                                                            <div x-show="filteredUsers.length === 0" class="searchable-empty">
                                                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:6px;opacity:0.4;">
                                                                    <circle cx="11" cy="11" r="8" stroke-width="2"/>
                                                                    <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2" stroke-linecap="round"/>
                                                                </svg>
                                                                <div style="font-weight:700;font-size:12px;color:var(--color-text-primary);">No person found</div>
                                                                <div style="font-size:11px;color:var(--color-text-secondary);margin-top:2px;">No match for "<span x-text="search"></span>"</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="field-label">Due Date <span style="font-size:10px;text-transform:none;opacity:0.6;font-weight:500;">(Defaults to batch date if blank)</span></label>
                                                <input type="date" :name="`subtasks[${index}][deadline]`" x-model="subtask.deadline" class="styled-input" :disabled="mode !== 'batch'">
                                            </div>
                                        </div>

                                        {{-- Dynamic Content: Outlines vs Brief --}}
                                        <div x-show="isOutlinesType(subtask.post_type)">
                                            <div class="form-field-group">
                                                <label class="field-label">Concept</label>
                                                <div x-init="initQuill($refs.subConcept, subtask.concept, (v) => subtask.concept = v, 'Visual concept, storyline, or angle...')">
                                                    <div x-ref="subConcept"></div>
                                                    <input type="hidden" :name="`subtasks[${index}][concept]`" :value="subtask.concept" :disabled="mode !== 'batch'">
                                                </div>
                                            </div>
                                            <div class="form-field-group">
                                                <label class="field-label">Caption</label>
                                                <div x-init="initQuill($refs.subCaption, subtask.caption, (v) => subtask.caption = v, 'Caption or headline...')">
                                                    <div x-ref="subCaption"></div>
                                                    <input type="hidden" :name="`subtasks[${index}][caption]`" :value="subtask.caption" :disabled="mode !== 'batch'">
                                                </div>
                                            </div>
                                            <div class="form-field-group">
                                                <label class="field-label">Post Copy</label>
                                                <div x-init="initQuill($refs.subCopy, subtask.post_copy, (v) => subtask.post_copy = v, 'Script or copy lines...')">
                                                    <div x-ref="subCopy"></div>
                                                    <input type="hidden" :name="`subtasks[${index}][post_copy]`" :value="subtask.post_copy" :disabled="mode !== 'batch'">
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="!isOutlinesType(subtask.post_type)">
                                            <div class="form-field-group">
                                                <label class="field-label">Brief / Specific Notes</label>
                                                <div x-init="initQuill($refs.subBrief, subtask.brief, (v) => subtask.brief = v, 'Brief or specific notes for this deliverable...')">
                                                    <div x-ref="subBrief"></div>
                                                    <input type="hidden" :name="`subtasks[${index}][brief]`" :value="subtask.brief" :disabled="mode !== 'batch'">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- References --}}
                                        <div class="form-grid-2">
                                            <div>
                                                <label class="field-label">Reference URL (Drive, Figma...)</label>
                                                <input type="url" :name="`subtasks[${index}][reference]`" x-model="subtask.reference" placeholder="https://..." class="styled-input" :disabled="mode !== 'batch'">
                                            </div>
                                            <div>
                                                <label class="field-label">Attach Reference File</label>
                                                <input type="file" :name="`subtasks[${index}][reference_file]`" class="styled-input" style="padding:6px 10px;" :disabled="mode !== 'batch'">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Add button at bottom of Deliverables --}}
                        <button type="button" @click="addSubtask()" class="add-deliverable-btn">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Another Deliverable to Batch
                        </button>
                    </div>
                </div>

                {{-- Batch Action Footer --}}
                <div class="form-footer-card">
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel" :style="isSubmitting ? 'pointer-events:none;opacity:0.6;' : ''">Cancel</a>
                    <button type="submit" class="btn btn-primary-blue" :disabled="isSubmitting" :style="isSubmitting ? 'pointer-events:none;opacity:0.85;' : ''">
                        <template x-if="!isSubmitting">
                            <span style="display:inline-flex;align-items:center;gap:6px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="`Create Batch (${subtasks.length} Deliverable${subtasks.length === 1 ? '' : 's'})`"></span>
                            </span>
                        </template>
                        <template x-if="isSubmitting">
                            <span style="display:inline-flex;align-items:center;gap:8px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:loopSpin 0.75s linear infinite;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating Batch…
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </form>

        {{-- Full-page loading overlay --}}
        <div id="pageLoadingOverlay"
             class="full-page-loading-overlay"
             x-show="isSubmitting"
             x-cloak>
            <div class="loading-spinner-ring"></div>
            <div style="display:flex;flex-direction:column;align-items:center;gap:6px;text-align:center;">
                <span style="color:#ffffff;font-size:16px;font-weight:800;letter-spacing:0.02em;" x-text="mode === 'batch' ? 'Creating deliverables batch…' : 'Creating deliverable…'">Creating deliverable…</span>
                <span style="color:rgba(255,255,255,0.75);font-size:13px;font-weight:500;">Uploading assets and configuring deliverable…</span>
            </div>
        </div>
    </div>

    <script>
        function deliverableForm() {
            return {
                mode: '{{ isset($parentId) ? 'batch' : 'single' }}',
                workflowType: '{{ $workflowType ?? 'retainer' }}',
                retainerTypes: @json($subtaskTypes->where('workflow_type', 'retainer')->pluck('name')->values()),
                campaignTypes: @json($subtaskTypes->where('workflow_type', 'campaign')->pluck('name')->values()),
                singlePostType: '',
                singleWriterId: '{{ auth()->user()->role === 'Writer' ? auth()->id() : old('writer_id', $parentTask->writer_id ?? '') }}',
                allUsers: @json(($allUsers ?? $users)->map(fn($u) => ['id' => (string)$u->id, 'name' => $u->name, 'role' => $u->role ?? ''])->values()),
                retainerUsers: @json(($allUsers ?? $users)->filter(fn($u) => strtolower($u->role ?? '') === 'writer')->map(fn($u) => ['id' => (string)$u->id, 'name' => $u->name, 'role' => $u->role ?? ''])->values()),
                subtasks: [],
                subtaskCounter: 0,
                isSubmitting: false,
                handleSubmit(e) {
                    if (this.isSubmitting) {
                        e.preventDefault();
                        return;
                    }
                    if (this.mode === 'single') {
                        if (!this.singleWriterId) {
                            e.preventDefault();
                            window.dispatchEvent(new CustomEvent('toast', { 
                                detail: { message: 'Please select an Assigned Person for this deliverable.', type: 'error' } 
                            }));
                            window.dispatchEvent(new CustomEvent('single-writer-missing'));
                            return;
                        }
                    } else if (this.mode === 'batch') {
                        for (let i = 0; i < this.subtasks.length; i++) {
                            if (!this.subtasks[i].writer_id) {
                                e.preventDefault();
                                this.subtasks[i].hasWriterError = true;
                                window.dispatchEvent(new CustomEvent('toast', { 
                                    detail: { message: `Please select an Assigned Person for Deliverable #${i + 1}.`, type: 'error' } 
                                }));
                                const cards = document.querySelectorAll('.deliverable-item-card');
                                if (cards && cards[i]) {
                                    cards[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                                return;
                            }
                        }
                    }
                    this.isSubmitting = true;
                    const overlay = document.getElementById('pageLoadingOverlay');
                    if (overlay) {
                        overlay.style.display = 'flex';
                    }
                },
                get availableUsers() {
                    const isCampaignOrPitch = (this.workflowType === 'campaign' || this.workflowType === 'pitch');
                    return isCampaignOrPitch ? this.allUsers : this.retainerUsers;
                },
                get currentSubtaskTypes() {
                    const types = (this.workflowType === 'campaign' || this.workflowType === 'pitch') 
                        ? this.campaignTypes 
                        : this.retainerTypes;
                    return types.length ? types : ['Static Post', 'Carousel', 'Reel', 'Story', 'Outlines', 'Graphic'];
                },
                init() {
                    this.singlePostType = '';
                    this.addSubtask();
                },
                isOutlinesType(type) {
                    if (this.workflowType === 'retainer') return true;
                    if (!type) return false;
                    const t = type.toString().trim().toLowerCase();
                    return t === 'outlines' || t === 'outline';
                },
                setMode(newMode) {
                    this.mode = newMode;
                    if (newMode === 'batch' && this.subtasks.length === 0) {
                        this.addSubtask();
                    }
                },
                addSubtask() {
                    this.subtaskCounter++;
                    this.subtasks.push({
                        id: Date.now() + Math.random(),
                        title: '',
                        post_type: '',
                        deadline: '',
                        writer_id: '{{ auth()->user()->role === 'Writer' ? auth()->id() : '' }}',
                        hasWriterError: false,
                        concept: '',
                        caption: '',
                        post_copy: '',
                        brief: '',
                        reference: ''
                    });
                },
                removeSubtask(index) {
                    if (this.subtasks.length > 1) {
                        this.subtasks.splice(index, 1);
                    }
                },
                updateProjectWorkflow(projectId) {
                    const projectWorkflows = @json($projects->pluck('workflow_type', 'id'));
                    if (projectId && projectWorkflows[projectId]) {
                        this.workflowType = projectWorkflows[projectId];
                        this.singlePostType = '';
                    }
                }
            }
        }

        function searchableUserSelect(config) {
            return {
                open: false,
                search: '',
                getUsers: config.getUsers || null,
                _users: config.users || [],
                selectedId: config.selectedId ? String(config.selectedId) : '',
                onSelect: config.onSelect || null,
                showError: false,
                highlightedIndex: 0,
                placeholder: config.placeholder || 'Select Assignee / Writer...',

                init() {
                    if (this.selectedId && this.onSelect) {
                        this.onSelect(this.selectedId);
                    }
                },

                get users() {
                    if (typeof this.getUsers === 'function') {
                        return this.getUsers() || [];
                    }
                    return this._users || [];
                },

                get selectedUser() {
                    if (!this.selectedId) return null;
                    return this.users.find(u => String(u.id) === String(this.selectedId)) || null;
                },

                get filteredUsers() {
                    const list = this.users;
                    if (!this.search || !this.search.trim()) {
                        return list;
                    }
                    const q = this.search.toLowerCase().trim();
                    return list.filter(u => {
                        const name = (u.name || '').toLowerCase();
                        const role = (u.role || '').toLowerCase();
                        return name.includes(q) || role.includes(q);
                    });
                },

                select(user) {
                    this.selectedId = user ? String(user.id) : '';
                    this.showError = false;
                    if (this.onSelect) {
                        this.onSelect(this.selectedId);
                    }
                    this.open = false;
                    this.search = '';
                },

                clear(e) {
                    if (e) e.stopPropagation();
                    this.selectedId = '';
                    this.showError = false;
                    if (this.onSelect) {
                        this.onSelect('');
                    }
                    this.search = '';
                },

                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.search = '';
                        this.highlightedIndex = 0;
                        this.showError = false;
                        this.$nextTick(() => {
                            if (this.$refs.searchInput) {
                                this.$refs.searchInput.focus();
                            }
                        });
                    }
                },

                handleTriggerKeyDown(e) {
                    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        this.toggle();
                    }
                },

                handleSearchKeyDown(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (this.filteredUsers.length > 0) {
                            this.highlightedIndex = (this.highlightedIndex + 1) % this.filteredUsers.length;
                            this.scrollToHighlighted();
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (this.filteredUsers.length > 0) {
                            this.highlightedIndex = (this.highlightedIndex - 1 + this.filteredUsers.length) % this.filteredUsers.length;
                            this.scrollToHighlighted();
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.filteredUsers.length > 0 && this.filteredUsers[this.highlightedIndex]) {
                            this.select(this.filteredUsers[this.highlightedIndex]);
                        }
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        this.open = false;
                    }
                },

                scrollToHighlighted() {
                    this.$nextTick(() => {
                        if (!this.$refs.list) return;
                        const items = this.$refs.list.querySelectorAll('.searchable-item');
                        if (items && items[this.highlightedIndex]) {
                            items[this.highlightedIndex].scrollIntoView({ block: 'nearest' });
                        }
                    });
                },

                getInitials(name) {
                    if (!name) return '?';
                    const parts = name.trim().split(/\s+/);
                    if (parts.length >= 2) {
                        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
                    }
                    return name.substring(0, 2).toUpperCase();
                },

                getAvatarStyle(id, name) {
                    const palette = [
                        { bg: 'rgba(59, 130, 246, 0.15)', text: '#3b82f6', border: 'rgba(59, 130, 246, 0.3)' },
                        { bg: 'rgba(16, 185, 129, 0.15)', text: '#10b981', border: 'rgba(16, 185, 129, 0.3)' },
                        { bg: 'rgba(245, 158, 11, 0.15)', text: '#f59e0b', border: 'rgba(245, 158, 11, 0.3)' },
                        { bg: 'rgba(139, 92, 246, 0.15)', text: '#8b5cf6', border: 'rgba(139, 92, 246, 0.3)' },
                        { bg: 'rgba(236, 72, 153, 0.15)', text: '#ec4899', border: 'rgba(236, 72, 153, 0.3)' },
                        { bg: 'rgba(14, 165, 233, 0.15)', text: '#0ea5e9', border: 'rgba(14, 165, 233, 0.3)' },
                        { bg: 'rgba(99, 102, 241, 0.15)', text: '#6366f1', border: 'rgba(99, 102, 241, 0.3)' },
                        { bg: 'rgba(20, 184, 166, 0.15)', text: '#14b8a6', border: 'rgba(20, 184, 166, 0.3)' }
                    ];
                    let hash = 0;
                    const str = String(id || '') + String(name || '');
                    for (let i = 0; i < str.length; i++) {
                        hash = (hash * 31 + str.charCodeAt(i)) & 0xffffffff;
                    }
                    const c = palette[Math.abs(hash) % palette.length];
                    return `background:${c.bg};color:${c.text};border:1px solid ${c.border};`;
                },

                highlightMatch(text, query) {
                    if (!text) return '';
                    const safeText = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    if (!query || !query.trim()) return safeText;
                    const q = query.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp(`(${q})`, 'gi');
                    return safeText.replace(regex, '<span style="color:#0055D4;font-weight:800;text-decoration:underline;">$1</span>');
                }
            };
        }

        function initQuill(el, initialValue, onUpdate, placeholder = 'Start typing...') {
            if (!el) return;
            const quill = new Quill(el, {
                theme: 'snow',
                placeholder: placeholder,
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
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
            });

            if (initialValue) {
                quill.clipboard.dangerouslyPasteHTML(initialValue);
            }

            quill.on('text-change', () => {
                const html = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
                onUpdate(html);
            });

            return quill;
        }
    </script>
</x-layout>
