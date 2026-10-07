<x-layout title="New Project">
<style>
.f-wrap{max-width:640px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;}
.f-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);}
.f-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:7px;}
.f-label.blue{color:#3b82f6;}
.f-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s;-webkit-appearance:none;appearance:none;}
.f-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
.f-input::placeholder{color:var(--color-text-secondary);opacity:0.45;}
.f-title{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
.f-title::placeholder{opacity:0.25;color:var(--color-text-primary);}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.f-footer{background:var(--color-bg-secondary);padding:14px 24px;display:flex;justify-content:flex-end;gap:8px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-c{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.12s;}
.btn-c:hover{background:var(--color-bg-secondary);color:var(--color-text-primary);}
.btn-s{padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,85,212,0.25);transition:all 0.12s;}
.btn-s:hover{background:#0044aa;}
.type-group{display:flex;gap:8px;margin-top:8px;}
.type-opt{flex:1;padding:11px 12px;border:1.5px solid var(--color-border-primary);border-radius:8px;cursor:pointer;background:var(--color-bg-secondary);transition:all 0.12s;text-align:center;}
.type-opt:hover{border-color:#93c5fd;}
.type-opt.active{border-color:#3b82f6;background:rgba(59,130,246,0.06);}
.type-opt h4{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--color-text-primary);margin-bottom:2px;}
.type-opt.active h4{color:#2563eb;}
.type-opt p{font-size:10px;color:var(--color-text-secondary);}
textarea.f-input{resize:vertical;min-height:90px;line-height:1.6;}
input[type="date"]::-webkit-calendar-picker-indicator{cursor:pointer;opacity:0.45;filter:invert(0.4);}
.dark input[type="date"]::-webkit-calendar-picker-indicator{filter:invert(0.6);}
/* Member picker */
.mp-wrap{border:1.5px solid var(--color-border-primary);border-radius:8px;overflow:hidden;}
.mp-search{display:flex;align-items:center;gap:8px;padding:9px 12px;border-bottom:1px solid var(--color-border-primary);background:var(--color-bg-secondary);}
.mp-search input{flex:1;background:transparent;border:none;outline:none;font-size:12px;color:var(--color-text-primary);}
.mp-search input::placeholder{color:var(--color-text-secondary);opacity:0.5;}
.mp-list{max-height:220px;overflow-y:auto;}
.mp-role{padding:5px 12px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--color-text-secondary);background:var(--color-bg-secondary);border-bottom:1px solid var(--color-border-primary);}
.mp-row{display:flex;align-items:center;gap:10px;padding:9px 12px;cursor:pointer;border-bottom:1px solid var(--color-border-primary);transition:background 0.1s;}
.mp-row:last-child{border-bottom:none;}
.mp-row:hover{background:var(--color-bg-secondary);}
.mp-row.selected{background:rgba(59,130,246,0.06);}
.mp-init{width:28px;height:28px;border-radius:6px;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--color-text-secondary);text-transform:uppercase;flex-shrink:0;transition:all 0.12s;}
.mp-row.selected .mp-init{background:rgba(59,130,246,0.1);border-color:rgba(59,130,246,0.3);color:#3b82f6;}
.mp-check{width:16px;height:16px;border-radius:50%;border:1.5px solid var(--color-border-primary);flex-shrink:0;display:flex;align-items:center;justify-content:center;margin-left:auto;transition:all 0.12s;}
.mp-row.selected .mp-check{background:#3b82f6;border-color:#3b82f6;}
</style>

<nav style="max-width:640px;margin:0 auto 12px;display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--color-text-secondary);">
    <a href="{{ route('brands.index') }}" style="text-decoration:none;color:inherit;">Brands</a>
    <span style="opacity:0.35;">/</span>
    @if(request('brand_id') && ($b = $brands->find(request('brand_id'))))
        <a href="{{ route('brands.show', $b->slug) }}" style="text-decoration:none;color:inherit;">{{ $b->name }}</a>
        <span style="opacity:0.35;">/</span>
    @endif
    <span style="color:var(--color-text-primary);">New Project</span>
</nav>

<div class="f-wrap">
    @if ($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 12px; font-weight: 600;">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form id="createProjectForm" action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(!request('brand_id') && $brands->count() > 1)
            <div class="f-section">
                <label class="f-label">Brand</label>
                <select name="brand_id" id="project_brand_select" class="f-input" style="max-width:320px;" onchange="updateCrmJobsForBrand(this.value)">
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ (old('brand_id', $selectedBrand->id ?? '') == $b->id) ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <input type="hidden" id="project_brand_select" name="brand_id" value="{{ request('brand_id', $selectedBrand->id ?? ($brands->first()->id ?? '')) }}">
        @endif
        <input type="hidden" name="priority" value="Medium">
        <input type="hidden" name="status" value="To commence">
        <input type="hidden" name="type" value="primary">

        {{-- Title --}}
        <div class="f-section">
            <label class="f-label blue">Project Title</label>
            <input type="text" name="name" required placeholder="Enter project title…" class="f-title" value="{{ old('name') }}">
            @error('name')<p style="color:#ef4444;font-size:11px;margin-top:6px;">{{ $message }}</p>@enderror
        </div>

        {{-- Job Number + Due Date --}}
        <div class="f-section">
            <div class="f-grid">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label class="f-label" style="margin-bottom:0;">Job Number</label>
                        <span id="crm_badge" style="display:none; font-size:10px; font-weight:700; color:#0055D4; background:rgba(0,85,212,0.08); border:1px solid rgba(0,85,212,0.2); padding:2px 7px; border-radius:5px;">
                            CRM Jobs Available
                        </span>
                    </div>

                    {{-- CRM Job Searchable Selector --}}
                    <div id="crm_job_dropdown_container" style="display:none; margin-bottom:8px; position:relative;">
                        <div style="position:relative; display:flex; align-items:center;">
                            <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--color-text-secondary); pointer-events:none; display:flex; align-items:center;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input type="text" id="crm_search_input" 
                                placeholder="Search by Job ID, last 4 digits (e.g. 0469), or Brand..." 
                                autocomplete="off"
                                onfocus="openCrmDropdown()"
                                onclick="openCrmDropdown()"
                                oninput="filterCrmJobs(this.value)"
                                class="f-input" 
                                style="font-size:11.5px; font-weight:600; padding-left:30px; padding-right:28px; color:var(--color-text-primary); background:var(--color-bg-secondary); border:1.5px solid #0055D4; cursor:text; width:100%; box-sizing:border-box;">
                            
                            <button type="button" id="crm_search_clear_btn" onclick="clearCrmSearch()" style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--color-text-secondary); cursor:pointer; font-size:15px; font-weight:bold; padding:2px 4px; line-height:1;">
                                &times;
                            </button>
                        </div>

                        {{-- Dropdown Results Panel --}}
                        <div id="crm_results_panel" style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); z-index:999; background:var(--color-bg-primary); border:1.5px solid var(--color-border-primary); border-radius:8px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.3); max-height:220px; overflow-y:auto; padding:4px;">
                        </div>
                    </div>

                    <input type="text" id="project_job_number" name="job_number" placeholder="e.g. JN-2025-001" class="f-input" value="{{ old('job_number') }}" autocomplete="off">
                    <span style="font-size:10px; color:var(--color-text-secondary); margin-top:3px; display:block;">Select an incoming CRM Job ID above or enter one manually.</span>
                </div>
                <div>
                    <label class="f-label">Due Date</label>
                    <input type="date" id="project_deadline" name="deadline" class="f-input" min="{{ date('Y-m-d') }}" value="{{ old('deadline') }}">
                </div>
            </div>
        </div>

        {{-- Project Type --}}
        <div class="f-section">
            <label class="f-label">Project Type</label>
            <div class="type-group">
                <div class="type-opt active" id="option-retainer" onclick="setWorkflow('retainer')">
                    <h4>Retainer</h4>
                    <p>7-stage approval flow</p>
                </div>
                <div class="type-opt" id="option-campaign" onclick="setWorkflow('campaign')">
                    <h4>Campaign</h4>
                    <p>4-stage initiative</p>
                </div>
                <div class="type-opt" id="option-pitch" onclick="setWorkflow('pitch')">
                    <h4>Pitch</h4>
                    <p>Business proposal</p>
                </div>
            </div>
            <input type="hidden" name="workflow_type" id="workflow_type" value="retainer">
        </div>

        {{-- Project Brief --}}

        <div class="f-section">
            <label class="f-label">Project Brief</label>
            <div x-data="quillEditor({{ json_encode(old('description')) }})" style="margin-bottom: 12px;">
                <textarea name="description" x-model="content" style="display:none;"></textarea>
                <div x-ref="editor" class="f-input" style="min-height: 120px; border-top-left-radius: 0; border-top-right-radius: 0; padding: 0;"></div>
            </div>
            <div id="project-batches-section" style="margin-top:16px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <label class="f-label" style="margin-bottom:0;font-size:12px;font-weight:700;">Project Batches</label>
                        <p style="font-size:11px;color:var(--color-text-secondary);margin:3px 0 0 0;font-weight:500;">
                            Keep the default batch date, add a batch due date, or set individual due dates for each deliverable.
                        </p>
                    </div>
                    <button type="button" onclick="addBatchCard()" style="padding:6px 12px;background:#0055D4;color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;box-shadow:0 2px 6px rgba(0,85,212,0.15);flex-shrink:0;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Add Batch
                    </button>
                </div>

                <div id="batches-container" style="display:flex;flex-direction:column;gap:14px;margin-top:8px;">
                    <!-- Dynamically loaded batch cards -->
                </div>
            </div>
            <div style="margin-top:12px;">
                <label class="f-label">Brief Document <span style="opacity:0.5;font-weight:400;">(PDF, DOC, PPT, PNG, JPG · max 10MB)</span></label>
                <input type="file" name="brief_file" class="f-input" style="padding:7px 12px;cursor:pointer;" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.jpg,.jpeg,.png">
            </div>
        </div>

        <div class="f-footer">
            <a href="{{ url()->previous() }}" class="btn-c">Cancel</a>
            <button type="submit" id="createProjectBtn" class="btn-s">Create Project</button>
        </div>
    </form>
</div>

{{-- Full-page loading overlay --}}
<div id="pageLoadingOverlay" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.55);backdrop-filter:blur(6px);flex-direction:column;align-items:center;justify-content:center;gap:16px;">
    <div style="width:52px;height:52px;border-radius:50%;border:3px solid rgba(255,255,255,0.15);border-top-color:#fff;animation:loopSpin 0.75s linear infinite;"></div>
    <span id="pageLoadingText" style="color:#fff;font-size:13px;font-weight:700;letter-spacing:0.04em;">Creating project…</span>
</div>
<style>@keyframes loopSpin{to{transform:rotate(360deg)}}</style>

<script>
const subtaskTypes = @json($subtaskTypes);
const oldBatches = @json(old('batches', []));
const allAvailableCrmJobs = @json($allCrmJobs ?? []);
const allBrandsList = @json($brands);
const allTeamMembers = @json(($allUsers ?? $users)->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role]));
let batchIndex = 0;

let currentCrmQuery = '';

function matchesCrmJob(j, q) {
    if (!q) return true;
    const cleanQ = q.trim().toLowerCase();
    if (!cleanQ) return true;

    const jobId = (j.crm_job_id || '').toLowerCase();
    const brandName = (j.brand_name || '').toLowerCase();
    const title = (j.title || '').toLowerCase();

    // 1. Direct contains in Job ID (e.g. "loops", "2026", "0469")
    if (jobId.includes(cleanQ)) return true;

    // 2. Direct contains in Brand Name (e.g. "dove", "sampath")
    if (brandName.includes(cleanQ)) return true;

    // 3. Direct contains in Title
    if (title.includes(cleanQ)) return true;

    // 4. Last digits search (strip non-digits and test endsWith or includes)
    const digitsOnlyQ = cleanQ.replace(/\D/g, '');
    const digitsOnlyJob = jobId.replace(/\D/g, '');
    if (digitsOnlyQ.length > 0 && (digitsOnlyJob.endsWith(digitsOnlyQ) || digitsOnlyJob.includes(digitsOnlyQ))) {
        return true;
    }

    // 5. Slash / dash segmented parts
    const segments = jobId.split(/[\/\-_]/);
    if (segments.some(seg => seg.includes(cleanQ))) {
        return true;
    }

    return false;
}

function highlightMatch(text, query) {
    if (!query || !text) return text || '';
    const cleanQ = query.trim();
    if (!cleanQ) return text;
    try {
        const regex = new RegExp(`(${cleanQ.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<span style="background:rgba(0,85,212,0.25); color:#60a5fa; border-radius:3px; padding:0 3px;">$1</span>');
    } catch(e) {
        return text;
    }
}

function openCrmDropdown() {
    const panel = document.getElementById('crm_results_panel');
    if (!panel) return;
    renderCrmDropdownItems(currentCrmQuery);
    panel.style.display = 'block';
}

function closeCrmDropdown() {
    const panel = document.getElementById('crm_results_panel');
    if (panel) panel.style.display = 'none';
}

function filterCrmJobs(query) {
    currentCrmQuery = query;
    const clearBtn = document.getElementById('crm_search_clear_btn');
    if (clearBtn) {
        clearBtn.style.display = query ? 'block' : 'none';
    }
    openCrmDropdown();
}

function clearCrmSearch() {
    currentCrmQuery = '';
    const searchInput = document.getElementById('crm_search_input');
    if (searchInput) searchInput.value = '';
    const clearBtn = document.getElementById('crm_search_clear_btn');
    if (clearBtn) clearBtn.style.display = 'none';
    renderCrmDropdownItems('');
    openCrmDropdown();
    if (searchInput) searchInput.focus();
}

function selectCrmJob(jobId) {
    const job = allAvailableCrmJobs.find(j => j.crm_job_id === jobId);
    if (!job) return;

    // Set search box text
    const searchInput = document.getElementById('crm_search_input');
    if (searchInput) {
        searchInput.value = `${job.crm_job_id}${job.brand_name ? ' (' + job.brand_name + ')' : ''}`;
    }

    // Set project job number
    const jobInput = document.getElementById('project_job_number');
    if (jobInput) jobInput.value = job.crm_job_id;

    // Auto-fill project title if empty
    const titleInput = document.querySelector('input[name="name"]');
    if (titleInput && (!titleInput.value || titleInput.value.trim() === '') && job.title) {
        titleInput.value = job.title;
    }

    // Auto-fill deadline if empty
    const deadlineInput = document.getElementById('project_deadline');
    if (deadlineInput && (!deadlineInput.value || deadlineInput.value.trim() === '') && job.deadline) {
        deadlineInput.value = job.deadline.substring(0, 10);
    }

    const clearBtn = document.getElementById('crm_search_clear_btn');
    if (clearBtn) clearBtn.style.display = 'block';

    closeCrmDropdown();
}

function renderCrmDropdownItems(query) {
    const panel = document.getElementById('crm_results_panel');
    if (!panel) return;

    const filtered = allAvailableCrmJobs.filter(j => matchesCrmJob(j, query));

    if (filtered.length === 0) {
        panel.innerHTML = `
            <div style="padding:12px; font-size:11.5px; color:var(--color-text-secondary); text-align:center;">
                No CRM jobs found matching "<strong style="color:var(--color-text-primary);">${query}</strong>"
            </div>
        `;
        return;
    }

    let html = '';
    filtered.forEach(j => {
        const highlightedId = highlightMatch(j.crm_job_id, query);
        const highlightedBrand = j.brand_name ? highlightMatch(j.brand_name, query) : '';
        const highlightedTitle = j.title ? highlightMatch(j.title, query) : '';

        html += `
            <div onclick="selectCrmJob('${j.crm_job_id}')"
                style="padding:8px 10px; border-radius:6px; cursor:pointer; display:flex; align-items:center; justify-content:space-between; gap:10px; transition:background 0.15s; margin-bottom:2px; border:1px solid transparent;"
                onmouseover="this.style.background='rgba(0,85,212,0.12)'; this.style.borderColor='rgba(0,85,212,0.2)';"
                onmouseout="this.style.background='transparent'; this.style.borderColor='transparent';">
                <div style="display:flex; flex-direction:column; gap:2px; min-width:0; overflow:hidden;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-family:monospace; font-size:12px; font-weight:700; color:#0055D4;">${highlightedId}</span>
                        ${j.brand_name ? `<span style="font-size:10px; font-weight:700; padding:1px 6px; border-radius:4px; background:rgba(255,255,255,0.06); border:1px solid var(--color-border-primary); color:var(--color-text-primary); white-space:nowrap;">${highlightedBrand}</span>` : ''}
                    </div>
                    ${j.title ? `<span style="font-size:11px; color:var(--color-text-secondary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${highlightedTitle}</span>` : ''}
                </div>
                ${j.deadline ? `<span style="font-size:10px; font-weight:600; color:var(--color-text-secondary); white-space:nowrap;">Due: ${j.deadline.substring(0, 10)}</span>` : ''}
            </div>
        `;
    });

    panel.innerHTML = html;
}

function updateCrmJobsForBrand(brandId) {
    refreshCrmJobsDropdown();
}

function refreshCrmJobsDropdown() {
    const container = document.getElementById('crm_job_dropdown_container');
    const badge = document.getElementById('crm_badge');
    if (!container) return;

    if (allAvailableCrmJobs.length > 0) {
        container.style.display = 'block';
        if (badge) {
            badge.textContent = `${allAvailableCrmJobs.length} CRM Job${allAvailableCrmJobs.length > 1 ? 's' : ''} Available`;
            badge.style.display = 'inline-block';
        }
        renderCrmDropdownItems('');
    } else {
        container.style.display = 'none';
        if (badge) badge.style.display = 'none';
    }
}

// Close dropdown panel when clicking outside
document.addEventListener('click', (e) => {
    const container = document.getElementById('crm_job_dropdown_container');
    if (container && !container.contains(e.target)) {
        closeCrmDropdown();
    }
});


function addBatchCard(existingData = null) {
    batchIndex++;
    const container = document.getElementById('batches-container');
    const workflowType = document.getElementById('workflow_type').value;

    const card = document.createElement('div');
    card.className = 'batch-card';
    card.id = `batch-card-${batchIndex}`;
    card.style = 'border:1.5px solid var(--color-border-primary);border-radius:10px;padding:16px;background:var(--color-bg-secondary);position:relative;display:flex;flex-direction:column;gap:10px;';

    const activeWorkflow = (workflowType === 'retainer') ? 'retainer' : 'campaign';
    const filteredTypes = subtaskTypes.filter(t => t.workflow_type === activeWorkflow);

    const batchName = existingData && existingData.name ? existingData.name : `Batch ${batchIndex}`;
    const batchDeadline = existingData && existingData.deadline ? existingData.deadline : '';

    let typesHtml = '';
    if (filteredTypes.length > 0) {
        typesHtml = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;">`;
        filteredTypes.forEach(type => {
            let countValue = 0;
            let dateValue = '';
            let savedItemDates = null;

            if (existingData && existingData.post_types && existingData.post_types[type.id]) {
                const ptData = existingData.post_types[type.id];
                if (typeof ptData === 'object') {
                    countValue = ptData.count || 0;
                    dateValue = ptData.deadline || '';
                    savedItemDates = ptData.dates || null;
                } else {
                    countValue = ptData || 0;
                }
            }

            typesHtml += `
                <div style="background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:10px 12px;display:flex;flex-direction:column;gap:4px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                        <span style="font-size:11px;font-weight:700;color:var(--color-text-primary);">${type.name}</span>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <span style="font-size:10px;font-weight:600;color:var(--color-text-secondary);">Qty:</span>
                            <input type="number" id="qty-input-${batchIndex}-${type.id}" name="batches[${batchIndex}][post_types][${type.id}][count]" min="0" max="200" value="${countValue}"
                                style="width:46px;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:6px;padding:3px 5px;font-size:12px;font-weight:700;color:var(--color-text-primary);text-align:center;outline:none;"
                                onfocus="this.select()"
                                oninput="renderItemDates(this, ${batchIndex}, ${type.id}, '${type.name.replace(/'/g, "\\'")}')">
                        </div>
                    </div>
                    <div id="item-dates-${batchIndex}-${type.id}" style="display:none;"></div>
                </div>
            `;
        });
        typesHtml += `</div>`;
    } else {
        const postsCount = existingData && existingData.posts_count ? (existingData.posts_count.count || existingData.posts_count) : 0;
        typesHtml = `
            <div style="display:flex;align-items:center;gap:14px;background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:10px 14px;">
                <div>
                    <label style="font-size:10px;font-weight:700;color:var(--color-text-secondary);display:block;margin-bottom:4px;text-transform:uppercase;">Posts Count</label>
                    <input type="number" name="batches[${batchIndex}][posts_count][count]" min="0" max="200" value="${postsCount}" class="f-input" style="max-width:90px;padding:4px 8px;font-size:12px;font-weight:700;">
                </div>
            </div>
        `;
    }

    card.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="flex:1;min-width:220px;display:flex;align-items:center;gap:8px;">
                <span style="font-size:11px;font-weight:800;color:var(--color-text-secondary);background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);width:22px;height:22px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;" class="batch-number-badge">1</span>
                <input type="text" name="batches[${batchIndex}][name]" value="${batchName}" placeholder="Enter Batch Name..." required
                    style="width:100%;max-width:220px;background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:6px 12px;font-size:12px;font-weight:700;color:var(--color-text-primary);outline:none;">
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <span style="font-size:10px;font-weight:700;color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:0.04em;">Batch Due:</span>
                    <input type="date" name="batches[${batchIndex}][deadline]" value="${batchDeadline}" min="${new Date().toISOString().split('T')[0]}"
                        style="background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:4px 8px;font-size:11px;font-weight:600;color:var(--color-text-primary);outline:none;">
                </div>
                <button type="button" onclick="removeBatchCard(${batchIndex})" style="background:none;border:none;color:#ef4444;font-size:11px;font-weight:700;cursor:pointer;padding:4px;display:inline-flex;align-items:center;gap:2px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Remove
                </button>
            </div>
        </div>
        ${typesHtml}
    `;

    container.appendChild(card);

    if (filteredTypes.length > 0) {
        filteredTypes.forEach(type => {
            const qtyInput = card.querySelector(`#qty-input-${batchIndex}-${type.id}`);
            if (qtyInput) {
                let savedDates = null;
                let savedAssignees = null;
                if (existingData && existingData.post_types && existingData.post_types[type.id] && typeof existingData.post_types[type.id] === 'object') {
                    savedDates = existingData.post_types[type.id].dates || null;
                    savedAssignees = existingData.post_types[type.id].assignees || null;
                }
                renderItemDates(qtyInput, batchIndex, type.id, type.name, savedDates, savedAssignees);
            }
        });
    }

    reindexBatchNumbers();
}

function renderItemDates(input, batchIdx, typeId, typeName, savedDates = null, savedAssignees = null) {
    const qty = parseInt(input.value) || 0;
    const container = document.getElementById(`item-dates-${batchIdx}-${typeId}`);
    if (!container) return;

    const existingDates = savedDates || {};
    if (!savedDates) {
        container.querySelectorAll('input[type="date"]').forEach((el) => {
            const match = el.name.match(/\[dates\]\[(\d+)\]/);
            if (match && match[1]) {
                existingDates[match[1]] = el.value;
            }
        });
    }

    const existingAssignees = savedAssignees || {};
    if (!savedAssignees) {
        container.querySelectorAll('select[name*="[assignees]"]').forEach((el) => {
            const match = el.name.match(/\[assignees\]\[(\d+)\]/);
            if (match && match[1]) {
                existingAssignees[match[1]] = el.value;
            }
        });
    }

    if (qty <= 0) {
        container.innerHTML = '';
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.gap = '6px';
    container.style.marginTop = '6px';
    container.style.paddingTop = '6px';
    container.style.borderTop = '1px dashed var(--color-border-primary)';

    const todayStr = new Date().toISOString().split('T')[0];
    let html = '';
    for (let i = 1; i <= qty; i++) {
        const dateVal = existingDates[i] || '';
        const currentAssigneeVal = existingAssignees[i] || '';
        const label = (qty === 1) ? typeName : `${typeName} ${i}`;

        let optionsHtml = '';
        allTeamMembers.forEach(m => {
            const isSel = (String(currentAssigneeVal) === String(m.id)) ? 'selected' : '';
            optionsHtml += `<option value="${m.id}" ${isSel}>${m.name} (${m.role})</option>`;
        });

        html += `
            <div style="background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);border-radius:6px;padding:6px 8px;display:flex;flex-direction:column;gap:5px;">
                <div style="font-size:10px;font-weight:700;color:var(--color-text-primary);letter-spacing:-0.01em;">${label}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;align-items:center;">
                    <div>
                        <span style="font-size:9px;font-weight:600;color:var(--color-text-secondary);display:block;margin-bottom:2px;">Due:</span>
                        <input type="date" name="batches[${batchIdx}][post_types][${typeId}][dates][${i}]" value="${dateVal}" min="${todayStr}"
                            style="width:100%;box-sizing:border-box;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:5px;padding:2px 5px;font-size:10.5px;font-weight:600;color:var(--color-text-primary);outline:none;">
                    </div>
                    <div>
                        <span style="font-size:9px;font-weight:600;color:var(--color-text-secondary);display:block;margin-bottom:2px;">Assignee:</span>
                        <select name="batches[${batchIdx}][post_types][${typeId}][assignees][${i}]"
                            style="width:100%;box-sizing:border-box;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:5px;padding:2px 4px;font-size:10.5px;font-weight:600;color:var(--color-text-primary);outline:none;text-overflow:ellipsis;">
                            <option value="">-- Project Default --</option>
                            ${optionsHtml}
                        </select>
                    </div>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;
}

function removeBatchCard(id) {
    const card = document.getElementById(`batch-card-${id}`);
    if (card) {
        card.remove();
        reindexBatchNumbers();
    }
}

function reindexBatchNumbers() {
    const cards = document.querySelectorAll('.batch-card');
    cards.forEach((card, index) => {
        const badge = card.querySelector('.batch-number-badge');
        if (badge) {
            badge.textContent = index + 1;
        }

        const removeBtn = card.querySelector('button[onclick^="removeBatchCard"]');
        if (removeBtn) {
            removeBtn.style.display = (cards.length === 1) ? 'none' : 'inline-flex';
        }
    });
}

function setWorkflow(type) {
    document.getElementById('workflow_type').value = type;
    ['retainer','campaign','pitch'].forEach(t =>
        document.getElementById('option-'+t).classList.toggle('active', t === type)
    );

    const batchesSection = document.getElementById('project-batches-section');
    const container = document.getElementById('batches-container');
    if (container) container.innerHTML = '';
    batchIndex = 0;
    
    if (type === 'campaign' || type === 'pitch') {
        if (batchesSection) batchesSection.style.display = 'none';
    } else {
        if (batchesSection) batchesSection.style.display = 'block';
        // Only auto-add an empty batch if there are NO old batches
        if (!oldBatches || Object.keys(oldBatches).length === 0) {
            addBatchCard();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Determine the workflow type (from old input, or default to retainer)
    const oldWorkflowType = "{{ old('workflow_type', request('workflow_type', request('type', 'retainer'))) }}";
    setWorkflow(oldWorkflowType);

    // If there are old batches from a validation error and workflow is retainer, reconstruct them
    if (oldWorkflowType === 'retainer' && oldBatches && Object.keys(oldBatches).length > 0) {
        const container = document.getElementById('batches-container');
        if (container) container.innerHTML = '';
        batchIndex = 0;
        for (const [key, batch] of Object.entries(oldBatches)) {
            addBatchCard(batch);
        }
    }

    // Initialize CRM Jobs dropdown showing all available jobs
    refreshCrmJobsDropdown();

    document.getElementById('createProjectForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('createProjectBtn');
        const overlay = document.getElementById('pageLoadingOverlay');
        setTimeout(() => { btn.disabled = true; }, 10);
        btn.style.pointerEvents = 'none';
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:loopSpin 1s linear infinite;"><path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg>Creating…';
        overlay.style.display = 'flex';
    });
});
</script>
</x-layout>
