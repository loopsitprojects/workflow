<div class="section-card">
    <div class="section-header-bar">
        <div>
            <h3 class="section-title">
                <svg width="15" height="15" fill="none" stroke="#3b82f6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                References & Attachments
            </h3>
            <p class="section-subtitle">Add external links and upload reference files for this deliverable</p>
        </div>
    </div>
    <div class="form-section-body">
        {{-- Reference URL --}}
        <div class="form-field-group">
            <label class="field-label">Reference URL (Drive, Figma, Links...)</label>
            <input type="url" name="reference" class="styled-input" :disabled="mode !== 'single'" placeholder="https://..." value="{{ old('reference', $parentTask->reference ?? '') }}">
        </div>

        {{-- Drag and Drop File Upload --}}
        <div class="form-field-group" x-data="{
            isDragging: false,
            selectedFiles: [],
            onFileChange(e) {
                this.selectedFiles = Array.from(e.target.files);
            },
            handleDrop(e) {
                this.isDragging = false;
                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                    this.selectedFiles = Array.from(e.dataTransfer.files);
                    if ($refs.singleRefFileInput) {
                        $refs.singleRefFileInput.files = e.dataTransfer.files;
                    }
                }
            },
            clearFiles() {
                this.selectedFiles = [];
                if ($refs.singleRefFileInput) {
                    $refs.singleRefFileInput.value = '';
                }
            },
            removeFile(idx) {
                this.selectedFiles.splice(idx, 1);
                const dt = new DataTransfer();
                this.selectedFiles.forEach(f => dt.items.add(f));
                if ($refs.singleRefFileInput) {
                    $refs.singleRefFileInput.files = dt.files;
                }
            },
            formatSize(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
            }
        }">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <label class="field-label" style="margin-bottom:0;">Attach Reference Files</label>
                <template x-if="selectedFiles.length > 0">
                    <button type="button" @click="clearFiles()" style="background:transparent; border:none; color:#ef4444; font-size:11px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:3px;">
                        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear (<span x-text="selectedFiles.length"></span>)
                    </button>
                </template>
            </div>

            <input type="file" 
                   name="reference_files[]" 
                   x-ref="singleRefFileInput"
                   @change="onFileChange($event)"
                   multiple 
                   accept="image/*,video/*,application/pdf,.pdf,.doc,.docx,.ppt,.pptx,.txt,.zip"
                   style="display:none;" 
                   :disabled="mode !== 'single'">

            <div class="file-dropzone"
                 :class="{ 'is-dragging': isDragging }"
                 @click="$refs.singleRefFileInput.click()"
                 @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="handleDrop($event)">
                <div class="dropzone-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </div>
                <div class="dropzone-text">
                    <span class="dropzone-main">Drop reference files here or <span class="dropzone-browse">browse</span></span>
                    <span class="dropzone-sub">Images, videos, PDFs, docs up to 50MB</span>
                </div>
            </div>

            {{-- Preview Selected Files --}}
            <template x-if="selectedFiles.length > 0">
                <div class="selected-files-list">
                    <template x-for="(file, fIdx) in selectedFiles" :key="fIdx">
                        <div class="file-preview-pill">
                            <div style="display:inline-flex; align-items:center; gap:7px; overflow:hidden; min-width:0;">
                                <svg width="13" height="13" fill="none" stroke="#3b82f6" viewBox="0 0 24 24" style="flex-shrink:0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span x-text="file.name" class="file-name-text"></span>
                            </div>
                            <div style="display:inline-flex; align-items:center; gap:8px; flex-shrink:0;">
                                <span x-text="formatSize(file.size)" class="file-size-text"></span>
                                <button type="button" @click.stop="removeFile(fIdx)" class="file-remove-btn" title="Remove file">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
