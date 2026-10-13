<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Services\Workflows\DirectDesignWorkflowService;
use App\Services\Workflows\WorkflowManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('direct design deliverable correctly resolves DirectDesignWorkflowService across any project type', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
    
    $retainerProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Retainer Project',
        'workflow_type' => 'retainer',
    ]);

    $campaignProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Project',
        'workflow_type' => 'campaign',
    ]);

    $directInRetainer = Deliverable::create([
        'project_id' => $retainerProject->id,
        'title' => 'Design In Retainer',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
    ]);

    $directInCampaign = Deliverable::create([
        'project_id' => $campaignProject->id,
        'title' => 'Design In Campaign',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
    ]);

    expect($directInRetainer->getWorkflow())->toBeInstanceOf(DirectDesignWorkflowService::class);
    expect($directInCampaign->getWorkflow())->toBeInstanceOf(DirectDesignWorkflowService::class);
});

test('direct design workflow calculates 3-stage progress milestones accurately', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-2']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project A',
        'workflow_type' => 'retainer',
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Design Task',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
    ]);

    expect($task->getStageProgress())->toBe(20);

    $task->approval_stage = 'Manager Review';
    expect($task->getStageProgress())->toBe(60);

    $task->approval_stage = 'Closed';
    expect($task->getStageProgress())->toBe(100);
});

test('manager can create a direct design batch with subtasks', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-3']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project With Batch',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $response = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'title' => 'Banner Set Q4',
        'designer_id' => $designer->id,
        'designer_deadline' => '2026-10-15 18:00',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'concept' => 'Need 3 banner formats for launch',
        'subtasks' => [
            [
                'title' => 'Square Post 1080x1080',
                'post_type' => 'Static Post',
                'concept' => 'Square format focus on product',
            ],
            [
                'title' => 'Story 1080x1920',
                'post_type' => 'Story',
                'concept' => 'Vertical story with swipe up',
            ],
        ],
    ]);

    $response->assertRedirect(route('projects.show', $project->id));

    $parent = Deliverable::where('title', 'Banner Set Q4')->first();
    expect($parent)->not->toBeNull();
    expect($parent->flow_type)->toBe('direct_design');
    expect($parent->approval_stage)->toBe('Designer');
    expect($parent->designer_id)->toBe($designer->id);
    expect($parent->brand_manager_id)->toBe($manager->id);

    $subtasks = $parent->subtasks;
    expect($subtasks)->toHaveCount(2);
    expect($subtasks[0]->flow_type)->toBe('direct_design');
    expect($subtasks[0]->approval_stage)->toBe('Designer');
    expect($subtasks[0]->designer_id)->toBe($designer->id);
});

test('designer can submit artwork and advance stage to Manager Review', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-4']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $otherUser = User::factory()->create(['role' => 'Writer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Design Advance',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Design Task 1',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 20,
    ]);

    // Unauthorized user cannot advance Designer stage
    $failResponse = $this->actingAs($otherUser)->postJson(route('deliverables.submit', $task->id), [
        'final_designs_link' => 'https://figma.com/file/12345',
    ]);
    expect($failResponse->status())->toBe(403);

    // Assigned designer advances stage
    $successResponse = $this->actingAs($designer)->post(route('deliverables.submit', $task->id), [
        'final_designs_link' => 'https://figma.com/file/12345',
    ]);

    $task->refresh();
    expect($task->approval_stage)->toBe('Manager Review');
    expect($task->progress_percent)->toBe(60);
    expect($task->final_designs_link)->toBe('https://figma.com/file/12345');
});

test('manager can approve and close deliverable from Manager Review stage', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-5']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Approval',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Design Task Ready For Approval',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Manager Review',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 60,
    ]);

    $response = $this->actingAs($manager)->post(route('deliverables.submit', $task->id));

    $task->refresh();
    expect($task->approval_stage)->toBe('Closed');
    expect($task->progress_percent)->toBe(100);
    expect($task->status)->toBe('Done');
});

test('manager can request revisions which returns deliverable to Designer stage', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-6']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Revision',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Design Needs Work',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Manager Review',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 60,
        'revisions' => 0,
    ]);

    $response = $this->actingAs($manager)->post(route('deliverables.revisions', $task->id), [
        'revision_instructions' => 'Please increase logo contrast and adjust CTA button size.',
    ]);

    $task->refresh();
    expect($task->approval_stage)->toBe('Designer');
    expect($task->progress_percent)->toBe(20);
    expect($task->revisions)->toBe(1);
    expect($task->revision_instructions)->toBe('Please increase logo contrast and adjust CTA button size.');
    expect($task->revisionsHistory)->toHaveCount(1);
});

test('fast track deliverable button is displayed on campaign, pitch, and retainer projects', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-fasttrack-btn']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);

    $retainerProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Retainer Proj',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $campaignProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Proj',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    $pitchProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Pitch Proj',
        'workflow_type' => 'pitch',
        'brand_manager_id' => $manager->id,
    ]);

    $resRetainer = $this->actingAs($manager)->get(route('projects.show', $retainerProject->id));
    $resRetainer->assertSee('Fast Track Deliverable');

    $resCampaign = $this->actingAs($manager)->get(route('projects.show', $campaignProject->id));
    $resCampaign->assertSee('Fast Track Deliverable');

    $resPitch = $this->actingAs($manager)->get(route('projects.show', $pitchProject->id));
    $resPitch->assertSee('Fast Track Deliverable');
});

test('can access fast track creation form and store direct design for campaign and pitch projects', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-fasttrack-allow']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $campaignProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Proj',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    // Access create_design form for campaign project -> 200 OK
    $resGet = $this->actingAs($manager)->get(route('deliverables.create', ['project_id' => $campaignProject->id, 'flow' => 'design']));
    $resGet->assertOk();

    // Store direct_design deliverable in campaign project
    $resPost = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $campaignProject->id,
        'flow_type' => 'direct_design',
        'title' => 'Fast Track In Campaign Deliverable',
        'designer_id' => $designer->id,
        'designer_deadline' => '2026-10-15 18:00',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
    ]);
    $resPost->assertRedirect(route('projects.show', $campaignProject->id));

    $deliverable = Deliverable::where('title', 'Fast Track In Campaign Deliverable')->first();
    expect($deliverable)->not->toBeNull();
    expect($deliverable->isDirectDesign())->toBeTrue();
    expect($deliverable->isOtherDeliverable())->toBeFalse();
    expect($deliverable->approval_stage)->toBe('Designer');
    expect($deliverable->progress_percent)->toBe(20);

    // Designer can submit artwork in Campaign Fast Track deliverable -> advances to Manager Review
    $resDesignerSubmit = $this->actingAs($designer)->post(route('deliverables.submit', $deliverable->id), [
        'final_designs_link' => 'https://example.com/artwork.png',
    ]);
    $resDesignerSubmit->assertRedirect();
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Manager Review');
    expect($deliverable->progress_percent)->toBe(60);

    // Manager can approve Campaign Fast Track deliverable -> moves to Closed
    $resManagerApprove = $this->actingAs($manager)->post(route('deliverables.submit', $deliverable->id));
    $resManagerApprove->assertRedirect();
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Closed');
    expect($deliverable->progress_percent)->toBe(100);
});


