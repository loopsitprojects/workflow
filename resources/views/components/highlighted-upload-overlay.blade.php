<div id="highlightedUploadOverlay"
     class="highlighted-upload-overlay"
     style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; z-index:9999999; background:rgba(15, 23, 42, 0.65); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s ease-out;">

    <div class="highlighted-upload-modal"
         style="position:relative; width:90%; max-width:420px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:18px; padding:22px 24px; box-shadow:0 24px 50px rgba(0, 0, 0, 0.3); display:flex; flex-direction:column; transform:scale(0.95); transition:transform 0.2s ease-out;">

        {{-- Header: Spinner Icon + Title + Percent --}}
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
            <div style="width:38px; height:38px; border-radius:11px; background:rgba(59,130,246,0.12); color:#3b82f6; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation: huo-spin 0.9s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" style="opacity:0.2;"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity:0.85;"></path>
                </svg>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                    <h3 id="huoTitle" style="margin:0; font-size:14px; font-weight:700; color:var(--color-text-primary); letter-spacing:-0.01em; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        Uploading Deliverable Work…
                    </h3>
                    <span id="huoPercentText" class="huo-percent" style="font-size:13px; font-weight:800; font-variant-numeric:tabular-nums; flex-shrink:0;">
                        0%
                    </span>
                </div>
                <p id="huoSubtitle" style="margin:2px 0 0 0; font-size:11.5px; font-weight:500; color:var(--color-text-secondary); max-width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    Preparing upload…
                </p>
            </div>
        </div>

        {{-- Progress Bar Container with animated shine --}}
        <div style="width:100%; background:var(--color-bg-secondary); border-radius:999px; height:9px; overflow:hidden; position:relative; border:1px solid var(--color-border-primary);">
            <div id="huoProgressBar"
                 class="huo-progress-fill"
                 style="position:absolute; top:0; left:0; bottom:0; width:0%; border-radius:999px; transition:width 0.25s ease;">
            </div>
        </div>

        {{-- Status note below loading bar --}}
        <div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
            <span id="huoStatusText" style="font-size:11px; font-weight:500; color:var(--color-text-secondary); display:inline-flex; align-items:center; gap:5px;">
                <span class="huo-dot" style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#3b82f6; animation:huo-pulse 1.2s ease-in-out infinite;"></span>
                <span>Uploading to server…</span>
            </span>
        </div>
    </div>
</div>

<style>
@keyframes huo-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
@keyframes huo-pulse {
    0%, 100% { opacity: 0.4; transform: scale(0.9); }
    50% { opacity: 1; transform: scale(1.2); }
}
@keyframes huo-stripe {
    0% { background-position: 0 0; }
    100% { background-position: 28px 0; }
}
.huo-progress-fill {
    background: #0055d4;
    background-image: linear-gradient(45deg, rgba(255,255,255,0.18) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.18) 50%, rgba(255,255,255,0.18) 75%, transparent 75%, transparent);
    background-size: 14px 14px;
    animation: huo-stripe 0.8s linear infinite;
}
.dark .huo-progress-fill {
    background: #3b82f6;
    background-image: linear-gradient(45deg, rgba(255,255,255,0.18) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.18) 50%, rgba(255,255,255,0.18) 75%, transparent 75%, transparent);
    background-size: 14px 14px;
    animation: huo-stripe 0.8s linear infinite;
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
        currentPct: 0,
        _timer: null,

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
            if (this._timer) {
                clearInterval(this._timer);
                this._timer = null;
            }

            const isArtwork = options.type === 'artwork' || (options.title && options.title.toLowerCase().includes('artwork'));
            const isRef = options.type === 'reference' || (options.title && options.title.toLowerCase().includes('reference'));

            if (this.titleEl) {
                this.titleEl.textContent = options.title || (isArtwork ? 'Uploading Deliverable Work…' : (isRef ? 'Uploading Reference Files…' : 'Uploading Files…'));
            }

            if (this.subtitleEl) {
                this.subtitleEl.textContent = options.filename || 'Preparing upload…';
                this.subtitleEl.title = options.filename || '';
            }

            this.currentPct = options.percent || 12;
            this.update(this.currentPct, options.status || 'Uploading to server…');

            this.overlay.style.display = 'flex';
            requestAnimationFrame(() => {
                this.overlay.style.opacity = '1';
                if (this.modal) this.modal.style.transform = 'scale(1)';
            });

            // Smooth progress simulation up to 88% while waiting for network
            this._timer = setInterval(() => {
                if (this.currentPct < 88) {
                    const step = Math.max(1, Math.round((88 - this.currentPct) * 0.16));
                    this.currentPct += step;
                    this.update(this.currentPct);
                }
            }, 300);
        },

        update(percent = 0, status = null) {
            this.init();
            this.currentPct = Math.min(100, Math.max(0, Math.round(percent)));
            if (this.progressBar) {
                this.progressBar.style.width = this.currentPct + '%';
            }
            if (this.percentText) {
                this.percentText.textContent = this.currentPct + '%';
            }
            if (status && this.statusText) {
                this.statusText.innerHTML = `<span class="huo-dot" style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#3b82f6; animation:huo-pulse 1.2s ease-in-out infinite;"></span><span>${status}</span>`;
            }
        },

        hide() {
            this.init();
            if (this._timer) {
                clearInterval(this._timer);
                this._timer = null;
            }
            if (!this.overlay) return;
            this.update(100, 'Upload complete!');
            setTimeout(() => {
                this.overlay.style.opacity = '0';
                if (this.modal) this.modal.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.overlay.style.display = 'none';
                    if (this.progressBar) this.progressBar.style.width = '0%';
                    if (this.percentText) this.percentText.textContent = '0%';
                }, 200);
            }, 250);
        }
    };

    // Global convenience aliases
    window.showHighlightedUpload = (opts) => window.HighlightedUpload.show(opts);
    window.updateHighlightedUpload = (pct, status) => window.HighlightedUpload.update(pct, status);
    window.hideHighlightedUpload = () => window.HighlightedUpload.hide();
})();
</script>
