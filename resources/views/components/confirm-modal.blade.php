<div id="globalConfirmModalOverlay"
     style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.75); backdrop-filter:blur(8px); z-index:99999; justify-content:center; align-items:center; opacity:0; transition:opacity 0.2s ease;">
    <div id="globalConfirmModalBox"
         style="background:var(--color-bg-primary, #1f2937); border:1px solid var(--color-border-primary, rgba(255,255,255,0.1)); border-radius:20px; padding:28px; width:420px; max-width:92vw; box-shadow:0 25px 60px rgba(0,0,0,0.4); transform:scale(0.95); transition:transform 0.2s ease;">
        
        <div style="display:flex; align-items:flex-start; gap:16px; margin-bottom:20px;">
            <div id="globalConfirmIconBg" style="width:44px; height:44px; border-radius:12px; background:rgba(239,68,68,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg id="globalConfirmIconDanger" width="22" height="22" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <svg id="globalConfirmIconInfo" width="22" height="22" fill="none" stroke="#3b82f6" viewBox="0 0 24 24" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 id="globalConfirmTitle" style="font-size:16px; font-weight:800; color:var(--color-text-primary, #fff); margin:0 0 6px 0; line-height:1.3;">
                    Confirm Action
                </h3>
                <p id="globalConfirmMessage" style="font-size:13px; font-weight:500; color:var(--color-text-secondary, #9ca3af); margin:0; line-height:1.5;">
                    Are you sure you want to proceed with this action?
                </p>
            </div>
        </div>

        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:24px;">
            <button id="globalConfirmCancelBtn" type="button"
                    style="padding:9px 18px; border-radius:10px; font-size:12px; font-weight:700; color:var(--color-text-secondary, #9ca3af); background:var(--color-bg-secondary, #374151); border:1px solid var(--color-border-primary, rgba(255,255,255,0.1)); cursor:pointer; transition:all 0.15s;">
                Cancel
            </button>
            <button id="globalConfirmOkBtn" type="button"
                    style="padding:9px 22px; border-radius:10px; font-size:12px; font-weight:700; color:#ffffff; background:#ef4444; border:none; cursor:pointer; transition:all 0.15s; box-shadow:0 4px 12px rgba(239,68,68,0.3);">
                Confirm
            </button>
        </div>
    </div>
</div>

<script>
window.customConfirm = function(options = {}) {
    return new Promise((resolve) => {
        const overlay = document.getElementById('globalConfirmModalOverlay');
        const box = document.getElementById('globalConfirmModalBox');
        const titleEl = document.getElementById('globalConfirmTitle');
        const msgEl = document.getElementById('globalConfirmMessage');
        const okBtn = document.getElementById('globalConfirmOkBtn');
        const cancelBtn = document.getElementById('globalConfirmCancelBtn');
        const iconBg = document.getElementById('globalConfirmIconBg');
        const dangerIcon = document.getElementById('globalConfirmIconDanger');
        const infoIcon = document.getElementById('globalConfirmIconInfo');

        const title = typeof options === 'string' ? options : (options.title || 'Confirm Action');
        const message = options.message || options.text || '';
        const confirmText = options.confirmText || 'Confirm';
        const cancelText = options.cancelText || 'Cancel';
        const isDanger = options.isDanger !== false;

        titleEl.innerHTML = title;
        msgEl.innerHTML = message;
        okBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;

        if (isDanger) {
            iconBg.style.background = 'rgba(239, 68, 68, 0.15)';
            dangerIcon.style.display = 'block';
            infoIcon.style.display = 'none';
            okBtn.style.background = '#ef4444';
            okBtn.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.3)';
        } else {
            iconBg.style.background = 'rgba(59, 130, 246, 0.15)';
            dangerIcon.style.display = 'none';
            infoIcon.style.display = 'block';
            okBtn.style.background = '#3b82f6';
            okBtn.style.boxShadow = '0 4px 12px rgba(59, 130, 246, 0.3)';
        }

        overlay.style.display = 'flex';
        requestAnimationFrame(() => {
            overlay.style.opacity = '1';
            box.style.transform = 'scale(1)';
        });

        const close = (result) => {
            overlay.style.opacity = '0';
            box.style.transform = 'scale(0.95)';
            setTimeout(() => {
                overlay.style.display = 'none';
                resolve(result);
            }, 180);
        };

        okBtn.onclick = () => close(true);
        cancelBtn.onclick = () => close(false);
        overlay.onclick = (e) => {
            if (e.target === overlay) close(false);
        };
    });
};

window.confirmAction = function(event, title = 'Delete Item?', message = 'Are you sure you want to proceed?', isDanger = true) {
    if (event.datasetConfirmed === 'true') {
        delete event.datasetConfirmed;
        return true;
    }

    event.preventDefault();
    const form = event.target.closest('form') || event.target;

    window.customConfirm({
        title: title,
        message: message,
        confirmText: isDanger ? 'Delete' : 'Confirm',
        isDanger: isDanger
    }).then((confirmed) => {
        if (confirmed) {
            if (form && typeof form.submit === 'function') {
                form.datasetConfirmed = 'true';
                form.submit();
            }
        }
    });

    return false;
};
</script>
