<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Services\Workflows\DirectDesignWorkflowService;
use App\Services\Workflows\WorkflowManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

    $task->approval_stage = 'Writer Review';
    expect($task->getStageProgress())->toBe(40);

    $task->approval_stage = 'Approver Review';
    expect($task->getStageProgress())->toBe(60);

    $task->approval_stage = 'Further Approver';
    expect($task->getStageProgress())->toBe(75);

    $task->approval_stage = 'Final Approval';
    expect($task->getStageProgress())->toBe(85);

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
        'designer_deadline' => now()->addDays(5)->format('Y-m-d H:i'),
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

test('designer can submit artwork and advance stage to Writer Review', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-4']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $writer = User::factory()->create(['role' => 'Writer']);
    $otherUser = User::factory()->create(['role' => 'Writer']);
    $brand->members()->attach([$manager->id, $designer->id, $writer->id, $otherUser->id]);

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
        'writer_id' => $writer->id,
    ]);
    expect($failResponse->status())->toBe(403);

    // Assigned designer advances stage
    $successResponse = $this->actingAs($designer)->post(route('deliverables.submit', $task->id), [
        'final_designs_link' => 'https://figma.com/file/12345',
        'writer_id' => $writer->id,
    ]);

    $task->refresh();
    expect($task->approval_stage)->toBe('Writer Review');
    expect($task->writer_id)->toBe($writer->id);
    expect($task->progress_percent)->toBe(40);
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
    $writer = User::factory()->create(['role' => 'Writer']);
    $approver = User::factory()->create(['role' => 'Approver']);
    $furtherApprover = User::factory()->create(['role' => 'Approver']);
    $brand->members()->attach([$manager->id, $designer->id, $writer->id, $approver->id, $furtherApprover->id]);

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
        'designer_deadline' => now()->addDays(5)->format('Y-m-d H:i'),
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

    // Designer submits artwork in Campaign Direct Design deliverable -> advances to Writer Review
    $resDesignerSubmit = $this->actingAs($designer)->post(route('deliverables.submit', $deliverable->id), [
        'final_designs_link' => 'https://example.com/artwork.png',
        'writer_id' => $writer->id,
    ]);
    $resDesignerSubmit->assertRedirect();
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Writer Review');
    expect($deliverable->progress_percent)->toBe(40);

    // Writer advances to Approver Review
    $this->actingAs($writer)->post(route('deliverables.submit', $deliverable->id), [
        'approver_id' => $approver->id,
    ]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Approver Review');
    expect($deliverable->progress_percent)->toBe(60);

    // Approver advances to Further Approver
    $this->actingAs($approver)->post(route('deliverables.submit', $deliverable->id), [
        'further_approver_id' => $furtherApprover->id,
    ]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Further Approver');
    expect($deliverable->progress_percent)->toBe(75);

    // Further Approver advances to Final Approval
    $this->actingAs($furtherApprover)->post(route('deliverables.submit', $deliverable->id), [
        'brand_manager_id' => $manager->id,
    ]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Final Approval');
    expect($deliverable->progress_percent)->toBe(85);

    // Manager can approve Direct Design deliverable -> moves to Closed
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
        'workflow_type' => 'retainer',
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

test('fast track deliverable creation persists writer_id in single and batch mode', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-writer']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $writer1 = User::factory()->create(['name' => 'Charlie Writer', 'role' => 'Writer']);
    $writer2 = User::factory()->create(['name' => 'Dave Writer', 'role' => 'Writer']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Fast Track Writer',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    // Single deliverable creation
    $response = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'single',
        'title' => 'Single Fast Track Item',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'designer_id' => $designer->id,
        'writer_id' => $writer1->id,
        'concept' => 'Single creative brief',
    ]);
    $response->assertRedirect(route('projects.show', $project->id));

    $single = Deliverable::where('title', 'Single Fast Track Item')->first();
    expect($single)->not->toBeNull();
    expect($single->writer_id)->toBe($writer1->id);

    // Batch creation with batch fallback and subtask specific writer
    $batchResponse = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'batch',
        'title' => 'Batch Fast Track Group',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'writer_id' => $writer1->id,
        'subtasks' => [
            [
                'title' => 'Subtask 1 with fallback writer',
                'designer_id' => $designer->id,
                'concept' => 'Brief 1',
            ],
            [
                'title' => 'Subtask 2 with explicit writer',
                'designer_id' => $designer->id,
                'writer_id' => $writer2->id,
                'concept' => 'Brief 2',
            ],
        ],
    ]);
    $batchResponse->assertRedirect(route('projects.show', $project->id));

    $batchParent = Deliverable::where('title', 'Batch Fast Track Group')->first();
    expect($batchParent)->not->toBeNull();
    expect($batchParent->writer_id)->toBe($writer1->id);

    $sub1 = Deliverable::where('title', 'Subtask 1 with fallback writer')->first();
    expect($sub1->writer_id)->toBe($writer1->id);

    $sub2 = Deliverable::where('title', 'Subtask 2 with explicit writer')->first();
    expect($sub2->writer_id)->toBe($writer2->id);
});

test('brand manager can advance direct design deliverable and assign writer for Writer Review', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-bm-advance']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $writer = User::factory()->create(['name' => 'Emma Writer', 'role' => 'Writer']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project BM Advance',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'title' => 'Fast Track Submit Test',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'final_designs' => 'artwork/sample.jpg',
    ]);

    // Brand manager submits deliverable to Writer Review via batch-submit endpoint
    $response = $this->actingAs($manager)->postJson(route('deliverables.batchSubmit', $task->id), [
        'writer_id' => $writer->id,
    ]);

    $response->assertOk();
    $task->refresh();
    expect($task->approval_stage)->toBe('Writer Review');
    expect($task->writer_id)->toBe($writer->id);
});

test('designer advance to Writer Review falls back to project writer when writer_id omitted', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-fallback-writer']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $projectWriter = User::factory()->create(['name' => 'Default Writer', 'role' => 'Writer']);
    $designer = User::factory()->create(['role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Fallback Writer',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
        'writer_id' => $projectWriter->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'title' => 'Auto Fallback Task',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'final_designs' => 'artwork/sample.jpg',
    ]);

    // Advance without passing writer_id
    $response = $this->actingAs($designer)->post(route('deliverables.submit', $task->id), []);
    $response->assertSessionHasNoErrors();

    $task->refresh();
    expect($task->approval_stage)->toBe('Writer Review');
    expect($task->writer_id)->toBe($projectWriter->id);
});

test('deliverables table displays designer deadline and revision deadline badges', function () {
    $brand = Brand::create(['name' => 'Brand Deadlines Test', 'slug' => 'brand-deadlines-test']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Deadlines Demo',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $batchParent = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Batch 1',
        'approval_stage' => 'Writer',
    ]);

    $sub1 = Deliverable::create([
        'project_id' => $project->id,
        'parent_deliverable_id' => $batchParent->id,
        'title' => 'Carousel 1',
        'approval_stage' => 'Writer',
        'designer_deadline' => '2026-10-24 18:00:00',
    ]);

    $sub2 = Deliverable::create([
        'project_id' => $project->id,
        'parent_deliverable_id' => $batchParent->id,
        'title' => 'Carousel 2',
        'approval_stage' => 'Writer',
        'designer_deadline' => '2026-10-25 15:00:00',
        'revision_deadline' => '2026-10-28 18:00:00',
    ]);

    $sub3 = Deliverable::create([
        'project_id' => $project->id,
        'parent_deliverable_id' => $batchParent->id,
        'title' => 'Static Post 1',
        'approval_stage' => 'Writer',
        'revision_deadline' => '2026-10-26 12:00:00',
    ]);

    $sub4 = Deliverable::create([
        'project_id' => $project->id,
        'parent_deliverable_id' => $batchParent->id,
        'title' => 'Static Post 2',
        'approval_stage' => 'Writer',
    ]);

    $response = $this->actingAs($manager)->get(route('projects.show', $project->id));
    $response->assertOk();

    // Verify sub1 has Designer deadline badge
    $response->assertSee('Des: Oct 24, 2026');

    // Verify sub2 has Revision deadline badge, but does NOT show Designer deadline
    $response->assertDontSee('Des: Oct 25, 2026');
    $response->assertSee('Rev: Oct 28, 2026');

    // Verify sub3 has Revision deadline badge
    $response->assertSee('Rev: Oct 26, 2026');
});

test('designer can submit artwork and advance directly to Approver Review skipping optional Writer Review', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-direct-approver']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $approver = User::factory()->create(['role' => 'Approver']);
    $brand->members()->attach([$manager->id, $designer->id, $approver->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Direct To Approver',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Direct to Approver Task',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 20,
    ]);

    // Submitting with send_to = 'approver' and approver_id advances straight to Approver Review
    $response = $this->actingAs($designer)->post(route('deliverables.submit', $task->id), [
        'final_designs_link' => 'https://figma.com/file/direct-approver-test',
        'send_to' => 'approver',
        'approver_id' => $approver->id,
    ]);

    $response->assertSessionHasNoErrors();
    $task->refresh();
    expect($task->approval_stage)->toBe('Approver Review');
    expect($task->approver_id)->toBe($approver->id);
    expect($task->progress_percent)->toBe(60);
    expect($task->final_designs_link)->toBe('https://figma.com/file/direct-approver-test');
});

test('designer advancing directly to Approver Review requires approver_id because approver stage is mandatory', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-approver-mandatory']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $brand->members()->attach([$manager->id, $designer->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Approver Mandatory',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Approver Mandatory Task',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 20,
    ]);

    // Attempt to submit directly to approver without selecting an approver
    $response = $this->actingAs($designer)->postJson(route('deliverables.submit', $task->id), [
        'final_designs_link' => 'https://figma.com/file/no-approver-test',
        'send_to' => 'approver',
    ]);

    expect($response->status())->toBe(422);
    expect($response->json('message'))->toContain('Please select an approver for approver review.');

    $task->refresh();
    expect($task->approval_stage)->toBe('Designer');
});

test('deliverable that skipped writer review can receive revision request from approver back to designer', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-rev-skip-writer']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $approver = User::factory()->create(['role' => 'Approver']);
    $brand->members()->attach([$manager->id, $designer->id, $approver->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Rev Skip Writer',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Task Direct Approver Rev',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 20,
    ]);

    // Advance directly to Approver Review
    $this->actingAs($designer)->post(route('deliverables.submit', $task->id), [
        'final_designs_link' => 'https://figma.com/file/direct-approver-rev',
        'send_to' => 'approver',
        'approver_id' => $approver->id,
    ]);

    $task->refresh();
    expect($task->approval_stage)->toBe('Approver Review');

    // Approver requests revisions
    $revResponse = $this->actingAs($approver)->post(route('deliverables.revisions', $task->id), [
        'revision_instructions' => 'Approver requested color adjustments',
    ]);
    $revResponse->assertSessionHasNoErrors();

    $task->refresh();
    expect($task->approval_stage)->toBe('Designer');
    expect($task->progress_percent)->toBe(20);
    expect($task->revisions)->toBe(1);
    expect($task->revision_instructions)->toBe('Approver requested color adjustments');
    expect($task->revision_target)->toBe('designer');
});

test('approver review is mandatory and advances to final approval', function () {
    $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test-approver-to-final']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $approver = User::factory()->create(['role' => 'Approver']);
    $brand->members()->attach([$manager->id, $designer->id, $approver->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Approver To Final',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Approver Review Task',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Approver Review',
        'designer_id' => $designer->id,
        'approver_id' => $approver->id,
        'brand_manager_id' => $manager->id,
        'progress_percent' => 60,
    ]);

    // Approver approves task
    $response = $this->actingAs($approver)->post(route('deliverables.submit', $task->id), []);
    $response->assertSessionHasNoErrors();

    $task->refresh();
    expect($task->approval_stage)->toBe('Final Approval');
    expect($task->progress_percent)->toBe(85);
});

test('direct design deliverable creation supports multiple reference URLs and multiple reference files in single mode', function () {
    Storage::fake('s3');
    Storage::fake('public');

    $brand = Brand::create(['name' => 'Brand Multi Ref', 'slug' => 'brand-multi-ref-single']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $brand->members()->attach([$manager->id, $designer->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Multi Ref Single',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    $file1 = UploadedFile::fake()->image('ref1.jpg');
    $file2 = UploadedFile::fake()->create('ref2.pdf', 100);

    $response = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'single',
        'title' => 'Single Deliverable With Multiple References',
        'designer_id' => $designer->id,
        'post_type' => 'Static Post',
        'priority' => 'High Priority',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'reference_urls' => [
            'https://figma.com/file/ref-single-1',
            'https://drive.google.com/folder/ref-single-2',
        ],
        'reference_files' => [
            $file1,
            $file2,
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $deliverable = Deliverable::where('title', 'Single Deliverable With Multiple References')->first();
    expect($deliverable)->not->toBeNull();
    expect($deliverable->flow_type)->toBe('direct_design');
    expect($deliverable->designer_id)->toBe($designer->id);
    expect($deliverable->approval_stage)->toBe('Designer');

    $refFiles = $deliverable->getReferenceFilesArray();
    expect(count($refFiles))->toBe(2);

    $refUrls = $deliverable->getReferenceUrlsArray();
    expect(count($refUrls))->toBe(2);
    expect($refUrls)->toContain('https://figma.com/file/ref-single-1');
    expect($refUrls)->toContain('https://drive.google.com/folder/ref-single-2');
});

test('direct design deliverable creation supports multiple reference URLs and multiple reference files for subtasks in batch mode', function () {
    Storage::fake('s3');
    Storage::fake('public');

    $brand = Brand::create(['name' => 'Brand Multi Ref Batch', 'slug' => 'brand-multi-ref-batch']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $brand->members()->attach([$manager->id, $designer->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Multi Ref Batch',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $manager->id,
    ]);

    $fileA = UploadedFile::fake()->image('itemA.png');
    $fileB = UploadedFile::fake()->create('itemB.pdf', 50);

    $response = $this->actingAs($manager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'creation_mode' => 'batch',
        'title' => 'Batch Campaign Package',
        'priority' => 'Medium',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'subtasks' => [
            [
                'title' => 'Batch Item 1 with Multiple Refs',
                'post_type' => 'Graphic',
                'designer_id' => $designer->id,
                'priority' => 'High Priority',
                'reference_urls' => [
                    'https://pinterest.com/pin/batch1-ref1',
                    'https://dribbble.com/shots/batch1-ref2',
                ],
                'reference_files' => [
                    $fileA,
                    $fileB,
                ],
            ],
            [
                'title' => 'Batch Item 2 Single Ref',
                'post_type' => 'Carousel',
                'designer_id' => $designer->id,
                'priority' => 'Medium',
                'reference' => 'https://behance.net/gallery/item2',
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $parent = Deliverable::where('title', 'Batch Campaign Package')->first();
    expect($parent)->not->toBeNull();

    $item1 = Deliverable::where('title', 'Batch Item 1 with Multiple Refs')->first();
    expect($item1)->not->toBeNull();
    expect($item1->parent_deliverable_id)->toBe($parent->id);
    expect($item1->flow_type)->toBe('direct_design');

    $item1Files = $item1->getReferenceFilesArray();
    expect(count($item1Files))->toBe(2);

    $item1Urls = $item1->getReferenceUrlsArray();
    expect(count($item1Urls))->toBe(2);
    expect($item1Urls)->toContain('https://pinterest.com/pin/batch1-ref1');
    expect($item1Urls)->toContain('https://dribbble.com/shots/batch1-ref2');

    $item2 = Deliverable::where('title', 'Batch Item 2 Single Ref')->first();
    expect($item2)->not->toBeNull();
    expect($item2->getReferenceUrlsArray())->toContain('https://behance.net/gallery/item2');
});

test('further approver is optional in direct design flow and can be skipped', function () {
    $brand = Brand::create(['name' => 'Brand Skip FA Test', 'slug' => 'brand-skip-fa-test']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $approver = User::factory()->create(['role' => 'Approver']);
    $furtherApprover = User::factory()->create(['role' => 'Approver']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Project Skip FA Test',
        'workflow_type' => 'retainer',
        'brand_manager_id' => $manager->id,
        'approver_id' => $approver->id,
    ]);

    // Deliverable at Approver Review with NO further approver
    $task1 = Deliverable::create([
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'title' => 'Direct Design Task Skip FA',
        'approval_stage' => 'Approver Review',
        'approver_id' => $approver->id,
        'further_approver_id' => null,
        'brand_manager_id' => $manager->id,
    ]);

    // 1. Advance without further_approver_id -> should go straight to Final Approval
    $res1 = $this->actingAs($approver)->postJson(route('deliverables.batchSubmit', $task1->id), [
        'further_approver_id' => '',
    ]);
    $res1->assertOk();
    $task1->refresh();
    expect($task1->approval_stage)->toBe('Final Approval');
    expect($task1->further_approver_id)->toBeNull();

    // 2. Deliverable with further_approver_id set, but user skips it
    $task2 = Deliverable::create([
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'title' => 'Direct Design Task Clear FA',
        'approval_stage' => 'Approver Review',
        'approver_id' => $approver->id,
        'further_approver_id' => $furtherApprover->id,
        'brand_manager_id' => $manager->id,
    ]);

    $res2 = $this->actingAs($approver)->postJson(route('deliverables.batchSubmit', $task2->id), [
        'further_approver_id' => '',
    ]);
    $res2->assertOk();
    $task2->refresh();
    expect($task2->approval_stage)->toBe('Final Approval');
    expect($task2->further_approver_id)->toBeNull();

    // 3. Deliverable advancing with further_approver_id set -> advances to Further Approver
    $task3 = Deliverable::create([
        'project_id' => $project->id,
        'flow_type' => 'direct_design',
        'title' => 'Direct Design Task Use FA',
        'approval_stage' => 'Approver Review',
        'approver_id' => $approver->id,
        'further_approver_id' => null,
        'brand_manager_id' => $manager->id,
    ]);

    $res3 = $this->actingAs($approver)->postJson(route('deliverables.batchSubmit', $task3->id), [
        'further_approver_id' => $furtherApprover->id,
    ]);
    $res3->assertOk();
    $task3->refresh();
    expect($task3->approval_stage)->toBe('Further Approver');
    expect($task3->further_approver_id)->toBe($furtherApprover->id);
});
