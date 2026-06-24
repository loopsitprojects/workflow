<x-layout title="Edit {{ $brand->name }}">
<style>
.f-wrap{max-width:640px;margin:24px auto;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);border-radius:14px;overflow:hidden;font-family:'Inter',sans-serif;}
.f-section{padding:20px 24px;border-bottom:1px solid var(--color-border-primary);}
.f-label{display:block;font-size:11px;font-weight:600;color:var(--color-text-secondary);margin-bottom:7px;}
.f-label.blue{color:#3b82f6;}
.f-input{width:100%;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);border-radius:8px;padding:9px 12px;font-size:13px;font-weight:500;color:var(--color-text-primary);outline:none;transition:border-color 0.15s;-webkit-appearance:none;appearance:none;}
.f-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.1);}
.f-title{width:100%;background:transparent;border:none;outline:none;font-size:20px;font-weight:800;color:var(--color-text-primary);letter-spacing:-0.02em;}
.f-title::placeholder{opacity:0.25;color:var(--color-text-primary);}
.f-footer{background:var(--color-bg-secondary);padding:14px 24px;display:flex;justify-content:space-between;gap:8px;align-items:center;border-top:1px solid var(--color-border-primary);}
.btn-c{padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;color:var(--color-text-secondary);background:transparent;border:1.5px solid var(--color-border-primary);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:all 0.12s;}
.btn-c:hover{background:var(--color-bg-secondary);color:var(--color-text-primary);}
.btn-s{padding:8px 22px;border-radius:8px;font-size:12px;font-weight:700;color:#fff;background:#0055D4;border:none;cursor:pointer;box-shadow:0 3px 10px rgba(0,85,212,0.25);transition:all 0.12s;}
.btn-s:hover{background:#0044aa;}
.btn-d{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;color:#ef4444;background:transparent;border:1.5px solid rgba(239,68,68,0.2);cursor:pointer;transition:all 0.12s;}
.btn-d:hover{background:rgba(239,68,68,0.05);}
.mp-wrap{border:1.5px solid var(--color-border-primary);border-radius:8px;overflow:hidden;}
.mp-search{display:flex;align-items:center;gap:8px;padding:9px 12px;border-bottom:1px solid var(--color-border-primary);background:var(--color-bg-secondary);}
.mp-search input{flex:1;background:transparent;border:none;outline:none;font-size:12px;color:var(--color-text-primary);}
.mp-search input::placeholder{color:var(--color-text-secondary);opacity:0.5;}
.mp-list{max-height:220px;overflow-y:auto;}
.mp-row{display:flex;align-items:center;gap:10px;padding:9px 12px;cursor:pointer;border-bottom:1px solid var(--color-border-primary);transition:background 0.1s;}
.mp-row:last-child{border-bottom:none;}
.mp-row:hover{background:var(--color-bg-secondary);}
.mp-row.selected{background:rgba(59,130,246,0.06);}
.mp-init{width:28px;height:28px;border-radius:6px;background:var(--color-bg-secondary);border:1.5px solid var(--color-border-primary);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--color-text-secondary);text-transform:uppercase;flex-shrink:0;transition:all 0.12s;}
.mp-row.selected .mp-init{background:rgba(59,130,246,0.1);border-color:rgba(59,130,246,0.3);color:#3b82f6;}
.mp-check{width:16px;height:16px;border-radius:50%;border:1.5px solid var(--color-border-primary);flex-shrink:0;display:flex;align-items:center;justify-content:center;margin-left:auto;transition:all 0.12s;}
.mp-row.selected .mp-check{background:#3b82f6;border-color:#3b82f6;}
.mp-footer{border-top:1px solid var(--color-border-primary);padding:8px 12px;display:flex;justify-content:space-between;align-items:center;background:var(--color-bg-secondary);}
</style>

<nav style="max-width:640px;margin:0 auto 12px;display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:var(--color-text-secondary);">
    <a href="{{ route('brands.index') }}" style="text-decoration:none;color:inherit;">Brands</a>
    <span style="opacity:0.35;">/</span>
    <a href="{{ route('brands.show', $brand->slug) }}" style="text-decoration:none;color:inherit;">{{ $brand->name }}</a>
    <span style="opacity:0.35;">/</span>
    <span style="color:var(--color-text-primary);">Edit</span>
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
            <h3 class="text-[15px] font-bold text-gray-900 dark:text-white mb-1">Delete Brand</h3>
            <p class="text-[13px] text-gray-500 dark:text-slate-400 mb-5">Are you sure you want to permanently delete <strong class="text-gray-800 dark:text-white">{{ $brand->name }}</strong>? All associated projects will be removed. This cannot be undone.</p>
            <div class="flex gap-2 justify-end">
                <button @click="showDeleteModal = false" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-white/[0.06] hover:bg-gray-200 dark:hover:bg-white/[0.10] transition-colors">Cancel</button>
                <button @click="document.getElementById('delete-form').submit()" class="px-4 py-2 rounded-lg text-[12px] font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">Delete</button>
            </div>
        </div>
    </div>

    <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Brand Name --}}
        <div class="f-section">
            <label class="f-label blue">Brand Name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required placeholder="Brand Name" class="f-title">
            <input type="hidden" name="slug" value="{{ $brand->slug }}">
            @error('name') <p style="color:#ef4444;font-size:11px;margin-top:6px;">{{ $message }}</p> @enderror
        </div>

        {{-- Logo Upload --}}
        <div class="f-section"
             x-data="{
                previewUrl: '{{ addslashes($brand->logo_url ?? '') }}',
                handleFile(event) {
                    const file = event.target.files[0];
                    if (file) { const reader = new FileReader(); reader.onload = (e) => { this.previewUrl = e.target.result; }; reader.readAsDataURL(file); }
                },
                clearLogo() { this.previewUrl = ''; document.getElementById('logo').value = ''; }
             }">
            <label class="f-label">Brand Logo <span style="opacity:0.5;font-weight:400;">(PNG, JPG, SVG · max 2MB)</span></label>
            <label for="logo"
                   style="display:flex;align-items:center;gap:12px;padding:10px 12px;border:1.5px dashed var(--color-border-primary);border-radius:8px;background:var(--color-bg-secondary);cursor:pointer;transition:border-color 0.15s;"
                   @dragover.prevent
                   @drop.prevent="handleFile({ target: { files: $event.dataTransfer.files } })">
                <div style="width:40px;height:40px;border-radius:6px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:var(--color-bg-primary);border:1px solid var(--color-border-primary);">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" style="width:100%;height:100%;object-fit:contain;">
                    </template>
                    <template x-if="!previewUrl">
                        <svg style="width:18px;height:18px;color:#94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </template>
                </div>
                <div style="flex:1;">
                    <p style="font-size:13px;font-weight:600;color:var(--color-text-primary);" x-text="previewUrl ? 'Change Logo' : 'Upload Brand Logo'"></p>
                    <p style="font-size:11px;color:var(--color-text-secondary);margin-top:1px;">Click to browse or drag &amp; drop</p>
                </div>
                <template x-if="previewUrl">
                    <span style="padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(16,185,129,0.1);color:#10b981;">&#10003; Set</span>
                </template>
                <input type="file" name="logo" id="logo" accept="image/*" class="hidden" @change="handleFile($event)">
            </label>
            <div x-show="previewUrl" style="display:none;margin-top:6px;">
                <button type="button" @click="clearLogo()" style="font-size:11px;font-weight:600;color:#f87171;background:none;border:none;cursor:pointer;display:flex;align-items:center;gap:4px;">
                    <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Remove logo
                </button>
            </div>
            @error('logo') <p style="color:#ef4444;font-size:11px;margin-top:8px;">{{ $message }}</p> @enderror
        </div>

        {{-- Team Members --}}
        <div class="f-section" style="border-bottom:none;"
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
            <label class="f-label">Team Members</label>
            <div class="mp-wrap">
                <div class="mp-search">
                    <svg style="width:12px;height:12px;flex-shrink:0;color:var(--color-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="search" placeholder="Search by name, email or role…">
                    <span x-show="search" @click="search=''" style="font-size:10px;font-weight:600;color:var(--color-text-secondary);cursor:pointer;">Clear</span>
                </div>
                <div class="mp-list">
                    <template x-for="user in filtered" :key="user.id">
                        <div class="mp-row" :class="{ selected: isSelected(user.id) }" @click="toggle(user.id)">
                            <div class="mp-init" x-text="user.name.charAt(0)"></div>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:13px;font-weight:600;color:var(--color-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" x-text="user.name"></div>
                                <div style="font-size:10px;font-weight:600;color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:0.04em;" x-text="user.role || 'Team'"></div>
                            </div>
                            <div class="mp-check">
                                <svg x-show="isSelected(user.id)" style="width:8px;height:8px;" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </template>
                    <template x-if="filtered.length === 0">
                        <div style="padding:20px;text-align:center;font-size:12px;color:var(--color-text-secondary);">No members match your search.</div>
                    </template>
                </div>
                <div class="mp-footer">
                    <span style="font-size:10px;font-weight:600;color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:0.06em;">Members</span>
                    <span style="padding:2px 8px;background:#3b82f6;color:#fff;font-size:10px;font-weight:700;border-radius:20px;" x-text="selected.length + ' selected'"></span>
                </div>
            </div>
            <template x-for="userId in selected" :key="'hidden-'+userId">
                <input type="hidden" name="members[]" :value="userId">
            </template>
            @error('members')<p style="color:#ef4444;font-size:11px;font-weight:600;margin-top:8px;">{{ $message }}</p>@enderror
        </div>

        <div class="f-footer">
            @if(auth()->user()->isAdmin() || $brand->created_by === auth()->id())
            <button type="button" @click="showDeleteModal = true" class="btn-d">Delete Brand</button>
            @else
            <span></span>
            @endif
            <div style="display:flex;gap:8px;">
                <a href="{{ route('brands.index') }}" class="btn-c">Cancel</a>
                <button type="submit" class="btn-s">Save Changes</button>
            </div>
        </div>
    </form>

    <form id="delete-form" action="{{ route('brands.destroy', $brand) }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>
</x-layout>
