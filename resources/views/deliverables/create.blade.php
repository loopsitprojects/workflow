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
            overflow: hidden;
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
            overflow: hidden;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .deliverable-item-card:hover {
            border-color: rgba(0,85,212,0.4);
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
        .btn-cancel {
            background: transparent;
            color: var(--color-text-secondary);
            border: 1.5px solid var(--color-border-primary);
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
        button.ql-active .ql-stroke { stroke: #0055D4 !important; }
        button.ql-active .ql-fill { fill: #0055D4 !important; }
        .ql-picker-label.ql-active { color: #0055D4 !important; }
        .ql-picker-label.ql-active .ql-stroke { stroke: #0055D4 !important; }
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

        <form action="{{ route('deliverables.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="status" value="To Do">
            <input type="hidden" name="task_type" value="Deliverable">
            <input type="hidden" name="progress_percent" value="{{ $progressPercent ?? 0 }}">
            <input type="hidden" name="approver_id" value="">
            <input type="hidden" name="approval_stage" value="Writer">

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
                                    <select name="writer_id" class="styled-input" :disabled="mode !== 'single'" :required="mode === 'single'">
                                        <option value="">Select Assignee / Writer...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ (old('writer_id', $parentTask->writer_id ?? '') == $user->id) ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary-blue">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Deliverable
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
                                                <select :name="`subtasks[${index}][writer_id]`" x-model="subtask.writer_id" class="styled-input" :disabled="mode !== 'batch'" :required="mode === 'batch'">
                                                    <option value="">Select Assignee / Writer...</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
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
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary-blue">
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
        function deliverableForm() {
            return {
                mode: '{{ isset($parentId) ? 'batch' : 'single' }}',
                workflowType: '{{ $workflowType ?? 'retainer' }}',
                retainerTypes: @json($subtaskTypes->where('workflow_type', 'retainer')->pluck('name')->values()),
                campaignTypes: @json($subtaskTypes->where('workflow_type', 'campaign')->pluck('name')->values()),
                singlePostType: '',
                subtasks: [],
                subtaskCounter: 0,
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
                        writer_id: '{{ auth()->user()->role === 'Writer' ? auth()->id() : ($users->first()->id ?? '') }}',
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
