<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('campaign other deliverable enforces correct roles at each stage: Assign -> Approve -> Close', function () {
    $brand = Brand::create(['name' => 'Campaign Brand Alpha', 'slug' => 'campaign-brand-alpha']);

    $brandManager = User::factory()->create(['name' => 'BM Alice', 'role' => 'Brand Manager']);
    $otherBrandManager = User::factory()->create(['name' => 'BM Bob', 'role' => 'Brand Manager']);
    $assignedPerson = User::factory()->create(['name' => 'Assignee Charlie', 'role' => 'Writer']);
    $unassignedPerson = User::factory()->create(['name' => 'Random Writer', 'role' => 'Writer']);
    $coordinator = User::factory()->create(['name' => 'Coord Dave', 'role' => 'Coordinator']);
    $admin = User::factory()->create(['name' => 'Admin Boss', 'role' => 'Admin']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Q4 Launch',
        'workflow_type' => 'campaign',
        'type' => 'Campaign',
        'status' => 'Active',
        'brand_manager_id' => $brandManager->id,
        'writer_id' => $assignedPerson->id,
    ]);

    // Create an "Other" deliverable (non-outlines, e.g. Static Post)
    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Social Post Graphic',
        'post_type' => 'Static Post',
        'approval_stage' => 'Assign',
        'status' => 'To Do',
        'progress_percent' => 10,
        'writer_id' => $assignedPerson->id,
        'brand_manager_id' => $brandManager->id,
    ]);

    expect($deliverable->isOtherDeliverable())->toBeTrue();
    expect($deliverable->approval_stage)->toBe('Assign');

    // 1. STAGE: Assign
    // 1a. Unassigned user tries to advance stage -> Blocked (403)
    $resUnauthAdvance = $this->actingAs($unassignedPerson)->json('POST', route('deliverables.submit', $deliverable), [
        'concept' => 'Sneak peek concept',
    ]);
    expect($resUnauthAdvance->status())->toBe(403);
    expect($deliverable->fresh()->approval_stage)->toBe('Assign');

    // 1b. Coordinator tries to advance stage -> Blocked (403)
    $resCoordAdvance = $this->actingAs($coordinator)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resCoordAdvance->status())->toBe(403);
    expect($deliverable->fresh()->approval_stage)->toBe('Assign');

    // 1c. Unassigned user tries to save content -> Blocked (403)
    $resUnauthSave = $this->actingAs($unassignedPerson)->json('POST', route('deliverables.submit', $deliverable), [
        'action' => 'save_only',
        'title' => 'Hacked Title',
    ]);
    expect($resUnauthSave->status())->toBe(403);

    // 1d. Assigned person saves content -> Allowed (200)
    $resAssignedSave = $this->actingAs($assignedPerson)->json('POST', route('deliverables.submit', $deliverable), [
        'action' => 'save_only',
        'title' => 'Updated By Assigned Person',
    ]);
    expect($resAssignedSave->status())->toBe(200);
    expect($deliverable->fresh()->title)->toBe('Updated By Assigned Person');

    // 1e. Assigned person advances stage -> Moves to Approve (50%)
    $resAssignedAdvance = $this->actingAs($assignedPerson)->json('POST', route('deliverables.submit', $deliverable), [
        'notes' => 'Work completed and ready for review',
    ]);
    expect($resAssignedAdvance->status())->toBeIn([200, 302]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Approve');
    expect($deliverable->getStageProgress())->toBe(50);

    // 2. STAGE: Approve
    // 2a. Assigned person tries to approve themselves -> Blocked (403)
    $resSelfApprove = $this->actingAs($assignedPerson)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resSelfApprove->status())->toBe(403);
    expect($deliverable->fresh()->approval_stage)->toBe('Approve');

    // 2b. Coordinator tries to approve -> Blocked (403)
    $resCoordApprove = $this->actingAs($coordinator)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resCoordApprove->status())->toBe(403);

    // 2c. Different Brand Manager (not assigned to this task or project) tries to approve -> Blocked (403)
    $resOtherBmApprove = $this->actingAs($otherBrandManager)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resOtherBmApprove->status())->toBe(403);

    // 2d. Assigned Brand Manager requests revisions -> Moves back to Assign (10%)
    $resRevision = $this->actingAs($brandManager)->post(route('deliverables.revisions', $deliverable), [
        'revision_instructions' => 'Please add more vibrant colors and update the copy.',
    ]);
    expect($resRevision->status())->toBeIn([200, 302]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Assign');
    expect($deliverable->getStageProgress())->toBe(10);
    expect($deliverable->revisions)->toBe(1);

    // 2e. Assigned person re-submits -> Moves back to Approve (50%)
    $resReadvance = $this->actingAs($assignedPerson)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resReadvance->status())->toBeIn([200, 302]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Approve');

    // 2f. Assigned Brand Manager approves -> Moves to Close (100%, Done)
    $resFinalApprove = $this->actingAs($brandManager)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resFinalApprove->status())->toBeIn([200, 302]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Close');
    expect($deliverable->getStageProgress())->toBe(100);
    expect($deliverable->status)->toBe('Done');
});

test('pitch other deliverable enforces correct roles at each stage: Assign -> Approve -> Close', function () {
    $brand = Brand::create(['name' => 'Pitch Brand Beta', 'slug' => 'pitch-brand-beta']);

    $brandManager = User::factory()->create(['name' => 'BM Pitch', 'role' => 'Brand Manager']);
    $assignedPerson = User::factory()->create(['name' => 'Pitch Presenter', 'role' => 'Designer']);
    $unassignedPerson = User::factory()->create(['name' => 'Random Designer', 'role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'New Client Pitch 2026',
        'workflow_type' => 'pitch',
        'type' => 'Pitch',
        'status' => 'Active',
        'brand_manager_id' => $brandManager->id,
        'writer_id' => $assignedPerson->id,
    ]);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Pitch Deck Presentation',
        'post_type' => 'Presentation',
        'approval_stage' => 'Assign',
        'status' => 'To Do',
        'progress_percent' => 10,
        'writer_id' => $assignedPerson->id,
        'brand_manager_id' => $brandManager->id,
    ]);

    // Unassigned user cannot submit
    $resUnauth = $this->actingAs($unassignedPerson)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resUnauth->status())->toBe(403);

    // Assigned person can submit -> moves to Approve
    $resAdvance = $this->actingAs($assignedPerson)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resAdvance->status())->toBeIn([200, 302]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Approve');

    // Assigned person cannot approve themselves
    $resSelf = $this->actingAs($assignedPerson)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resSelf->status())->toBe(403);

    // Brand Manager approves -> moves to Close
    $resApprove = $this->actingAs($brandManager)->json('POST', route('deliverables.submit', $deliverable), []);
    expect($resApprove->status())->toBeIn([200, 302]);
    $deliverable->refresh();
    expect($deliverable->approval_stage)->toBe('Close');
    expect($deliverable->getStageProgress())->toBe(100);
});

test('campaign outline deliverable strictly checks roles at each of the 10 stages', function () {
    $brand = Brand::create(['name' => 'Campaign Brand Gamma', 'slug' => 'campaign-brand-gamma']);

    $writer = User::factory()->create(['name' => 'Writer Wendy', 'role' => 'Writer']);
    $otherWriter = User::factory()->create(['name' => 'Writer Other', 'role' => 'Writer']);
    $approver = User::factory()->create(['name' => 'Approver Andy', 'role' => 'Approver']);
    $furtherApprover = User::factory()->create(['name' => 'FurtherApprover Fiona', 'role' => 'Approver']);
    $brandManager = User::factory()->create(['name' => 'BM Brenda', 'role' => 'Brand Manager']);
    $coordinator = User::factory()->create(['name' => 'Coord Carl', 'role' => 'Coordinator']);
    $designer = User::factory()->create(['name' => 'Designer Dan', 'role' => 'Designer']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Multi-Stage Outlines',
        'workflow_type' => 'campaign',
        'type' => 'Campaign',
        'status' => 'Active',
        'brand_manager_id' => $brandManager->id,
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'further_approver_id' => $furtherApprover->id,
        'coordinator_id' => $coordinator->id,
        'designer_id' => $designer->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Instagram Outlines Campaign',
        'post_type' => 'Outlines',
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'progress_percent' => 0,
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'further_approver_id' => $furtherApprover->id,
        'brand_manager_id' => $brandManager->id,
        'coordinator_id' => $coordinator->id,
        'designer_id' => $designer->id,
    ]);

    expect($task->getWorkflow())->toBeInstanceOf(\App\Services\Workflows\CampaignWorkflowService::class);

    // 1. Stage: Writer
    // Other writer tries to submit -> 403
    $res = $this->actingAs($otherWriter)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Assigned writer submits -> Advances to Approver
    $res = $this->actingAs($writer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Approver');

    // 2. Stage: Approver
    // Writer tries to submit Approver stage -> 403
    $res = $this->actingAs($writer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Approver submits routing to Further Approver -> Advances to Further Approver
    $res = $this->actingAs($approver)->json('POST', route('deliverables.submit', $task), [
        'further_approver_id' => $furtherApprover->id,
    ]);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Further Approver');

    // 3. Stage: Further Approver
    // Initial Approver tries to submit Further Approver stage -> 403
    $res = $this->actingAs($approver)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Assigned Further Approver submits -> Advances to Brand Manager
    $res = $this->actingAs($furtherApprover)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Brand Manager');

    // 4. Stage: Brand Manager
    // Coordinator tries to submit BM stage -> 403
    $res = $this->actingAs($coordinator)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Brand Manager submits -> Advances to Coordinator
    $res = $this->actingAs($brandManager)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Coordinator');

    // 5. Stage: Coordinator
    // Designer tries to submit Coordinator stage -> 403
    $res = $this->actingAs($designer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Coordinator submits -> Advances to Designer
    $res = $this->actingAs($coordinator)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Designer');

    // 6. Stage: Designer
    // Non-designer tries to submit -> 403
    $res = $this->actingAs($writer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Designer submits without artwork -> 422
    $res = $this->actingAs($designer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(422);
    // Designer submits with artwork -> Advances to Writer Review
    $fakeArt = UploadedFile::fake()->image('campaign_art.png', 800, 800);
    $res = $this->actingAs($designer)->post(route('deliverables.submit', $task), [
        'final_designs_file' => $fakeArt,
        'work_hours' => 3.5,
    ]);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Writer Review');

    // 7. Stage: Writer Review
    // Designer tries to submit Writer Review -> 403
    $res = $this->actingAs($designer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Assigned writer submits -> Advances to Approver Review
    $res = $this->actingAs($writer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Approver Review');

    // 8. Stage: Approver Review
    // Writer tries to submit Approver Review -> 403
    $res = $this->actingAs($writer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Approver submits -> Advances to AM/BD
    $res = $this->actingAs($approver)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('AM/BD');

    // 9. Stage: AM/BD
    // Approver tries to submit AM/BD -> 403
    $res = $this->actingAs($approver)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Brand Manager / AM submits -> Advances to Final Approval
    $res = $this->actingAs($brandManager)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Final Approval');

    // 10. Stage: Final Approval
    // Designer tries to submit Final Approval -> 403
    $res = $this->actingAs($designer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);
    // Brand Manager submits -> Advances to Closed (100%)
    $res = $this->actingAs($brandManager)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Closed');
    expect($task->fresh()->getStageProgress())->toBe(100);
});

test('pitch outline deliverable follows identical stage-role validation', function () {
    $brand = Brand::create(['name' => 'Pitch Brand Gamma', 'slug' => 'pitch-brand-gamma']);

    $writer = User::factory()->create(['name' => 'Pitch Writer', 'role' => 'Writer']);
    $otherWriter = User::factory()->create(['name' => 'Pitch Intruder', 'role' => 'Writer']);
    $approver = User::factory()->create(['name' => 'Pitch Approver', 'role' => 'Approver']);
    $brandManager = User::factory()->create(['name' => 'Pitch BM', 'role' => 'Brand Manager']);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Big Pitch Outlines 2026',
        'workflow_type' => 'pitch',
        'type' => 'Pitch',
        'status' => 'Active',
        'brand_manager_id' => $brandManager->id,
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
    ]);

    $task = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Pitch Outlines Package',
        'post_type' => 'Outlines',
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'brand_manager_id' => $brandManager->id,
    ]);

    expect($task->getWorkflow())->toBeInstanceOf(\App\Services\Workflows\CampaignWorkflowService::class);

    // Other writer blocked
    $res = $this->actingAs($otherWriter)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBe(403);

    // Assigned writer succeeds
    $res = $this->actingAs($writer)->json('POST', route('deliverables.submit', $task), []);
    expect($res->status())->toBeIn([200, 302]);
    expect($task->fresh()->approval_stage)->toBe('Approver');
});
