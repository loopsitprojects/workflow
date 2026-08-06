<?php
$content = file_get_contents('resources/views/projects/show.blade.php');
preg_match('/<!-- Detail Modal -->.*?<!-- Hidden Delete Form for Modal -->/s', $content, $matches);
if(isset($matches[0])) {
    if(!is_dir('resources/views/deliverables')) mkdir('resources/views/deliverables');
    file_put_contents('resources/views/deliverables/show.blade.php', str_replace('<!-- Hidden Delete Form for Modal -->', '', $matches[0]));
    echo 'Done';
} else {
    echo 'Not found';
}
