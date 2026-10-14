<x-layout title="Edit: {{ $deliverable->title }}">
<style>
.f-wrap{max-width:640px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:visible;font-family:'Inter',sans-serif;}
.f-wrap > form > .f-section:first-child { border-top-left-radius: 13px; border-top-right-radius: 13px; }
.f-footer { border-bottom-left-radius: 13px; border-bottom-right-radius: 13px; }
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

/* Searchable User Combobox Component */
.searchable-user-wrapper {
    position: relative;
    width: 100%;
}
.searchable-select-trigger {
    width: 100%;
    background: var(--color-bg-secondary);
    border: 1.5px solid var(--color-border-primary);
    border-radius: 8px;
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

<nav style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin:0 auto 12px;max-width:640px;">
    <a href="{{ route('projects.show', $deliverable->project_id) }}" style="text-decoration:none;color:inherit;">{{ $deliverable->project->name ?? 'Project' }}</a>
    <span style="opacity:0.4;">/</span>
    <span style="color:var(--color-text-primary);">Edit Deliverable</span>
</nav>
<div class="f-wrap" x-data="deliverableEditForm()">
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
                    <select name="project_id" required class="f-input" @change="updateProject($event.target.value)">
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
                    <label class="f-label">Priority</label>
                    <select name="priority" class="f-input">
                        <option value="High Priority" {{ old('priority', $deliverable->priority) == 'High Priority' ? 'selected' : '' }}>High Priority</option>
                        <option value="Medium" {{ old('priority', $deliverable->priority) == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Low Priority" {{ old('priority', $deliverable->priority) == 'Low Priority' ? 'selected' : '' }}>Low Priority</option>
                    </select>
                </div>
            </div>
            <div class="f-grid" style="margin-top:16px;">
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
                    <div x-data="searchableUserSelect({
                        getUsers: () => availableUsers,
                        selectedId: selectedWriterId,
                        onSelect: (val) => { selectedWriterId = val }
                    })" 
                         class="searchable-user-wrapper" 
                         @click.outside="open = false">
                        
                        <input type="hidden" name="writer_id" :value="selectedId">

                        {{-- Trigger Box --}}
                        <div class="searchable-select-trigger"
                             :class="{ 'is-open': open }"
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
                                        <span>— Unassigned —</span>
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
                                {{-- Unassigned Item (visible when no search query or matching "unassigned") --}}
                                <div x-show="!search || 'unassigned'.includes(search.toLowerCase())"
                                     class="searchable-item"
                                     :class="{
                                         'is-highlighted': highlightedIndex === -1,
                                         'is-selected': !selectedId
                                     }"
                                     @click="clear($event)">
                                    <div style="display:flex;align-items:center;gap:9px;min-width:0;flex:1;">
                                        <span class="user-avatar-pill" style="background:var(--color-bg-secondary);color:var(--color-text-secondary);border:1px dashed var(--color-border-primary);">&oslash;</span>
                                        <span class="user-name-text" style="color:var(--color-text-secondary);">— Unassigned —</span>
                                    </div>
                                    <template x-if="!selectedId">
                                        <svg width="15" height="15" fill="none" stroke="#0055D4" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </template>
                                </div>

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
                                <div x-show="filteredUsers.length === 0 && search && !'unassigned'.includes(search.toLowerCase())" class="searchable-empty">
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
                    <label class="f-label">Deadline</label>
                    <input type="date" name="deadline" class="f-input" min="{{ date('Y-m-d') }}"
                           value="{{ old('deadline', $deliverable->deadline ? \Carbon\Carbon::parse($deliverable->deadline)->format('Y-m-d') : '') }}">
                </div>
                <div>
                    <label class="f-label">Revision Count</label>
                    <input type="number" name="revisions" min="0" class="f-input"
                           value="{{ old('revisions', $deliverable->revisions) }}">
                </div>
            </div>
            <div class="f-grid" style="margin-top:16px;">
                <div style="grid-column: span 2;">
                    <label class="f-label">Final Design Link</label>
                    <input type="url" name="final_designs" placeholder="https://…" class="f-input"
                           value="{{ old('final_designs', $deliverable->final_designs) }}">
                </div>
            </div>
        </div>

        {{-- Reference --}}
        <div class="f-section">
            <label class="f-label">Reference <span style="opacity:0.6;font-weight:400;font-size:11px;">(Add reference link, reference image/file, or both)</span></label>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; align-items:start;">
                {{-- Reference Link --}}
                <div>
                    <label style="display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:6px;">Reference Link / URL</label>
                    <input type="url" name="reference" placeholder="https://…" class="f-input"
                           value="{{ old('reference', $deliverable->reference) }}">
                </div>

                {{-- Reference File --}}
                <div x-data="{ removing: false }">
                    <label style="display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:6px;">Reference Image / File</label>
                    @php $editRefFiles = $deliverable->getReferenceFilesArray(); @endphp
                    @if(!empty($editRefFiles))
                        <div x-show="!removing" style="display:flex;flex-direction:column;gap:8px;padding:8px 12px;background:var(--color-bg-secondary);border:1px solid var(--color-border-primary);border-radius:8px;margin-bottom:10px;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                @foreach($editRefFiles as $erf)
                                    <a href="{{ $erf }}" target="_blank" style="display:inline-block;">
                                        @if(preg_match('/\.(mp4|webm|ogg|mov)(?:$|\?)/i', $erf))
                                            <video src="{{ $erf }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" preload="metadata"></video>
                                        @elseif(preg_match('/\.pdf(?:$|\?)/i', $erf))
                                            <div style="width:40px;height:40px;border-radius:6px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;color:#ef4444;font-size:10px;font-weight:800;border:1px solid rgba(239,68,68,0.25);">PDF</div>
                                        @else
                                            <img src="{{ $erf }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" onerror="this.style.display='none'">
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:11px;font-weight:600;color:var(--color-text-secondary);">Current reference ({{ count($editRefFiles) }} {{ \Illuminate\Support\Str::plural('file', count($editRefFiles)) }})</span>
                                @if($deliverable->can_remove_reference)
                                <button type="button" @click="removing = true"
                                    style="padding:4px 10px;font-size:10px;font-weight:700;color:#ef4444;background:transparent;border:1.5px solid rgba(239,68,68,0.3);border-radius:6px;cursor:pointer;">
                                    Remove
                                </button>
                                @endif
                            </div>
                        </div>
                        @if($deliverable->can_remove_reference)
                        <div x-show="removing" style="padding:8px 12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.2);border-radius:8px;margin-bottom:10px;font-size:12px;font-weight:600;color:#ef4444;display:none;">
                            Files will be removed on save.
                            <button type="button" @click="removing = false" style="margin-left:8px;font-size:11px;color:var(--color-text-secondary);background:none;border:none;cursor:pointer;font-weight:600;">Undo</button>
                        </div>
                        <input type="hidden" name="delete_reference_file" :value="removing ? '1' : '0'">
                        @endif
                    @endif
                    <div x-show="!removing ?? true">
                        <input type="file" name="reference_file" accept="image/*,video/*,application/pdf" class="f-input" style="padding:9px 14px;cursor:pointer;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="f-section">
            <label class="f-label">Deliverable Brief</label>
            <div x-data="quillEditor({{ json_encode(old('description', $deliverable->description)) }})" style="margin-bottom: 12px;">
                <textarea name="description" x-model="content" style="display:none;"></textarea>
                <div x-ref="editor" class="f-input" style="min-height: 120px; border-top-left-radius: 0; border-top-right-radius: 0; padding: 0;"></div>
            </div>
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
function deliverableEditForm() {
    return {
        showDeleteModal: false,
        workflowType: '{{ $deliverable->project->workflow_type ?? 'retainer' }}',
        projectWorkflows: @json($projects->pluck('workflow_type', 'id')),
        selectedWriterId: '{{ (string)old('writer_id', $deliverable->writer_id ?? '') }}',
        allUsers: @json(($allUsers ?? $users)->map(fn($u) => ['id' => (string)$u->id, 'name' => $u->name, 'role' => $u->role ?? ''])->values()),
        retainerUsers: @json(($allUsers ?? $users)->filter(fn($u) => strtolower($u->role ?? '') === 'writer')->map(fn($u) => ['id' => (string)$u->id, 'name' => $u->name, 'role' => $u->role ?? ''])->values()),
        get availableUsers() {
            const isCampaignOrPitch = (this.workflowType === 'campaign' || this.workflowType === 'pitch');
            return isCampaignOrPitch ? this.allUsers : this.retainerUsers;
        },
        updateProject(projectId) {
            if (projectId && this.projectWorkflows && this.projectWorkflows[projectId]) {
                this.workflowType = this.projectWorkflows[projectId];
            }
        }
    };
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
        placeholder: config.placeholder || '— Unassigned —',

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
