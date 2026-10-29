@php
    $creatorKey = match($colStatus) {
        'Done', 'Completed' => 'complete',
        'Approval Stage', 'Approval', 'Review' => 'approval',
        'In Progress' => 'inprogress',
        default => 'todo',
    };
@endphp

<div class="kanban-inline-card" 
     style="background: var(--bg-card); border: 1.5px solid #64748b; border-radius: 12px; padding: 12px 14px; box-shadow: var(--shadow-card); margin-bottom: 8px;">
    
    <form onsubmit="submitInlineTask(event, '{{ $creatorKey }}', '{{ $colStatus }}')">
        {{-- Top Header: Type selector and Stage on top very small --}}
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px; margin-bottom: 8px;">
            <div style="display: inline-flex; align-items: center; gap: 4px;">
                <select name="post_type" 
                        style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: var(--text-card-sub); background: var(--bg-column); border: 1px solid var(--border-card); padding: 1px 4px; border-radius: 4px; line-height: 1.2; cursor: pointer; outline: none;">
                    @foreach($subtaskTypes as $st)
                        <option value="{{ $st->name }}" style="background: var(--bg-card); color: var(--text-card-title);" {{ strtolower($st->name) === 'outlines' ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
                <span style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; color: #3b82f6; background: rgba(59,130,246,0.08); border: 1px solid rgba(59,130,246,0.2); padding: 1px 4px; border-radius: 4px; line-height: 1.2; display: inline-flex; align-items: center;">
                    {{ $colStatus }}
                </span>
            </div>
        </div>

        {{-- Row 1: Title Input (clean, full-width) --}}
        <div style="margin-bottom: 8px;">
            <input type="text" 
                   name="task_title" 
                   placeholder="Task Name…" 
                   required
                   autocomplete="off"
                   style="width: 100%; border: none; background: transparent; font-size: 13.5px; font-weight: 700; color: var(--text-card-title); outline: none; padding: 2px 0;"
                   onkeydown="if(event.key === 'Enter') { event.preventDefault(); submitInlineTask(event, '{{ $creatorKey }}', '{{ $colStatus }}'); } else if(event.key === 'Escape') { closeInlineCreator('{{ $creatorKey }}'); }">
        </div>

        {{-- Row 2: Action Pickers with Icons (Matching reference layout) --}}
        <div style="display: flex; flex-direction: column; gap: 6px; padding-top: 6px; border-top: 1px dashed var(--border-card);">
            
            {{-- Add Assignee --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--text-card-sub)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <select name="writer_id" 
                        style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--text-card-sub); cursor: pointer; outline: none; padding: 2px 0;">
                    <option value="" style="background: var(--bg-card); color: var(--text-card-title);">Add assignee…</option>
                    @foreach($selectableUsers as $u)
                        <option value="{{ $u->id }}" style="background: var(--bg-card); color: var(--text-card-title);">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Add Approver (hidden in To Do stage, shown in In Progress / Approval / Done) --}}
            @if($colStatus !== 'To Do')
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--text-card-sub)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <select name="approver_id" 
                        style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--text-card-sub); cursor: pointer; outline: none; padding: 2px 0;">
                    <option value="" style="background: var(--bg-card); color: var(--text-card-title);">Add approver…</option>
                    @foreach($selectableUsers as $u)
                        <option value="{{ $u->id }}" style="background: var(--bg-card); color: var(--text-card-title);">{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Add Dates --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--text-card-sub)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <input type="date" 
                       name="deadline" 
                       placeholder="Add dates…"
                       min="{{ date('Y-m-d') }}"
                       style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--text-card-sub); cursor: pointer; outline: none; padding: 2px 0;">
            </div>

            {{-- Add Priority --}}
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="13" height="13" fill="none" stroke="var(--text-card-sub)" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                <select name="priority" 
                        style="width: 100%; border: none; background: transparent; font-size: 11.5px; font-weight: 600; color: var(--text-card-sub); cursor: pointer; outline: none; padding: 2px 0;">
                    <option value="Medium" style="background: var(--bg-card); color: var(--text-card-title);">Add priority (Medium)</option>
                    <option value="High Priority" style="background: var(--bg-card); color: var(--text-card-title);">High Priority</option>
                    <option value="Urgent" style="background: var(--bg-card); color: var(--text-card-title);">Urgent</option>
                    <option value="Low" style="background: var(--bg-card); color: var(--text-card-title);">Low</option>
                </select>
            </div>
        </div>

        {{-- Footer actions: Cancel & Save on right (All buttons at the bottom) --}}
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--color-border-primary);">
            <button type="button" 
                    onclick="closeInlineCreator('{{ $creatorKey }}')"
                    style="background: transparent; border: none; font-size: 11.5px; font-weight: 700; color: var(--color-text-secondary); cursor: pointer; padding: 4px 8px; border-radius: 6px;">
                Cancel
            </button>
            <button type="submit" 
                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-md bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-100 hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors border-0 cursor-pointer">
                <span>Save</span>
                <span style="font-size: 11px; opacity: 0.7;">↵</span>
            </button>
        </div>
    </form>
</div>
