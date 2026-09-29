<x-layout title="New Fast Track Deliverable">
    <style>
        .create-page-wrapper{max-width:760px;margin:24px auto 48px;padding:0 16px;box-sizing:border-box;}
        .create-breadcrumb{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:12px;flex-wrap:wrap;}
        .create-bc-link{text-decoration:none;color:inherit;transition:color 0.15s;}
        .create-bc-link:hover{color:var(--color-text-primary);}
        .create-bc-sep{opacity:0.35;}
        .create-bc-current{color:var(--color-text-primary);}
        .form-container{width:100%;margin:0 auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.05);font-family:'Inter',sans-serif;}
        .form-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);position:relative;}
        .form-close-btn{position:absolute;top:16px;right:20px;width:30px;height:30px;border-radius:8px;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);display:flex;align-items:center;justify-content:center;color:var(--color-text-secondary);text-decoration:none;transition:all 0.15s;}
        .form-close-btn:hover{color:var(--color-text-primary);background:var(--color-border-primary);transform:scale(1.05);}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
        .grid-cell{padding:18px 24px;border-bottom:1px solid var(--color-border-primary);}
        .grid-cell.br{border-right:none;}
        @media(min-width:768px){.grid-cell.br{border-right:1px solid var(--color-border-primary);}}
        .field-label{display:block;font-size:11px;font-weight:700;color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;}
        .field-label.purple{color:#6366f1;}
        .massive-input{width:100%;background:transparent;border:none;outline:none;font-size:22px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
        .massive-input::placeholder{opacity:0.3;color:var(--color-text-primary);}
        .styled-input-wrapper{position:relative;display:flex;align-items:center;}
        .styled-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:9px;padding:10px 14px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s, box-shadow 0.15s;-webkit-appearance:none;appearance:none;box-sizing:border-box;}
        .styled-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,0.15);}
        select.styled-input{cursor:pointer;}
        .styled-textarea{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:9px;padding:12px 14px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;resize:vertical;min-height:90px;transition:border-color 0.15s, box-shadow 0.15s;box-sizing:border-box;line-height:1.6;}
        .styled-textarea:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,0.15);}
        
        /* Direct Flow Badge */
        .flow-pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.25);border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#6366f1;margin-bottom:12px;}
        
        /* Subtask Cards */
        .subtask-block{background:var(--color-bg-primary);border-radius:12px;border:1.5px solid var(--color-border-primary);overflow:hidden;margin-bottom:16px;box-shadow:0 2px 10px rgba(0,0,0,0.02);transition:border-color 0.2s;}
        .subtask-block:hover{border-color:rgba(99,102,241,0.4);}
        .subtask-header{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;background:var(--color-bg-secondary);border-bottom:1px solid var(--color-border-primary);}
        .subtask-tag{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#6366f1;}
        .subtask-tag-dot{width:7px;height:7px;border-radius:50%;background:#6366f1;}
        .subtask-body{display:grid;grid-template-columns:1fr;gap:0;}
        @media(min-width:768px){.subtask-body{grid-template-columns:1fr 1fr;}}
        .subtask-cell{padding:14px 18px;border-bottom:1px solid var(--color-border-primary);}
        .subtask-cell.br{border-right:none;}
        @media(min-width:768px){.subtask-cell.br{border-right:1px solid var(--color-border-primary);}}
        .subtask-cell.full{grid-column:1/-1;}
        .remove-btn{background:none;border:none;color:var(--color-text-secondary);cursor:pointer;padding:6px;border-radius:6px;display:inline-flex;align-items:center;transition:all 0.15s;}
        .remove-btn:hover{color:#ef4444;background:rgba(239,68,68,0.1);}
        
        /* Add button */
        .add-btn{width:100%;padding:14px 20px;background:transparent;border:1.5px dashed rgba(99,102,241,0.4);border-radius:10px;color:#6366f1;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;transition:all 0.2s;}
        .add-btn:hover{border-color:#6366f1;background:rgba(99,102,241,0.06);transform:translateY(-1px);}
        
        /* Footer */
        .form-footer{background:var(--color-bg-secondary);padding:18px 24px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--color-border-primary);}
        .btn{padding:9px 20px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.15s;}
        .btn-primary-purple{background:#6366f1;color:#fff;border:none;box-shadow:0 4px 14px rgba(99,102,241,0.3);}
        .btn-primary-purple:hover{background:#4f46e5;transform:translateY(-1px);}
        .btn-cancel{background:transparent;color:var(--color-text-secondary);border:1.5px solid var(--color-border-primary);}
        .btn-cancel:hover{color:var(--color-text-primary);background:var(--color-bg-secondary);}

        /* Step Indicators */
        .direct-flow-steps{display:flex;align-items:center;gap:8px;margin-bottom:16px;}
        .step-badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:var(--color-text-secondary);background:var(--color-bg-secondary);padding:4px 10px;border-radius:6px;border:1px solid var(--color-border-primary);}
        .step-badge.active{color:#6366f1;background:rgba(99,102,241,0.08);border-color:rgba(99,102,241,0.25);}
        .step-arrow{color:var(--color-text-secondary);font-size:11px;}
    </style>

    <div class="create-page-wrapper">
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
            <span class="create-bc-current">{{ isset($parentId) ? 'Add Subtasks to Fast Track Deliverable' : 'New Fast Track Deliverable' }}</span>
        </nav>

        <div class="form-container" x-data="designBatchForm()">
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
                    <div style="margin:20px 24px;padding:14px;background:#fff1f2;border:1px solid #fecaca;border-radius:8px;color:#be123c;font-size:13px;font-weight:600;">
                        <ul style="margin:0;padding-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Header Section --}}
                <div class="form-section">
                    @if($selectedProjectId)
                        <a href="{{ route('projects.show', $selectedProjectId) }}" class="form-close-btn" style="z-index: 10;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif

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

                    @if($selectedProjectId)
                        <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
                    @else
                        <div style="margin-bottom: 16px;">
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

                    {{-- Title --}}
                    <label class="field-label purple">{{ isset($parentId) ? 'Adding Subtasks to' : 'Fast Track Deliverable Title' }}</label>
                    <input type="text" name="title" id="deliverable_title_input" required placeholder="e.g. Social Campaign Creatives / Banner Set..." class="massive-input"
                        value="{{ old('title', $parentTask->title ?? '') }}" {{ isset($parentId) ? 'readonly' : '' }}>
                    @error('title') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;">{{ $message }}</p> @enderror
                </div>

                {{-- Designer & Deadline Grid --}}
                <div class="form-grid">
                    <div class="grid-cell br">
                        <label class="field-label purple">Assigned Designer <span style="color:#ef4444;">*</span></label>
                        <select name="designer_id" class="styled-input" required>
                            <option value="">Select Designer...</option>
                            @foreach($designers as $d)
                                <option value="{{ $d->id }}" {{ (old('designer_id', $parentTask->designer_id ?? '') == $d->id) ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('designer_id') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid-cell">
                        <label class="field-label">Designer Deadline</label>
                        <input type="datetime-local" name="designer_deadline" class="styled-input"
                            value="{{ old('designer_deadline', isset($parentTask->designer_deadline) ? \Carbon\Carbon::parse($parentTask->designer_deadline)->format('Y-m-d\TH:i') : '') }}">
                        @error('designer_deadline') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Creative Brief & Instructions --}}
                <div class="form-section">
                    <label class="field-label">Creative Brief & Design Instructions</label>
                    <textarea name="concept" class="styled-textarea" placeholder="Detail the visual concept, color palette, dimensions, or specific design guidelines...">{{ old('concept', $parentTask->concept ?? '') }}</textarea>
                </div>

                {{-- References (Files & URLs) --}}
                <div class="form-grid">
                    <div class="grid-cell br">
                        <label class="field-label">Reference URLs (Figma, Pinterest, Drive...)</label>
                        <input type="text" name="reference" class="styled-input" placeholder="https://..." value="{{ old('reference', $parentTask->reference ?? '') }}">
                    </div>
                    <div class="grid-cell">
                        <label class="field-label">Attach Reference Files</label>
                        <input type="file" name="reference_file" class="styled-input" style="padding:7px 10px;">
                    </div>
                </div>

                {{-- Subtasks / Batch Items Repeater --}}
                <div class="form-section">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                        <div>
                            <span class="field-label purple" style="margin:0;">Batch Subtasks</span>
                            <p style="font-size:11px;color:var(--color-text-secondary);margin:2px 0 0;">Add individual deliverables to this batch (e.g. 1080x1080 Post, Story, Banner).</p>
                        </div>
                    </div>

                    <div id="subtasks-container">
                        <template x-for="(subtask, index) in subtasks" :key="subtask.id">
                            <div class="subtask-block">
                                <div class="subtask-header">
                                    <span class="subtask-tag">
                                        <span class="subtask-tag-dot"></span>
                                        Subtask <span x-text="index + 1"></span>
                                    </span>
                                    <button type="button" @click="removeSubtask(index)" class="remove-btn" title="Remove subtask">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="subtask-body">
                                    <div class="subtask-cell br">
                                        <label class="field-label">Deliverable Title / Dimension</label>
                                        <input type="text" :name="`subtasks[${index}][title]`" x-model="subtask.title" placeholder="e.g. Square Post (1080x1080)" class="styled-input">
                                    </div>
                                    <div class="subtask-cell">
                                        <label class="field-label">Format / Type</label>
                                        <select :name="`subtasks[${index}][post_type]`" x-model="subtask.post_type" class="styled-input">
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
                                    <div class="subtask-cell full">
                                        <label class="field-label">Specific Notes / Specs</label>
                                        <textarea :name="`subtasks[${index}][concept]`" x-model="subtask.concept" placeholder="Specific text or instructions for this variation..." class="styled-textarea" style="min-height:65px;"></textarea>
                                    </div>
                                    <div class="subtask-cell br">
                                        <label class="field-label">Reference Link</label>
                                        <input type="text" :name="`subtasks[${index}][reference]`" x-model="subtask.reference" placeholder="https://..." class="styled-input">
                                    </div>
                                    <div class="subtask-cell">
                                        <label class="field-label">Reference File</label>
                                        <input type="file" :name="`subtasks[${index}][reference_file]`" class="styled-input" style="padding:7px 10px;">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addSubtask()" class="add-btn">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Subtask Deliverable
                    </button>
                </div>

                {{-- Footer --}}
                <div class="form-footer">
                    <a href="{{ $selectedProjectId ? route('projects.show', $selectedProjectId) : url()->previous() }}" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary-purple">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Fast Track Deliverable
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function designBatchForm() {
            return {
                subtasks: [],
                subtaskCounter: 0,
                addSubtask() {
                    this.subtaskCounter++;
                    this.subtasks.push({
                        id: Date.now() + Math.random(),
                        title: '',
                        post_type: 'Static Post',
                        concept: '',
                        reference: ''
                    });
                },
                removeSubtask(index) {
                    this.subtasks.splice(index, 1);
                }
            }
        }
    </script>
</x-layout>
