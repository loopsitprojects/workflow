<?php

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('s3');
});

test('only the person who uploaded reference can remove it', function () {
    $uploader = User::factory()->create(['role' => 'Writer']);
    $otherUser = User::factory()->create(['role' => 'Writer']);
    $admin = User::factory()->create(['role' => 'Admin']);

    $brand = Brand::create(['name' => 'Brand A', 'slug' => 'brand-a']);
    $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Test Task',
        'status' => 'To Do',
        'approval_stage' => 'Assign',
        'reference_file' => '/references/reference.jpg',
        'reference_uploaded_by' => $uploader->id,
        'writer_id' => $uploader->id,
    ]);

    // Other user attempts to remove reference
    $this->actingAs($otherUser)
        ->post(route('deliverables.submit', $deliverable), [
            'action' => 'save_only',
            'delete_reference_file' => '1',
        ])
        ->assertStatus(403);

    expect($deliverable->fresh()->reference_file)->not->toBeNull();

    // Uploader removes reference
    $this->actingAs($uploader)
        ->post(route('deliverables.submit', $deliverable), [
            'action' => 'save_only',
            'delete_reference_file' => '1',
        ])
        ->assertRedirect();

    expect($deliverable->fresh()->reference_file)->toBeNull();
    expect($deliverable->fresh()->reference_uploaded_by)->toBeNull();
});

test('only the person who uploaded artwork can remove it', function () {
    $artUploader = User::factory()->create(['role' => 'Designer']);
    $otherDesigner = User::factory()->create(['role' => 'Designer']);

    $brand = Brand::create(['name' => 'Brand B', 'slug' => 'brand-b']);
    $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project 2', 'workflow_type' => 'campaign']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Artwork Task',
        'status' => 'In Progress',
        'approval_stage' => 'Designer',
        'final_designs' => 'https://s3.example.com/artwork/design.jpg',
        'artwork_uploaded_by' => $artUploader->id,
    ]);

    // Other designer attempts to delete artwork
    $this->actingAs($otherDesigner)
        ->post(route('deliverables.submit', $deliverable), [
            'delete_final_designs' => '1',
        ])
        ->assertStatus(403);

    expect($deliverable->fresh()->final_designs)->not->toBeNull();

    // Uploader deletes artwork
    $this->actingAs($artUploader)
        ->post(route('deliverables.submit', $deliverable), [
            'delete_final_designs' => '1',
        ])
        ->assertRedirect();

    expect($deliverable->fresh()->final_designs)->toBeNull();
    expect($deliverable->fresh()->artwork_uploaded_by)->toBeNull();
});
