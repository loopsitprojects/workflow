<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\DeliverableRevision;
use App\Models\DeliverableApproval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('s3');
    Storage::fake('public');
});

test('comprehensive end-to-end verification of all workflows and roles in a new project', function () {
    // ---------------------------------------------------------
    // 1. SETUP: Create Users for Every Role
    // ---------------------------------------------------------
    $admin = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin_test@loops.com',
        'role' => 'Admin',
    ]);

    $brandManager = User::factory()->create([
        'name' => 'Yoshini BM',
        'email' => 'bm_test@loops.com',
        'role' => 'Brand Manager',
    ]);

    $approver = User::factory()->create([
        'name' => 'Oken Approver',
        'email' => 'approver_test@loops.com',
        'role' => 'Approver',
    ]);

    $furtherApprover = User::factory()->create([
        'name' => 'Irangi Further Approver',
        'email' => 'f_approver_test@loops.com',
        'role' => 'Approver Coordinator',
    ]);

    $coordinator = User::factory()->create([
        'name' => 'Kavi Coordinator',
        'email' => 'coordinator_test@loops.com',
        'role' => 'Coordinator',
    ]);

    $writer = User::factory()->create([
        'name' => 'Natasha Writer',
        'email' => 'writer_test@loops.com',
        'role' => 'Writer',
    ]);

    $assignee = User::factory()->create([
        'name' => 'Vinul Assignee',
        'email' => 'assignee_test@loops.com',
        'role' => 'Writer', // acts as Assignee
    ]);

    $designer = User::factory()->create([
        'name' => 'Bravin Designer',
        'email' => 'designer_test@loops.com',
        'role' => 'Designer',
    ]);

    // ---------------------------------------------------------
    // 2. BRAND & PROJECT CREATION (Brand Manager)
    // ---------------------------------------------------------
    $brand = Brand::create([
        'name' => 'Global Beverage Corp',
        'slug' => 'global-beverage-corp',
    ]);
    $brand->members()->attach([
        $admin->id, $brandManager->id, $approver->id, $furtherApprover->id,
        $coordinator->id, $writer->id, $assignee->id, $designer->id
    ]);

    // Create New Campaign Project
    $projectResponse = $this->actingAs($brandManager)->post(route('projects.store'), [
        'brand_id' => $brand->id,
        'name' => 'Q4 Mega Campaign 2026',
        'workflow_type' => 'campaign',
        'type' => 'Campaign',
        'status' => 'Active',
        'priority' => 'Medium',
        'brand_manager_id' => $brandManager->id,
        'approver_id' => $approver->id,
        'coordinator_id' => $coordinator->id,
        'writer_id' => $writer->id,
        'deadline' => now()->addDays(30)->toDateString(),
    ]);
    expect($projectResponse->status())->toBeIn([200, 302]);

    $project = Project::where('name', 'Q4 Mega Campaign 2026')->first();
    expect($project)->not->toBeNull();
    expect($project->workflow_type)->toBe('campaign');

    // =========================================================
    // FLOW 1: CAMPAIGN OUTLINES FLOW (11 Stages, Multi-Role)
    // =========================================================
    // Stage 1: Writer creates Outlines deliverable
    $outlineResponse = $this->actingAs($writer)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'title' => 'Digital Banking - Launch Outlines',
        'task_type' => 'Deliverable',
        'status' => 'To Do',
        'progress_percent' => 0,
        'post_type' => 'Outlines',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'further_approver_id' => $furtherApprover->id,
        'brand_manager_id' => $brandManager->id,
        'coordinator_id' => $coordinator->id,
        'designer_id' => $designer->id,
        'priority' => 'High Priority',
        'concept' => '3-part narrative series highlighting seamless banking.',
        'post_copy' => 'Hook: Still waiting in branch queues? Body: Switch to instant digital banking.',
        'caption' => 'Experience seamless banking from your phone. #DigitalBanking',
        'deadline' => now()->addDays(7)->toDateString(),
    ]);
    expect($outlineResponse->status())->toBeIn([200, 302]);

    $outline = Deliverable::where('title', 'Digital Banking - Launch Outlines')->first();
    expect($outline)->not->toBeNull();
    expect($outline->approval_stage)->toBe('Writer');
    expect($outline->priority)->toBe('High Priority');
    expect($outline->getStageProgress())->toBe(0);

    // Authorization Test: Designer tries to submit Writer stage -> should fail
    $unauthResp = $this->actingAs($designer)->post(route('deliverables.submit', $outline), [
        'approver_id' => $approver->id,
    ]);
    $unauthResp->assertSessionHas('error');
    expect($outline->fresh()->approval_stage)->toBe('Writer');

    // Step 1: Writer Natasha submits stage -> moves to Approver (10%)
    $s1Resp = $this->actingAs($writer)->post(route('deliverables.submit', $outline), [
        'approver_id' => $approver->id,
    ]);
    expect($s1Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Approver');
    expect($outline->getStageProgress())->toBe(10);

    // Step 2: Approver Oken reviews and routes to Further Approver Irangi (20%)
    $s2Resp = $this->actingAs($approver)->post(route('deliverables.submit', $outline), [
        'further_approver_id' => $furtherApprover->id,
    ]);
    expect($s2Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Further Approver');
    expect($outline->getStageProgress())->toBe(20);

    // Step 3: Further Approver Irangi submits -> moves to Brand Manager (30%)
    $s3Resp = $this->actingAs($furtherApprover)->post(route('deliverables.submit', $outline), [
        'brand_manager_id' => $brandManager->id,
    ]);
    expect($s3Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Brand Manager');
    expect($outline->getStageProgress())->toBe(30);

    // Step 4: Brand Manager Yoshini submits -> moves to Coordinator (40%)
    $s4Resp = $this->actingAs($brandManager)->post(route('deliverables.submit', $outline), [
        'coordinator_id' => $coordinator->id,
    ]);
    expect($s4Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Coordinator');
    expect($outline->getStageProgress())->toBe(40);

    // Step 5: Coordinator Kavi submits -> moves to Designer (50%)
    $s5Resp = $this->actingAs($coordinator)->post(route('deliverables.submit', $outline), [
        'designer_id' => $designer->id,
    ]);
    expect($s5Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Designer');
    expect($outline->getStageProgress())->toBe(50);

    // Step 6: Designer Gate Test: Submitting without artwork should fail
    $noArtResp = $this->actingAs($designer)->post(route('deliverables.submit', $outline), [
        'writer_id' => $writer->id,
    ]);
    $noArtResp->assertSessionHas('error');
    expect($outline->fresh()->approval_stage)->toBe('Designer');

    // Step 6 (valid): Designer Bravin uploads artwork and submits -> moves to Writer Review (60%)
    $fakeArtwork = UploadedFile::fake()->image('launch_key_visual.jpg', 1200, 800);
    $s6Resp = $this->actingAs($designer)->post(route('deliverables.submit', $outline), [
        'writer_id' => $writer->id,
        'final_designs_file' => $fakeArtwork,
        'final_designs_link' => 'https://figma.com/file/sample-key-visual',
        'work_hours' => 3.5,
    ]);
    expect($s6Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Writer Review');
    expect($outline->getStageProgress())->toBe(60);
    expect($outline->final_designs)->not->toBeNull();

    // Step 7: Writer Review: Writer Natasha reviews artwork -> moves to Approver Review (70%)
    $s7Resp = $this->actingAs($writer)->post(route('deliverables.submit', $outline), [
        'approver_id' => $approver->id,
    ]);
    expect($s7Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Approver Review');
    expect($outline->getStageProgress())->toBe(70);

    // Step 8: Approver Review: Approver Oken reviews -> moves to AM/BD (80%)
    $s8Resp = $this->actingAs($approver)->post(route('deliverables.submit', $outline), [
        'brand_manager_id' => $brandManager->id,
    ]);
    expect($s8Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('AM/BD');
    expect($outline->getStageProgress())->toBe(80);

    // Step 9: AM/BD: Brand Manager Yoshini reviews -> moves to Final Approval (90%)
    $s9Resp = $this->actingAs($brandManager)->post(route('deliverables.submit', $outline), [
        'brand_manager_id' => $brandManager->id,
    ]);
    expect($s9Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Final Approval');
    expect($outline->getStageProgress())->toBe(90);

    // Step 10: Final Approval: Brand Manager Yoshini gives final approval -> Closed (100%)
    $s10Resp = $this->actingAs($brandManager)->post(route('deliverables.submit', $outline), []);
    expect($s10Resp->status())->toBeIn([200, 302]);
    $outline->refresh();
    expect($outline->approval_stage)->toBe('Closed');
    expect($outline->getStageProgress())->toBe(100);
    expect($outline->status)->toBe('Done');

    // =========================================================
    // FLOW 2: CAMPAIGN OTHER DELIVERABLES FLOW (3 Stages: Assign -> Approve -> Close)
    // =========================================================
    $otherResponse = $this->actingAs($brandManager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'title' => 'Paid Media & Influencer Strategy Plan',
        'task_type' => 'Deliverable',
        'status' => 'To Do',
        'progress_percent' => 10,
        'post_type' => 'Strategy Deck',
        'writer_id' => $assignee->id,
        'brand_manager_id' => $brandManager->id,
        'concept' => 'Complete media mix modeling with influencer tiering.',
        'deadline' => now()->addDays(14)->toDateString(),
    ]);
    expect($otherResponse->status())->toBeIn([200, 302]);

    $otherDeliverable = Deliverable::where('title', 'Paid Media & Influencer Strategy Plan')->first();
    expect($otherDeliverable)->not->toBeNull();
    expect($otherDeliverable->approval_stage)->toBe('Assign');
    expect($otherDeliverable->getStageProgress())->toBe(10);

    // Authorization Test: Unassigned user cannot submit Assign stage
    $unauthOtherResp = $this->actingAs($designer)->post(route('deliverables.submit', $otherDeliverable), []);
    $unauthOtherResp->assertSessionHas('error');
    expect($otherDeliverable->fresh()->approval_stage)->toBe('Assign');

    // Step 1: Assigned user Vinul submits Assign stage -> moves to Approve (50%)
    $otherS1Resp = $this->actingAs($assignee)->post(route('deliverables.submit', $otherDeliverable), [
        'brand_manager_id' => $brandManager->id,
    ]);
    expect($otherS1Resp->status())->toBeIn([200, 302]);
    $otherDeliverable->refresh();
    expect($otherDeliverable->approval_stage)->toBe('Approve');
    expect($otherDeliverable->getStageProgress())->toBe(50);

    // Authorization Test: Regular writer/designer cannot approve
    $unauthApproveResp = $this->actingAs($writer)->post(route('deliverables.submit', $otherDeliverable), []);
    $unauthApproveResp->assertSessionHas('error');
    expect($otherDeliverable->fresh()->approval_stage)->toBe('Approve');

    // Step 2: Brand Manager Yoshini approves -> moves to Close (100%)
    $otherS2Resp = $this->actingAs($brandManager)->post(route('deliverables.submit', $otherDeliverable), []);
    expect($otherS2Resp->status())->toBeIn([200, 302]);
    $otherDeliverable->refresh();
    expect($otherDeliverable->approval_stage)->toBe('Close');
    expect($otherDeliverable->getStageProgress())->toBe(100);
    expect($otherDeliverable->status)->toBe('Done');

    // =========================================================
    // FLOW 3: FAST TRACK DELIVERABLE FLOW (Designer -> Manager Review -> Closed)
    // =========================================================
    $fastTrackResponse = $this->actingAs($brandManager)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'title' => 'Flash Sale Social Banners',
        'flow_type' => 'direct_design',
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 20,
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'designer_deadline' => now()->addDays(3)->toDateString(),
        'priority' => 'High Priority',
        'concept' => 'Need 3 urgent story banners for 24-hour flash sale.',
    ]);
    expect($fastTrackResponse->status())->toBeIn([200, 302]);

    $fastTrack = Deliverable::where('title', 'Flash Sale Social Banners')->first();
    expect($fastTrack)->not->toBeNull();
    expect($fastTrack->flow_type)->toBe('direct_design');
    expect($fastTrack->approval_stage)->toBe('Designer');
    expect($fastTrack->getStageProgress())->toBe(20);

    // Authorization Test: Writer cannot submit Designer stage of fast track
    $ftUnauthResp = $this->actingAs($writer)->post(route('deliverables.submit', $fastTrack), []);
    $ftUnauthResp->assertSessionHas('error');
    expect($fastTrack->fresh()->approval_stage)->toBe('Designer');

    // Step 1: Designer Bravin submits Fast Track -> moves to Manager Review (60%)
    $ftFakeArt = UploadedFile::fake()->image('flash_sale_banner.png', 1080, 1920);
    $ftS1Resp = $this->actingAs($designer)->post(route('deliverables.submit', $fastTrack), [
        'final_designs_file' => $ftFakeArt,
        'work_hours' => 2.0,
    ]);
    expect($ftS1Resp->status())->toBeIn([200, 302]);
    $fastTrack->refresh();
    expect($fastTrack->approval_stage)->toBe('Manager Review');
    expect($fastTrack->getStageProgress())->toBe(60);

    // Step 2: Manager Review: Brand Manager approves -> moves to Closed (100%)
    $ftS2Resp = $this->actingAs($brandManager)->post(route('deliverables.submit', $fastTrack), []);
    expect($ftS2Resp->status())->toBeIn([200, 302]);
    $fastTrack->refresh();
    expect($fastTrack->approval_stage)->toBe('Closed');
    expect($fastTrack->getStageProgress())->toBe(100);
    expect($fastTrack->status)->toBe('Done');

    // =========================================================
    // FLOW 4: REVISION CYCLE FLOW & TARGETING
    // =========================================================
    // Create deliverable to test revision loops
    $revItem = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Instagram Reel Storyboard',
        'post_type' => 'Outlines',
        'approval_stage' => 'Approver',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'designer_id' => $designer->id,
        'brand_manager_id' => $brandManager->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 10,
        'revisions' => 0,
    ]);

    // Approver requests revision targeting Writer
    $revResp = $this->actingAs($approver)->post(route('deliverables.revisions', $revItem), [
        'revision_instructions' => 'Please rewrite the third scene hook to be punchier.',
        'revision_target' => 'writer',
    ]);
    expect($revResp->status())->toBeIn([200, 302]);
    $revItem->refresh();
    expect($revItem->approval_stage)->toBe('Writer');
    expect($revItem->revisions)->toBe(1);

    // Fast Track Deliverable revision test
    $ftRevItem = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Display Banner Ad',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Manager Review',
        'designer_id' => $designer->id,
        'brand_manager_id' => $brandManager->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 60,
        'revisions' => 0,
    ]);

    $ftRevResp = $this->actingAs($brandManager)->post(route('deliverables.revisions', $ftRevItem), [
        'revision_instructions' => 'Make logo 20% larger and adjust CTA contrast.',
        'revision_target' => 'designer',
    ]);
    expect($ftRevResp->status())->toBeIn([200, 302]);
    $ftRevItem->refresh();
    expect($ftRevItem->approval_stage)->toBe('Designer');
    expect($ftRevItem->revisions)->toBe(1);

    // =========================================================
    // FLOW 5: PRIORITY, CLIENT STATUS, & DESIGNER REASSIGNMENT
    // =========================================================
    // Priority update
    $prioResp = $this->actingAs($brandManager)->post(route('deliverables.update-priority', $revItem), [
        'priority' => 'High Priority',
    ]);
    expect($prioResp->status())->toBeIn([200, 302]);
    $revItem->refresh();
    expect($revItem->priority)->toBe('High Priority');

    // Client Status update
    $clientStatusResp = $this->actingAs($brandManager)->post(route('deliverables.update-client-status', $revItem), [
        'client_status' => 'Sent to Client',
    ]);
    expect($clientStatusResp->status())->toBeIn([200, 302]);
    $revItem->refresh();
    expect($revItem->client_status)->toBe('Sent to Client');

    // Designer Reassignment
    $newDesigner = User::factory()->create(['role' => 'Designer', 'name' => 'Sahan Designer']);
    $reassignResp = $this->actingAs($coordinator)->post(route('deliverables.reassign-designer', $revItem), [
        'designer_id' => $newDesigner->id,
        'designer_deadline' => now()->addDays(5)->toDateString(),
        'reason' => 'Workload rebalancing for sprint deadline.',
    ]);
    expect($reassignResp->status())->toBe(200);
    $revItem->refresh();
    expect($revItem->designer_id)->toBe($newDesigner->id);

    // =========================================================
    // FLOW 6: ADMIN BYPASS & ROLE PERMISSION BOUNDARIES
    // =========================================================
    // Admin bypasses any stage without restriction
    $bypassItem = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'VIP Press Release',
        'post_type' => 'Outlines',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'final_designs_link' => 'https://figma.com/sample',
    ]);
    $adminResp = $this->actingAs($admin)->post(route('deliverables.submit', $bypassItem), [
        'writer_id' => $writer->id,
    ]);
    expect($adminResp->status())->toBeIn([200, 302]);
    $bypassItem->refresh();
    expect($bypassItem->approval_stage)->toBe('Writer Review');

    // Non-admin writer cannot access admin routes
    $writerAdminResp = $this->actingAs($writer)->get('/admin/settings');
    expect($writerAdminResp->status())->toBe(403);

    // Non-admin coordinator cannot delete deliverable
    $coordDeleteResp = $this->actingAs($coordinator)->delete(route('deliverables.destroy', $bypassItem));
    expect($coordDeleteResp->status())->toBe(403);
});
