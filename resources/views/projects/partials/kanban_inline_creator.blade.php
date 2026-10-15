@php
    $creatorKey = match($colStatus) {
        'Done', 'Completed' => 'complete',
        'Approval Stage', 'Approval', 'Review' => 'approval',
        'In Progress' => 'inprogress',
        default => 'todo',
    };
@endphp

<div class="kanban-inline-card" 
     style="background: var(--color-bg-primary); border: 1.5px solid #64748b; border-radius: 12px; padding: 12px 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); margin-bottom: 8px;">
    
    <form onsubmit="submitInlineTask(event, '{{ $creatorKey }}', '{{ $colStatus }}')">
        {{-- Row 1: Title Input + Save Button (Matching reference) --}}
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
            <input type="text" 
                   name="task_title" 
                   placeholder="Task Name…" 
                   required
                   autocomplete="off"
                   style="width: 100%; border: none; background: transparent; font-size: 13.5px; font-weight: 700; color: var(--color-text-primary); outline: none; padding: 2px 0;"
                   onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitInlineTask(event, '{{ $creatorKey }}', '{{ $colStatus }}'); } else if(event.key === 'Escape') { closeInlineCreator('{{ $creatorKey }}'); }">
            
            <button type="submit" 
                    style="background: #e2e8f0; dark:background: #334155; color: #1e293b; dark:color: #f8fafc; border: none; border-radius: 6px; padding: 3px 8px; font-size: 11px; font-weight: 700; cursor: pointer; flex-shrink: 0; display: inline-flex; align-items: center; gap: 3px; transition: all 0.15s;"
                    onmouseover="this.style.background='#cbd5e1'"
                    onmouseout="this.style.background='#e2e8f0'">
                <span>Save</span>
                <span style="font-size: 11px; opacity: 0.7;">↵</span>
            </button>
        </div>

        {{-- Row 2: List / Post Type selector --}}
        <div style="margin-bottom: 10px;">
            <select name="post_type" 
                    style="border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; padding: 0; outline: none;">
                @foreach($subtaskTypes as $st)
                    <option value="{{ $st->name }}" {{ strtolower($st->name) === 'outlines' ? 'selected' : '' }}>
                        {{ $st->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Row 3: Action Pickers with Icons (Matching reference layout) --}}
        <div style="display: flex; flex-direction: column; gap: 6px; padding-top: 6px; border-top: 1px dashed var(--color-border-primary);">
            
            {{-- Add Assignee --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <select name="writer_id" 
                        style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                    <option value="">Add assignee…</option>
                    @foreach($selectableUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Add Dates --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="date" 
                       name="deadline" 
                       placeholder="Add dates…"
                       min="{{ date('Y-m-d') }}"
                       style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
            </div>

            {{-- Add Priority --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                <select name="priority" 
                        style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); cursor: pointer; outline: none; padding: 2px 0;">
                    <option value="Medium">Add priority (Medium)</option>
                    <option value="High Priority">High Priority</option>
                    <option value="Urgent">Urgent</option>
                    <option value="Low">Low</option>
                </select>
            </div>

            {{-- Add Tag --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--color-text-secondary)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span style="font-size: 11.5px; font-weight: 600; color: var(--color-text-secondary); opacity: 0.8;">
                    Campaign deliverable
                </span>
            </div>
        </div>

        {{-- Footer actions: Cancel --}}
        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 8px; padding-top: 6px;">
            <button type="button" 
                    onclick="closeInlineCreator('{{ $creatorKey }}')"
                    style="background: transparent; border: none; font-size: 11px; font-weight: 700; color: var(--color-text-secondary); cursor: pointer; padding: 2px 6px;">
                Cancel
            </button>
        </div>
    </form>
</div>
