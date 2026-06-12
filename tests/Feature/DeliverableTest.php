<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can create a deliverable with subtasks containing notes and no priority', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);
    $writer = User::factory()->create(['role' => 'Writer']);

    $response = $this->actingAs($admin)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'title' => 'Parent Deliverable',
        'writer_id' => $writer->id,
        'deadline' => now()->addDays(7)->toDateString(),
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'subtasks' => [
            [
                'title' => 'Subtask Post 1',
                'post_type' => 'Static Post',
                'concept' => 'Test Concept 1',
                'caption' => 'Test Caption 1',
                'post_copy' => 'Test Copy 1',
                'reference' => 'https://example.com/ref1',
                'writer_id' => $writer->id,
            ],
            [
                'title' => 'Subtask Post 2',
                'post_type' => 'Carousel',
                'concept' => 'Test Concept 2',
                'caption' => 'Test Caption 2',
                'post_copy' => 'Test Copy 2',
                'reference' => 'https://example.com/ref2',
                'writer_id' => $writer->id,
            ]
        ]
    ]);

    $response->assertRedirect();
    
    // Assert parent deliverable was created
    $this->assertDatabaseHas('deliverables', [
        'title' => 'Parent Deliverable',
        'parent_deliverable_id' => null,
        'priority' => 'Medium',
    ]);

    // Assert subtasks were created with correct priority and content fields
    $this->assertDatabaseHas('deliverables', [
        'title' => 'Subtask Post 1',
        'concept' => 'Test Concept 1',
        'caption' => 'Test Caption 1',
        'post_copy' => 'Test Copy 1',
        'reference' => 'https://example.com/ref1',
        'priority' => 'Medium',
    ]);

    $this->assertDatabaseHas('deliverables', [
        'title' => 'Subtask Post 2',
        'concept' => 'Test Concept 2',
        'caption' => 'Test Caption 2',
        'post_copy' => 'Test Copy 2',
        'reference' => 'https://example.com/ref2',
        'priority' => 'Medium',
    ]);
});

test('dashboard only shows pending deliverables where the logged-in user is responsible for the current stage', function () {
    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);

    $writer = User::factory()->create(['role' => 'Writer']);
    $approver = User::factory()->create(['role' => 'Approver']);

    // d1 is in Writer stage (pending for writer)
    $d1 = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable 1 (Writer Stage)',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'deadline' => now()->addDays(7)->toDateString(),
    ]);

    // d2 is in Approver stage (pending for approver)
    $d2 = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable 2 (Approver Stage)',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'approval_stage' => 'Approver',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'deadline' => now()->addDays(7)->toDateString(),
    ]);

    // d3 is completed (Done)
    $d3 = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable 3 (Completed)',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'approval_stage' => 'Closed',
        'status' => 'Done',
        'task_type' => 'Deliverable',
        'progress_percent' => 100,
        'deadline' => now()->subDays(2)->toDateString(),
    ]);

    // d4 is a parent task (batch) with writer_id set to $writer, so it would normally match, but it has subtasks
    $d4 = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable 4 (Parent Batch)',
        'writer_id' => $writer->id,
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'deadline' => now()->addDays(7)->toDateString(),
    ]);
    
    // Create a subtask for d4
    $subtaskOfD4 = Deliverable::create([
        'parent_deliverable_id' => $d4->id,
        'project_id' => $project->id,
        'title' => 'Subtask of d4',
        'writer_id' => $writer->id,
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'deadline' => now()->addDays(7)->toDateString(),
    ]);

    // As Writer, they should see d1, d3, and subtaskOfD4, but NOT d2 (another user stage) or d4 (parent batch)
    $response = $this->actingAs($writer)->get(route('dashboard'));
    $response->assertStatus(200);
    $viewDeliverables = $response->viewData('deliverables');

    expect($viewDeliverables->contains('id', $d1->id))->toBeTrue();
    expect($viewDeliverables->contains('id', $d2->id))->toBeFalse();
    expect($viewDeliverables->contains('id', $d3->id))->toBeTrue();
    expect($viewDeliverables->contains('id', $d4->id))->toBeFalse();
    expect($viewDeliverables->contains('id', $subtaskOfD4->id))->toBeTrue();

    // As Approver, they should see d2 (Approver stage, active) and d3 (Done, participated), but NOT d1 (Writer stage, active)
    $response = $this->actingAs($approver)->get(route('dashboard'));
    $response->assertStatus(200);
    $viewDeliverables = $response->viewData('deliverables');

    expect($viewDeliverables->contains('id', $d1->id))->toBeFalse();
    expect($viewDeliverables->contains('id', $d2->id))->toBeTrue();
    expect($viewDeliverables->contains('id', $d3->id))->toBeTrue();
});

test('only admin and brand manager can delete a deliverable', function () {
    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);

    $admin = User::factory()->create(['role' => 'Admin']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $writer = User::factory()->create(['role' => 'Writer']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Delete Test Deliverable',
        'writer_id' => $writer->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
    ]);

    // Writer tries to delete (should be forbidden - 403)
    $response = $this->actingAs($writer)->delete(route('deliverables.destroy', $deliverable));
    $response->assertStatus(403);
    $this->assertDatabaseHas('deliverables', ['id' => $deliverable->id]);

    // Brand Manager tries to delete (should succeed)
    $response = $this->actingAs($manager)->delete(route('deliverables.destroy', $deliverable));
    $response->assertRedirect();
    $this->assertDatabaseMissing('deliverables', ['id' => $deliverable->id]);

    // Re-create deliverable for Admin delete test
    $deliverable2 = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Delete Test Deliverable 2',
        'writer_id' => $writer->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
    ]);

    // Admin tries to delete (should succeed)
    $response = $this->actingAs($admin)->delete(route('deliverables.destroy', $deliverable2));
    $response->assertRedirect();
    $this->assertDatabaseMissing('deliverables', ['id' => $deliverable2->id]);
});

test('a designer can upload artwork and advance the stage', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);

    $designer = User::factory()->create(['role' => 'Designer']);
    $brandManager = User::factory()->create(['role' => 'Brand Manager']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Artwork Test Deliverable',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'brand_manager_id' => $brandManager->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 50,
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->image('test_artwork.png');

    $response = $this->actingAs($designer)->post(route('deliverables.submit', $deliverable), [
        'action' => 'submit',
        'final_designs_file' => $file,
        'final_designs_link' => 'https://example.com/artwork',
    ]);

    $response->assertRedirect();
    
    $deliverable->refresh();

    // Verify it advanced to Final Approval stage
    expect($deliverable->approval_stage)->toBe('Final Approval');
    
    // Verify artwork file was stored
    expect($deliverable->final_designs)->not->toBeEmpty();
    expect($deliverable->final_designs)->toContain('storage/artwork/');
    
    // Verify artwork link was stored
    expect($deliverable->final_designs_link)->toBe('https://example.com/artwork');
});

test('a designer can save artwork without advancing the stage', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);

    $designer = User::factory()->create(['role' => 'Designer']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Artwork Save Test Deliverable',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 50,
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->image('save_artwork.png');

    $response = $this->actingAs($designer)->post(route('deliverables.submit', $deliverable), [
        'action' => 'save_only',
        'final_designs_file' => $file,
        'final_designs_link' => 'https://example.com/save-artwork-link',
    ]);

    $response->assertRedirect();
    
    $deliverable->refresh();

    // Verify it remained in Designer stage
    expect($deliverable->approval_stage)->toBe('Designer');
    
    // Verify artwork file was stored
    expect($deliverable->final_designs)->not->toBeEmpty();
    expect($deliverable->final_designs)->toContain('storage/artwork/');
    
    // Verify artwork link was stored
    expect($deliverable->final_designs_link)->toBe('https://example.com/save-artwork-link');
});

test('user can export batch pdf', function () {
    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);

    $writer = User::factory()->create(['role' => 'Writer']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Batch PDF Parent',
        'writer_id' => $writer->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
    ]);

    $subtask = Deliverable::create([
        'parent_deliverable_id' => $deliverable->id,
        'project_id' => $project->id,
        'title' => 'Batch PDF Subtask',
        'writer_id' => $writer->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
    ]);

    $response = $this->actingAs($writer)->get(route('deliverables.export-batch.pdf', $deliverable));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');

    // Render the view directly to assert that only the subtasks are shown as deliverables
    $html = view('deliverables.batch_pdf', [
        'deliverables' => $deliverable->subtasks,
        'parent' => $deliverable
    ])->render();

    expect($html)->toContain('Batch PDF Subtask');
    expect($html)->not->toContain('<h1>Batch PDF Parent</h1>');
});test('submitting a deliverable without specifying approver_id uses the pre-assigned approver_id', function () {
    $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand', 'description' => 'Test Brand Description']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Test Project',
        'workflow_type' => 'retainer',
        'priority' => 'Medium',
    ]);

    $writer = User::factory()->create(['role' => 'Writer']);
    $approver = User::factory()->create(['role' => 'Approver']);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Resubmit Test Deliverable',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
    ]);

    // Submit the stage transition WITHOUT passing approver_id in POST parameters
    $response = $this->actingAs($writer)->post(route('deliverables.submit', $deliverable), [
        'action' => 'submit',
    ]);

    $response->assertRedirect();
    
    $deliverable->refresh();

    // Verify it advanced to Approver stage and kept the same approver
    expect($deliverable->approval_stage)->toBe('Approver');
    expect($deliverable->approver_id)->toBe($approver->id);
});
