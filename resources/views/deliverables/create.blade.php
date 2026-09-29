<x-layout title="New Deliverable">
    <style>
        .create-page-wrapper{max-width:640px;margin:24px auto 48px;padding:0 12px;box-sizing:border-box;}
        .create-breadcrumb{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:12px;flex-wrap:wrap;}
        .create-bc-link{text-decoration:none;color:inherit;transition:color 0.15s;}
        .create-bc-link:hover{color:var(--color-text-primary);}
        .create-bc-sep{opacity:0.35;}
        .create-bc-current{color:var(--color-text-primary);}
        .form-container{width:100%;margin:0 auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;}
        .form-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);position:relative;}
        .form-close-btn{position:absolute;top:16px;right:20px;width:30px;height:30px;border-radius:8px;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);display:flex;align-items:center;justify-content:center;color:var(--color-text-secondary);text-decoration:none;transition:all 0.15s;}
        .form-close-btn:hover{color:var(--color-text-primary);background:var(--color-border-primary);transform:scale(1.05);}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        .grid-cell{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);}
        .grid-cell.br{border-right:none;}
        @media(min-width:768px){.grid-cell.br{border-right:1px solid var(--color-border-primary);}}
        .field-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:7px;}
        .field-label.blue{color:#3b82f6;}
        .massive-input{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
        .massive-input::placeholder{opacity:0.25;color:var(--color-text-primary);}
        .styled-input-wrapper{position:relative;display:flex;align-items:center;}
        .styled-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s;-webkit-appearance:none;appearance:none;box-sizing:border-box;}
        .styled-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
        select.styled-input{cursor:pointer;}
        .styled-textarea{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;resize:vertical;min-height:80px;transition:border-color 0.15s;box-sizing:border-box;line-height:1.6;}
        .styled-textarea:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
        /* Subtask Card */
        .subtask-block{background:transparent;border-radius:10px;border:1.5px solid var(--color-border-primary);overflow:hidden;margin-bottom:12px;transition:border-color 0.2s;}
        .subtask-block:hover{border-color:#0055D440;}
        .subtask-header{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:var(--color-bg-secondary);border-bottom:1px solid var(--color-border-primary);}
        .subtask-tag{display:inline-flex;align-items:center;gap:8px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;color:#0055D4;}
        .subtask-tag-dot{width:6px;height:6px;border-radius:50%;background:#0055D4;}
        .subtask-body{display:grid;grid-template-columns:1fr;}
        @media(min-width:768px){.subtask-body{grid-template-columns:1fr 1fr;}}
        .subtask-cell{padding:14px 16px;border-bottom:1px solid var(--color-border-primary);}
        .subtask-cell.br{border-right:none;}
        @media(min-width:768px){.subtask-cell.br{border-right:1px solid var(--color-border-primary);}}
        .subtask-cell.full{grid-column:1/-1;}
        .subtask-cell:last-child,.subtask-cell:nth-last-child(2):not(.full){border-bottom:none;}
        .remove-btn{background:none;border:none;color:#cbd5e1;cursor:pointer;padding:4px;border-radius:6px;display:inline-flex;align-items:center;transition:all 0.2s;}
        .remove-btn:hover{color:#ef4444;background:#fef2f2;}
        /* Add button */
        .add-btn{width:100%;padding:14px 20px;background:transparent;border:1.5px dashed var(--color-border-primary);border-radius:8px;color:var(--color-text-secondary);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.12em;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;transition:all 0.2s;}
        .add-btn:hover{border-color:#0055D4;color:#0055D4;background:rgba(0,85,212,0.04);}
        /* Footer */
        .form-footer{background:var(--color-bg-secondary);padding:14px 24px;display:flex;justify-content:space-between;align-items:center;border-top:1px solid var(--color-border-primary);}
        .btn{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all 0.12s;}
        .btn-primary{background:#0055D4;color:#fff;border:none;box-shadow:0 3px 10px rgba(0,85,212,0.25);font-weight:700;padding:8px 22px;}
        .btn-primary:hover{background:#0044aa;}
        .btn-cancel{background:transparent;color:var(--color-text-secondary);border:1.5px solid var(--color-border-primary);}
        .btn-cancel:hover{color:var(--color-text-primary);background:var(--color-bg-secondary);}
        /* Focus Modal */
        .focus-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(12px);z-index:9999;display:none;align-items:center;justify-content:center;padding:40px;animation:fadeIn 0.3s ease-out;}
        .focus-modal-content{background:var(--color-bg-primary);width:100%;max-width:680px;border-radius:14px;border:1px solid var(--color-border-primary);box-shadow:0 40px 100px rgba(0,0,0,0.3);padding:24px;display:flex;flex-direction:column;gap:16px;animation:scaleUp 0.3s cubic-bezier(0.34,1.56,0.64,1);}
        .focus-modal-header{display:flex;justify-content:space-between;align-items:center;}
        .focus-modal-textarea{width:100%;height:400px;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:16px;font-size:14px;line-height:1.6;color:var(--color-text-primary);outline:none;resize:none;font-weight:500;}
        .focus-modal-textarea:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
        .expand-trigger{padding:4px;color:#3b82f6;cursor:pointer;opacity:0.6;transition:opacity 0.2s;display:inline-flex;align-items:center;}
        .expand-trigger:hover{opacity:1;}
        /* Reference Toggle */
        .ref-toggle-container{display:flex;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);border-radius:8px;padding:3px;width:fit-content;margin-bottom:10px;}
        .ref-toggle-btn{border:none;background:transparent;padding:5px 12px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-secondary);border-radius:6px;cursor:pointer;transition:all 0.15s;}
        .ref-toggle-btn.active{background:var(--color-bg-primary);color:#0055D4;box-shadow:0 2px 6px rgba(0,0,0,0.06);}
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        @keyframes scaleUp{from{transform:scale(0.95);opacity:0}to{transform:scale(1);opacity:1}}
    </style>

    {{-- Pass PHP data to JS safely via data attributes --}}
    <div id="app-data" data-users="{{ json_encode(($allUsers ?? $users)->map(fn($u) => ['id' => $u->id, 'name' => $u->name])) }}"
        data-is-admin="{{ (auth()->check() && strtolower(auth()->user()->role ?? '') === 'admin') ? 'true' : 'false' }}"
        style="display:none;"></div>

    <div class="create-page-wrapper">
        {{-- Breadcrumb Navigation --}}
        <nav id="create-breadcrumb" class="create-breadcrumb">
            <a href="{{ route('brands.index') }}" class="create-bc-link">Brands</a>
            @if(isset($project) && $project && $project->brand)
                <span class="create-bc-sep">/</span>
                <a href="{{ route('brands.show', $project->brand) }}" class="create-bc-link">{{ $project->brand->name }}</a>
            @endif
            @if(isset($project) && $project)
                <span class="create-bc-sep">/</span>
                <a href="{{ route('projects.show', $project) }}" class="create-bc-link">{{ $project->name }}</a>
            @endif
            @if(isset($parentTask) && $parentTask)
                <span class="create-bc-sep">/</span>
                <a href="{{ route('deliverables.show', $parentTask) }}" class="create-bc-link">{{ $parentTask->title }}</a>
            @endif
            <span class="create-bc-sep">/</span>
            <span class="create-bc-current">{{ isset($parentId) ? 'Add Subtasks' : 'New Deliverable' }}</span>
        </nav>

        <div class="form-container">
            <form action="{{ route('deliverables.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div
                    style="margin: 20px 24px; padding: 14px; background: #fff1f2; border: 1px solid #fecaca; border-radius: 8px; color: #be123c; font-size: 13px; font-weight: 600;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(isset($parentId))
                <input type="hidden" name="parent_deliverable_id" value="{{ $parentId }}">
            @endif

            @if($selectedProjectId)
                <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
            @else
                <div class="form-section">
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

            @php
                $isCampaignOrPitch = in_array($workflowType ?? '', ['campaign', 'pitch']);
            @endphp

            @if($selectedProjectId)
                <a href="{{ route('projects.show', $selectedProjectId) }}" class="form-close-btn" style="z-index: 10;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
            @endif

            {{-- ── Top Orchestration Fields (Title, Writer, Date, Priority) — Hidden for Campaign & Pitch ── --}}
            <div id="top-orchestration-container" style="{{ $isCampaignOrPitch ? 'display:none;' : '' }}">
                {{-- ── Title ── --}}
                <div class="form-section">
                    <label
                        class="field-label blue">{{ isset($parentId) ? 'Adding Subtasks to' : 'Deliverable Title' }}</label>
                    <input type="text" name="title" id="deliverable_title_input" {{ $isCampaignOrPitch ? '' : 'required' }} placeholder="Name this deliverable..." class="massive-input"
                        value="{{ old('title', $parentTask->title ?? '') }}" {{ isset($parentId) ? 'readonly' : '' }}>
                    @error('title') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:8px;">{{ $message }}</p> @enderror
                </div>

                {{-- ── Global Orchestration (Writer, Date, Priority) ── --}}
                <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="grid-cell br">
                        <label class="field-label">Assigned Writer</label>
                        @if(auth()->user()->role === 'Writer')
                            <input type="hidden" name="writer_id" value="{{ auth()->id() }}">
                            <div class="styled-input" style="cursor:default;opacity:0.75;">{{ auth()->user()->name }}</div>
                        @else
                            <div class="styled-input-wrapper">
                                <select name="writer_id" id="deliverable_writer_select" class="styled-input" {{ $isCampaignOrPitch ? '' : 'required' }}>
                                    <option value="">Select Writer...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (old('writer_id', $parentTask->writer_id ?? '') == $user->id) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('writer_id') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;">{{ $message }}</p> @enderror
                        @endif
                    </div>

                    <div class="grid-cell br">
                        <label class="field-label">Project Due Date</label>
                        <div class="styled-input-wrapper">
                            <input type="date" name="deadline" id="deadline" class="styled-input" min="{{ date('Y-m-d') }}"
                                value="{{ old('deadline', $parentTask->deadline ?? '') }}" {{ $isCampaignOrPitch ? '' : 'required' }}>
                        </div>
                        @error('deadline') <p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid-cell br">
                        <label class="field-label">Priority</label>
                        <div class="styled-input-wrapper">
                            <select name="priority" id="deliverable_priority_select" class="styled-input">
                                <option value="High Priority" {{ old('priority', $parentTask->priority ?? '') == 'High Priority' ? 'selected' : '' }}>High Priority</option>
                                <option value="Medium" {{ old('priority', $parentTask->priority ?? 'Medium') == 'Medium' ? 'selected' : '' }}>Medium</option>
                                <option value="Low Priority" {{ old('priority', $parentTask->priority ?? '') == 'Low Priority' ? 'selected' : '' }}>Low Priority</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Hidden Defaults ── --}}
            <input type="hidden" name="status" value="To Do">
            <input type="hidden" name="progress_percent" value="{{ $progressPercent ?? 0 }}">
            <input type="hidden" name="task_type" value="Deliverable">

            {{-- ── Hidden Defaults ── --}}
            <input type="hidden" name="approver_id" value="">
            <input type="hidden" name="approval_stage" value="Writer">

            {{-- ── Posts ── --}}
            <div class="form-section" style="border-bottom:none;">
                <label class="field-label" style="margin-bottom:16px;">Posts</label>

                <div id="subtasks-container"></div>

                <button type="button" class="add-btn" onclick="addSubtask()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Post
                </button>
            </div>

            {{-- ── Footer ── --}}
            <div class="form-footer">
                <a href="{{ url()->previous() }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" id="createDeliverableBtn" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>

    {{-- Full-page loading overlay --}}
    <div id="dlvLoadingOverlay" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);flex-direction:column;align-items:center;justify-content:center;gap:16px;">
        <div style="width:52px;height:52px;border-radius:50%;border:3px solid rgba(255,255,255,0.15);border-top-color:#fff;animation:dlvSpin 0.75s linear infinite;"></div>
        <span style="color:#fff;font-size:13px;font-weight:700;letter-spacing:0.04em;">Creating deliverable…</span>
    </div>
    <style>@keyframes dlvSpin{to{transform:rotate(360deg)}}</style>

    {{-- ── Focus Mode Modal ── --}}
    <div id="focus-modal" class="focus-modal-overlay">
        <div class="focus-modal-content">
            <div class="focus-modal-header">
                <div>
                    <span id="focus-modal-label" class="field-label blue" style="margin-bottom: 4px;">Focus View</span>
                    <h3 id="focus-modal-title" style="font-size: 16px; font-weight: 800; color: var(--color-text-primary); margin: 0;">Editing Content</h3>
                </div>
                <button type="button" onclick="closeFocusModal()" class="form-close-btn" style="position: static;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <textarea id="focus-modal-input" class="focus-modal-textarea" placeholder="Start typing..."></textarea>
            <div style="display: flex; justify-content: flex-end;">
                <button type="button" onclick="closeFocusModal()" class="btn btn-primary">Done & Close</button>
            </div>
        </div>
    </div>

    <script>
        // Focus Mode Logic
        let activeInputName = null;

        function openFocusModal(inputName, title) {
            activeInputName = inputName;
            const sourceEl = document.querySelector('[name="' + inputName + '"]');
            const modal = document.getElementById('focus-modal');
            const modalInput = document.getElementById('focus-modal-input');
            const modalTitle = document.getElementById('focus-modal-title');

            if (!sourceEl || !modal || !modalInput) return;

            modalTitle.textContent = title;
            modalInput.value = sourceEl.value;
            modal.style.display = 'flex';
            modalInput.focus();

            // Sync on every keystroke
            modalInput.oninput = function() {
                sourceEl.value = this.value;
            };
        }

        function closeFocusModal() {
            const modal = document.getElementById('focus-modal');
            if (modal) modal.style.display = 'none';
            activeInputName = null;
        }

        // Close on ESC
        window.onkeydown = function(e) {
            if (e.key === 'Escape') closeFocusModal();
        };

        const APP = document.getElementById('app-data');
        const USERS = JSON.parse(APP.dataset.users || '[]');
        const IS_ADMIN = APP.dataset.isAdmin === 'true';


        const PROJECT_WORKFLOWS = @json($projects->pluck('workflow_type', 'id'));
        const PROJECT_DETAILS = @json($projects->mapWithKeys(fn($p) => [$p->id => ['name' => $p->name, 'brand_name' => $p->brand->name ?? '', 'brand_slug' => $p->brand->slug ?? '']]));
        const RETAINER_TYPES = @json($subtaskTypes->where('workflow_type', 'retainer')->pluck('name'));
        const CAMPAIGN_TYPES = @json($subtaskTypes->where('workflow_type', 'campaign')->pluck('name'));

        let initialType = "{{ $workflowType ?? 'retainer' }}";
        let currentWorkflow = initialType;
        let SUBTASK_TYPES = (initialType === 'campaign' || initialType === 'pitch') ? CAMPAIGN_TYPES : RETAINER_TYPES;
        let subtaskIndex = 0;

        function isOutlinesType(type) {
            if (currentWorkflow === 'retainer') return true;
            if (!type) return false;
            var t = type.toString().trim().toLowerCase();
            return t === 'outlines' || t === 'outline';
        }

        function handlePostTypeChange(idx, val) {
            const outlinesSec = document.getElementById('subtask-outlines-' + idx);
            const briefSec = document.getElementById('subtask-brief-' + idx);
            if (!outlinesSec || !briefSec) return;

            const assigneeSelect = briefSec.querySelector('.subtask-assignee-select');

            if (!val && currentWorkflow !== 'retainer') {
                outlinesSec.style.display = 'none';
                briefSec.style.display = 'none';
                outlinesSec.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
                briefSec.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
                if (assigneeSelect) assigneeSelect.required = false;
                return;
            }

            const isOutlines = isOutlinesType(val);
            if (isOutlines) {
                outlinesSec.style.display = 'block';
                briefSec.style.display = 'none';
                outlinesSec.querySelectorAll('input, textarea, select').forEach(el => el.disabled = false);
                briefSec.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
                if (assigneeSelect) assigneeSelect.required = false;
            } else {
                outlinesSec.style.display = 'none';
                briefSec.style.display = 'block';
                outlinesSec.querySelectorAll('input, textarea, select').forEach(el => el.disabled = true);
                briefSec.querySelectorAll('input, textarea, select').forEach(el => el.disabled = false);
                if (assigneeSelect) assigneeSelect.required = true;
            }
        }

        function buildOpts(items, valKey, labelKey, selected) {
            return items.map(function (i) {
                var v = valKey ? i[valKey] : i;
                var l = labelKey ? i[labelKey] : i;
                return '<option value="' + v + '"' + (v === selected ? ' selected' : '') + '>' + l + '</option>';
            }).join('');
        }

        function addSubtask() {
            var idx = subtaskIndex++;
            var container = document.getElementById('subtasks-container');
            if (!container) return;
            var card = document.createElement('div');
            card.className = 'subtask-block';
            card.id = 'subtask-' + idx;

            var isCampaign = (currentWorkflow === 'campaign' || currentWorkflow === 'pitch');
            var defaultPostType = isCampaign ? '' : (RETAINER_TYPES[0] || '');
            var isOutlines = isOutlinesType(defaultPostType);
            var showOutlines = isCampaign ? (defaultPostType && isOutlines) : true;
            var showBrief = isCampaign ? (defaultPostType && !isOutlines) : false;

            card.innerHTML =
                '<div class="subtask-header">' +
                '<div class="subtask-tag"><span class="subtask-tag-dot"></span><span class="subtask-label">Post ' + (idx + 1) + '</span></div>' +
                '<button type="button" class="remove-btn" onclick="removeSubtask(' + idx + ')">' +
                '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
                '</div>' +
                '<div class="subtask-body" style="grid-template-columns:1fr;">' +
                '<div class="subtask-cell full">' +
                '<label class="field-label blue">Post Name</label>' +
                '<input type="text" name="subtasks[' + idx + '][title]" placeholder="e.g. Ramadan Sale — Carousel Post..." class="styled-input" required>' +
                '</div>' +
                '<div class="subtask-cell full">' +
                '<label class="field-label blue">Post Type</label>' +
                '<select name="subtasks[' + idx + '][post_type]" class="styled-input subtask-type-select" data-idx="' + idx + '" onchange="handlePostTypeChange(' + idx + ', this.value)" required>' +
                '<option value=""' + (!defaultPostType ? ' selected' : '') + '>Select type...</option>' +
                buildOpts(SUBTASK_TYPES, null, null, defaultPostType) +
                '</select>' +
                '</div>' +
                '<div class="subtask-cell full">' +
                '<label class="field-label blue">Priority</label>' +
                '<select name="subtasks[' + idx + '][priority]" class="styled-input" style="width:100%; border-radius:12px;">' +
                '<option value="Medium">Medium (Stable)</option>' +
                '<option value="High Priority">High Priority (Urgent)</option>' +
                '<option value="Low Priority">Low Priority (Paused)</option>' +
                '</select>' +
                '</div>' +

                // Outlines section: Concept, Caption, Post Copy, Reference
                '<div id="subtask-outlines-' + idx + '" style="' + (showOutlines ? 'display:block;' : 'display:none;') + '">' +
                '<div class="subtask-cell full">' +
                '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">' +
                '<label class="field-label" style="margin-bottom:0;">Concept</label>' +
                '<span class="expand-trigger" onclick="openFocusModal(\'subtasks[' + idx + '][concept]\', \'Post \' + (idx + 1) + \' Concept\')">' +
                '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4h4M4 4l5 5m11-1V4h-4m4 0l-5 5M4 16v4h4m-4 0l5-5m11 5v-4h-4m4 4l-5-5"/></svg>' +
                '</span>' +
                '</div>' +
                '<textarea name="subtasks[' + idx + '][concept]" rows="2" placeholder="N/A" class="styled-textarea" style="min-height:60px;"' + (showOutlines ? '' : ' disabled') + '></textarea>' +
                '</div>' +
                '<div class="subtask-cell full">' +
                '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">' +
                '<label class="field-label" style="margin-bottom:0;">Caption</label>' +
                '<span class="expand-trigger" onclick="openFocusModal(\'subtasks[' + idx + '][caption]\', \'Post \' + (idx + 1) + \' Caption\')">' +
                '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4h4M4 4l5 5m11-1V4h-4m4 0l-5 5M4 16v4h4m-4 0l5-5m11 5v-4h-4m4 4l-5-5"/></svg>' +
                '</span>' +
                '</div>' +
                '<textarea name="subtasks[' + idx + '][caption]" rows="2" placeholder="N/A" class="styled-textarea" style="min-height:60px;"' + (showOutlines ? '' : ' disabled') + '></textarea>' +
                '</div>' +
                '<div class="subtask-cell full">' +
                '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">' +
                '<label class="field-label" style="margin-bottom:0;">Post Copy</label>' +
                '<span class="expand-trigger" onclick="openFocusModal(\'subtasks[' + idx + '][post_copy]\', \'Post \' + (idx + 1) + \' Post Copy\')">' +
                '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4h4M4 4l5 5m11-1V4h-4m4 0l-5 5M4 16v4h4m-4 0l5-5m11 5v-4h-4m4 4l-5-5"/></svg>' +
                '</span>' +
                '</div>' +
                '<textarea name="subtasks[' + idx + '][post_copy]" rows="3" placeholder="N/A" class="styled-textarea" style="min-height:70px;"' + (showOutlines ? '' : ' disabled') + '></textarea>' +
                '</div>' +
                '<div class="subtask-cell full" style="border-bottom:none;">' +
                '<label class="field-label">Reference <span style="opacity:0.6;font-weight:400;font-size:11px;">(Link, Image/File, or Both)</span></label>' +
                '<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; align-items:start; margin-top:6px;">' +
                '<div>' +
                '<label style="display:block;font-size:10px;font-weight:700;color:var(--color-text-secondary);margin-bottom:4px;text-transform:uppercase;">Reference Link</label>' +
                '<input type="url" name="subtasks[' + idx + '][reference]" placeholder="https://..." class="styled-input"' + (showOutlines ? '' : ' disabled') + '>' +
                '</div>' +
                '<div>' +
                '<label style="display:block;font-size:10px;font-weight:700;color:var(--color-text-secondary);margin-bottom:4px;text-transform:uppercase;">Reference Image / File</label>' +
                '<input type="file" name="subtasks[' + idx + '][reference_file]" accept="image/*,video/*" class="styled-input" style="padding:9px 14px;cursor:pointer;"' + (showOutlines ? '' : ' disabled') + '>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>' +

                // Brief, Assign Person and File Upload section: for other post types
                '<div id="subtask-brief-' + idx + '" style="' + (showBrief ? 'display:block;' : 'display:none;') + '">' +
                '<div class="subtask-cell full">' +
                '<label class="field-label blue">Assign Person</label>' +
                '<select name="subtasks[' + idx + '][writer_id]" class="styled-input subtask-assignee-select" style="width:100%; border-radius:8px;"' + (showBrief ? ' required' : ' disabled') + '>' +
                '<option value="">Select person...</option>' +
                buildOpts(USERS, 'id', 'name') +
                '</select>' +
                '</div>' +
                '<div class="subtask-cell full">' +
                '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:7px;">' +
                '<label class="field-label" style="margin-bottom:0;">Brief</label>' +
                '<span class="expand-trigger" onclick="openFocusModal(\'subtasks[' + idx + '][brief]\', \'Post \' + (idx + 1) + \' Brief\')">' +
                '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8V4h4M4 4l5 5m11-1V4h-4m4 0l-5 5M4 16v4h4m-4 0l5-5m11 5v-4h-4m4 4l-5-5"/></svg>' +
                '</span>' +
                '</div>' +
                '<textarea name="subtasks[' + idx + '][brief]" rows="3" placeholder="Enter brief or details..." class="styled-textarea" style="min-height:80px;"' + (showBrief ? '' : ' disabled') + '></textarea>' +
                '</div>' +
                '<div class="subtask-cell full" style="border-bottom:none;">' +
                '<label class="field-label">File Upload <span style="opacity:0.6;font-weight:400;font-size:11px;">(Documents, Media, Assets)</span></label>' +
                '<input type="file" name="subtasks[' + idx + '][reference_file]" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,image/*,video/*" class="styled-input" style="padding:9px 14px;cursor:pointer;margin-top:6px;"' + (showBrief ? '' : ' disabled') + '>' +
                '</div>' +
                '</div>' +

                '</div>';
            container.appendChild(card);
            renumber();
        }

        function removeSubtask(idx) {
            var el = document.getElementById('subtask-' + idx);
            if (el) { el.style.opacity = '0'; el.style.transform = 'scale(0.97)'; el.style.transition = 'all 0.2s'; setTimeout(function () { el.remove(); renumber(); }, 200); }
        }

        function renumber() {
            document.querySelectorAll('.subtask-label').forEach(function (el, i) { el.textContent = 'Post ' + (i + 1); });
        }

        function toggleRefType(idx, type) {
            const card = document.getElementById('subtask-' + idx);
            if (!card) return;

            const linkArea = document.getElementById('ref-input-link-' + idx);
            const uploadArea = document.getElementById('ref-input-upload-' + idx);
            const btns = card.querySelectorAll('.ref-toggle-btn');

            if (type === 'link') {
                linkArea.style.display = 'block';
                uploadArea.style.display = 'none';
                btns[0].classList.add('active');
                btns[1].classList.remove('active');
            } else {
                linkArea.style.display = 'none';
                uploadArea.style.display = 'block';
                btns[0].classList.remove('active');
                btns[1].classList.add('active');
            }
        }

        // Initial subtask
        addSubtask();

        // Loading state and fallback title on form submit
        document.querySelector('form').addEventListener('submit', function() {
            const titleInput = document.getElementById('deliverable_title_input');
            if (titleInput && !titleInput.value.trim()) {
                const firstPostTitle = document.querySelector('input[name="subtasks[0][title]"]');
                if (firstPostTitle && firstPostTitle.value.trim()) {
                    titleInput.value = firstPostTitle.value.trim();
                }
            }

            const btn = document.getElementById('createDeliverableBtn');
            const overlay = document.getElementById('dlvLoadingOverlay');
            btn.disabled = true;
            btn.innerHTML = '<svg style="width:13px;height:13px;animation:dlvSpin 0.75s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>Submitting…';
            overlay.style.display = 'flex';
        });

        // Handle Dynamic Types & Top Fields on Project Change
        const projectSelect = document.querySelector('select[name="project_id"]');
        if (projectSelect) {
            projectSelect.addEventListener('change', function () {
                const pid = this.value;
                const flow = PROJECT_WORKFLOWS[pid] || 'retainer';
                currentWorkflow = flow;
                const isCampaign = (flow === 'campaign' || flow === 'pitch');

                const topContainer = document.getElementById('top-orchestration-container');
                const titleInput = document.getElementById('deliverable_title_input');
                const writerSelect = document.getElementById('deliverable_writer_select');
                const deadlineInput = document.getElementById('deadline');

                if (topContainer) {
                    topContainer.style.display = isCampaign ? 'none' : '';
                }
                if (titleInput) titleInput.required = !isCampaign;
                if (writerSelect) writerSelect.required = !isCampaign;
                if (deadlineInput) deadlineInput.required = !isCampaign;

                SUBTASK_TYPES = isCampaign ? CAMPAIGN_TYPES : RETAINER_TYPES;

                // Update breadcrumb dynamically
                const bcEl = document.getElementById('create-breadcrumb');
                if (bcEl) {
                    const pInfo = PROJECT_DETAILS[pid];
                    let html = '<a href="/brands" class="create-bc-link">Brands</a>';
                    if (pInfo) {
                        if (pInfo.brand_name) {
                            html += '<span class="create-bc-sep">/</span><a href="/brands/' + encodeURIComponent(pInfo.brand_slug) + '" class="create-bc-link">' + pInfo.brand_name + '</a>';
                        }
                        html += '<span class="create-bc-sep">/</span><a href="/projects/' + pid + '" class="create-bc-link">' + pInfo.name + '</a>';
                    }
                    html += '<span class="create-bc-sep">/</span><span class="create-bc-current">' + ({{ isset($parentId) ? 'true' : 'false' }} ? 'Add Subtasks' : 'New Deliverable') + '</span>';
                    bcEl.innerHTML = html;
                }

                // Refresh existing dropdowns
                document.querySelectorAll('select.subtask-type-select').forEach((sel, i) => {
                    const defaultVal = isCampaign ? '' : (RETAINER_TYPES[0] || '');
                    sel.innerHTML = '<option value=""' + (!defaultVal ? ' selected' : '') + '>Select type...</option>' + buildOpts(SUBTASK_TYPES, null, null, defaultVal);
                    sel.value = defaultVal;
                    handlePostTypeChange(sel.dataset.idx || i, defaultVal);
                });
            });
        }
    </script>
</x-layout>
