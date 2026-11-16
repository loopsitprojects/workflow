<div id="deliverableClosingOverlay"
     class="deliverable-closing-overlay"
     style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; width:100vw; height:100vh; z-index:9999999; background:rgba(15, 23, 42, 0.7); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px); align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s ease-out;">

    <div class="deliverable-closing-modal"
         style="position:relative; width:90%; max-width:400px; background:var(--color-bg-primary); border:1px solid var(--color-border-primary); border-radius:20px; padding:28px 24px; box-shadow:0 25px 60px rgba(0, 0, 0, 0.35); display:flex; flex-direction:column; align-items:center; text-align:center; transform:scale(0.95); transition:transform 0.2s ease-out;">

        {{-- Animated Ring with Centered Check Icon --}}
        <div style="position:relative; width:64px; height:64px; margin-bottom:18px; display:flex; align-items:center; justify-content:center;">
            {{-- Outer spinning ring --}}
            <div style="position:absolute; inset:0; border-radius:50%; border:3.5px solid rgba(16, 185, 129, 0.18); border-top-color:#10b981; border-right-color:#10b981; animation:cdo-spin 0.85s linear infinite;"></div>
            {{-- Inner soft background icon --}}
            <div style="width:44px; height:44px; border-radius:50%; background:rgba(16, 185, 129, 0.12); display:flex; align-items:center; justify-content:center; color:#10b981;">
                <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        {{-- Title & Subtitle --}}
        <h3 id="cdoTitle" style="margin:0 0 6px 0; font-size:16px; font-weight:800; color:var(--color-text-primary); letter-spacing:-0.01em;">
            Closing Deliverable…
        </h3>
        <p id="cdoSubtitle" style="margin:0 0 16px 0; font-size:12.5px; font-weight:500; color:var(--color-text-secondary); line-height:1.45; max-width:320px; word-break:break-word;">
            Finalizing approval and archiving deliverable…
        </p>

        {{-- Status Pill --}}
        <div style="display:inline-flex; align-items:center; gap:7px; font-size:11px; font-weight:600; color:#10b981; background:rgba(16, 185, 129, 0.1); border:1px solid rgba(16, 185, 129, 0.22); padding:5px 14px; border-radius:999px;">
            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#10b981; animation:cdo-pulse 1.1s ease-in-out infinite;"></span>
            <span id="cdoStatusText">Submitting final changes…</span>
        </div>
    </div>
</div>

<style>
@keyframes cdo-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
@keyframes cdo-pulse {
    0%, 100% { opacity: 0.35; transform: scale(0.85); }
    50% { opacity: 1; transform: scale(1.15); }
}
</style>

<script>
(function() {
    window.DeliverableClosingOverlay = {
        overlay: null,
        modal: null,
        titleEl: null,
        subtitleEl: null,
        statusTextEl: null,

        init() {
            this.overlay = document.getElementById('deliverableClosingOverlay');
            if (!this.overlay) return;
            this.modal = this.overlay.querySelector('.deliverable-closing-modal');
            this.titleEl = document.getElementById('cdoTitle');
            this.subtitleEl = document.getElementById('cdoSubtitle');
            this.statusTextEl = document.getElementById('cdoStatusText');
        },

        show(options = {}) {
            this.init();
            if (!this.overlay) return;

            if (this.titleEl) {
                this.titleEl.textContent = options.title || 'Closing Deliverable…';
            }
            if (this.subtitleEl) {
                this.subtitleEl.textContent = options.subtitle || 'Finalizing approval and archiving deliverable…';
            }
            if (this.statusTextEl) {
                this.statusTextEl.textContent = options.status || 'Submitting final changes…';
            }

            this.overlay.style.display = 'flex';
            requestAnimationFrame(() => {
                this.overlay.style.opacity = '1';
                if (this.modal) this.modal.style.transform = 'scale(1)';
            });
        },

        hide(delay = 0) {
            this.init();
            if (!this.overlay) return;

            const doHide = () => {
                this.overlay.style.opacity = '0';
                if (this.modal) this.modal.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.overlay.style.display = 'none';
                }, 200);
            };

            if (delay > 0) {
                setTimeout(doHide, delay);
            } else {
                doHide();
            }
        }
    };

    window.showDeliverableClosingOverlay = (opts) => window.DeliverableClosingOverlay.show(opts);
    window.hideDeliverableClosingOverlay = (delay) => window.DeliverableClosingOverlay.hide(delay);
})();
</script>
