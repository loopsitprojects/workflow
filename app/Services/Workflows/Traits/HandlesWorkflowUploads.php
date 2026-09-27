<?php

namespace App\Services\Workflows\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesWorkflowUploads
{
    /**
     * Move an uploaded file to storage (S3 with local fallback handled by disk configuration).
     */
    public function moveUploadedFile(UploadedFile $file, string $folder): string
    {
        if (!$file->isValid()) {
            throw new \Exception("File upload failed: " . $file->getErrorMessage());
        }
        
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);
        $filename = date('Y-m-d') . '_' . $safeName . '.' . $file->getClientOriginalExtension();
        
        try {
            $path = Storage::disk('s3')->putFileAs($folder, $file, $filename);
        } catch (\Throwable $e) {
            throw new \Exception("S3 Upload Exception: " . $e->getMessage());
        }

        if ($path === false) {
            throw new \Exception("Failed to upload file to S3. Please verify your AWS credentials and bucket permissions.");
        }
        return Storage::disk('s3')->url($path);
    }
}
