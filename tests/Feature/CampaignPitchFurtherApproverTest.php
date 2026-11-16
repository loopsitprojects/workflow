<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\DeliverableApproval;
use App\Models\DeliverableReassignment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);

    $this->admin = User::factory()->create(['name' => 'Admin User', 'role' => 'Admin']);
    $this->brandManager = User::factory()->create(['name' => 'Brand Manager Sarah', 'role' => 'Brand Manager']);
    $this->approver1 = User::factory()->create(['name' => 'Approver Dave', 'role' => 'Approver']);
    $this->furtherApprover = User::factory()->create(['name' => 'Further Approver Irangi', 'role' => 'Approver']);
    $this->writer = User::factory()->create(['name' => 'Writer Natasha', 'role' => 'Writer']);
    $this->randomUser = User::factory()->create(['name' => 'Random Writer', 'role' => 'Writer']);

    $this->campaignProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Summer Campaign',
        'workflow_type' => 'campaign',
        'type' => 'Campaign',
        'status' => 'Active',
        'brand_manager_id' => $this->brandManager->id,
        'approver_id' => $this->approver1->id,
    ]);

    $this->pitchProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Q4 Pitch Deck',
        'workflow_type' => 'pitch',
        'type' => 'Pitch',
        'status' => 'Active',
        'brand_manager_id' => $this->brandManager->id,
        'approver_id' => $this->approver1->id,
    ]);

    $this->retainerProject = Project::create([
        'brand_id' => $this->brand->id,
        'name' => 'Monthly Retainer',
        'workflow_type' => 'retainer',
        'type' => 'Retainer',
        'status' => 'Active',
        'brand_manager_id' => $this->brandManager->id,
    ]);
});

test('Campaign Flow: Approver can forward deliverable to Further Approver in Approval Stage', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Campaign Social Post',
        'post_type' => 'Carousel',
        'status' => 'Approval Stage',
        'approval_stage' => 'Approve',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver1->id,
    ]);

    // Send to Further Approver
    $res = $this->actingAs($this->approver1)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
        'notes' => 'Please do final review of the slide copy.',
    ]);

    expect($res->status())->toBe(200);
    $data = $res->json();
    expect($data['success'])->toBeTrue();
    expect($data['approval_stage'])->toBe('Further Approver');

    // Deliverable remains in Approval Stage column, with Further Approver stage
    $deliverable->refresh();
    expect($deliverable->status)->toBe('Approval Stage');
    expect($deliverable->approval_stage)->toBe('Further Approver');
    expect((int)$deliverable->further_approver_id)->toBe($this->furtherApprover->id);
    expect((int)$deliverable->approver_id)->toBe($this->furtherApprover->id);

    // Verify approval history audit
    $lastApproval = $deliverable->approvalsHistory()->latest('id')->first();
    expect($lastApproval)->not->toBeNull();
    expect($lastApproval->stage)->toBe('Further Approver');
    expect($lastApproval->notes)->toBe('Please do final review of the slide copy.');

    // Verify reassignment audit
    $reassign = DeliverableReassignment::where('deliverable_id', $deliverable->id)->latest('id')->first();
    expect($reassign)->not->toBeNull();
    expect((int)$reassign->to_user_id)->toBe($this->furtherApprover->id);

    // Further Approver can now complete the deliverable
    $completeRes = $this->actingAs($this->furtherApprover)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'Done',
    ]);
    expect($completeRes->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('Done');
});

test('Pitch Flow: Approver can forward deliverable to Further Approver in Approval Stage', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->pitchProject->id,
        'title' => 'Pitch Deck Strategy Slides',
        'post_type' => 'Pitch Deck',
        'status' => 'Approval Stage',
        'approval_stage' => 'Approve',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver1->id,
    ]);

    // Send to Further Approver
    $res = $this->actingAs($this->approver1)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
        'notes' => 'Review financials slide in pitch deck.',
    ]);

    expect($res->status())->toBe(200);
    expect($res->json('success'))->toBeTrue();

    $deliverable->refresh();
    expect($deliverable->status)->toBe('Approval Stage');
    expect($deliverable->approval_stage)->toBe('Further Approver');
    expect((int)$deliverable->further_approver_id)->toBe($this->furtherApprover->id);
    expect((int)$deliverable->approver_id)->toBe($this->furtherApprover->id);

    // Further Approver can request revisions back to In Progress
    $revRes = $this->actingAs($this->furtherApprover)->json('POST', route('deliverables.update-kanban-status', $deliverable), [
        'status' => 'In Progress',
        'revision_instructions' => 'Update slide 4 budget figures.',
    ]);
    expect($revRes->status())->toBe(200);
    expect($deliverable->fresh()->status)->toBe('In Progress');
});

test('Further Approver: Unauthorized users cannot forward deliverables', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Deliverable Task',
        'status' => 'Approval Stage',
        'approval_stage' => 'Approve',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver1->id,
    ]);

    // Random user cannot forward
    $res = $this->actingAs($this->randomUser)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
    ]);
    expect($res->status())->toBe(403);

    // Writer cannot forward
    $resWriter = $this->actingAs($this->writer)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
    ]);
    expect($resWriter->status())->toBe(403);

    // Admin CAN forward
    $resAdmin = $this->actingAs($this->admin)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
    ]);
    expect($resAdmin->status())->toBe(200);
});

test('Further Approver: Cannot forward deliverable outside Approval Stage', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Deliverable In Progress',
        'status' => 'In Progress',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver1->id,
    ]);

    $res = $this->actingAs($this->approver1)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
    ]);
    expect($res->status())->toBe(422);
});

test('Further Approver: Retainer projects are restricted', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->retainerProject->id,
        'title' => 'Retainer Deliverable',
        'status' => 'Approval Stage',
        'approval_stage' => 'Approver',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver1->id,
    ]);

    $res = $this->actingAs($this->approver1)->json('POST', route('campaign-kanban.further-approve', $deliverable), [
        'further_approver_id' => $this->furtherApprover->id,
    ]);
    expect($res->status())->toBe(403);
});

test('Campaign & Pitch: Once assigned, only assigned person and admin can make changes', function () {
    $deliverable = Deliverable::create([
        'project_id' => $this->campaignProject->id,
        'title' => 'Original Title',
        'status' => 'In Progress',
        'writer_id' => $this->writer->id,
        'approver_id' => $this->approver1->id,
    ]);

    // 1. Random user attempting to make changes gets 403
    $resRandom = $this->actingAs($this->randomUser)->json('POST', route('campaign-kanban.quick-update', $deliverable), [
        'title' => 'Hacked Title',
    ]);
    expect($resRandom->status())->toBe(403);

    // 2. Approver attempting to edit deliverable details directly gets 403
    $resApprover = $this->actingAs($this->approver1)->json('POST', route('campaign-kanban.quick-update', $deliverable), [
        'title' => 'Approver Edited Title',
    ]);
    expect($resApprover->status())->toBe(403);

    // 3. Assigned person CAN make changes
    $resAssigned = $this->actingAs($this->writer)->json('POST', route('campaign-kanban.quick-update', $deliverable), [
        'title' => 'Writer Updated Title',
        'priority' => 'High Priority',
    ]);
    expect($resAssigned->status())->toBe(200);
    expect($deliverable->fresh()->title)->toBe('Writer Updated Title');
    expect($deliverable->fresh()->priority)->toBe('High Priority');

    // 4. Admin CAN make changes
    $resAdmin = $this->actingAs($this->admin)->json('POST', route('campaign-kanban.quick-update', $deliverable), [
        'title' => 'Admin Updated Title',
    ]);
    expect($resAdmin->status())->toBe(200);
    expect($deliverable->fresh()->title)->toBe('Admin Updated Title');
});
