<?php
$file_path = 'resources/views/projects/show.blade.php';
$content = file_get_contents($file_path);

// Replace the modal HTML
$modalHtml = '<img id="imagePreviewSrc" src="" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 12px; border: 4px solid var(--color-bg-primary); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">' . "\n" .
             '            <video id="videoPreviewSrc" src="" controls style="display:none; max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 12px; border: 4px solid var(--color-bg-primary); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);"></video>';

$content = str_replace('<img id="imagePreviewSrc" src="" style="max-width: 100%; max-height: 80vh; object-fit: contain; border-radius: 12px; border: 4px solid var(--color-bg-primary); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">', $modalHtml, $content);

// Replace the openImagePreview function logic
$funcFind = "document.getElementById('imagePreviewSrc').src = url;";
$funcReplace = "const isVideo = url.match(/\.(mp4|webm|ogg|mov)$/i);\n" .
               "            if (isVideo) {\n" .
               "                document.getElementById('imagePreviewSrc').style.display = 'none';\n" .
               "                document.getElementById('imagePreviewSrc').src = '';\n" .
               "                document.getElementById('videoPreviewSrc').style.display = 'block';\n" .
               "                document.getElementById('videoPreviewSrc').src = url;\n" .
               "            } else {\n" .
               "                document.getElementById('videoPreviewSrc').style.display = 'none';\n" .
               "                document.getElementById('videoPreviewSrc').src = '';\n" .
               "                document.getElementById('imagePreviewSrc').style.display = 'block';\n" .
               "                document.getElementById('imagePreviewSrc').src = url;\n" .
               "            }";
               
$content = str_replace($funcFind, $funcReplace, $content);

// Replace the closeImagePreview function logic
$closeFind = "function closeImagePreview(e = null) {";
$closeReplace = "function closeImagePreview(e = null) {\n" .
                "            document.getElementById('videoPreviewSrc').pause();\n" .
                "            document.getElementById('videoPreviewSrc').src = '';";
$content = str_replace($closeFind, $closeReplace, $content);

file_put_contents($file_path, $content);
echo "Updated openImagePreview logic.\n";
