<x-layout title="New Deliverable">
    <style>
        .form-container {
            max-width: 880px;
            margin: 20px auto;
            background: var(--color-bg-primary);
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-border-primary);
            overflow: hidden;
            font-family: 'Inter', -apple-system, sans-serif;
            transition: background 0.3s, border-color 0.3s;
        }

        .form-section {
            padding: 36px 44px;
            border-bottom: 1px solid var(--color-border-primary);
            position: relative;
        }

        .form-close-btn {
            position: absolute;
            top: 36px;
            right: 44px;
            color: var(--color-text-secondary);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            text-decoration: none;
        }

        .form-close-btn:hover {
            color: var(--color-text-primary);
            background: var(--color-border-primary);
            transform: scale(1.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        .grid-cell {
            padding: 28px 44px;
            border-bottom: 1px solid var(--color-border-primary);
        }

        .grid-cell.br {
            border-right: none;
        }

        @media (min-width: 768px) {
            .grid-cell.br {
                border-right: 1px solid var(--color-border-primary);
            }
        }

        .field-label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .field-label.blue {
            color: #2563eb;
        }

        .massive-input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            font-size: 32px;
            font-weight: 900;
            color: var(--color-text-primary);
            letter-spacing: -0.04em;
            padding: 0;
            margin-top: 10px;
        }

        .massive-input::placeholder {
            color: #e8edf4;
        }

        .styled-input {
            width: 100%;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: 20px;
            padding: 18px 22px;
            font-size: 14px;
            font-weight: 700;
            color: var(--color-text-primary);
            transition: all 0.2s;
            outline: none;
            appearance: none;
            box-sizing: border-box;
        }

        .styled-input:focus {
            background: var(--color-bg-primary);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .styled-textarea {
            width: 100%;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: 20px;
            padding: 18px 22px;
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-primary);
            outline: none;
            resize: vertical;
            min-height: 90px;
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .styled-textarea:focus {
            background: var(--color-bg-primary);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        /* Subtask Card */
        .subtask-block {
            background: transparent;
            border-radius: 32px;
            border: 1.5px solid var(--color-border-primary);
            overflow: hidden;
            margin-bottom: 20px;
            transition: border-color 0.2s;
        }

        .subtask-block:hover {
            border-color: #0055D440;
        }

        .subtask-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 28px;
            background: var(--color-bg-primary);
            border-bottom: 1px solid var(--color-border-primary);
        }

        .subtask-tag {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #0055D4;
        }

        .subtask-tag-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #0055D4;
        }

        .subtask-body {
            display: grid;
            grid-template-columns: 1fr;
        }

        @media (min-width: 768px) {
            .subtask-body {
                grid-template-columns: 1fr 1fr;
            }
        }

        .subtask-cell {
            padding: 22px 28px;
            border-bottom: 1px solid var(--color-border-primary);
        }

        .subtask-cell.br {
            border-right: none;
        }

        @media (min-width: 768px) {
            .subtask-cell.br {
                border-right: 1px solid var(--color-border-primary);
            }
        }

        .subtask-cell.full {
            grid-column: 1 / -1;
        }

        .subtask-cell:last-child,
        .subtask-cell:nth-last-child(2):not(.full) {
            border-bottom: none;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            padding: 6px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            color: #ef4444;
            background: #fef2f2;
        }

        /* Add button */
        .add-btn {
            width: 100%;
            padding: 22px 30px;
            background: transparent;
            border: 2px dashed var(--color-border-primary);
            border-radius: 28px;
            color: var(--color-text-secondary);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .add-btn:hover {
            border-color: #0055D4;
            color: #0055D4;
            background: rgba(0, 85, 212, 0.05);
        }

        /* Admin panel */
        .admin-panel {
            margin-top: 24px;
            background: #fffbeb;
            border: 1.5px dashed #fcd34d;
            border-radius: 24px;
            padding: 24px 28px;
        }

        .admin-badge {
            display: inline-block;
            background: #f59e0b;
            color: #fff;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 16px;
        }

        /* Footer */
        .form-footer {
            background: var(--color-bg-secondary);
            padding: 30px 44px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--color-border-primary);
        }

        .btn {
            padding: 16px 40px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0055D4;
            color: #fff;
            box-shadow: 0 10px 25px rgba(0, 85, 212, 0.2);
        }

        .btn-primary:hover {
            background: #0044aa;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: transparent;
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border-primary);
        }

        .btn-cancel:hover {
            color: var(--color-text-primary);
            background: var(--color-border-primary);
        }

        select.styled-input {
            cursor: pointer;
        }

        /* Modal Focus Mode */
        .focus-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 40px;
            animation: fadeIn 0.3s ease-out;
        }

        .focus-modal-content {
            background: var(--color-bg-primary);
            width: 100%;
            max-width: 900px;
            border-radius: 40px;
            border: 1px solid var(--color-border-primary);
            box-shadow: 0 50px 150px rgba(0, 0, 0, 0.5);
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .focus-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .focus-modal-textarea {
            width: 100%;
            height: 500px;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: 24px;
            padding: 30px;
            font-size: 16px;
            line-height: 1.6;
            color: var(--color-text-primary);
            outline: none;
            resize: none;
            font-weight: 500;
        }

        .expand-trigger {
            padding: 4px;
            color: #3b82f6;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .expand-trigger:hover {
            opacity: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleUp {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Reference Toggle */
        .ref-toggle-container {
            display: flex;
            background: var(--color-bg-secondary);
            border: 1px solid var(--color-border-primary);
            border-radius: 12px;
            padding: 4px;
            width: fit-content;
            margin-bottom: 12px;
        }
        .ref-toggle-btn {
            border: none;
            background: transparent;
            padding: 6px 12px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--color-text-secondary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ref-toggle-btn.active {
            background: var(--color-bg-primary);
            color: #0055D4;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
    </style>

    {{-- Pass PHP data to JS safely via data attributes --}}
    <div id="app-data" data-users="{{ json_encode($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])) }}"
        data-is-admin="{{ (auth()->check() && strtolower(auth()->user()->role ?? '') === 'admin') ? 'true' : 'false' }}"
        style="display:none;"></div>

    <div class="form-container">
        <form action="{{ route('deliverables.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div
                    style="margin: 20px 44px; padding: 16px; background: #fff1f2; border: 1px solid #fecaca; border-radius: 16px; color: #be123c; font-size: 13px; font-weight: 600;">
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

            {{-- ── Title ── --}}
            <div class="form-section">
                @if($selectedProjectId)
                    <a href="{{ route('projects.show', $selectedProjectId) }}" class="form-close-btn">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
                <label
                    class="field-label blue">{{ isset($parentId) ? 'Adding Subtasks to' : 'Deliverable Title' }}</label>
                <input type="text" name="title" required placeholder="Name this deliverable..." class="massive-input"
                    value="{{ old('title', $parentTask->title ?? '') }}" {{ isset($parentId) ? 'readonly' : '' }}>
                @error('title') <p style="color:#ef4444;font-size:11px;font-weight:700;margin-top:12px;">{{ $message }}</p> @enderror
            </div>

            {{-- ── Global Orchestration (Writer, Date, Priority) ── --}}
            <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="grid-cell br">
                    <label class="field-label">Assigned Writer</label>
                    <div class="styled-input-wrapper">
                        <select name="writer_id" class="styled-input" required>
                            <option value="">Select Writer...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('writer_id', $parentTask->writer_id ?? '') == $user->id) ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('writer_id') <p style="color:#ef4444;font-size:11px;font-weight:700;margin-top:8px;">{{ $message }}</p> @enderror
                </div>

                <div class="grid-cell br">
                    <label class="field-label">Project Due Date</label>
                    <div class="styled-input-wrapper">
                        <input type="date" name="deadline" id="deadline" class="styled-input"
                            value="{{ old('deadline', $parentTask->deadline ?? '') }}" required>
                    </div>
                    @error('deadline') <p style="color:#ef4444;font-size:11px;font-weight:700;margin-top:8px;">{{ $message }}</p> @enderror
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
                <label class="field-label" style="margin-bottom:24px;">Posts</label>

                <div id="subtasks-container"></div>

                <button type="button" class="add-btn" onclick="addSubtask()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Post
                </button>
            </div>

            {{-- ── Footer ── --}}
            <div class="form-footer">
                <a href="{{ url()->previous() }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>

    {{-- ── Focus Mode Modal ── --}}
    <div id="focus-modal" class="focus-modal-overlay">
        <div class="focus-modal-content">
            <div class="focus-modal-header">
                <div>
                    <span id="focus-modal-label" class="field-label blue" style="margin-bottom: 4px;">Focus View</span>
                    <h3 id="focus-modal-title" style="font-size: 18px; font-weight: 800; color: var(--color-text-primary); margin: 0;">Editing Content</h3>
                </div>
                <button type="button" onclick="closeFocusModal()" class="form-close-btn" style="position: static;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
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
        const RETAINER_TYPES = @json($subtaskTypes->where('workflow_type', 'retainer')->pluck('name'));
        const CAMPAIGN_TYPES = @json($subtaskTypes->where('workflow_type', 'campaign')->pluck('name'));

        let initialType = "{{ $workflowType ?? 'retainer' }}";
        let SUBTASK_TYPES = (initialType === 'campaign' || initialType === 'pitch') ? CAMPAIGN_TYPES : RETAINER_TYPES;
        let subtaskIndex = 0;

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
            card.innerHTML =
                '<div class="subtask-header">' +
                '<div class="subtask-tag"><span class="subtask-tag-dot"></span><span class="subtask-label">Post ' + (idx + 1) + '</span></div>' +
                '<button type="button" class="remove-btn" onclick="removeSubtask(' + idx + ')">' +
                '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
                '</div>' +
                '<div class="subtask-body" style="grid-template-columns:1fr;">' +
                '<div class="subtask-cell full">' +
                '<label class="field-label blue">Post Name</label>' +
                '<input type="text" name="subtasks[' + idx + '][title]" placeholder="e.g. Ramadan Sale \u2014 Carousel Post..." class="styled-input" required>' +
                '</div>' +
                '<div class="subtask-cell full">' +
                '<label class="field-label blue">Post Type</label>' +
                '<select name="subtasks[' + idx + '][post_type]" class="styled-input subtask-type-select">' +
                '<option value="">Select type...</option>' +
                buildOpts(SUBTASK_TYPES, null, null, '') +
                '</select>' +
                '</div>' +
                '<div class="subtask-cell full" style="border-bottom:none;">' +
                '<label class="field-label">Notes</label>' +
                '<textarea name="subtasks[' + idx + '][notes]" rows="3" placeholder="Any notes or direction for this post..." class="styled-textarea" style="min-height:80px;"></textarea>' +
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

        // Handle Dynamic Types on Project Change
        const projectSelect = document.querySelector('select[name="project_id"]');
        if (projectSelect) {
            projectSelect.addEventListener('change', function () {
                const pid = this.value;
                const flow = PROJECT_WORKFLOWS[pid] || 'retainer';
                SUBTASK_TYPES = (flow === 'campaign' || flow === 'pitch') ? CAMPAIGN_TYPES : RETAINER_TYPES;

                // Refresh existing dropdowns
                document.querySelectorAll('select.subtask-type-select').forEach(sel => {
                    const currentVal = sel.value;
                    sel.innerHTML = '<option value="">Select type...</option>' + buildOpts(SUBTASK_TYPES, null, null, currentVal);
                });
            });
        }
    </script>
</x-layout>