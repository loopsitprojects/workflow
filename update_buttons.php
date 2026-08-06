<?php
$content = file_get_contents('resources/views/projects/show.blade.php');

$search = '/<button type="button" onclick="event.stopPropagation\(\); openTaskModal\(\{\{ \$(\w+)->append.*?\}\}\)" class="quick-action-btn btn-view-quick">\s*<svg.*?<\/svg>\s*View\s*<\/button>/s';
$replace = '<a href="{{ route(\'deliverables.show\', $$1->id) }}" class="quick-action-btn btn-view-quick" onclick="event.stopPropagation()">
    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
    View
</a>';

$content = preg_replace($search, $replace, $content);
file_put_contents('resources/views/projects/show.blade.php', $content);
echo "Buttons updated.\n";
