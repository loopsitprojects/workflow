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
    <form id="createProjectForm" action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="brand_id" value="{{ request('brand_id', $brands->first()->id ?? '') }}">
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
                    <label class="f-label">Job Number</label>
                    <input type="text" name="job_number" placeholder="e.g. JN-2025-001" class="f-input" value="{{ old('job_number') }}">
                </div>
                <div>
                    <label class="f-label">Due Date</label>
                    <input type="date" name="deadline" class="f-input" value="{{ old('deadline') }}">
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

        {{-- Brief --}}
        <div class="f-section">
            <label class="f-label">Project Brief</label>
            <div x-data="quillEditor(`{!! old('description') !!}`)" style="margin-bottom: 12px;">
                <textarea name="description" x-model="content" style="display:none;"></textarea>
                <div x-ref="editor" class="f-input" style="min-height: 120px; border-top-left-radius: 0; border-top-right-radius: 0; padding: 0;"></div>
            </div>
            <div style="margin-top:12px;">
                <label class="f-label">Brief Document <span style="opacity:0.5;font-weight:400;">(PDF, DOC, PNG, JPG · max 10MB)</span></label>
                <input type="file" name="brief_file" class="f-input" style="padding:7px 12px;cursor:pointer;">
            </div>
            <div style="margin-top:16px;">
                <label class="f-label">Deliverables by Post Type <span style="opacity:0.5;font-weight:400;">(auto-generates deliverable slots)</span></label>

                @php
                    $retainerTypes = $subtaskTypes->where('workflow_type', 'retainer')->values();
                    $campaignTypes = $subtaskTypes->where('workflow_type', 'campaign')->values();
                    $allTypes = $subtaskTypes;
                @endphp

                {{-- Post type grid (shown when workflow type has subtask types) --}}
                <div id="post-type-grid" style="margin-top:8px;">
                    {{-- Retainer types --}}
                    @if($retainerTypes->isNotEmpty())
                    <div class="post-type-section" data-workflow="retainer" style="display:block;">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;">
                            @foreach($retainerTypes as $type)
                            <div style="display:flex;align-items:center;justify-content:space-between;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:8px 12px;">
                                <span style="font-size:12px;font-weight:600;color:var(--color-text-primary);">{{ $type->name }}</span>
                                <input type="number" name="post_type_counts[{{ $type->id }}]" min="0" max="200" value="{{ old('post_type_counts.'.$type->id, 0) }}"
                                    style="width:52px;background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);border-radius:6px;padding:4px 6px;font-size:12px;font-weight:700;color:var(--color-text-primary);text-align:center;outline:none;"
                                    onfocus="this.select()">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Campaign/Pitch types --}}
                    @if($campaignTypes->isNotEmpty())
                    <div class="post-type-section" data-workflow="campaign pitch" style="display:none;">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;">
                            @foreach($campaignTypes as $type)
                            <div style="display:flex;align-items:center;justify-content:space-between;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:8px 12px;">
                                <span style="font-size:12px;font-weight:600;color:var(--color-text-primary);">{{ $type->name }}</span>
                                <input type="number" name="post_type_counts[{{ $type->id }}]" min="0" max="200" value="{{ old('post_type_counts.'.$type->id, 0) }}"
                                    style="width:52px;background:var(--color-bg-primary);border:1.5px solid var(--color-border-primary);border-radius:6px;padding:4px 6px;font-size:12px;font-weight:700;color:var(--color-text-primary);text-align:center;outline:none;"
                                    onfocus="this.select()">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Fallback: no subtask types defined --}}
                    @if($allTypes->isEmpty())
                    <input type="number" name="posts_count" min="0" max="200" placeholder="0" class="f-input" style="max-width:140px;" value="{{ old('posts_count', 0) }}">
                    @endif
                </div>
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
function setWorkflow(type) {
    document.getElementById('workflow_type').value = type;
    ['retainer','campaign','pitch'].forEach(t =>
        document.getElementById('option-'+t).classList.toggle('active', t === type)
    );

    // Show/hide post type sections based on workflow
    document.querySelectorAll('.post-type-section').forEach(section => {
        const workflows = section.dataset.workflow.split(' ');
        const visible = workflows.includes(type);
        section.style.display = visible ? 'block' : 'none';
        // Zero out hidden section inputs so they don't submit
        if (!visible) {
            section.querySelectorAll('input[type="number"]').forEach(i => i.value = 0);
        }
    });
}

// Set initial state on page load (retainer is default)
document.addEventListener('DOMContentLoaded', () => {
    setWorkflow('retainer');

    document.getElementById('createProjectForm').addEventListener('submit', function() {
        const btn = document.getElementById('createProjectBtn');
        const overlay = document.getElementById('pageLoadingOverlay');
        btn.disabled = true;
        btn.innerHTML = '<svg style="width:14px;height:14px;animation:loopSpin 0.75s linear infinite;display:inline-block;vertical-align:middle;margin-right:6px;" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>Creating…';
        overlay.style.display = 'flex';
    });
});
</script>
</x-layout>
