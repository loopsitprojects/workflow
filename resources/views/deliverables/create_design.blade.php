<x-layout title="New Fast Track Deliverable">
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
        .flow-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: rgba(99,102,241,0.1);
            border: 1px solid rgba(99,102,241,0.25);
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6366f1;
        }
        .direct-flow-steps {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--color-text-secondary);
            background: var(--color-bg-secondary);
            padding: 4px 10px;
            border-radius: 6px;
            border: 1px solid var(--color-border-primary);
        }
        .step-badge.active {
            color: #6366f1;
            background: rgba(99,102,241,0.08);
            border-color: rgba(99,102,241,0.25);
        }
        .step-arrow {
            color: var(--color-text-secondary);
            font-size: 11px;
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
            background: #6366f1;
            color: #ffffff;
            box-shadow: 0 2px 10px rgba(99,102,241,0.35);
        }

        /* Distinct Separated Form Cards */
        .section-card {
            width: 100%;
            background: var(--color-bg-primary);
            border: 1.5px solid var(--color-border-primary);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 22px;
            transition: border-color 0.2s;
        }
        .section-card.batch-meta-card {
            border-color: rgba(99, 102, 241, 0.25);
        }
        .section-card.deliverables-card {
            border-color: rgba(99, 102, 241, 0.35);
        }
        .section-header-bar {
            padding: 14px 22px;
            background: var(--color-bg-secondary);
            border-bottom: 1px solid var(--color-border-primary);
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
            background: rgba(99,102,241,0.12);
            color: #818cf8;
            border: 1px solid rgba(99,102,241,0.25);
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
        .field-label.purple {
            color: #818cf8;
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
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
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
            min-height: 75px;
            transition: border-color 0.15s, box-shadow 0.15s;
            box-sizing: border-box;
            line-height: 1.5;
        }
        .styled-textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
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
            overflow: hidden;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .deliverable-item-card:hover {
            border-color: rgba(99,102,241,0.4);
        }
        .deliverable-item-header {
            padding: 10px 16px;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--color-border-primary);
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
            color: #818cf8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .deliverable-item-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #6366f1;
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
            border: 1.5px dashed rgba(99,102,241,0.4);
            border-radius: 10px;
            color: #818cf8;
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
            border-color: #6366f1;
            background: rgba(99,102,241,0.06);
            color: #6366f1;
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
        .btn-primary-purple {
            background: #6366f1;
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .btn-primary-purple:hover {
            background: #4f46e5;
            transform: translateY(-1px);
        }
        .btn-cancel {
            background: transparent;
            color: var(--color-text-secondary);
            border: 1.5px solid var(--color-border-primary);
        }
        .btn-cancel:hover {
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
        button.ql-active .ql-stroke { stroke: #6366f1 !important; }
        button.ql-active .ql-fill { fill: #6366f1 !important; }
        .ql-picker-label.ql-active { color: #6366f1 !important; }
        .ql-picker-label.ql-active .ql-stroke { stroke: #6366f1 !important; }
    </style>

    <div class="create-page-wrapper" x-data="designBatchForm()">
        {{-- Breadcrumbs --}}
        <nav class="create-breadcrumb">
            @if($project && $project->brand)
                <a href="{{ route('brands.show', $project->brand->slug) }}" class="create-bc-link">{{ $project->brand->name }}</a>
                <span class="create-bc-sep">/</span>
            @endif
            @if($project)
                <a href="{{ route('projects.show', $project->id) }}" class="create-bc-link">{{ $project->name }}</a>
                <span class="create-bc-sep">/</span>
            @endif
            <span class="create-bc-current">{{ isset($parentId) ? 'Add Deliverables to Batch' : 'New Fast Track Deliverable' }}</span>
        </nav>

        {{-- Top Control Header --}}
        <div class="top-ctrl-bar">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div class="flow-pill">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Fast Track Flow
                </div>
                <div class="direct-flow-steps">
                    <div class="step-badge active">1. Designer</div>
                    <span class="step-arrow">→</span>
                    <div class="step-badge">2. Manager Review</div>
                    <span class="step-arrow">→</span>
                    <div class="step-badge">3. Closed</div>
                </div>
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

        <form action="{{ route('deliverables.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="flow_type" value="direct_design">
            <input type="hidden" name="status" value="To Do">
            <input type="hidden" name="task_type" value="Deliverable">
            <input type="hidden" name="progress_percent" value="20">
            <input type="hidden" name="approval_stage" value="Designer">

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
                    <select name="project_id" class="styled-input" required>
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
                                <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Deliverable Details
                            </h3>
                            <p class="section-subtitle">Create a single direct design deliverable</p>
                        </div>
                        <span class="section-badge">Single Deliverable</span>
                    </div>

                    <div class="form-section-body">
                        {{-- Deliverable Title --}}
                        <div class="form-field-group">
                            <label class="field-label purple">Deliverable Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" :disabled="mode !== 'single'" :required="mode === 'single'" placeholder="e.g. Social Campaign Promo Banner / Product Launch Graphic..." class="massive-input"
                                value="{{ old('title', $parentTask->title ?? '') }}">
                            @error('title') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:5px;">{{ $message }}</p> @enderror
                        </div>

                        {{-- Details Grid --}}
                        <div class="form-grid-3 form-field-group">
                            <div>
                                <label class="field-label purple">Assigned Designer <span style="color:#ef4444;">*</span></label>
                                <select name="designer_id" class="styled-input" :disabled="mode !== 'single'" :required="mode === 'single'">
                                    <option value="">Select Designer...</option>
                                    @foreach($designers as $d)
                                        <option value="{{ $d->id }}" {{ (old('designer_id', $parentTask->designer_id ?? '') == $d->id) ? 'selected' : '' }}>
                                            {{ $d->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="field-label">Designer Deadline</label>
                                <input type="date" name="designer_deadline" class="styled-input" :disabled="mode !== 'single'"
                                    value="{{ old('designer_deadline', isset($parentTask->designer_deadline) ? \Carbon\Carbon::parse($parentTask->designer_deadline)->format('Y-m-d') : '') }}">
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

                        {{-- Format --}}
                        <div class="form-field-group">
                            <label class="field-label">Deliverable Type <span style="color:#ef4444;">*</span></label>
                            <select name="post_type" class="styled-input" :disabled="mode !== 'single'" :required="mode === 'single'">
                                <option value="">Select Type...</option>
                                <option value="Static Post">Static Post</option>
                                <option value="Carousel">Carousel</option>
                                <option value="Story">Story</option>
                                <option value="Reel / Video">Reel / Video</option>
                                <option value="Poster / Banner">Poster / Banner</option>
                                <option value="Thumbnail">Thumbnail</option>
                                <option value="Print">Print</option>
                                <option value="Graphic">Graphic</option>
                            </select>
                        </div>

                        {{-- Creative Brief --}}
                        <div class="form-field-group">
                            <label class="field-label">Creative Brief & Design Instructions</label>
                            <div x-data="{ localConcept: {{ json_encode(old('concept', $parentTask->concept ?? '')) }} }"
                                 x-init="initQuill($refs.singleConceptEditor, localConcept, (val) => { localConcept = val; }, 'Detail the visual concept, color palette, dimensions, or specific design guidelines...')">
                                <div x-ref="singleConceptEditor"></div>
                                <input type="hidden" name="concept" :value="localConcept" :disabled="mode !== 'single'">
                            </div>
                        </div>

                        {{-- References --}}
                        <div class="form-grid-2 form-field-group">
                            <div>
                                <label class="field-label">Reference URLs (Figma, Drive...)</label>
                                <input type="text" name="reference" class="styled-input" :disabled="mode !== 'single'" placeholder="https://..." value="{{ old('reference', $parentTask->reference ?? '') }}">
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
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary-purple">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Fast Track Deliverable
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
                                <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Batch Details
                            </h3>
                            <p class="section-subtitle">Define the batch campaign title, default due date, and priority</p>
                        </div>
                        <span class="section-badge">Batch Settings</span>
                    </div>

                    <div class="form-section-body">
                        {{-- Batch Name --}}
                        <div class="form-field-group">
                            <label class="field-label purple">Batch Name / Campaign Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" :disabled="mode !== 'batch'" :required="mode === 'batch'" placeholder="e.g. Q4 Festive Campaign Assets / Social Launch Package..." class="massive-input"
                                value="{{ old('title', $parentTask->title ?? '') }}" {{ isset($parentId) ? 'readonly' : '' }}>
                            @error('title') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:5px;">{{ $message }}</p> @enderror
                        </div>

                        {{-- Batch Date & Priority (Clean 2-Column Row) --}}
                        <div class="form-grid-2 form-field-group">
                            <div>
                                <label class="field-label">Batch Due Date</label>
                                <input type="date" name="designer_deadline" class="styled-input" :disabled="mode !== 'batch'"
                                    value="{{ old('designer_deadline', isset($parentTask->designer_deadline) ? \Carbon\Carbon::parse($parentTask->designer_deadline)->format('Y-m-d') : '') }}">
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
                                <svg width="16" height="16" fill="none" stroke="#6366f1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Deliverables in this Batch
                            </h3>
                            <p class="section-subtitle">Add each deliverable item. Each has its own title, format, designer, deadline, and specs.</p>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="section-badge" x-text="subtasks.length + ' Deliverable(s)'"></span>
                            <button type="button" @click="addSubtask()" class="btn btn-primary-purple" style="padding:5px 12px;font-size:11px;">
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
                                                <label class="field-label purple">Deliverable Name / Dimensions <span style="color:#ef4444;">*</span></label>
                                                <input type="text" :name="`subtasks[${index}][title]`" x-model="subtask.title" placeholder="e.g. 1080x1080 Feed Post / Story Ad / YouTube Banner" class="styled-input" :disabled="mode !== 'batch'" :required="mode === 'batch'">
                                            </div>
                                            <div>
                                                <label class="field-label">Deliverable Type <span style="color:#ef4444;">*</span></label>
                                                <select :name="`subtasks[${index}][post_type]`" x-model="subtask.post_type" class="styled-input" :disabled="mode !== 'batch'" :required="mode === 'batch'">
                                                    <option value="">Select Type...</option>
                                                    <option value="Static Post">Static Post</option>
                                                    <option value="Carousel">Carousel</option>
                                                    <option value="Story">Story</option>
                                                    <option value="Reel / Video">Reel / Video</option>
                                                    <option value="Poster / Banner">Poster / Banner</option>
                                                    <option value="Thumbnail">Thumbnail</option>
                                                    <option value="Print">Print</option>
                                                    <option value="Graphic">Graphic</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Row 2: Assigned Designer & Custom Due Date --}}
                                        <div class="form-grid-2" style="margin-bottom:12px;">
                                            <div>
                                                <label class="field-label purple">Assigned Designer <span style="color:#ef4444;">*</span></label>
                                                <select :name="`subtasks[${index}][designer_id]`" x-model="subtask.designer_id" class="styled-input" :disabled="mode !== 'batch'" :required="mode === 'batch'">
                                                    <option value="">Select Designer...</option>
                                                    @foreach($designers as $d)
                                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="field-label">Due Date <span style="font-size:10px;text-transform:none;opacity:0.6;font-weight:500;">(Defaults to batch date if blank)</span></label>
                                                <input type="date" :name="`subtasks[${index}][designer_deadline]`" x-model="subtask.designer_deadline" class="styled-input" :disabled="mode !== 'batch'">
                                            </div>
                                        </div>

                                        {{-- Row 3: Creative Brief & Specific Notes --}}
                                        <div class="form-field-group">
                                            <label class="field-label">Creative Brief & Specific Notes</label>
                                            <div x-init="initQuill($refs.subtaskEditor, subtask.concept, (val) => { subtask.concept = val; }, 'Visual concept, color palette, dimensions, or specific design instructions for this deliverable...')">
                                                <div x-ref="subtaskEditor"></div>
                                                <input type="hidden" :name="`subtasks[${index}][concept]`" :value="subtask.concept" :disabled="mode !== 'batch'">
                                            </div>
                                        </div>

                                        {{-- Row 4: References --}}
                                        <div class="form-grid-2">
                                            <div>
                                                <label class="field-label">Reference URL (Figma, Drive...)</label>
                                                <input type="text" :name="`subtasks[${index}][reference]`" x-model="subtask.reference" placeholder="https://..." class="styled-input" :disabled="mode !== 'batch'">
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
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary-purple">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="`Create Batch (${subtasks.length} Deliverable${subtasks.length === 1 ? '' : 's'})`"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function designBatchForm() {
            return {
                mode: '{{ isset($parentId) ? 'batch' : 'single' }}',
                subtasks: [],
                subtaskCounter: 0,
                init() {
                    this.addSubtask();
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
                        designer_deadline: '',
                        designer_id: '{{ $designers->first()->id ?? '' }}',
                        concept: '',
                        reference: ''
                    });
                },
                removeSubtask(index) {
                    if (this.subtasks.length > 1) {
                        this.subtasks.splice(index, 1);
                    }
                }
            }
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
                    ]
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
