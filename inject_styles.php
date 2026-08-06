<?php
$projectFile = 'resources/views/projects/show.blade.php';
$deliverableFile = 'resources/views/deliverables/show.blade.php';

$projectContent = file_get_contents($projectFile);
$deliverableContent = file_get_contents($deliverableFile);

// Extract the main style block from projects/show.blade.php
preg_match('/<style>.*?<\/style>/s', $projectContent, $matches);
if (isset($matches[0])) {
    $styles = $matches[0];
    
    // Check if we already injected it (to avoid duplicates)
    if (strpos($deliverableContent, '/* Content Deliverables Table Styles */') === false) {
        // Inject styles right before our custom style block
        $deliverableContent = str_replace('<style>', $styles . "\n\n    <style>", $deliverableContent);
        file_put_contents($deliverableFile, $deliverableContent);
        echo "Styles injected successfully.\n";
    } else {
        echo "Styles already exist in deliverables/show.blade.php.\n";
    }
} else {
    echo "Styles not found in projects/show.blade.php.\n";
}
