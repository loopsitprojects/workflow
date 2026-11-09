<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $writer;
    private User $designer;
    private User $approver;
    private User $brandManager;
    private Brand $brand;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('s3');

        $this->admin = User::create([
            'name' => 'Admin Boss',
            'username' => 'admin_img',
            'email' => 'admin_img@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Admin',
        ]);

        $this->brandManager = User::create([
            'name' => 'BM User',
            'username' => 'bm_img',
            'email' => 'bm_img@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Brand Manager',
        ]);

        $this->writer = User::create([
            'name' => 'Writer User',
            'username' => 'writer_img',
            'email' => 'writer_img@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Writer',
        ]);

        $this->designer = User::create([
            'name' => 'Designer User',
            'username' => 'designer_img',
            'email' => 'designer_img@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Designer',
        ]);

        $this->approver = User::create([
            'name' => 'Approver User',
            'username' => 'approver_img',
            'email' => 'approver_img@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Approver',
        ]);

        $this->brand = Brand::create([
            'name' => 'Image Test Brand',
            'slug' => 'image-test-brand',
        ]);
        $this->brand->members()->attach([
            $this->admin->id,
            $this->brandManager->id,
            $this->writer->id,
            $this->designer->id,
            $this->approver->id,
        ]);

        $this->project = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Image Test Campaign',
            'status' => 'In Progress',
            'priority' => 'Medium',
            'type' => 'Standard',
            'workflow_type' => 'campaign',
            'writer_id' => $this->writer->id,
            'brand_manager_id' => $this->brandManager->id,
            'approver_id' => $this->approver->id,
            'created_by' => $this->brandManager->id,
        ]);
    }

    public function test_deliverable_creation_with_reference_image_uploads()
    {
        $this->actingAs($this->brandManager);

        $refImage1 = UploadedFile::fake()->image('moodboard_ref.png', 600, 400);
        $refImage2 = UploadedFile::fake()->image('palette_ref.jpg', 800, 600);

        $response = $this->post(route('deliverables.store'), [
            'project_id' => $this->project->id,
            'title' => 'Social Campaign Post',
            'task_type' => 'Standard',
            'post_type' => 'Static Post',
            'priority' => 'High Priority',
            'status' => 'To Do',
            'progress_percent' => 0,
            'writer_id' => $this->writer->id,
            'concept' => '<p>Visual concept with vibrant colors</p>',
            'reference_files' => [$refImage1, $refImage2],
        ]);

        $response->assertSessionHasNoErrors();
        $deliverable = Deliverable::where('title', 'Social Campaign Post')->first();
        $this->assertNotNull($deliverable);

        $refFiles = $deliverable->getReferenceFilesArray();
        $this->assertCount(2, $refFiles);
        $this->assertStringContainsString('moodboard-ref', $refFiles[0]);
        $this->assertStringContainsString('palette-ref', $refFiles[1]);
    }

    public function test_deliverable_update_with_new_reference_image()
    {
        $deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Update Image Test',
            'task_type' => 'Standard',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'writer_id' => $this->writer->id,
            'approval_stage' => 'Writer',
            'progress_percent' => 10,
            'created_by' => $this->writer->id,
            'reference_uploaded_by' => $this->writer->id,
        ]);

        $this->actingAs($this->writer);

        $newRef = UploadedFile::fake()->image('updated_reference.png', 400, 400);

        $response = $this->put(route('deliverables.update', $deliverable), [
            'project_id' => $this->project->id,
            'title' => 'Update Image Test - Revised',
            'task_type' => 'Standard',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'progress_percent' => 15,
            'reference_file' => $newRef,
        ]);

        $response->assertSessionHasNoErrors();
        $deliverable->refresh();

        $this->assertEquals('Update Image Test - Revised', $deliverable->title);
        $this->assertNotNull($deliverable->reference_file);
        $this->assertStringContainsString('updated-reference', $deliverable->reference_file);
    }

    public function test_designer_stage_artwork_image_upload_and_download()
    {
        $deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Banner Artwork Item',
            'task_type' => 'Standard',
            'priority' => 'High Priority',
            'status' => 'In Progress',
            'writer_id' => $this->writer->id,
            'designer_id' => $this->designer->id,
            'approval_stage' => 'Designer',
            'progress_percent' => 50,
            'created_by' => $this->brandManager->id,
        ]);

        $this->actingAs($this->designer);

        $artwork1 = UploadedFile::fake()->image('banner_v1.png', 1200, 628);
        $artwork2 = UploadedFile::fake()->image('banner_v2.jpg', 1080, 1080);

        $response = $this->post(route('deliverables.submit', $deliverable), [
            'action' => 'save_only',
            'final_designs_files' => [$artwork1, $artwork2],
        ]);

        $response->assertSessionHasNoErrors();
        $deliverable->refresh();

        // Check artwork was saved
        $artFiles = $deliverable->getFinalDesignsArray();
        $this->assertCount(2, $artFiles);
        $this->assertStringContainsString('banner-v1', $artFiles[0]);
        $this->assertStringContainsString('banner-v2', $artFiles[1]);

        // Download first artwork file
        $downloadResponse = $this->get(route('deliverables.download-artwork', ['deliverable' => $deliverable, 'index' => 0]));
        $this->assertTrue(in_array($downloadResponse->status(), [200, 302]));
    }

    public function test_revision_request_with_markup_image_upload()
    {
        $deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Proofing Deliverable',
            'task_type' => 'Standard',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'writer_id' => $this->writer->id,
            'designer_id' => $this->designer->id,
            'approver_id' => $this->approver->id,
            'approval_stage' => 'AM/BD',
            'progress_percent' => 70,
            'created_by' => $this->brandManager->id,
        ]);

        $this->actingAs($this->brandManager);

        $markupImage = UploadedFile::fake()->image('screenshot_markup.png', 800, 600);

        $response = $this->post(route('deliverables.revisions', $deliverable), [
            'revision_instructions' => 'Please align the logo 20px to the left as shown in markup screenshot.',
            'revision_target' => 'designer',
            'revision_image' => $markupImage,
        ]);

        $response->assertSessionHasNoErrors();
        $deliverable->refresh();

        $this->assertCount(1, $deliverable->revisionsHistory);
        $latestRevision = $deliverable->revisionsHistory->first();
        $this->assertNotNull($latestRevision->image_path);
        $this->assertStringContainsString('screenshot-markup', $latestRevision->image_path);
    }

    public function test_brand_logo_image_upload_and_project_brief_upload()
    {
        $this->actingAs($this->admin);

        // 1. Brand Logo Upload
        $brandLogo = UploadedFile::fake()->image('brand_main_logo.png', 300, 300);
        $brandResponse = $this->post(route('brands.store'), [
            'name' => 'Logo Test Brand',
            'slug' => 'logo-test-brand',
            'logo' => $brandLogo,
        ]);

        $brandResponse->assertSessionHasNoErrors();
        $createdBrand = Brand::where('slug', 'logo-test-brand')->first();
        $this->assertNotNull($createdBrand);
        $this->assertNotNull($createdBrand->logo_url);
        $this->assertStringContainsString('brand_logos/', $createdBrand->logo_url);

        // 2. Project Brief Image/Doc Upload
        $briefFile = UploadedFile::fake()->image('campaign_brief_visual.jpg', 1200, 1600);
        $projectResponse = $this->post(route('projects.store'), [
            'brand_id' => $createdBrand->id,
            'name' => 'Visual Brief Campaign',
            'status' => 'To Do',
            'priority' => 'Medium',
            'type' => 'Standard',
            'workflow_type' => 'campaign',
            'brief_file' => $briefFile,
        ]);

        $projectResponse->assertSessionHasNoErrors();
        $createdProject = Project::where('name', 'Visual Brief Campaign')->first();
        $this->assertNotNull($createdProject);
        $this->assertNotNull($createdProject->brief_file_path);
        $this->assertStringContainsString('briefs/', $createdProject->brief_file_path);
    }

    public function test_presigned_url_generation_for_valid_image_formats()
    {
        $this->actingAs($this->writer);

        foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $ext) {
            $response = $this->postJson(route('deliverables.presigned-url'), [
                'filename' => "sample_design.{$ext}",
                'folder' => 'artwork',
            ]);

            $response->assertStatus(200);
            $response->assertJsonStructure(['url', 'path', 'full_url']);
            $this->assertStringContainsString(".{$ext}", $response->json('path'));
        }
    }

    public function test_export_ppt_with_uploaded_artwork_and_reference_images()
    {
        $deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Export PPT Images Deliverable',
            'task_type' => 'Standard',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'writer_id' => $this->writer->id,
            'designer_id' => $this->designer->id,
            'approval_stage' => 'AM/BD',
            'progress_percent' => 70,
            'created_by' => $this->brandManager->id,
        ]);

        // Upload reference file
        $this->actingAs($this->brandManager);
        $ref = UploadedFile::fake()->image('ref_for_ppt.jpg', 300, 300);
        $this->post(route('deliverables.submit', $deliverable), [
            'action' => 'save_only',
            'reference_files' => [$ref],
        ]);

        // Upload artwork file
        $this->actingAs($this->designer);
        $art = UploadedFile::fake()->image('art_for_ppt.png', 400, 400);
        $this->post(route('deliverables.submit', $deliverable), [
            'action' => 'save_only',
            'final_designs_files' => [$art],
        ]);

        $deliverable->refresh();
        $this->assertNotEmpty($deliverable->reference_file);
        $this->assertNotEmpty($deliverable->final_designs);

        // Test PPT export with embedded images
        $this->actingAs($this->writer);
        $response = $this->get(route('deliverables.export.ppt', $deliverable));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
    }
}
