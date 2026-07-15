<?php
$files = [
    'resources/views/projects/show.blade.php',
    'resources/views/deliverables/batch.blade.php'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        // Replace {{ $something->concept }} with {{ strip_tags($something->concept) }}
        $c = preg_replace('/\{\{\s*(\$(subtask|task|post)->(concept|caption|post_copy))\s*\}\}/', '{{ strip_tags($1) }}', $c);
        file_put_contents($f, $c);
        echo "Updated $f\n";
    }
}
