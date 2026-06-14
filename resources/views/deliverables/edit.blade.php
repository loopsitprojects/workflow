<x-layout title="Edit: {{ $deliverable->title }}">
<style>
.f-wrap{max-width:640px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;}
.f-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);position:relative;}
.f-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:7px;}
.f-label.blue{color:#3b82f6;}
.f-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s;-webkit-appearance:none;appearance:none;}
.f-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
.f-input::placeholder{color:var(--color-text-secondary);opacity:0.45;}
.f-title{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.f-footer{background:var(--color-bg-secondary);padding:16px 24px;display:flex;justify-content:space-between;gap:8px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-cancel{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.12s;}
.btn-cancel:hover{background:var(--color-bg-secondary);color:var(--color-text-primary);}
.btn-submit{padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,85,212,0.25);transition:all 0.12s;}
.btn-submit:hover{background:#0044aa;}
.btn-danger{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;color:#ef4444;background:transparent;border:1.5px solid rgba(239,68,68,0.2);cursor:pointer;transition:all 0.12s;}
.btn-danger:hover{background:rgba(239,68,68,0.05);}
.f-close{position:absolute;top:16px;right:20px;width:30px;height:30px;border-radius:8px;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);display:flex;align-items:center;justify-content:center;color:var(--color-text-secondary);text-decoration:none;transition:all 0.15s;}
.f-close:hover{color:var(--color-text-primary);background:var(--color-border-primary);}
.ref-toggle{display:flex;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);border-radius:8px;padding:3px;width:fit-content;margin-bottom:10px;}
.ref-btn{border:none;background:transparent;padding:5px 12px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-secondary);border-radius:6px;cursor:pointer;transition:all 0.15s;}
.ref-btn.active{background:var(--color-bg-primary);color:#0055D4;box-shadow:0 2px 6px rgba(0,0,0,0.06);}
textarea.f-input{resize:vertical;min-height:90px;line-height:1.6;}
</style>

<nav style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin:0 auto 12px;max-width:640px;">
    <a href="{{ route('projects.show', $deliverable->project_id) }}" style="text-decoration:none;color:inherit;">{{ $deliverable->project->name ?? 'Project' }}</a>
    <span style="opacity:0.4;">/</span>
    <span style="color:var(--color-text-primary);">Edit Deliverable</span>
</nav>
<div class="f-wrap" x-data="{ showDeleteModal: false }">
    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div class="bg-white dark:bg-[#111827] rounded-2xl border border-gray-100 dark:border-white/[0.08] shadow-2xl p-6 w-full max-w-sm">
            <div class="w-11 h-11 bg-red-50 dark:bg-red-500/10 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white mb-1">Delete Deliverable</h3>
            <p class="text-[13px] text-gray-500 dark:text-slate-400 mb-5">Are you sure you want to permanently delete <strong class="text-gray-800 dark:text-white">{{ $deliverable->title }}</strong>? This cannot be undone.</p>
            <div class="flex gap-2 justify-end">
                <button @click="showDeleteModal = false" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-white/[0.06] hover:bg-gray-200 dark:hover:bg-white/[0.10] transition-colors">Cancel</button>
                <button @click="document.getElementById('delete-form').submit()" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">Delete</button>
            </div>
        </div>
    </div>

    <form action="{{ route('deliverables.update', $deliverable) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Title --}}
        <div class="f-section">
            <a href="{{ route('projects.show', $deliverable->project_id) }}" class="f-close" title="Back to project">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
            <label class="f-label blue">Deliverable Title</label>
            <input type="text" name="title" required class="f-title"
                   value="{{ old('title', $deliverable->title) }}">
            @error('title')<p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:8px;">{{ $message }}</p>@enderror
        </div>

        {{-- Project + Post Type --}}
        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Project</label>
                    <select name="project_id" required class="f-input">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $deliverable->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->brand->name }} / {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')<p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:6px;">{{ $message }}</p>@enderror
                </div>
                <div>
                    @php
                        $currentWorkflow = $deliverable->project->workflow_type ?? 'retainer';
                        $filteredTypes   = $subtaskTypes->where('workflow_type', $currentWorkflow);
                        $currentExists   = $filteredTypes->contains('name', $deliverable->task_type);
                    @endphp
                    <label class="f-label">Post Type</label>
                    <select name="task_type" required class="f-input">
                        @if(!$currentExists && $deliverable->task_type)
                            <option value="{{ $deliverable->task_type }}" selected>{{ $deliverable->task_type }} (Legacy)</option>
                        @endif
                        @foreach($filteredTypes as $type)
                            <option value="{{ $type->name }}" {{ old('task_type', $deliverable->task_type) == $type->name ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Progress + Stage --}}
        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Progress</label>
                    <select name="progress_percent" id="progress_percent" required class="f-input">
                        <option value="0"   {{ old('progress_percent', $deliverable->progress_percent) == 0   ? 'selected' : '' }}>To commence (0%)</option>
                        <option value="20"  {{ old('progress_percent', $deliverable->progress_percent) == 20  ? 'selected' : '' }}>In progress (20%)</option>
                        <option value="40"  {{ old('progress_percent', $deliverable->progress_percent) == 40  ? 'selected' : '' }}>Done (40%)</option>
                        <option value="60"  {{ old('progress_percent', $deliverable->progress_percent) == 60  ? 'selected' : '' }}>Revisions (60%)</option>
                        <option value="80"  {{ old('progress_percent', $deliverable->progress_percent) == 80  ? 'selected' : '' }}>Ready (80%)</option>
                        <option value="100" {{ old('progress_percent', $deliverable->progress_percent) == 100 ? 'selected' : '' }}>Closed (100%)</option>
                    </select>
                </div>
                <div>
                    <label class="f-label">Approval Stage</label>
                    <select name="approval_stage" class="f-input">
                        @foreach(\App\Models\Deliverable::STAGES as $stage)
                            <option value="{{ $stage }}" {{ old('approval_stage', $deliverable->approval_stage) == $stage ? 'selected' : '' }}>{{ $stage }}</option>
                        @endforeach
                        @foreach(\App\Models\Deliverable::CAMPAIGN_STAGES as $stage)
                            @if(!in_array($stage, \App\Models\Deliverable::STAGES))
                                <option value="{{ $stage }}" {{ old('approval_stage', $deliverable->approval_stage) == $stage ? 'selected' : '' }}>{{ $stage }} (Campaign)</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Approver + Writer + Revisions --}}
        <div class="f-section">
            <div class="f-grid">
                <div>
                    <label class="f-label">Writer / Assignee</label>
                    <select name="writer_id" class="f-input">
                        <option value="">— Unassigned —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('writer_id', $deliverable->writer_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="f-label">Approver</label>
                    <select name="approver_id" class="f-input">
                        <option value="">— No approver —</option>
                        @foreach($approvers as $approver)
                            <option value="{{ $approver->id }}" {{ old('approver_id', $deliverable->approver_id) == $approver->id ? 'selected' : '' }}>{{ $approver->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="f-grid" style="margin-top:16px;">
                <div>
                    <label class="f-label">Revision Count</label>
                    <input type="number" name="revisions" min="0" class="f-input"
                           value="{{ old('revisions', $deliverable->revisions) }}">
                </div>
                <div>
                    <label class="f-label">Final Design Link</label>
                    <input type="url" name="final_designs" placeholder="https://…" class="f-input"
                           value="{{ old('final_designs', $deliverable->final_designs) }}">
                </div>
            </div>
        </div>

        {{-- Reference --}}
        <div class="f-section">
            <label class="f-label">Reference</label>
            <div class="ref-toggle">
                <button type="button" class="ref-btn {{ !$deliverable->reference_file ? 'active' : '' }}" onclick="toggleRef('link')">Link</button>
                <button type="button" class="ref-btn {{ $deliverable->reference_file ? 'active' : '' }}" onclick="toggleRef('upload')">Upload</button>
            </div>
            <div id="ref-link" style="display:{{ !$deliverable->reference_file ? 'block' : 'none' }};">
                <input type="url" name="reference" placeholder="https://…" class="f-input"
                       value="{{ old('reference', $deliverable->reference) }}">
            </div>
            <div id="ref-upload" style="display:{{ $deliverable->reference_file ? 'block' : 'none' }};" x-data="{ removing: false }">
                @if($deliverable->reference_file)
                    <div x-show="!removing" style="display:flex;align-items:center;gap:10px;padding:8px 12px;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);border-radius:8px;margin-bottom:10px;">
                        <a href="{{ $deliverable->reference_file }}" target="_blank">
                            <img src="{{ $deliverable->reference_file }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" onerror="this.style.display='none'">
                        </a>
                        <span style="flex:1;font-size:11px;font-weight:600;color:var(--color-text-secondary);">Current reference image</span>
                        <button type="button" @click="removing = true"
                            style="padding:4px 10px;font-size:10px;font-weight:700;color:#ef4444;background:transparent;border:1.5px solid rgba(239,68,68,0.3);border-radius:6px;cursor:pointer;">
                            Remove
                        </button>
                    </div>
                    <div x-show="removing" style="padding:8px 12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:8px;margin-bottom:10px;font-size:12px;font-weight:600;color:#ef4444;display:none;">
                        Reference image will be removed on save.
                        <button type="button" @click="removing = false" style="margin-left:8px;font-size:11px;color:var(--color-text-secondary);background:none;border:none;cursor:pointer;font-weight:600;">Undo</button>
                    </div>
                    <input type="hidden" name="delete_reference_file" :value="removing ? '1' : '0'">
                @endif
                <div x-show="!removing ?? true">
                    <label style="display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:6px;">
                        {{ $deliverable->reference_file ? 'Upload replacement' : 'Upload reference image' }}
                    </label>
                    <input type="file" name="reference_file" accept="image/*,application/pdf" class="f-input" style="padding:9px 14px;cursor:pointer;">
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="f-section" style="border-bottom:none;">
            <label class="f-label">Brief & Objectives</label>
            <textarea name="description" rows="4" placeholder="Outline the success criteria…" class="f-input">{{ old('description', $deliverable->description) }}</textarea>
        </div>

        <input type="hidden" name="status" id="status_mapping" value="{{ $deliverable->status }}">

        {{-- Footer --}}
        <div class="f-footer">
            <button type="button" @click="showDeleteModal = true" class="btn-danger">Delete Deliverable</button>
            <div style="display:flex;gap:10px;">
                <a href="{{ url()->previous() }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Save Changes</button>
            </div>
        </div>
    </form>

    <form id="delete-form" action="{{ route('deliverables.destroy', $deliverable) }}" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
</div>

<script>
function toggleRef(type) {
    document.getElementById('ref-link').style.display    = type === 'link'   ? 'block' : 'none';
    document.getElementById('ref-upload').style.display  = type === 'upload' ? 'block' : 'none';
    document.querySelectorAll('.ref-btn').forEach((b,i) => b.classList.toggle('active', (type==='link') === (i===0)));
}
document.getElementById('progress_percent').addEventListener('change', function() {
    const map = {0:'To Do', 20:'In Progress', 40:'Review', 100:'Done'};
    document.getElementById('status_mapping').value = map[this.value] || 'In Progress';
});
</script>
</x-layout>
