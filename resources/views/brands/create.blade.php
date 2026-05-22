<x-layout title="Initialize Brand">
    <style>
        .premium-form-container {
            max-width: 800px;
            margin: 20px auto;
            background: var(--color-bg-primary);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid var(--color-border-primary);
            overflow: hidden;
            font-family: 'Inter', -apple-system, sans-serif;
            transition: background 0.3s, border-color 0.3s;
        }
        .form-section {
            padding: 30px 40px;
            border-bottom: 1px solid var(--color-border-primary);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .grid-cell {
            padding: 50px 60px;
            border-bottom: 1px solid var(--color-border-primary);
        }
        .grid-cell.border-r {
            border-right: 1px solid var(--color-border-primary);
        }
        .field-label {
            display: block;
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.25em;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .field-label.primary {
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
            letter-spacing: -0.05em;
            padding: 0;
            margin-top: 5px;
        }
        .massive-input::placeholder {
            color: var(--color-border-primary);
        }
        .styled-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-prefix {
            position: absolute;
            left: 20px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            color: #3b82f6;
            pointer-events: none;
            z-index: 10;
        }
        .styled-input {
            width: 100%;
            background: var(--color-bg-secondary);
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 20px 24px;
            font-size: 14px;
            font-weight: 700;
            color: var(--color-text-primary);
            transition: all 0.2s;
            outline: none;
        }
        .styled-input:focus {
            background: var(--color-bg-primary);
            border-color: #3b82f620;
            box-shadow: 0 0 0 4px #3b82f605;
        }
        .styled-input.with-prefix {
            padding-left: 95px;
        }
        .styled-input.with-icon {
            padding-left: 55px;
        }
        .input-icon {
            position: absolute;
            left: 22px;
            color: #cbd5e1;
            pointer-events: none;
        }
        .status-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--color-bg-secondary);
            border-radius: 20px;
            padding: 20px 24px;
            font-size: 14px;
            font-weight: 900;
            color: var(--color-text-secondary);
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 12px rgba(16,185,129,0.4);
        }
        .description-textarea {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            font-size: 18px;
            line-height: 1.6;
            color: var(--color-text-primary);
            font-weight: 500;
            resize: none;
            padding: 40px;
        }
        .description-box {
            background: var(--color-bg-secondary);
            border-radius: 30px;
            overflow: hidden;
            margin-top: 20px;
        }
        .toolbar {
            padding: 20px 30px;
            border-bottom: 1px solid var(--color-border-primary);
            display: flex;
            gap: 30px;
            color: #cbd5e1;
        }
        .footer-actions {
            background: var(--color-bg-secondary);
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 18px 45px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #0055D4;
            color: #ffffff;
            box-shadow: 0 15px 35px rgba(0,85,212,0.25);
        }
        .btn-primary:hover {
            background: #0044aa;
            transform: translateY(-2px);
        }
        .btn-outline {
            background: var(--color-bg-primary);
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border-primary);
        }
        .btn-outline:hover {
            color: var(--color-text-primary);
            background: var(--color-bg-secondary);
        }
        .help-link {
            font-size: 10px;
            font-weight: 900;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
    </style>

    <div class="premium-form-container">
        <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- Header Section (Name) -->
            <div class="form-section">
                <label class="field-label primary">Brand Identity</label>
                <input type="text" name="name" id="name" required placeholder="e.g., Acme Global" class="massive-input">
                @error('name') <p style="color: #ef4444; font-size: 11px; font-weight: 700; margin-top: 20px;">{{ $message }}</p> @enderror
            </div>

            <!-- Upload Section -->
            <div class="form-section">
                <label class="field-label">Brand Asset (Logo Upload)</label>
                <div class="styled-input-wrapper" style="max-width: 50%;">
                     <span class="input-icon" style="top: 50%; transform: translateY(-50%);">
                         <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    </span>
                    <input type="file" name="logo" id="logo" accept="image/*" class="styled-input with-icon" style="padding-top: 15px; padding-bottom: 15px; cursor: pointer;">
                </div>
                @error('logo') <p style="color: #ef4444; font-size: 11px; font-weight: 700; margin-top: 10px;">{{ $message }}</p> @enderror
            </div>

            <!-- Team Members Section -->
            <div class="form-section" style="border-bottom: none;"
                 x-data="{
                    users: {{ \Illuminate\Support\Js::from($users->sortBy('role')->values()->map(fn($u) => ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role])) }},
                    selected: {{ \Illuminate\Support\Js::from(old('members', [])) }},
                    search: '',
                    get filtered() {
                        const s = this.search.toLowerCase();
                        return s ? this.users.filter(u => u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s) || (u.role||'').toLowerCase().includes(s)) : this.users;
                    },
                    toggle(id) { const i=this.selected.indexOf(id.toString()); i>-1?this.selected.splice(i,1):this.selected.push(id.toString()); },
                    isSelected(id) { return this.selected.includes(id.toString()); }
                 }">
                <label class="field-label">Team Members</label>

                {{-- Search bar --}}
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-white/[0.04] border border-gray-100 dark:border-white/[0.06] mb-4">
                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" placeholder="Search by name, email or role…"
                           class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700 dark:text-slate-300 placeholder-gray-400 dark:placeholder-slate-500">
                    <span x-show="search" @click="search=''" class="text-[10px] font-bold text-gray-400 cursor-pointer hover:text-gray-600 dark:hover:text-slate-300 uppercase tracking-wide">Clear</span>
                </div>

                {{-- Card grid --}}
                <div class="bg-gray-50/50 dark:bg-white/[0.02] rounded-2xl border border-gray-100 dark:border-white/[0.06] overflow-hidden">
                    <div class="max-h-80 overflow-y-auto p-3 custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <template x-for="user in filtered" :key="user.id">
                                <button type="button" @click="toggle(user.id)"
                                        class="flex items-center justify-between p-3 rounded-xl border-2 transition-all text-left"
                                        :class="isSelected(user.id)
                                            ? 'border-blue-500 bg-blue-500/5 dark:bg-blue-500/10'
                                            : 'border-transparent bg-white dark:bg-[#111827] hover:border-blue-500/20 card-shadow'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        {{-- Initial circle --}}
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold uppercase flex-shrink-0 transition-all"
                                             :class="isSelected(user.id)
                                                ? 'bg-blue-500 text-white'
                                                : 'bg-gray-100 dark:bg-white/[0.06] text-gray-500 dark:text-slate-400'"
                                             x-text="user.name.charAt(0)"></div>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-semibold truncate transition-colors"
                                               :class="isSelected(user.id) ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white'"
                                               x-text="user.name"></p>
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500"
                                               x-text="user.role || 'Team'"></p>
                                        </div>
                                    </div>
                                    {{-- Checkbox --}}
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ml-2 transition-all"
                                         :class="isSelected(user.id) ? 'bg-blue-500 border-blue-500' : 'border-gray-200 dark:border-slate-700'">
                                        <svg x-show="isSelected(user.id)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </button>
                            </template>
                            <template x-if="filtered.length === 0">
                                <div class="col-span-2 py-8 text-center text-sm text-gray-400 dark:text-slate-500">No members match your search.</div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer count --}}
                    <div class="border-t border-gray-100 dark:border-white/[0.06] px-4 py-2.5 flex justify-between items-center bg-white dark:bg-[#111827]">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Team Members</span>
                        <span class="px-2.5 py-1 bg-blue-500 text-white text-[10px] font-bold rounded-full"
                              x-text="selected.length + ' selected'"></span>
                    </div>
                </div>

                <template x-for="userId in selected" :key="'hidden-'+userId">
                    <input type="hidden" name="members[]" :value="userId">
                </template>
                @error('members')<p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:8px;">{{ $message }}</p>@enderror
            </div>

            <!-- Footer -->
            <div class="footer-actions">
                <div style="display: flex; gap: 20px;">
                    <a href="{{ route('brands.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Brand</button>
                </div>
            </div>
        </form>
    </div>
</x-layout>
