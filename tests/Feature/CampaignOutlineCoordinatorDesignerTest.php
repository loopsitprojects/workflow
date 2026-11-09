<?php

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\DeliverableReassignment;
use App\Models\Project;
use App\Models\SubtaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->brand = Brand::create([
        'name' => 'Acme Campaign Brand',
        'slug' => 'acme-campaign-brand',
    ]);

    $this->admin = User::factory()->create(['role' => 'Admin']);
    $this->brandManager = User::factory()->create(['role' => 'Brand Manager']);
    $this->coordinator = User::factory()->create(['role' => 'Coordinator']);
    $this->designer = User::factory()->create(['role' => 'Designer']);
    $this->approver = User::factory()->create(['role' => 'Approver']);
    $this->writer = User::factory()->create(['role' => 'Writer']);

    $this->outlineType = SubtaskType::firstOrCreate(
        ['name' => 'Outlines'],
        ['category' => 'Campaign', 'status' => 'active']
    );

    $this->campaignProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Winter Campaign 2026',
        'workflow_type' => 'campaign',
        'brand_manager_id' => $this->brandManager->id,
        'coordinator_id' => $this->coordinator->id,
        'approver_id' => $this->approver->id,
        'writer_id' => $this->writer->id,
        'designer_id' => $this->designer->id,
        'status' => 'active',
        'deadline' => now()->addDays(14)->toDateString(),
    ]);

    $this->pitchProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Summer Pitch 2026',
        'workflow_type' => 'pitch',
        'brand_manager_id' => $this->brandManager->id,
        'coordinator_id' => $this->coordinator->id,
        'approver_id' => $this->approver->id,
        'writer_id' => $this->writer->id,
        'designer_id' => $this->designer->id,
        'status' => 'active',
        'deadline' => now()->addDays(20)->toDateString(),
    ]);
});

test('creating outline deliverable via quickStore on campaign kanban assigns coordinator and sets Coordinator stage', function () {
    Notification::fake();

    $response = $this->actingAs($this->brandManager)->json('POST', route('campaign-kanban.quick-store', $this->campaignProject), [
        'title' => 'Campaign Master Outline',
        'post_type' => 'Outlines',
        'status' => 'To Do',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $deliverable = Deliverable::where('title', 'Campaign Master Outline')->first();
    expect($deliverable)->not->toBeNull();
    expect($deliverable->coordinator_id)->toBe($this->coordinator->id);
    expect($deliverable->writer_id)->toBeNull();
    expect($deliverable->approval_stage)->toBe('Coordinator');
    expect($deliverable->assignee_name)->toBe($this->coordinator->name);
    expect($deliverable->isOutline())->toBeTrue();
});

test('creating outline deliverable via Project store sets Coordinator stage and coordinator_id', function () {
    $response = $this->actingAs($this->brandManager)->post(route('projects.store'), [
        'brand_id' => $this->brand->id,
        'name' => 'Spring Launch Campaign',
        'status' => 'active',
        'priority' => 'Medium',
        'type' => 'Standard',
        'workflow_type' => 'campaign',
        'coordinator_id' => $this->coordinator->id,
        'brand_manager_id' => $this->brandManager->id,
        'approver_id' => $this->approver->id,
        'deadline' => now()->addDays(30)->toDateString(),
        'post_types' => [$this->outlineType->id],
        'post_type_counts' => [
            $this->outlineType->id => 2,
        ],
    ]);

    $project = Project::where('name', 'Spring Launch Campaign')->first();
    expect($project)->not->toBeNull();

    $deliverables = Deliverable::where('project_id', $project->id)->get();
    expect($deliverables->count())->toBeGreaterThanOrEqual(1);

    foreach ($deliverables as $del) {
        if ($del->isOutline()) {
            expect($del->coordinator_id)->toBe($this->coordinator->id);
            expect($del->writer_id)->toBeNull();
            expect($del->approval_stage)->toBe('Coordinator');
            expect($del->assignee_name)->toBe($this->coordinator->name);
        }
    }
});

test('coordinator can move outline deliverable from To Do to In Progress on Kanban', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'created_by' => $this->brandManager->id,
        'title' => 'Q4 Outline Brief',
        'post_type' => 'Outlines',
        'coordinator_id' => $this->coordinator->id,
        'writer_id' => null,
        'assignee_name' => $this->coordinator->name,
        'approval_stage' => 'Coordinator',
        'status' => 'To Do',
        'progress_percent' => 0,
        'approver_id' => $this->approver->id,
        'brand_manager_id' => $this->brandManager->id,
    ]);

    // Unrelated writer cannot move it
    $failResponse = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($failResponse->status())->toBe(403);

    // Assigned coordinator CAN move it
    $successResponse = $this->actingAs($this->coordinator)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($successResponse->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('In Progress');
});

test('coordinator can assign designer to outline deliverable via reassignWriter', function () {
    Notification::fake();

    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'created_by' => $this->brandManager->id,
        'title' => 'Social Media Outline Plan',
        'post_type' => 'Outlines',
        'coordinator_id' => $this->coordinator->id,
        'writer_id' => null,
        'designer_id' => null,
        'assignee_name' => $this->coordinator->name,
        'approval_stage' => 'Coordinator',
        'status' => 'To Do',
        'progress_percent' => 0,
        'approver_id' => $this->approver->id,
        'brand_manager_id' => $this->brandManager->id,
    ]);

    // Coordinator assigns designer with designer deadline
    $response = $this->actingAs($this->coordinator)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->designer->id,
        'designer_deadline' => now()->addDays(5)->toDateString(),
        'reason' => 'Assigning designer to produce artwork assets',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $deliverable->refresh();
    expect($deliverable->designer_id)->toBe($this->designer->id);
    expect($deliverable->writer_id)->toBeNull();
    expect($deliverable->approval_stage)->toBe('Designer');
    expect($deliverable->assignee_name)->toBe($this->designer->name);
    // Should remain in To Do so designer can move it to In Progress
    expect($deliverable->status)->toBe('To Do');
    expect($deliverable->designer_deadline)->not->toBeNull();

    // Designer moves the deliverable to In Progress
    $moveResp = $this->actingAs($this->designer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($moveResp->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('In Progress');

    // Audit trail should record role 'designer'
    $reassignment = DeliverableReassignment::where('deliverable_id', $deliverable->id)->first();
    expect($reassignment)->not->toBeNull();
    expect($reassignment->role)->toBe('designer');
    expect($reassignment->to_user_id)->toBe($this->designer->id);
    expect($reassignment->reassigned_by_user_id)->toBe($this->coordinator->id);

    // Designer received notification
    Notification::assertSentTo($this->designer, \App\Notifications\DeliverableUpdated::class);
});

test('coordinator can assign designer via quickUpdateCampaignKanban', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'created_by' => $this->brandManager->id,
        'title' => 'Key Visual Outline Pack',
        'post_type' => 'Outlines',
        'coordinator_id' => $this->coordinator->id,
        'writer_id' => null,
        'designer_id' => null,
        'assignee_name' => $this->coordinator->name,
        'approval_stage' => 'Coordinator',
        'status' => 'To Do',
        'approver_id' => $this->approver->id,
        'brand_manager_id' => $this->brandManager->id,
    ]);

    $response = $this->actingAs($this->coordinator)->json('POST', route('campaign-kanban.quick-update', $deliverable), [
        'title' => 'Key Visual Outline Pack Updated',
        'writer_id' => $this->designer->id,
        'reassignment_reason' => 'Assigned Bravin for 3D visual design',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $deliverable->refresh();
    expect($deliverable->designer_id)->toBe($this->designer->id);
    expect($deliverable->approval_stage)->toBe('Designer');
    expect($deliverable->assignee_name)->toBe($this->designer->name);
    expect($deliverable->status)->toBe('To Do');
});

test('pitch flow operates identically for outline coordinator to designer assignment', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->pitchProject->id,
        'created_by' => $this->brandManager->id,
        'title' => 'Pitch Deck Outline',
        'post_type' => 'Outlines',
        'coordinator_id' => $this->coordinator->id,
        'writer_id' => null,
        'designer_id' => null,
        'assignee_name' => $this->coordinator->name,
        'approval_stage' => 'Coordinator',
        'status' => 'To Do',
        'approver_id' => $this->approver->id,
        'brand_manager_id' => $this->brandManager->id,
    ]);

    $response = $this->actingAs($this->coordinator)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->designer->id,
        'reason' => 'Assigning deck designer for pitch graphics',
    ]);

    $response->assertStatus(200);
    $deliverable->refresh();
    expect($deliverable->designer_id)->toBe($this->designer->id);
    expect($deliverable->approval_stage)->toBe('Designer');
    expect($deliverable->assignee_name)->toBe($this->designer->name);
    expect($deliverable->status)->toBe('To Do');
});

test('advancing outline from Designer stage skips Writer Review straight to Approver Review when no writer exists', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'created_by' => $this->brandManager->id,
        'title' => 'Digital Banner Outline',
        'post_type' => 'Outlines',
        'coordinator_id' => $this->coordinator->id,
        'writer_id' => null,
        'designer_id' => $this->designer->id,
        'assignee_name' => $this->designer->name,
        'approval_stage' => 'Designer',
        'status' => 'In Progress',
        'approver_id' => $this->approver->id,
        'brand_manager_id' => $this->brandManager->id,
    ]);

    $next = $deliverable->getNextStage();
    expect($next)->toBe('Approver Review');

    // Designer uploads artwork and advances stage
    $response = $this->actingAs($this->designer)->json('POST', route('deliverables.submit', $deliverable), [
        'final_designs_link' => 'https://drive.google.com/artwork/sample.png',
        'work_hours' => 2.5,
    ]);

    $response->assertStatus(200);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Approver Review');
});

test('creating outline deliverable via deliverables.store sets Coordinator stage and coordinator_id', function () {
    Notification::fake();

    $response = $this->actingAs($this->brandManager)->post(route('deliverables.store'), [
        'project_id' => $this->campaignProject->id,
        'title' => 'Single Master Outline',
        'post_type' => 'Outlines',
        'writer_id' => $this->coordinator->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'deadline' => now()->addDays(7)->toDateString(),
        'priority' => 'Medium',
    ]);

    $response->assertRedirect(route('projects.show', $this->campaignProject->id));

    $deliverable = Deliverable::where('title', 'Single Master Outline')->first();
    expect($deliverable)->not->toBeNull();
    expect($deliverable->coordinator_id)->toBe($this->coordinator->id);
    expect($deliverable->writer_id)->toBeNull();
    expect($deliverable->approval_stage)->toBe('Coordinator');
    expect($deliverable->assignee_name)->toBe($this->coordinator->name);
    expect($deliverable->isOutline())->toBeTrue();
});

test('creating outline batch deliverable via deliverables.store sets Coordinator stage and coordinator_id for subtasks', function () {
    Notification::fake();

    $response = $this->actingAs($this->brandManager)->post(route('deliverables.store'), [
        'project_id' => $this->campaignProject->id,
        'title' => 'Campaign Outlines Batch',
        'post_type' => 'Outlines',
        'writer_id' => $this->coordinator->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'deadline' => now()->addDays(10)->toDateString(),
        'priority' => 'Medium',
        'subtasks' => [
            [
                'title' => 'Sub Outline 1',
                'post_type' => 'Outlines',
                'writer_id' => $this->coordinator->id,
                'deadline' => now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Sub Standard Post',
                'post_type' => 'KV',
                'writer_id' => $this->writer->id,
                'deadline' => now()->addDays(8)->toDateString(),
            ],
        ],
    ]);

    $response->assertRedirect(route('projects.show', $this->campaignProject->id));

    $sub1 = Deliverable::where('title', 'Sub Outline 1')->first();
    expect($sub1)->not->toBeNull();
    expect($sub1->coordinator_id)->toBe($this->coordinator->id);
    expect($sub1->writer_id)->toBeNull();
    expect($sub1->approval_stage)->toBe('Coordinator');

    $sub2 = Deliverable::where('title', 'Sub Standard Post')->first();
    expect($sub2)->not->toBeNull();
    expect($sub2->writer_id)->toBe($this->writer->id);
    expect($sub2->approval_stage)->toBe('Assign');
});
