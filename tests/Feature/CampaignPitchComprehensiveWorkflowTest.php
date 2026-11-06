<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\DeliverableReassignment;
use App\Models\DeliverableRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->brand = Brand::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

    $this->admin = User::factory()->create(['name' => 'Admin User', 'role' => 'Admin']);
    $this->brandManager = User::factory()->create(['name' => 'Brand Manager Sarah', 'role' => 'Brand Manager']);
    $this->approver = User::factory()->create(['name' => 'Approver Dave', 'role' => 'Approver']);
    $this->writer = User::factory()->create(['name' => 'Writer Natasha', 'role' => 'Writer']);
    $this->writer2 = User::factory()->create(['name' => 'Writer Bob', 'role' => 'Writer']);
    $this->designer = User::factory()->create(['name' => 'Designer Livinya', 'role' => 'Designer']);
    $this->otherUser = User::factory()->create(['name' => 'Random User', 'role' => 'Writer']);

    $this->campaignProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Q4 Holiday Campaign',
        'workflow_type' => 'campaign',
        'type' => 'Campaign',
        'status' => 'Active',
        'brand_manager_id' => $this->brandManager->id,
        'approver_id' => $this->approver->id,
    ]);

    $this->pitchProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Client Pitch Deck 2026',
        'workflow_type' => 'pitch',
        'type' => 'Pitch',
        'status' => 'Active',
        'brand_manager_id' => $this->brandManager->id,
        'approver_id' => $this->approver->id,
    ]);
});

// SCENARIO 1: To Do Stage & Initial Transition Restrictions
test('Scenario 1: To Do stage transitions - unassigned blocked, assigned/BM/Admin allowed, invalid jumps blocked', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Campaign Reel Script',
        'post_type' => 'Reel',
        'status' => 'To Do',
        'writer_id' => null,
        'approver_id' => $this->approver->id,
    ]);

    // 1a. Unassigned deliverable cannot be moved to In Progress by random user
    $res = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($res->status())->toBe(403);
    expect($deliverable->fresh()->status)->toBe('To Do');

    // 1b. Assign writer
    $deliverable->update(['writer_id' => $this->writer->id, 'assignee_name' => $this->writer->name]);

    // 1c. Random outside user cannot move it
    $resRandom = $this->actingAs($this->otherUser)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($resRandom->status())->toBe(403);

    // 1d. Invalid jump: To Do -> Approval Stage is blocked (must be In Progress first)
    $resInvalid = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Approval Stage',
    ]);
    expect($resInvalid->status())->toBe(422);

    // 1e. Invalid jump: To Do -> Done is blocked
    $resInvalid2 = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($resInvalid2->status())->toBe(422);

    // 1f. Assigned writer can move To Do -> In Progress
    $resWriter = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($resWriter->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('In Progress');
});

// SCENARIO 2: Forward-Only Rule - Cannot Move Back to To Do
test('Scenario 2: Forward-only rule - deliverables cannot be moved back to To Do from any stage', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Social Banner',
        'post_type' => 'Static Post',
        'status' => 'In Progress',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver->id,
    ]);

    // 2a. Attempt In Progress -> To Do (Blocked with 422)
    $res = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'To Do',
    ]);
    expect($res->status())->toBe(422);
    expect($res->json('message'))->toBe('Deliverables cannot be moved back to To Do.');
    expect($deliverable->fresh()->status)->toBe('In Progress');

    // Even admin cannot move back to To Do
    $resAdmin = $this->actingAs($this->admin)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'To Do',
    ]);
    expect($resAdmin->status())->toBe(422);

    // 2b. Attempt Approval Stage -> To Do (Blocked with 422)
    $deliverable->update(['status' => 'Approval Stage']);
    $resAppr = $this->actingAs($this->approver)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'To Do',
    ]);
    expect($resAppr->status())->toBe(422);
    expect($resAppr->json('message'))->toBe('Deliverables cannot be moved back to To Do.');

    // 2c. Attempt Done -> To Do (Blocked with 422)
    $deliverable->update(['status' => 'Done']);
    $resDone = $this->actingAs($this->approver)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'To Do',
    ]);
    expect($resDone->status())->toBe(422);
    expect($resDone->json('message'))->toBe('Deliverables cannot be moved back to To Do.');
});

// SCENARIO 3: Direct Completion from In Progress is Blocked
test('Scenario 3: Deliverables in In Progress cannot be marked as complete directly', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Pitch Deck Slide 1-5',
        'post_type' => 'Pitch Deck',
        'status' => 'In Progress',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver->id,
    ]);

    $res = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($res->status())->toBe(422);
    expect($res->json('message'))->toContain('cannot be marked as complete directly');
    expect($deliverable->fresh()->status)->toBe('In Progress');
});

// SCENARIO 4: Moving In Progress -> Approval Stage
test('Scenario 4: Moving from In Progress to Approval Stage enforces assigned user or manager permissions', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Brochure Copy',
        'post_type' => 'Outlines',
        'status' => 'In Progress',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver->id,
    ]);

    // Random user cannot submit to approval
    $resOther = $this->actingAs($this->otherUser)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Approval Stage',
    ]);
    expect($resOther->status())->toBe(403);

    // Assigned writer without artwork cannot submit to approval (requires artwork/file)
    $resWriterNoArt = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Approval Stage',
    ]);
    expect($resWriterNoArt->status())->toBe(422);
    expect($resWriterNoArt->json('require_file'))->toBeTrue();

    // Attach artwork/deliverable file
    $deliverable->update(['final_designs_link' => 'https://drive.google.com/test-artwork']);

    // Assigned writer with artwork can submit to approval
    $resWriter = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Approval Stage',
    ]);
    expect($resWriter->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('Approval Stage');
});

// SCENARIO 5: Approval Stage Actions - Assignee self-approval blocked, Approver/BM/Admin allowed
test('Scenario 5: In Approval Stage, assignee cannot self-approve or request revisions; only Approver, BM, and Admin can', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'TV Commercial Script',
        'post_type' => 'Outlines',
        'status' => 'Approval Stage',
        'final_designs_link' => 'https://drive.google.com/tv-commercial-artwork',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver->id,
    ]);

    // 5a. Writer cannot approve their own work
    $resWriterApprove = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($resWriterApprove->status())->toBe(403);
    expect($deliverable->fresh()->status)->toBe('Approval Stage');

    // 5b. Writer cannot send back for revisions
    $resWriterRev = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
        'revision_instructions' => 'I want to change my own copy',
    ]);
    expect($resWriterRev->status())->toBe(403);

    // 5c. Random user cannot approve
    $resOther = $this->actingAs($this->otherUser)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($resOther->status())->toBe(403);

    // 5d. Designated Approver can request revisions -> Returns to In Progress and logs revision
    $resApproverRev = $this->actingAs($this->approver)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
        'revision_instructions' => 'Tone needs to be more punchy in paragraph 2',
    ]);
    expect($resApproverRev->status())->toBe(200);
    $fresh = $deliverable->fresh();
    expect($fresh->status)->toBe('In Progress');
    expect((int)$fresh->revisions)->toBe(1);
    expect($fresh->revision_instructions)->toBe('Tone needs to be more punchy in paragraph 2');

    // Check revision history record created and initially awaiting updates (fixed_at is null)
    $revRecord = DeliverableRevision::where('deliverable_id', $deliverable->id)->first();
    expect($revRecord)->not->toBeNull();
    expect($revRecord->instructions)->toBe('Tone needs to be more punchy in paragraph 2');
    expect($revRecord->fixed_at)->toBeNull();

    // 5e. Writer completes revision and submits back to Approval Stage
    $resWriterResubmit = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Approval Stage',
    ]);
    expect($resWriterResubmit->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('Approval Stage');
    
    // Verify revision record is now marked as resolved/submitted with timestamp and submitter
    $revRecordFresh = $revRecord->fresh();
    expect($revRecordFresh->fixed_at)->not->toBeNull();
    expect($revRecordFresh->fixed_by_user_id)->toBe($this->writer->id);

    // 5f. Designated Approver approves -> Moves to Done
    $resApproverApprove = $this->actingAs($this->approver)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($resApproverApprove->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('Done');
});

// SCENARIO 6: Reassignment Permissions
test('Scenario 6: Deliverable reassignment can only be done by Approver, Brand Manager, or Admin', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Keynote Presentation',
        'post_type' => 'Presentation',
        'status' => 'In Progress',
        'writer_id' => $this->writer->id,
        'assignee_name' => $this->writer->name,
        'approver_id' => $this->approver->id,
    ]);

    // 6a. Assigned writer tries to reassign to writer2 via reassign-writer endpoint -> Blocked (403)
    $resWriter = $this->actingAs($this->writer)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->writer2->id,
        'reason' => 'Too busy',
    ]);
    expect($resWriter->status())->toBe(403);
    expect($deliverable->fresh()->writer_id)->toBe($this->writer->id);

    // 6b. Writer tries to reassign via quick-update endpoint -> Blocked (403)
    $resQuick = $this->actingAs($this->writer)->json('POST', route('campaign-kanban.quick-update', $deliverable), [
        'title' => $deliverable->title,
        'writer_id' => $this->writer2->id,
        'reason' => 'Passing to colleague',
    ]);
    expect($resQuick->status())->toBe(403);
    expect($deliverable->fresh()->writer_id)->toBe($this->writer->id);

    // 6c. Approver reassigns -> Success (200)
    $resApprover = $this->actingAs($this->approver)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->writer2->id,
        'reason' => 'Workload rebalance by approver',
    ]);
    expect($resApprover->status())->toBe(200);
    expect($deliverable->fresh()->writer_id)->toBe($this->writer2->id);

    // Verify reassignment audit record logged
    $audit = DeliverableReassignment::where('deliverable_id', $deliverable->id)->first();
    expect($audit)->not->toBeNull();
    expect($audit->from_user_id)->toBe($this->writer->id);
    expect($audit->to_user_id)->toBe($this->writer2->id);
    expect($audit->reason)->toBe('Workload rebalance by approver');

    // 6d. Brand Manager reassigns -> Success (200)
    $resBM = $this->actingAs($this->brandManager)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->writer->id,
        'reason' => 'Reassigned by BM',
    ]);
    expect($resBM->status())->toBe(200);
    expect($deliverable->fresh()->writer_id)->toBe($this->writer->id);

    // 6e. Admin reassigns -> Success (200)
    $resAdmin = $this->actingAs($this->admin)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->writer2->id,
        'reason' => 'Admin override',
    ]);
    expect($resAdmin->status())->toBe(200);
    expect($deliverable->fresh()->writer_id)->toBe($this->writer2->id);
});

// SCENARIO 7: Pitch Flow Equivalence
test('Scenario 7: Pitch flow operates identically under the same workflow constraints', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->pitchProject->id,
        'title' => 'Pitch Deck Concept',
        'post_type' => 'Pitch Deck',
        'status' => 'To Do',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver->id,
    ]);

    // Move To Do -> In Progress
    $res1 = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
    ]);
    expect($res1->status())->toBe(200);

    // Cannot move back to To Do
    $res2 = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'To Do',
    ]);
    expect($res2->status())->toBe(422);

    // Cannot jump direct to Done
    $res3 = $this->actingAs($this->writer)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($res3->status())->toBe(422);

    // Writer cannot reassign
    $res4 = $this->actingAs($this->writer)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->writer2->id,
    ]);
    expect($res4->status())->toBe(403);

    // Approver can reassign
    $res5 = $this->actingAs($this->approver)->json('POST', route('deliverables.reassign-writer', $deliverable), [
        'writer_id' => $this->writer2->id,
    ]);
    expect($res5->status())->toBe(200);
    expect($deliverable->fresh()->writer_id)->toBe($this->writer2->id);
});

// SCENARIO 8: Retainer Flow Isolation Check
test('Scenario 8: Retainer workflow logic and endpoints remain strictly isolated and unaffected', function () {
    $retainerProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Monthly Retainer 2026',
        'workflow_type' => 'retainer',
        'type' => 'Retainer',
        'status' => 'Active',
    ]);

    $retainerDeliverable = Deliverable::create([
        'project_id' => $retainerProject->id,
        'title' => 'Retainer Carousel',
        'post_type' => 'Carousel',
        'status' => 'In Progress',
        'approval_stage' => 'Writer',
        'writer_id' => $this->writer->id,
    ]);

    // Kanban status update is restricted to Campaign and Pitch projects
    $resKanban = $this->actingAs($this->admin)->json('POST', route('deliverables.update-kanban-status', $retainerDeliverable), [
        'status' => 'Done',
    ]);
    expect($resKanban->status())->toBe(403);
    expect($resKanban->json('message'))->toContain('Campaign and Pitch');

    // Quick update is restricted to Campaign and Pitch projects
    $resQuick = $this->actingAs($this->admin)->json('POST', route('campaign-kanban.quick-update', $retainerDeliverable), [
        'title' => 'Updated Title',
    ]);
    expect($resQuick->status())->toBe(403);
    expect($resQuick->json('message'))->toContain('campaign and pitch');
});
