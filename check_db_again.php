<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Deliverable::all() as $d) {
    echo "ID: {$d->id} | Title: {$d->title} | final_designs: '{$d->final_designs}' | final_designs_link: '{$d->final_designs_link}'\n";
}
