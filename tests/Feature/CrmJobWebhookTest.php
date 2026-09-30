<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CrmJob;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmJobWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_webhook_creates_job_and_matches_brand_by_name()
    {
        $brand = Brand::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
        ]);

        $payload = [
            'job_id'     => 'CRM-JOB-101',
            'brand_name' => 'acme corporation', // lower-case to test case-insensitive match
            'title'      => 'Holiday Banner Campaign',
            'deadline'   => '2026-12-25',
        ];

        $response = $this->postJson('/api/webhooks/crm-jobs', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('processed.0.crm_job_id', 'CRM-JOB-101');
        $response->assertJsonPath('processed.0.matched_brand_id', $brand->id);

        $this->assertDatabaseHas('crm_jobs', [
            'crm_job_id' => 'CRM-JOB-101',
            'brand_id'   => $brand->id,
            'title'      => 'Holiday Banner Campaign',
            'deadline'   => '2026-12-25 00:00:00',
            'status'     => 'available',
        ]);
    }

    public function test_crm_webhook_handles_batch_of_jobs_idempotently()
    {
        $brand = Brand::create([
            'name' => 'Nike Global',
            'slug' => 'nike-global',
        ]);

        $payload = [
            'jobs' => [
                [
                    'job_id'     => 'NIKE-001',
                    'brand_name' => 'Nike Global',
                    'title'      => 'Nike Air Max Launch',
                ],
                [
                    'job_id'     => 'NIKE-002',
                    'brand_name' => 'Nike Global',
                    'title'      => 'Nike Pro Training',
                ],
            ]
        ];

        $response = $this->postJson('/api/webhooks/crm-jobs', $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertCount(2, CrmJob::all());

        // Re-sending should update rather than duplicate
        $response2 = $this->postJson('/api/webhooks/crm-jobs', $payload);
        $response2->assertStatus(200);
        $this->assertCount(2, CrmJob::all());
    }

    public function test_api_returns_jobs_for_brand()
    {
        $brand = Brand::create(['name' => 'Tesla', 'slug' => 'tesla']);
        CrmJob::create([
            'crm_job_id' => 'TSLA-01',
            'brand_name' => 'Tesla',
            'brand_id'   => $brand->id,
            'title'      => 'Model 3 Promo',
            'status'     => 'available',
        ]);
        CrmJob::create([
            'crm_job_id' => 'TSLA-02',
            'brand_name' => 'Tesla',
            'brand_id'   => $brand->id,
            'title'      => 'Cybertruck Promo',
            'status'     => 'assigned', // already used
        ]);

        $response = $this->getJson("/api/brands/{$brand->id}/crm-jobs");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'jobs');
        $response->assertJsonPath('jobs.0.crm_job_id', 'TSLA-01');
    }

    public function test_creating_project_with_job_number_marks_crm_job_assigned()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Puma', 'slug' => 'puma']);

        $crmJob = CrmJob::create([
            'crm_job_id' => 'PUMA-999',
            'brand_name' => 'Puma',
            'brand_id'   => $brand->id,
            'title'      => 'Puma Running Event',
            'status'     => 'available',
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('projects.store'), [
            'brand_id'      => $brand->id,
            'name'          => 'Puma Running Campaign',
            'job_number'    => 'PUMA-999',
            'status'        => 'To commence',
            'priority'      => 'Medium',
            'type'          => 'primary',
            'workflow_type' => 'campaign',
        ]);

        $response->assertSessionHasNoErrors();

        $crmJob->refresh();
        $this->assertEquals('assigned', $crmJob->status);
        $this->assertNotNull($crmJob->project_id);
    }
}
