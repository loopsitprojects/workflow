<div id="highlightedUploadOverlay"
     class="highlighted-upload-overlay"
     style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; z-index:9999999; background:rgba(15, 23, 42, 0.6); backdrop-filter:blur(5px); -webkit-backdrop-filter:blur(5px); align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s ease-out;">

    <div class="highlighted-upload-modal"
         style="position:relative; width:90%; max-width:420px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:16px; padding:22px 24px; box-shadow:0 20px 45px rgba(0, 0, 0, 0.2); display:flex; flex-direction:column; transform:scale(0.95); transition:transform 0.2s ease-out;">

        {{-- Title --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
            <h3 id="huoTitle" style="margin:0; font-size:14px; font-weight:700; color:var(--color-text-primary); letter-spacing:-0.01em;">
                Uploading Deliverable Artwork…
            </h3>
            <span id="huoPercentText" class="huo-percent" style="font-size:13px; font-weight:800; font-variant-numeric:tabular-nums;">
                0%
            </span>
        </div>

        {{-- Subtitle / Filename indicator --}}
        <p id="huoSubtitle" style="margin:0 0 14px 0; font-size:11.5px; font-weight:500; color:var(--color-text-secondary); max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
            Preparing upload…
        </p>

        {{-- Progress Bar Container --}}
        <div style="width:100%; background:var(--color-bg-secondary); border-radius:999px; height:8px; overflow:hidden; position:relative; border:1px solid var(--color-border-primary);">
            <div id="huoProgressBar"
                 class="huo-progress-fill"
                 style="position:absolute; top:0; left:0; bottom:0; width:0%; border-radius:999px; transition:width 0.2s ease;">
            </div>
        </div>

        {{-- Status note below loading bar --}}
        <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
            <span id="huoStatusText" style="font-size:11px; font-weight:500; color:var(--color-text-secondary);">
                Uploading to server…
            </span>
        </div>
    </div>
</div>

<style>
.huo-progress-fill {
    background: #0055d4;
}
.dark .huo-progress-fill {
    background: #3b82f6;
}
.huo-percent {
    color: #0055d4;
}
.dark .huo-percent {
    color: #60a5fa;
}
</style>

<script>
(function() {
    window.HighlightedUpload = {
        overlay: null,
        modal: null,
        titleEl: null,
        subtitleEl: null,
        progressBar: null,
        percentText: null,
        statusText: null,

        init() {
            this.overlay = document.getElementById('highlightedUploadOverlay');
            if (!this.overlay) return;
            this.modal = this.overlay.querySelector('.highlighted-upload-modal');
            this.titleEl = document.getElementById('huoTitle');
            this.subtitleEl = document.getElementById('huoSubtitle');
            this.progressBar = document.getElementById('huoProgressBar');
            this.percentText = document.getElementById('huoPercentText');
            this.statusText = document.getElementById('huoStatusText');
        },

        show(options = {}) {
            this.init();
            if (!this.overlay) return;

            const isArtwork = options.type === 'artwork' || (options.title && options.title.toLowerCase().includes('artwork'));
            const isRef = options.type === 'reference' || (options.title && options.title.toLowerCase().includes('reference'));

            if (this.titleEl) {
                this.titleEl.textContent = options.title || (isArtwork ? 'Uploading Artwork…' : (isRef ? 'Uploading Reference Files…' : 'Uploading Files…'));
            }

            if (this.subtitleEl) {
                this.subtitleEl.textContent = options.filename || 'Preparing upload…';
                this.subtitleEl.title = options.filename || '';
            }

            this.update(options.percent || 0, options.status || 'Uploading to server…');

            this.overlay.style.display = 'flex';
            requestAnimationFrame(() => {
                this.overlay.style.opacity = '1';
                if (this.modal) this.modal.style.transform = 'scale(1)';
            });
        },

        update(percent = 0, status = null) {
            this.init();
            const pct = Math.min(100, Math.max(0, Math.round(percent)));
            if (this.progressBar) {
                this.progressBar.style.width = pct + '%';
            }
            if (this.percentText) {
                this.percentText.textContent = pct + '%';
            }
            if (status && this.statusText) {
                this.statusText.textContent = status;
            }
        },

        hide() {
            this.init();
            if (!this.overlay) return;
            this.overlay.style.opacity = '0';
            if (this.modal) this.modal.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.overlay.style.display = 'none';
                if (this.progressBar) this.progressBar.style.width = '0%';
                if (this.percentText) this.percentText.textContent = '0%';
            }, 200);
        }
    };

    // Global convenience aliases
    window.showHighlightedUpload = (opts) => window.HighlightedUpload.show(opts);
    window.updateHighlightedUpload = (pct, status) => window.HighlightedUpload.update(pct, status);
    window.hideHighlightedUpload = () => window.HighlightedUpload.hide();
})();
</script>
