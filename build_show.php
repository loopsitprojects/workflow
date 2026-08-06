<?php
$projectFile = 'resources/views/projects/show.blade.php';
$content = file_get_contents($projectFile);

// 1. Extract Modal HTML
preg_match('/<!-- Detail Modal -->.*?<!-- Hidden Delete Form for Modal -->/s', $content, $htmlMatches);
$modalHtml = $htmlMatches[0] ?? '';

// 2. Extract Modal JS
// The JS starts around line 2213 with <script>. But we only want the JS related to the modal.
// Actually, let's just grab the whole JS block, and we'll manually clean it up later if needed,
// OR just grab the specific functions. It's safer to just move the whole <script> block and split it.
// Let's just create Deliverables/show.blade.php first.

if ($modalHtml) {
    $blade = "<x-layout title=\"{{ \$deliverable->title }}\">\n";
    $blade .= "    @php\n";
    $blade .= "        \$isAdmin = auth()->user()->isAdmin();\n";
    $blade .= "        \$userRole = strtolower(str_replace(' ', '', auth()->user()->role));\n";
    $blade .= "        \$currentUserId = auth()->id();\n";
    $blade .= "    @endphp\n\n";
    
    // Add CSS for modal overrides
    $blade .= "    <style>\n";
    $blade .= "        .cd-modal-overlay { position: relative !important; display: block !important; opacity: 1 !important; z-index: 1 !important; background: transparent !important; backdrop-filter: none !important; padding: 20px 0; }\n";
    $blade .= "        .cd-modal { max-width: 1000px !important; width: 100% !important; margin: 0 auto; transform: none !important; box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }\n";
    $blade .= "        #modalTaskTitle { border: 1px solid var(--color-border-primary) !important; background: var(--color-bg-secondary) !important; }\n";
    $blade .= "    </style>\n\n";

    $blade .= "    <div class=\"container mx-auto px-4\">\n";
    $blade .= "        <div class=\"mb-6\">\n";
    $blade .= "            <a href=\"{{ route('projects.show', \$deliverable->project_id) }}\" class=\"cd-btn cd-btn-outline\" style=\"display:inline-flex; align-items:center; gap:8px;\">\n";
    $blade .= "                <svg width=\"16\" height=\"16\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10 19l-7-7m0 0l7-7m-7 7h18\"/></svg>\n";
    $blade .= "                Back to Project\n";
    $blade .= "            </a>\n";
    $blade .= "        </div>\n\n";

    // Inject the HTML, removing the hidden delete form part and the overlay onclick
    $cleanHtml = str_replace('onclick="closeTaskModal(event)"', '', $modalHtml);
    $cleanHtml = str_replace('onclick="event.stopPropagation()"', '', $cleanHtml);
    $cleanHtml = str_replace('<!-- Hidden Delete Form for Modal -->', '', $cleanHtml);
    // Remove the close button
    $cleanHtml = preg_replace('/<button onclick="closeTaskModal\(\)".*?<\/button>/s', '', $cleanHtml);
    
    $blade .= "        " . $cleanHtml . "\n";
    $blade .= "    </div>\n\n";
    
    // Copy the entire script block from projects/show.blade.php
    preg_match('/<script>.*?<\/script>/s', $content, $scriptMatches);
    $script = $scriptMatches[0] ?? '';
    
    // We will append a script to hydrate the page
    $blade .= $script . "\n";
    
    $blade .= "    <script>\n";
    $blade .= "        const AUTH_USER_ID = {{ auth()->id() }};\n";
    $blade .= "        const AUTH_USER_ROLE = '{{ auth()->user()->role }}';\n";
    $blade .= "        \n";
    $blade .= "        document.addEventListener('DOMContentLoaded', () => {\n";
    $blade .= "            const task = @json(\$deliverable);\n";
    $blade .= "            openTaskModal(task);\n";
    $blade .= "        });\n";
    $blade .= "    </script>\n";
    
    $blade .= "</x-layout>\n";

    if (!is_dir('resources/views/deliverables')) {
        mkdir('resources/views/deliverables');
    }
    file_put_contents('resources/views/deliverables/show.blade.php', $blade);
    echo 'Created deliverables/show.blade.php successfully.';
} else {
    echo 'Modal HTML not found.';
}
