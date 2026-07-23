<?php
$file = 'resources/views/projects/show.blade.php';
$content = file_get_contents($file);

// 1. Remove inline onsubmit from submitStageForm
$pattern = '/<form id="submitStageForm" method="POST" enctype="multipart\/form-data" style="display:none; align-items:center; gap:12px;" onsubmit="(.*?)">/is';
$replacement = '<form id="submitStageForm" method="POST" enctype="multipart/form-data" style="display:none; align-items:center; gap:12px;">';
$content = preg_replace($pattern, $replacement, $content);

// 2. Append JS script
$js = <<<'EOD'

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ajaxForms = ['submitStageForm', 'revisionsForm', 'artworkDeliveryForm'];
    
    ajaxForms.forEach(formId => {
        const form = document.getElementById(formId);
        if (!form) return;
        
        // Track which button was clicked
        form.addEventListener('click', function(e) {
            const btn = e.target.closest('button[type="submit"]');
            if (btn) {
                form._submitter = btn;
            }
        });
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitter = form._submitter || form.querySelector('button[type="submit"]');
            
            // Set all buttons to disabled/uploading state
            const allBtns = form.querySelectorAll('button[type="submit"]');
            allBtns.forEach(b => {
                if (!b.dataset.orig) b.dataset.orig = b.innerHTML;
                b.style.pointerEvents = 'none';
                b.style.opacity = '0.7';
                if (b === submitter) {
                    b.innerHTML = '<svg style="animation: spin 1s linear infinite; height:1em; width:1em; display:inline-block; margin-right:6px; vertical-align:middle;" viewBox="0 0 24 24"><circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Uploading (0%)...';
                }
            });
            
            const formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.append(submitter.name, submitter.value);
            }
            
            const xhr = new XMLHttpRequest();
            xhr.open(form.method || 'POST', form.action, true);
            // Laravel needs this for proper JSON validation error responses
            xhr.setRequestHeader('Accept', 'application/json');
            // If there's no CSRF token in the form, make sure we have it from head
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfMeta.content);
            }
            
            xhr.upload.onprogress = function(event) {
                if (event.lengthComputable) {
                    const percentComplete = Math.round((event.loaded / event.total) * 100);
                    if (submitter) {
                        submitter.innerHTML = '<svg style="animation: spin 1s linear infinite; height:1em; width:1em; display:inline-block; margin-right:6px; vertical-align:middle;" viewBox="0 0 24 24"><circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Uploading (' + percentComplete + '%)...';
                    }
                }
            };
            
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    // Success! Redirect or reload.
                    // If Laravel returned a redirect, xhr follows it automatically.
                    // The safest bet is just to reload the page to see changes.
                    if (submitter) submitter.innerHTML = 'Success!';
                    window.location.reload();
                } else if (xhr.status === 422) {
                    // Validation errors
                    try {
                        const response = JSON.parse(xhr.responseText);
                        let errMsg = "Validation Error:\n";
                        if (response.errors) {
                            for (const field in response.errors) {
                                errMsg += "- " + response.errors[field].join("\n- ") + "\n";
                            }
                        } else {
                            errMsg += response.message || "Invalid input data.";
                        }
                        alert(errMsg);
                    } catch(e) {
                        alert('A validation error occurred, but the response was invalid.');
                    }
                    resetButtons();
                } else {
                    // Server error
                    alert('An error occurred while uploading. Server responded with: ' + xhr.status);
                    resetButtons();
                }
            };
            
            xhr.onerror = function() {
                alert('A network error occurred while uploading.');
                resetButtons();
            };
            
            function resetButtons() {
                allBtns.forEach(b => {
                    if (b.dataset.orig) b.innerHTML = b.dataset.orig;
                    b.style.pointerEvents = 'auto';
                    b.style.opacity = '1';
                });
            }
            
            xhr.send(formData);
        });
    });
});
</script>
EOD;

$content = str_replace('</body>', $js . "\n</body>", $content);
file_put_contents($file, $content);
echo "Added AJAX upload handler.\n";
