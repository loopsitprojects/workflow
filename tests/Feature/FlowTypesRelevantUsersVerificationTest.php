<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlowTypesRelevantUsersVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $brandManager;
    protected User $approver;
    protected User $furtherApprover;
    protected User $coordinator;
    protected User $writer;
    protected User $assignee;
    protected User $designer;
    protected User $otherUser;
    protected Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
        Storage::fake('public');

        // Create relevant users for each distinct role
        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Admin',
        ]);

        $this->brandManager = User::create([
            'name' => 'Yoshini BrandManager',
            'email' => 'bm@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Brand Manager',
        ]);

        $this->approver = User::create([
            'name' => 'Oken Approver',
            'email' => 'approver@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Approver',
        ]);

        $this->furtherApprover = User::create([
            'name' => 'Irangi FurtherApprover',
            'email' => 'fapprover@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Approver Coordinator',
        ]);

        $this->coordinator = User::create([
            'name' => 'Kavi Coordinator',
            'email' => 'coord@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Coordinator',
        ]);

        $this->writer = User::create([
            'name' => 'Natasha Writer',
            'email' => 'writer@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        $this->assignee = User::create([
            'name' => 'Vinul Assignee',
            'email' => 'assignee@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        $this->designer = User::create([
            'name' => 'Bravin Designer',
            'email' => 'designer@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Designer',
        ]);

        $this->otherUser = User::create([
            'name' => 'Unrelated User',
            'email' => 'other@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        // Create Brand and attach all team members
        $this->brand = Brand::create([
            'name' => 'Sampath Bank',
            'slug' => 'sampath-bank',
            'description' => 'Premier Commercial Bank',
        ]);
        $this->brand->members()->attach([
            $this->admin->id,
            $this->brandManager->id,
            $this->approver->id,
            $this->furtherApprover->id,
            $this->coordinator->id,
            $this->writer->id,
            $this->assignee->id,
            $this->designer->id,
        ]);
    }

    /**
     * FLOW 1: Retainer Standard Flow (11 Stages, Step-by-Step with Relevant Users)
     * Writer -> Approver -> Further Approver -> Brand Manager -> Coordinator ->
     * Designer -> Writer Review -> Approver Review -> Final Approval -> Scheduled -> Closed
     */
    public function test_retainer_standard_flow_using_relevant_users_through_all_stages(): void
    {
        $project = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Retainer Monthly Deliverables',
            'workflow_type' => 'retainer',
            'brand_manager_id' => $this->brandManager->id,
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
            'coordinator_id' => $this->coordinator->id,
        ]);

        // 1. Initial Creation: Writer creates Deliverable in Writer stage
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Social Awareness Campaign Post',
            'approval_stage' => 'Writer',
            'status' => 'To Do',
            'post_type' => 'Static Post',
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
            'further_approver_id' => $this->furtherApprover->id,
            'brand_manager_id' => $this->brandManager->id,
            'coordinator_id' => $this->coordinator->id,
            'designer_id' => $this->designer->id,
        ]);

        $this->assertEquals('Writer', $deliverable->approval_stage);
        $this->assertEquals(0, $deliverable->getStageProgress());

        // Authorization check: Designer cannot submit Writer stage
        $unauthResp = $this->actingAs($this->designer)->post(route('deliverables.submit', $deliverable), [
            'approver_id' => $this->approver->id,
        ]);
        $unauthResp->assertSessionHas('error');
        $this->assertEquals('Writer', $deliverable->fresh()->approval_stage);

        // STAGE 1: Writer submits to Approver
        $s1 = $this->actingAs($this->writer)->post(route('deliverables.submit', $deliverable), [
            'approver_id' => $this->approver->id,
            'post_copy' => 'Save smarter with our high-yield account.',
            'caption' => 'Secure your financial future today.',
        ]);
        $this->assertTrue(in_array($s1->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Approver', $deliverable->approval_stage);
        $this->assertEquals(9, $deliverable->getStageProgress());

        // STAGE 2: Approver assigns Further Approver and submits to Further Approver
        $s2 = $this->actingAs($this->approver)->post(route('deliverables.submit', $deliverable), [
            'further_approver_id' => $this->furtherApprover->id,
        ]);
        $this->assertTrue(in_array($s2->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Further Approver', $deliverable->approval_stage);
        $this->assertEquals(18, $deliverable->getStageProgress());

        // STAGE 3: Further Approver submits to Brand Manager
        $s3 = $this->actingAs($this->furtherApprover)->post(route('deliverables.submit', $deliverable), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($s3->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Brand Manager', $deliverable->approval_stage);
        $this->assertEquals(27, $deliverable->getStageProgress());

        // STAGE 4: Brand Manager submits to Coordinator
        $s4 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $deliverable), [
            'coordinator_id' => $this->coordinator->id,
        ]);
        $this->assertTrue(in_array($s4->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Coordinator', $deliverable->approval_stage);
        $this->assertEquals(36, $deliverable->getStageProgress());

        // STAGE 5: Coordinator assigns Designer and submits to Designer stage
        $s5 = $this->actingAs($this->coordinator)->post(route('deliverables.submit', $deliverable), [
            'designer_id' => $this->designer->id,
        ]);
        $this->assertTrue(in_array($s5->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Designer', $deliverable->approval_stage);
        $this->assertEquals(45, $deliverable->getStageProgress());

        // STAGE 6 (Gate): Designer cannot submit without artwork
        $gateResp = $this->actingAs($this->designer)->post(route('deliverables.submit', $deliverable), [
            'writer_id' => $this->writer->id,
        ]);
        $gateResp->assertSessionHas('error');
        $this->assertEquals('Designer', $deliverable->fresh()->approval_stage);

        // STAGE 6 (Valid): Designer uploads artwork and submits to Writer Review
        $artworkFile = UploadedFile::fake()->image('post_artwork.png', 1080, 1080);
        $s6 = $this->actingAs($this->designer)->post(route('deliverables.submit', $deliverable), [
            'final_designs_file' => $artworkFile,
            'writer_id' => $this->writer->id,
            'work_hours' => 2.5,
        ]);
        $this->assertTrue(in_array($s6->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Writer Review', $deliverable->approval_stage);
        $this->assertEquals(54, $deliverable->getStageProgress());
        $this->assertNotNull($deliverable->final_designs);

        // STAGE 7: Writer Review: Writer reviews artwork and submits to Approver Review
        $s7 = $this->actingAs($this->writer)->post(route('deliverables.submit', $deliverable), [
            'approver_id' => $this->approver->id,
        ]);
        $this->assertTrue(in_array($s7->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Approver Review', $deliverable->approval_stage);
        $this->assertEquals(63, $deliverable->getStageProgress());

        // STAGE 8: Approver Review: Approver reviews artwork and submits to Final Approval
        $s8 = $this->actingAs($this->approver)->post(route('deliverables.submit', $deliverable), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($s8->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Final Approval', $deliverable->approval_stage);
        $this->assertEquals(72, $deliverable->getStageProgress());

        // STAGE 9: Final Approval: Brand Manager submits to Scheduled
        $s9 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $deliverable), [
            'writer_id' => $this->writer->id,
        ]);
        $this->assertTrue(in_array($s9->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Scheduled', $deliverable->approval_stage);
        $this->assertEquals(81, $deliverable->getStageProgress());

        // STAGE 10: Scheduled: Writer/Brand Manager marks as Closed
        $s10 = $this->actingAs($this->writer)->post(route('deliverables.submit', $deliverable), []);
        $this->assertTrue(in_array($s9->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Closed', $deliverable->approval_stage);
        $this->assertEquals(100, $deliverable->getStageProgress());
        $this->assertEquals('Done', $deliverable->status);
    }

    /**
     * FLOW 2: Retainer Direct Design / Fast Track Flow (flow_type = 'direct_design')
     * Coordinator -> Designer -> Writer Review -> Approver Review -> Further Approver -> Final Approval -> Closed
     */
    public function test_retainer_direct_design_flow_using_relevant_users(): void
    {
        $project = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Fast Track Deliverables Project',
            'workflow_type' => 'retainer',
            'brand_manager_id' => $this->brandManager->id,
            'coordinator_id' => $this->coordinator->id,
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Emergency Promo Banner',
            'flow_type' => 'direct_design',
            'approval_stage' => 'Coordinator',
            'status' => 'To Do',
            'coordinator_id' => $this->coordinator->id,
            'designer_id' => $this->designer->id,
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
            'further_approver_id' => $this->furtherApprover->id,
            'brand_manager_id' => $this->brandManager->id,
        ]);

        $this->assertTrue($deliverable->isDirectDesign());
        $this->assertEquals('Coordinator', $deliverable->approval_stage);
        $this->assertEquals(10, $deliverable->getStageProgress());

        // Step 1: Coordinator assigns designer and advances to Designer stage
        $r1 = $this->actingAs($this->coordinator)->post(route('deliverables.submit', $deliverable), [
            'designer_id' => $this->designer->id,
        ]);
        $this->assertTrue(in_array($r1->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Designer', $deliverable->approval_stage);
        $this->assertEquals(25, $deliverable->getStageProgress());

        // Step 2: Designer uploads artwork and advances to Writer Review
        $art = UploadedFile::fake()->image('promo_banner.png', 800, 600);
        $r2 = $this->actingAs($this->designer)->post(route('deliverables.submit', $deliverable), [
            'final_designs_file' => $art,
            'writer_id' => $this->writer->id,
            'work_hours' => 1.5,
        ]);
        $this->assertTrue(in_array($r2->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Writer Review', $deliverable->approval_stage);
        $this->assertEquals(45, $deliverable->getStageProgress());

        // Step 3: Writer Review -> advances to Approver Review
        $r3 = $this->actingAs($this->writer)->post(route('deliverables.submit', $deliverable), [
            'approver_id' => $this->approver->id,
        ]);
        $this->assertTrue(in_array($r3->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Approver Review', $deliverable->approval_stage);
        $this->assertEquals(65, $deliverable->getStageProgress());

        // Step 4: Approver Review -> advances to Further Approver
        $r4 = $this->actingAs($this->approver)->post(route('deliverables.submit', $deliverable), [
            'further_approver_id' => $this->furtherApprover->id,
        ]);
        $this->assertTrue(in_array($r4->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Further Approver', $deliverable->approval_stage);
        $this->assertEquals(75, $deliverable->getStageProgress());

        // Step 5: Further Approver -> advances to Final Approval
        $r5 = $this->actingAs($this->furtherApprover)->post(route('deliverables.submit', $deliverable), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r5->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Final Approval', $deliverable->approval_stage);
        $this->assertEquals(85, $deliverable->getStageProgress());

        // Step 6: Brand Manager approves and closes
        $r6 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $deliverable), []);
        $this->assertTrue(in_array($r6->status(), [200, 302]));
        $deliverable->refresh();
        $this->assertEquals('Closed', $deliverable->approval_stage);
        $this->assertEquals(100, $deliverable->getStageProgress());
        $this->assertEquals('Done', $deliverable->status);
    }

    /**
     * FLOW 3: Campaign Outlines Flow (post_type = 'Outlines')
     * Coordinator -> Designer -> Writer Review -> Approver Review -> AM/BD -> Final Approval -> Closed
     */
    public function test_campaign_outlines_flow_using_relevant_users(): void
    {
        $campaignProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Q4 Holiday Campaign',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $this->brandManager->id,
            'approver_id' => $this->approver->id,
            'coordinator_id' => $this->coordinator->id,
            'writer_id' => $this->writer->id,
        ]);

        $outline = Deliverable::create([
            'project_id' => $campaignProject->id,
            'title' => 'Holiday Teaser Reel Outlines',
            'post_type' => 'Outlines',
            'approval_stage' => 'Coordinator',
            'status' => 'To Do',
            'coordinator_id' => $this->coordinator->id,
            'designer_id' => $this->designer->id,
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
            'brand_manager_id' => $this->brandManager->id,
        ]);

        $this->assertEquals('Coordinator', $outline->approval_stage);
        $this->assertEquals(40, $outline->getStageProgress());

        // Step 1: Coordinator assigns designer and advances to Designer stage
        $r1 = $this->actingAs($this->coordinator)->post(route('deliverables.submit', $outline), [
            'designer_id' => $this->designer->id,
        ]);
        $this->assertTrue(in_array($r1->status(), [200, 302]));
        $outline->refresh();
        $this->assertEquals('Designer', $outline->approval_stage);
        $this->assertEquals(50, $outline->getStageProgress());

        // Step 2: Designer uploads artwork and advances to Writer Review
        $reelArtwork = UploadedFile::fake()->image('reel_cover.jpg', 1080, 1920);
        $r2 = $this->actingAs($this->designer)->post(route('deliverables.submit', $outline), [
            'final_designs_file' => $reelArtwork,
            'writer_id' => $this->writer->id,
            'work_hours' => 3.0,
        ]);
        $this->assertTrue(in_array($r2->status(), [200, 302]));
        $outline->refresh();
        $this->assertEquals('Writer Review', $outline->approval_stage);
        $this->assertEquals(60, $outline->getStageProgress());

        // Step 3: Writer Review -> advances to Approver Review
        $r3 = $this->actingAs($this->writer)->post(route('deliverables.submit', $outline), [
            'approver_id' => $this->approver->id,
        ]);
        $this->assertTrue(in_array($r3->status(), [200, 302]));
        $outline->refresh();
        $this->assertEquals('Approver Review', $outline->approval_stage);
        $this->assertEquals(70, $outline->getStageProgress());

        // Step 4: Approver Review -> advances to AM/BD
        $r4 = $this->actingAs($this->approver)->post(route('deliverables.submit', $outline), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r4->status(), [200, 302]));
        $outline->refresh();
        $this->assertEquals('AM/BD', $outline->approval_stage);
        $this->assertEquals(80, $outline->getStageProgress());

        // Step 5: AM/BD -> advances to Final Approval
        $r5 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $outline), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r5->status(), [200, 302]));
        $outline->refresh();
        $this->assertEquals('Final Approval', $outline->approval_stage);
        $this->assertEquals(90, $outline->getStageProgress());

        // Step 6: Final Approval -> Closed
        $r6 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $outline), []);
        $this->assertTrue(in_array($r6->status(), [200, 302]));
        $outline->refresh();
        $this->assertEquals('Closed', $outline->approval_stage);
        $this->assertEquals(100, $outline->getStageProgress());
        $this->assertEquals('Done', $outline->status);
    }

    /**
     * FLOW 4: Campaign Lean Other Deliverables Flow (non-outlines: Static Post, Carousel, etc.)
     * Assign (10%) -> Approve (50%) -> Close (100%)
     */
    public function test_campaign_other_deliverables_lean_flow_using_relevant_users(): void
    {
        $campaignProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Influencer Marketing Campaign',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $this->brandManager->id,
            'writer_id' => $this->assignee->id,
        ]);

        $other = Deliverable::create([
            'project_id' => $campaignProject->id,
            'title' => 'Influencer Briefing Doc',
            'post_type' => 'Brief',
            'approval_stage' => 'Assign',
            'status' => 'To Do',
            'writer_id' => $this->assignee->id,
            'brand_manager_id' => $this->brandManager->id,
        ]);

        $this->assertEquals('Assign', $other->approval_stage);
        $this->assertEquals(10, $other->getStageProgress());

        // Authorization test: Unrelated designer cannot submit Assign stage
        $unauthResp = $this->actingAs($this->designer)->post(route('deliverables.submit', $other), []);
        $unauthResp->assertSessionHas('error');
        $this->assertEquals('Assign', $other->fresh()->approval_stage);

        // Step 1: Assignee submits work -> moves to Approve stage
        $r1 = $this->actingAs($this->assignee)->post(route('deliverables.submit', $other), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r1->status(), [200, 302]));
        $other->refresh();
        $this->assertEquals('Approve', $other->approval_stage);
        $this->assertEquals(50, $other->getStageProgress());

        // Step 2: Brand Manager approves -> moves to Close stage
        $r2 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $other), []);
        $this->assertTrue(in_array($r2->status(), [200, 302]));
        $other->refresh();
        $this->assertEquals('Close', $other->approval_stage);
        $this->assertEquals(100, $other->getStageProgress());
        $this->assertEquals('Done', $other->status);
    }

    /**
     * FLOW 5: Pitch Outlines Flow (post_type = 'Outlines' in Pitch workflow)
     */
    public function test_pitch_outlines_flow_using_relevant_users(): void
    {
        $pitchProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Automotive Brand Pitch 2026',
            'workflow_type' => 'pitch',
            'brand_manager_id' => $this->brandManager->id,
            'coordinator_id' => $this->coordinator->id,
            'approver_id' => $this->approver->id,
            'writer_id' => $this->writer->id,
        ]);

        $pitchOutline = Deliverable::create([
            'project_id' => $pitchProject->id,
            'title' => 'Pitch Video Storyboard Outlines',
            'post_type' => 'Outlines',
            'approval_stage' => 'Coordinator',
            'status' => 'To Do',
            'coordinator_id' => $this->coordinator->id,
            'designer_id' => $this->designer->id,
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
            'brand_manager_id' => $this->brandManager->id,
        ]);

        $this->assertEquals('Coordinator', $pitchOutline->approval_stage);
        $this->assertEquals(40, $pitchOutline->getStageProgress());

        // Coordinator assigns Designer
        $r1 = $this->actingAs($this->coordinator)->post(route('deliverables.submit', $pitchOutline), [
            'designer_id' => $this->designer->id,
        ]);
        $this->assertTrue(in_array($r1->status(), [200, 302]));
        $pitchOutline->refresh();
        $this->assertEquals('Designer', $pitchOutline->approval_stage);
        $this->assertEquals(50, $pitchOutline->getStageProgress());

        // Designer uploads artwork and advances to Writer Review
        $storyboard = UploadedFile::fake()->image('storyboard.png', 1920, 1080);
        $r2 = $this->actingAs($this->designer)->post(route('deliverables.submit', $pitchOutline), [
            'final_designs_file' => $storyboard,
            'writer_id' => $this->writer->id,
            'work_hours' => 4.0,
        ]);
        $this->assertTrue(in_array($r2->status(), [200, 302]));
        $pitchOutline->refresh();
        $this->assertEquals('Writer Review', $pitchOutline->approval_stage);
        $this->assertEquals(60, $pitchOutline->getStageProgress());

        // Writer Review -> Approver Review
        $r3 = $this->actingAs($this->writer)->post(route('deliverables.submit', $pitchOutline), [
            'approver_id' => $this->approver->id,
        ]);
        $this->assertTrue(in_array($r3->status(), [200, 302]));
        $pitchOutline->refresh();
        $this->assertEquals('Approver Review', $pitchOutline->approval_stage);
        $this->assertEquals(70, $pitchOutline->getStageProgress());

        // Approver Review -> AM/BD
        $r4 = $this->actingAs($this->approver)->post(route('deliverables.submit', $pitchOutline), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r4->status(), [200, 302]));
        $pitchOutline->refresh();
        $this->assertEquals('AM/BD', $pitchOutline->approval_stage);
        $this->assertEquals(80, $pitchOutline->getStageProgress());

        // AM/BD -> Final Approval
        $r5 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $pitchOutline), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r5->status(), [200, 302]));
        $pitchOutline->refresh();
        $this->assertEquals('Final Approval', $pitchOutline->approval_stage);
        $this->assertEquals(90, $pitchOutline->getStageProgress());

        // Final Approval -> Closed
        $r6 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $pitchOutline), []);
        $this->assertTrue(in_array($r6->status(), [200, 302]));
        $pitchOutline->refresh();
        $this->assertEquals('Closed', $pitchOutline->approval_stage);
        $this->assertEquals(100, $pitchOutline->getStageProgress());
    }

    /**
     * FLOW 6: Pitch Lean Other Deliverables Flow (Pitch Deck / Strategy)
     * Assign -> Approve -> Close
     */
    public function test_pitch_other_deliverables_lean_flow_using_relevant_users(): void
    {
        $pitchProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'FMCG Pitch Deck',
            'workflow_type' => 'pitch',
            'brand_manager_id' => $this->brandManager->id,
            'writer_id' => $this->assignee->id,
        ]);

        $pitchDeck = Deliverable::create([
            'project_id' => $pitchProject->id,
            'title' => 'Master Pitch Presentation Deck',
            'post_type' => 'Pitch Deck',
            'approval_stage' => 'Assign',
            'status' => 'To Do',
            'writer_id' => $this->assignee->id,
            'brand_manager_id' => $this->brandManager->id,
        ]);

        $this->assertEquals('Assign', $pitchDeck->approval_stage);
        $this->assertEquals(10, $pitchDeck->getStageProgress());

        // Assignee submits
        $r1 = $this->actingAs($this->assignee)->post(route('deliverables.submit', $pitchDeck), [
            'brand_manager_id' => $this->brandManager->id,
        ]);
        $this->assertTrue(in_array($r1->status(), [200, 302]));
        $pitchDeck->refresh();
        $this->assertEquals('Approve', $pitchDeck->approval_stage);
        $this->assertEquals(50, $pitchDeck->getStageProgress());

        // Brand Manager approves
        $r2 = $this->actingAs($this->brandManager)->post(route('deliverables.submit', $pitchDeck), []);
        $this->assertTrue(in_array($r2->status(), [200, 302]));
        $pitchDeck->refresh();
        $this->assertEquals('Close', $pitchDeck->approval_stage);
        $this->assertEquals(100, $pitchDeck->getStageProgress());
        $this->assertEquals('Done', $pitchDeck->status);
    }

    /**
     * FLOW 7: Revision Loops Across Different Flow Types
     */
    public function test_revision_cycles_in_retainer_and_direct_design_and_campaign_flows(): void
    {
        $retainerProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Retainer for Revisions',
            'workflow_type' => 'retainer',
            'brand_manager_id' => $this->brandManager->id,
        ]);

        // Retainer Standard Revision: Approver sends back to Writer
        $standardDeliverable = Deliverable::create([
            'project_id' => $retainerProject->id,
            'title' => 'Copy for Approval',
            'approval_stage' => 'Approver',
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
            'brand_manager_id' => $this->brandManager->id,
            'revisions' => 0,
        ]);

        $revResp = $this->actingAs($this->approver)->post(route('deliverables.revisions', $standardDeliverable), [
            'revision_instructions' => 'Tone should be more engaging and action-oriented.',
            'revision_target' => 'writer',
        ]);
        $this->assertTrue(in_array($revResp->status(), [200, 302]));
        $standardDeliverable->refresh();
        $this->assertEquals('Writer', $standardDeliverable->approval_stage);
        $this->assertEquals(1, $standardDeliverable->revisions);

        // Direct Design Revision: Approver sends back to Designer
        $directDesignDeliverable = Deliverable::create([
            'project_id' => $retainerProject->id,
            'title' => 'Design for Review',
            'flow_type' => 'direct_design',
            'approval_stage' => 'Approver Review',
            'designer_id' => $this->designer->id,
            'approver_id' => $this->approver->id,
            'brand_manager_id' => $this->brandManager->id,
            'revisions' => 0,
        ]);

        $ddRevResp = $this->actingAs($this->approver)->post(route('deliverables.revisions', $directDesignDeliverable), [
            'revision_instructions' => 'Change background gradient to match brand navy.',
            'revision_target' => 'designer',
        ]);
        $this->assertTrue(in_array($ddRevResp->status(), [200, 302]));
        $directDesignDeliverable->refresh();
        $this->assertEquals('Designer', $directDesignDeliverable->approval_stage);
        $this->assertEquals(1, $directDesignDeliverable->revisions);

        // Campaign Lean Revision: Brand Manager sends back to Assignee
        $campaignProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Campaign for Revisions',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $this->brandManager->id,
        ]);

        $campaignDeliverable = Deliverable::create([
            'project_id' => $campaignProject->id,
            'title' => 'Campaign Strategy Proposal',
            'post_type' => 'Strategy Deck',
            'approval_stage' => 'Approve',
            'writer_id' => $this->assignee->id,
            'brand_manager_id' => $this->brandManager->id,
            'revisions' => 0,
        ]);

        $cRevResp = $this->actingAs($this->brandManager)->post(route('deliverables.revisions', $campaignDeliverable), [
            'revision_instructions' => 'Include competitive analysis in appendix.',
        ]);
        $this->assertTrue(in_array($cRevResp->status(), [200, 302]));
        $campaignDeliverable->refresh();
        $this->assertEquals('Assign', $campaignDeliverable->approval_stage);
        $this->assertEquals(1, $campaignDeliverable->revisions);
    }
}
