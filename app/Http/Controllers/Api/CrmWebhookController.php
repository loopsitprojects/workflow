<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CrmJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrmWebhookController extends Controller
{
    /**
     * Handle incoming CRM Job webhook.
     */
    public function handleJobWebhook(Request $request)
    {
        // 1. Optional Secret Validation
        $configuredSecret = env('CRM_WEBHOOK_SECRET');
        if (!empty($configuredSecret)) {
            $providedSecret = $request->header('X-CRM-Secret')
                ?? $request->header('X-Webhook-Secret')
                ?? $request->bearerToken()
                ?? $request->query('secret');

            if ($providedSecret !== $configuredSecret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Invalid webhook secret token.',
                ], 401);
            }
        }

        // 2. Normalize payload into a list of jobs
        $payload = $request->all();
        $items = [];

        if (isset($payload['jobs']) && is_array($payload['jobs'])) {
            $items = $payload['jobs'];
        } elseif (array_is_list($payload)) {
            $items = $payload;
        } else {
            $items = [$payload];
        }

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No job payload found.',
            ], 422);
        }

        $processed = [];
        $errors = [];

        foreach ($items as $index => $item) {
            $jobId = $item['job_id'] ?? $item['job_number'] ?? $item['id'] ?? null;
            $brandName = $item['brand_name'] ?? $item['brand'] ?? $item['client'] ?? null;
            $title = $item['title'] ?? $item['name'] ?? $item['job_title'] ?? null;
            $deadline = $item['deadline'] ?? $item['due_date'] ?? null;

            if (empty($jobId) || empty($brandName)) {
                $errors[] = "Item at index {$index} is missing required 'job_id' or 'brand_name'.";
                continue;
            }

            // Find matching Brand by name (case-insensitive) or slug
            $cleanBrand = trim($brandName);
            $matchedBrand = Brand::whereRaw('LOWER(name) = ?', [strtolower($cleanBrand)])
                ->orWhere('slug', Str::slug($cleanBrand))
                ->first();

            $parsedDate = null;
            if (!empty($deadline)) {
                try {
                    $parsedDate = Carbon::parse($deadline)->format('Y-m-d');
                } catch (\Exception $e) {
                    $parsedDate = null;
                }
            }

            // Upsert CRM job (keep assigned status if already assigned)
            $existing = CrmJob::where('crm_job_id', (string) $jobId)->first();
            $status = $existing && $existing->status === 'assigned' ? 'assigned' : 'available';

            $crmJob = CrmJob::updateOrCreate(
                ['crm_job_id' => (string) $jobId],
                [
                    'brand_name' => $cleanBrand,
                    'brand_id'   => $matchedBrand?->id,
                    'title'      => $title,
                    'deadline'   => $parsedDate,
                    'status'     => $status,
                    'payload'    => $item,
                ]
            );

            $processed[] = [
                'crm_job_id'       => $crmJob->crm_job_id,
                'brand_name'       => $crmJob->brand_name,
                'matched_brand_id' => $crmJob->brand_id,
                'matched_brand'    => $matchedBrand?->name,
                'status'           => $crmJob->status,
            ];
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Processed ' . count($processed) . ' CRM job(s).',
            'processed' => $processed,
            'errors'    => $errors,
        ], count($processed) > 0 ? 200 : 422);
    }

    /**
     * Get available CRM jobs for a given brand (by ID or slug).
     */
    public function getJobsForBrand($brandIdentifier)
    {
        $brand = is_numeric($brandIdentifier)
            ? Brand::find($brandIdentifier)
            : Brand::where('slug', $brandIdentifier)->orWhereRaw('LOWER(name) = ?', [strtolower(trim($brandIdentifier))])->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
            ], 404);
        }

        $jobs = CrmJob::where('status', 'available')
            ->where(function ($q) use ($brand) {
                $q->where('brand_id', $brand->id)
                  ->orWhereRaw('LOWER(brand_name) = ?', [strtolower(trim($brand->name))]);
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'crm_job_id', 'title', 'deadline']);

        return response()->json([
            'success' => true,
            'brand'   => $brand->name,
            'jobs'    => $jobs,
        ]);
    }
}
