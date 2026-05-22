<x-layout title="Edit {{ $brand->name }}">
    <style>
        .premium-form-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--color-bg-primary);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid var(--color-border-primary);
            overflow: hidden;
            font-family: 'Inter', -apple-system, sans-serif;
            transition: background 0.3s, border-color 0.3s;
        }
        .form-section {
            padding: 40px;
            border-bottom: 1px solid var(--color-border-primary);
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
        .styled-input.with-icon {
            padding-left: 55px;
        }
        .input-icon {
            position: absolute;
            left: 22px;
            color: #cbd5e1;
            pointer-events: none;
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
        .btn-danger {
            background: transparent;
            color: #f87171;
            font-size: 10px;
        }
        .btn-danger:hover {
            color: #ef4444;
        }
    </style>

    <!-- Breadcrumbs -->
    <div class="flex items-center gap-2 mb-6" style="font-size:11px; font-weight:700; color:var(--color-text-secondary);">
        <a href="{{ route('brands.index') }}" class="flex items-center gap-1.5 hover:text-blue-500 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Brand Directory
        </a>
        <svg class="w-3 h-3" style="color:var(--color-border-primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:var(--color-text-primary);">{{ $brand->name }}</span>
        <svg class="w-3 h-3" style="color:var(--color-border-primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span style="color:#0055D4;">Edit</span>
    </div>

    <div class="premium-form-container">
        <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Brand Name Section -->
            <div class="form-section">
                <label class="field-label primary">Brand Identity</label>
                <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required placeholder="Brand Name" class="massive-input">
                <input type="hidden" name="slug" value="{{ $brand->slug }}">
                @error('name') <p style="color: #ef4444; font-size: 11px; font-weight: 700; margin-top: 20px;">{{ $message }}</p> @enderror
            </div>

            <!-- Logo Upload Section -->
            <div class="form-section">
                <label class="field-label">Brand Asset (Logo Upload)</label>

                <div x-data="{
                    previewUrl: '{{ addslashes($brand->logo_url ?? '') }}',
                    handleFile(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => { this.previewUrl = e.target.result; };
                            reader.readAsDataURL(file);
                        }
                    },
                    clearLogo() {
                        this.previewUrl = '';
                        document.getElementById('logo').value = '';
                    }
                }">
                    <!-- Upload area -->
                    <label for="logo"
                           class="flex items-center gap-5 p-5 rounded-2xl cursor-pointer transition-all"
                           style="border: 2px dashed var(--color-border-primary); background: var(--color-bg-secondary);"
                           @dragover.prevent
                           @drop.prevent="handleFile({ target: { files: $event.dataTransfer.files } })">

                        <!-- Preview / Placeholder -->
                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center"
                             style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary);">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" class="w-full h-full object-contain">
                            </template>
                            <template x-if="!previewUrl">
                                <svg class="w-8 h-8" style="color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </template>
                        </div>

                        <!-- Text info -->
                        <div class="flex-1">
                            <p class="text-sm font-bold" style="color: var(--color-text-primary);">
                                <span x-text="previewUrl ? 'Change Logo' : 'Upload Brand Logo'"></span>
                            </p>
                            <p class="text-[11px] mt-1" style="color: var(--color-text-secondary);">
                                Click to browse or drag &amp; drop &middot; PNG, JPG, SVG (max 2MB)
                            </p>
                            <input type="file" name="logo" id="logo" accept="image/*" class="hidden" @change="handleFile($event)">
                        </div>

                        <!-- Status badge -->
                        <div>
                            <template x-if="previewUrl">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider" style="background: rgba(16,185,129,0.1); color: #10b981;">
                                    &#10003; Logo Set
                                </span>
                            </template>
                            <template x-if="!previewUrl">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider" style="background: rgba(148,163,184,0.1); color: #94a3b8;">
                                    No Logo
                                </span>
                            </template>
                        </div>
                    </label>

                    <!-- Clear button -->
                    <div x-show="previewUrl" style="display:none;">
                        <button type="button" @click="clearLogo()" class="mt-3 text-[11px] font-bold flex items-center gap-1 transition-colors" style="color: #f87171;">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Remove logo
                        </button>
                    </div>
                </div>

                @error('logo') <p style="color: #ef4444; font-size: 11px; font-weight: 700; margin-top: 10px;">{{ $message }}</p> @enderror
            </div>

            <!-- Team Members Section -->
            <div class="form-section" style="border-bottom: none;"
                 x-data="{
                    users: {{ \Illuminate\Support\Js::from($users->sortBy('role')->values()->map(fn($u) => ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->role])) }},
                    selected: {{ \Illuminate\Support\Js::from(old('members', $brand->members->pluck('id')->map(fn($id) => (string)$id)->toArray())) }},
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

            <!-- Footer Actions -->
            <div class="footer-actions">
                <button type="button" onclick="confirmDelete()" class="btn btn-danger">Delete Brand</button>
                <div style="display: flex; gap: 20px;">
                    <a href="{{ route('brands.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form -->
        <form id="delete-form" action="{{ route('brands.destroy', $brand) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

    <script>
        function confirmDelete() {
            if (confirm('CRITICAL ACTION: Are you sure you want to PERMANENTLY dissolve this brand workspace and all associated projects? This action cannot be undone.')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
</x-layout>
