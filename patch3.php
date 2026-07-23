<?php
$file = 'resources/views/projects/show.blade.php';
$content = file_get_contents($file);
$pattern = '/<script>\s*\/\*\s*(?:.*?)\s*Form loading states(.*?)\s*<\/style>/is';
$content = preg_replace($pattern, '', $content);
file_put_contents($file, $content);
echo "Removed old form loading states block.\n";
