<x-layout title="Edit {{ $project->name }}">
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
.f-footer{background:var(--color-bg-secondary);padding:14px 24px;display:flex;justify-content:space-between;gap:8px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-c{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.12s;}
.btn-c:hover{background:var(--color-bg-secondary);}
.btn-s{padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,85,212,0.25);transition:all 0.12s;}
.btn-s:hover{background:#0044aa;}
.btn-d{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;color:#ef4444;background:transparent;border:1.5px solid rgba(239,68,68,0.2);cursor:pointer;transition:all 0.12s;}
.btn-d:hover{background:rgba(239,68,68,0.05);}
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
.mp-wrap{border:1.5px solid var(--color-border-primary);border-radius:8px;overflow:hidden;}
.mp-search{display:flex;align-items:center;gap:8px;padding:9px 12px;border-bottom:1px solid var(--color-border-primary);background:var(--color-bg-secondary);}
.mp-search input{flex:1;background:transparent;border:none;outline:none;font-size:12px;color:var(--color-text-primary);}
.mp-search input::placeholder{color:var(--color-text-secondary);opacity:0.5;}
.mp-list{max-height:220px;overflow-y:auto;}
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
    <a href="{{ route('brands.show', $project->brand) }}" style="text-decoration:none;color:inherit;">{{ $project->brand->name }}</a>
    <span style="opacity:0.35;">/</span>
    <a href="{{ route('projects.show', $project) }}" style="text-decoration:none;color:inherit;">{{ $project->name }}</a>
    <span style="opacity:0.35;">/</span>
    <span style="color:var(--color-text-primary);">Settings</span>
</nav>

<div class="f-wrap" x-data="{ showDeleteModal: false }">
    @if(auth()->user()->isAdmin())
    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-100 dark:border-white/[0.08] shadow-2xl p-6 w-full max-w-sm">
            <div class="w-11 h-11 bg-red-50 dark:bg-red-500/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white mb-1">Delete Project</h3>
            <p class="text-[13px] text-gray-500 dark:text-slate-400 mb-5">Are you sure you want to permanently delete <strong class="text-gray-800 dark:text-white">{{ $project->name }}</strong>? All deliverables will be removed. This cannot be undone.</p>
            <div class="flex gap-2 justify-end">
                <button @click="showDeleteModal = false" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-white/[0.06] hover:bg-gray-200 dark:hover:bg-white/[0.10] transition-colors">Cancel</button>
                <button @click="document.getElementById('delete-form').submit()" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">Delete</button>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('projects.update', $project) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="brand_id" value="{{ $project->brand_id }}">
        <input type="hidden" name="priority" value="{{ old('priority', $project->priority) }}">
        <input type="hidden" name="type" value="{{ $project->type }}">

        {{-- Title --}}
        <div class="f-section">
            <label class="f-label blue">Project Title</label>
            <input type="text" name="name" required placeholder="Project title…" class="f-title" value="{{ old('name', $project->name) }}">
            @error('name')<p style="color:#ef4444;font-size:11px;margin-top:6px;">{{ $message }}</p>@enderror
        </div>

        {{-- Job Number + Due Date --}}
        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Job Number</label>
                    <input type="text" name="job_number" placeholder="e.g. JN-2025-001" class="f-input" value="{{ old('job_number', $project->job_number) }}">
                </div>
                <div>
                    <label class="f-label">Due Date</label>
                    <input type="date" name="deadline" class="f-input" value="{{ old('deadline', $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('Y-m-d') : '') }}">
                </div>
            </div>
        </div>

        {{-- Project Type --}}
        <div class="f-section">
            <label class="f-label">Project Type</label>
            <div class="type-group">
                <div class="type-opt {{ $project->workflow_type === 'retainer' ? 'active' : '' }}" id="option-retainer" onclick="setWorkflow('retainer')">
                    <h4>Retainer</h4>
                    <p>7-stage approval flow</p>
                </div>
                <div class="type-opt {{ $project->workflow_type === 'campaign' ? 'active' : '' }}" id="option-campaign" onclick="setWorkflow('campaign')">
                    <h4>Campaign</h4>
                    <p>4-stage initiative</p>
                </div>
                <div class="type-opt {{ $project->workflow_type === 'pitch' ? 'active' : '' }}" id="option-pitch" onclick="setWorkflow('pitch')">
                    <h4>Pitch</h4>
                    <p>Business proposal</p>
                </div>
            </div>
            <input type="hidden" name="workflow_type" id="workflow_type" value="{{ old('workflow_type', $project->workflow_type) }}">
        </div>

        {{-- Status --}}
        <div class="f-section">
            <label class="f-label">Status</label>
            <select name="status" class="f-input" style="max-width:240px;">
                @foreach(['Not Started','In Progress','On Hold','Completed'] as $s)
                    <option value="{{ $s }}" {{ old('status', $project->status) == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>

        {{-- Brief --}}
        <div class="f-section">
            <label class="f-label">Project Brief</label>
            <textarea name="description" rows="3" placeholder="Core objectives and creative vision…" class="f-input">{{ old('description', $project->description) }}</textarea>
            <div style="margin-top:12px;">
                <label class="f-label">Brief Document <span style="opacity:0.5;font-weight:400;">(PDF, DOC, PNG, JPG · max 10MB)</span></label>
                @if($project->brief_file_path)
                    <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(59,130,246,0.04);border:1px solid rgba(59,130,246,0.15);border-radius:7px;margin-bottom:8px;">
                        <svg width="12" height="12" fill="none" stroke="#3b82f6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <a href="{{ asset('storage/' . $project->brief_file_path) }}" target="_blank" style="font-size:12px;font-weight:600;color:#3b82f6;text-decoration:none;flex:1;">{{ basename($project->brief_file_path) }}</a>
                        <span style="font-size:10px;color:var(--color-text-secondary);">Current</span>
                    </div>
                @endif
                <input type="file" name="brief_file" class="f-input" style="padding:7px 12px;cursor:pointer;">
            </div>
        </div>

        <div class="f-footer">
            @if(auth()->user()->isAdmin())
            <button type="button" @click="showDeleteModal = true" class="btn-d">Delete Project</button>
            @endif
            <div style="display:flex;gap:8px;">
                <a href="{{ url()->previous() }}" class="btn-c">Cancel</a>
                <button type="submit" class="btn-s">Save Changes</button>
            </div>
        </div>
    </form>

    @if(auth()->user()->isAdmin())
    <form id="delete-form" action="{{ route('projects.destroy', $project) }}" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
    @endif
</div>

<script>
function setWorkflow(type) {
    document.getElementById('workflow_type').value = type;
    ['retainer','campaign','pitch'].forEach(t =>
        document.getElementById('option-'+t).classList.toggle('active', t === type)
    );
}
</script>
</x-layout>
