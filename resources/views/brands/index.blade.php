<x-layout title="Brand Directory">
    <div class="flex flex-col gap-10" x-data="{
        editOpen: false,
        editBrandId: null,
        editBrandSlug: null,
        search: '',
        brands: {{ \Illuminate\Support\Js::from($brands->map(fn($b) => ['slug' => $b->slug, 'name' => $b->name])) }},
        get filteredSlugs() {
            if (!this.search.trim()) return null;
            const s = this.search.toLowerCase();
            return this.brands.filter(b => b.name.toLowerCase().includes(s)).map(b => b.slug);
        },
        openEdit(slug) {
            this.editBrandSlug = slug;
            this.editOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeEdit() {
            this.editOpen = false;
            document.body.style.overflow = '';
        }
    }" @open-edit.window="openEdit($event.detail.slug)" @keydown.escape.window="closeEdit()">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="fixed top-6 right-6 z-[9999] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl"
                 style="background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.25); backdrop-filter: blur(12px);">
                <svg class="w-4 h-4 flex-shrink-0" style="color:#10b981" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span style="font-size:12px; font-weight:700; color:#10b981;">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Header Section -->
        <div class="flex justify-between items-end gap-4 flex-wrap">
            <div>
                <p class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mb-2">Workspace Overview</p>
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">Brand Directory</h1>
            </div>
            <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-white dark:bg-white/[0.04] border border-gray-200 dark:border-white/[0.08] shadow-sm w-64">
                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" placeholder="Search brands…"
                       class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700 dark:text-slate-300 placeholder-gray-400 dark:placeholder-slate-500">
                <span x-show="search" @click="search=''" class="text-[10px] font-bold text-gray-400 cursor-pointer hover:text-gray-600 dark:hover:text-slate-300 uppercase tracking-wide">Clear</span>
            </div>
        </div>

        <!-- Brands Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($brands as $brand)
                <div x-show="!filteredSlugs || filteredSlugs.includes('{{ $brand->slug }}')">
                    <x-brand-card :brand="$brand" />
                </div>
            @endforeach

            @if(auth()->user()->isAdmin())
            <!-- Create New Brand Placeholder -->
            <a href="{{ route('brands.create') }}" class="border-2 border-dashed border-gray-200 dark:border-slate-800 rounded-2xl p-8 flex flex-col items-center justify-center text-center group cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all min-h-[300px]">
                <div class="w-16 h-16 bg-gray-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-100 dark:group-hover:bg-blue-900 transition-colors">
                    <svg class="w-8 h-8 text-gray-400 dark:text-slate-500 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Create New Brand</h3>
                <p class="text-sm text-gray-500 dark:text-slate-400 max-w-[200px] leading-relaxed">Launch a new dedicated workspace for your team or client.</p>
            </a>
            @endif
        </div>

        <!-- ========== EDIT MODAL ========== -->

        @if(auth()->user()->isAdmin())
        <!-- Backdrop -->
        <div x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeEdit()"
             class="fixed inset-0 z-40"
             style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display:none;">
        </div>

        <!-- Modal Panel -->
        <div x-show="editOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="pointer-events:none; display:none;">
            <div class="w-full max-w-lg max-h-[85vh] overflow-y-auto rounded-2xl shadow-2xl"
                 style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary); pointer-events:all;">

                @foreach($brands as $brand)
                <div x-show="editBrandSlug === '{{ $brand->slug }}'" style="display:none;">

                    <style>
                        .modal-form-section { padding: 18px 24px; border-bottom: 1px solid var(--color-border-primary); }
                        .modal-field-label { display:block; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.25em; color:#94a3b8; margin-bottom:8px; }
                        .modal-field-label.primary { color:#2563eb; }
                        .modal-massive-input { width:100%; background:transparent; border:none; outline:none; font-size:20px; font-weight:900; color:var(--color-text-primary); padding:0; margin-top:2px; }
                        .modal-massive-input::placeholder { color:var(--color-border-primary); }
                    </style>

                    <form action="{{ route('brands.update', $brand) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--color-border-primary);">
                            <div>
                                <p class="text-[9px] font-black uppercase tracking-widest" style="color:#0055D4;">Edit Brand</p>
                                <h2 class="text-base font-extrabold mt-0.5" style="color: var(--color-text-primary);">{{ $brand->name }}</h2>
                            </div>
                            <button type="button" @click="closeEdit()" class="w-7 h-7 rounded-full flex items-center justify-center transition-colors" style="background: var(--color-bg-secondary);">
                                <svg class="w-3.5 h-3.5" style="color:var(--color-text-secondary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Brand Name -->
                        <div class="modal-form-section">
                            <label class="modal-field-label primary">Brand Name</label>
                            <input type="text" name="name" value="{{ old('name', $brand->name) }}" required placeholder="Brand Name" class="modal-massive-input">
                            <input type="hidden" name="slug" value="{{ $brand->slug }}">
                        </div>

                        <!-- Logo Upload -->
                        <div class="modal-form-section" x-data="{
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
                                this.$el.querySelector('input[type=file]').value = '';
                            }
                        }">
                            <label class="modal-field-label">Brand Logo</label>

                            <label class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition-all"
                                   style="border: 1.5px dashed var(--color-border-primary); background: var(--color-bg-secondary);"
                                   @dragover.prevent
                                   @drop.prevent="handleFile({ target: { files: $event.dataTransfer.files } })">

                                <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 flex items-center justify-center"
                                     style="background: var(--color-bg-primary); border: 1px solid var(--color-border-primary);">
                                    <template x-if="previewUrl">
                                        <img :src="previewUrl" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!previewUrl">
                                        <svg class="w-5 h-5" style="color:#94a3b8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </template>
                                </div>

                                <div class="flex-1">
                                    <p class="text-xs font-bold" style="color:var(--color-text-primary)">
                                        <span x-text="previewUrl ? 'Change Logo' : 'Upload Logo'"></span>
                                    </p>
                                    <p class="text-[10px]" style="color:var(--color-text-secondary)">PNG, JPG, SVG · max 2MB</p>
                                    <input type="file" name="logo" accept="image/*" class="hidden" @change="handleFile($event)">
                                </div>

                                <div>
                                    <template x-if="previewUrl">
                                        <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase" style="background:rgba(16,185,129,0.1);color:#10b981;">✓ Set</span>
                                    </template>
                                    <template x-if="!previewUrl">
                                        <span class="px-2 py-1 rounded-full text-[9px] font-black uppercase" style="background:rgba(148,163,184,0.1);color:#94a3b8;">None</span>
                                    </template>
                                </div>
                            </label>

                            <div x-show="previewUrl" style="display:none;" class="mt-1.5">
                                <button type="button" @click="clearLogo()" class="text-[10px] font-bold flex items-center gap-1" style="color:#f87171;">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Remove logo
                                </button>
                            </div>
                        </div>

                        <!-- Team Selection -->
                        <div class="modal-form-section" style="border-bottom:none;"
                             x-data="{
                                selected: {{ \Illuminate\Support\Js::from(old('members', $brand->members->pluck('id')->map(fn($id) => (string)$id)->toArray())) }},
                                toggle(id) {
                                    const index = this.selected.indexOf(id.toString());
                                    if (index > -1) { this.selected.splice(index, 1); }
                                    else { this.selected.push(id.toString()); }
                                },
                                isSelected(id) { return this.selected.includes(id.toString()); }
                             }">
                            <label class="modal-field-label">Team Members</label>

                            <div class="rounded-xl overflow-hidden" style="border: 1px solid var(--color-border-primary);">
                                <div class="max-h-40 overflow-y-auto p-2">
                                    <div class="grid grid-cols-2 gap-1.5">
                                        @foreach(\App\Models\User::all() as $user)
                                        <button type="button" @click="toggle({{ $user->id }})"
                                            class="flex items-center gap-2 p-2 rounded-lg transition-all text-left"
                                            :style="isSelected({{ $user->id }}) ? 'background: rgba(0,85,212,0.08); border: 1.5px solid rgba(0,85,212,0.3);' : 'background: var(--color-bg-secondary); border: 1.5px solid transparent;'">
                                            <img src="{{ $user->avatar_url }}" class="w-6 h-6 rounded-full flex-shrink-0">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[11px] font-bold truncate" style="color:var(--color-text-primary)">{{ $user->name }}</p>
                                                <p class="text-[9px] font-bold uppercase tracking-wider truncate" style="color:var(--color-text-secondary)">{{ $user->role ?: 'Team' }}</p>
                                            </div>
                                            <div class="w-3.5 h-3.5 rounded-full flex-shrink-0 flex items-center justify-center transition-colors"
                                                 :style="isSelected({{ $user->id }}) ? 'background:#0055D4;' : 'background:transparent; border: 1.5px solid var(--color-border-primary);'">
                                                <svg x-show="isSelected({{ $user->id }})" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="px-3 py-2 flex justify-between items-center" style="border-top: 1px solid var(--color-border-primary); background: var(--color-bg-secondary);">
                                    <span class="text-[9px] font-black uppercase tracking-wider" style="color:var(--color-text-secondary)">Team Members</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black" style="background:#0055D4; color:#fff;" x-text="selected.length + ' assigned'"></span>
                                </div>
                                <template x-for="userId in selected" :key="'member-'+userId">
                                    <input type="hidden" name="members[]" :value="userId">
                                </template>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-between items-center px-5 py-3.5" style="border-top: 1px solid var(--color-border-primary); background: var(--color-bg-secondary);">
                            <button type="button" @click="closeEdit()" class="px-4 py-2 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all" style="background:var(--color-bg-primary); color:var(--color-text-secondary); border: 1px solid var(--color-border-primary);">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all" style="background:#0055D4; color:#fff; box-shadow: 0 4px 16px rgba(0,85,212,0.25);">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                @endforeach

            </div>
        </div>
        @endif

    </div>
</x-layout>
