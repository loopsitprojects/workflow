<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$file = Illuminate\Http\UploadedFile::fake()->create('Pringles.mp4', 15000);
$originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
$safeName = \Illuminate\Support\Str::slug($originalName);
$filename = date('Y-m-d') . '_' . $safeName . '.' . $file->getClientOriginalExtension();

try {
    $path = \Illuminate\Support\Facades\Storage::disk('s3')->putFileAs('references', $file, $filename);
    echo 'Success: ' . $path;
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
