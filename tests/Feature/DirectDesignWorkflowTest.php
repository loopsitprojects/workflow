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
    expect($parent->designer_id)->toBeNull();
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

test('manager can request revisions with attached image file for direct design deliverable', function () {
    \Illuminate\Support\Facades\Storage::fake('s3');

    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-rev-img']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Revision With Image',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Design Needs Work Image',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Manager Review',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 60,
        'revisions' => 0,
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->image('annotation.png');

    $response = $this->actingAs($manager)->post(route('deliverables.revisions', $task->id), [
        'revision_instructions' => 'See attached reference for the new icon colors.',
        'revision_image' => $file,
    ]);

    $response->assertSessionHasNoErrors();
    $task->refresh();
    expect($task->approval_stage)->toBe('Designer');
    expect($task->revisionsHistory)->toHaveCount(1);
    expect($task->revisionsHistory->first()->image_path)->not->toBeNull();
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
    $resRetainer->assertSee('Direct Design');

    $resCampaign = $this->actingAs($manager)->get(route('projects.show', $campaignProject->id));
    $resCampaign->assertSee('Direct Design');

    $resPitch = $this->actingAs($manager)->get(route('projects.show', $pitchProject->id));
    $resPitch->assertSee('Direct Design');
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

test('fast track deliverable preserves brief text and returns it in json endpoint', function () {
    $brand = Brand::create(['name' => 'Brand Brief', 'slug' => 'brand-brief']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign With Brief',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    $briefHtml = '<p>This is the creative brief for the promotional flyer.</p>';

    $resPost = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'single',
        'title' => 'Promotional Flyer',
        'designer_id' => $designer->id,
        'post_type' => 'Graphic',
        'concept' => $briefHtml,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
    ]);
    $resPost->assertRedirect(route('projects.show', $project->id));

    $deliverable = Deliverable::where('title', 'Promotional Flyer')->first();
    expect($deliverable)->not->toBeNull();
    expect($deliverable->concept)->toBe($briefHtml);
    expect($deliverable->notes)->toBe($briefHtml);
    expect($deliverable->is_direct_design)->toBeTrue();
    expect($deliverable->is_other_deliverable)->toBeFalse();

    // Verify GET deliverables/{id} with JSON returns concept and notes
    $resJson = $this->actingAs($manager)->getJson(route('deliverables.show', $deliverable->id));
    $resJson->assertOk();
    $data = $resJson->json();
    expect($data['concept'])->toBe($briefHtml);
    expect($data['notes'])->toBe($briefHtml);
    expect($data['is_direct_design'])->toBeTrue();
});

test('fast track deliverable due date sets deliverable deadline and designer deadline', function () {
    $brand = Brand::create(['name' => 'Brand Fast Track Deadline', 'slug' => 'brand-ft-deadline']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Fast Track Deadline Project',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    // 1. Single Fast Track deliverable creation with deadline
    $res = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'single',
        'title' => 'Single Fast Track Asset',
        'designer_id' => $designer->id,
        'deadline' => '2026-11-15',
        'post_type' => 'Static Post',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
    ]);
    $res->assertRedirect(route('projects.show', $project->id));

    $task = Deliverable::where('title', 'Single Fast Track Asset')->first();
    expect($task)->not->toBeNull();
    expect(\Carbon\Carbon::parse($task->deadline)->format('Y-m-d'))->toBe('2026-11-15');
    expect(\Carbon\Carbon::parse($task->designer_deadline)->format('Y-m-d'))->toBe('2026-11-15');

    // 2. Update deadline via save_only action
    $updateRes = $this->actingAs($manager)->postJson(route('deliverables.submit', $task->id), [
        'action' => 'save_only',
        'deadline' => '2026-11-20',
    ]);
    $updateRes->assertJson(['success' => true]);

    $task->refresh();
    expect(\Carbon\Carbon::parse($task->deadline)->format('Y-m-d'))->toBe('2026-11-20');
    expect(\Carbon\Carbon::parse($task->designer_deadline)->format('Y-m-d'))->toBe('2026-11-20');

    // 3. Batch Fast Track deliverable creation with deadline
    $batchRes = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'batch',
        'title' => 'Batch Fast Track Campaign',
        'deadline' => '2026-11-25',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'subtasks' => [
            [
                'title' => 'Batch Item 1',
                'post_type' => 'Static Post',
                'designer_id' => $designer->id,
                'deadline' => '2026-11-22',
            ],
            [
                'title' => 'Batch Item 2',
                'post_type' => 'Carousel',
                'designer_id' => $designer->id,
                // leaves deadline empty to default to parent batch deadline
            ],
        ],
    ]);
    $batchRes->assertRedirect(route('projects.show', $project->id));

    $batchParent = Deliverable::where('title', 'Batch Fast Track Campaign')->first();
    expect($batchParent)->not->toBeNull();
    expect(\Carbon\Carbon::parse($batchParent->deadline)->format('Y-m-d'))->toBe('2026-11-25');
    expect(\Carbon\Carbon::parse($batchParent->designer_deadline)->format('Y-m-d'))->toBe('2026-11-25');

    $sub1 = Deliverable::where('title', 'Batch Item 1')->first();
    expect($sub1)->not->toBeNull();
    expect(\Carbon\Carbon::parse($sub1->deadline)->format('Y-m-d'))->toBe('2026-11-22');
    expect(\Carbon\Carbon::parse($sub1->designer_deadline)->format('Y-m-d'))->toBe('2026-11-22');

    $sub2 = Deliverable::where('title', 'Batch Item 2')->first();
    expect($sub2)->not->toBeNull();
    expect(\Carbon\Carbon::parse($sub2->deadline)->format('Y-m-d'))->toBe('2026-11-25');
    expect(\Carbon\Carbon::parse($sub2->designer_deadline)->format('Y-m-d'))->toBe('2026-11-25');

    // 4. Verify project show page renders without view errors with Fast Track deliverables present
    $projShowRes = $this->actingAs($manager)->get(route('projects.show', $project->id));
    $projShowRes->assertOk();
    $projShowRes->assertSee('Direct Design');
    $projShowRes->assertSee('Batch Fast Track Campaign');
});

test('brand manager sees fast track deliverables in manager review on dashboard', function () {
    $brand = Brand::create(['name' => 'Brand Dashboard Test', 'slug' => 'brand-dash-test']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $brand->members()->attach($manager->id);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Dashboard Campaign Project',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    // 1. Deliverable in Designer stage -> should not be pending for manager
    $designerTask = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'In Design Fast Track',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'status' => 'To Do',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'deadline' => '2026-11-01',
    ]);

    // 2. Deliverable in Manager Review stage -> MUST appear on manager dashboard
    $reviewTask = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Ready For Review Fast Track',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Manager Review',
        'status' => 'To Do',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'deadline' => '2026-11-05',
    ]);

    $dashResponse = $this->actingAs($manager)->get(route('dashboard'));
    $dashResponse->assertOk();
    $dashResponse->assertSee('Ready For Review Fast Track');
    $dashResponse->assertSee('Manager Review');
    $dashResponse->assertDontSee('In Design Fast Track');
});

test('fast track batch deliverable does not put designer for batch and assigns only to deliverables', function () {
    $brand = Brand::create(['name' => 'Brand Test Fast Track Batch', 'slug' => 'brand-ft-batch']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designerAlice = User::factory()->create(['name' => 'Alice Designer', 'role' => 'Designer']);
    $designerBob = User::factory()->create(['name' => 'Bob Designer', 'role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Fast Track Batch Isolation',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    $response = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'batch',
        'title' => 'Batch Week 1 Launch',
        'designer_deadline' => '2026-11-15 18:00',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'concept' => 'Batch creative instructions',
        'subtasks' => [
            [
                'title' => 'Deliverable Alpha',
                'post_type' => 'Static Post',
                'designer_id' => $designerAlice->id,
                'concept' => 'Concept for Alpha',
            ],
            [
                'title' => 'Deliverable Beta',
                'post_type' => 'Reel',
                'designer_id' => $designerBob->id,
                'concept' => 'Concept for Beta',
            ],
        ],
    ]);

    $response->assertRedirect(route('projects.show', $project->id));

    $parent = Deliverable::where('title', 'Batch Week 1 Launch')->first();
    expect($parent)->not->toBeNull();
    // Batch parent must NOT have a designer assigned
    expect($parent->designer_id)->toBeNull();
    expect($parent->assignee_name)->toBeNull();

    $subtasks = $parent->subtasks;
    expect($subtasks)->toHaveCount(2);

    // Subtasks MUST have their assigned designers
    expect($subtasks[0]->title)->toBe('Deliverable Alpha');
    expect($subtasks[0]->designer_id)->toBe($designerAlice->id);
    expect($subtasks[0]->assignee_name)->toBe('Alice Designer');

    expect($subtasks[1]->title)->toBe('Deliverable Beta');
    expect($subtasks[1]->designer_id)->toBe($designerBob->id);
    expect($subtasks[1]->assignee_name)->toBe('Bob Designer');

    // Verify rendered table in projects.show shows deliverables' designers
    $projectView = $this->actingAs($manager)->get(route('projects.show', $project->id));
    $projectView->assertOk();
    $projectView->assertSee('Batch Week 1 Launch');
    $projectView->assertSee('Alice Designer');
    $projectView->assertSee('Bob Designer');
});
