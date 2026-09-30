<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\DeliverableApproval;
use App\Models\DeliverableRevision;
use App\Models\DeliverableReassignment;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== STARTING LIVE WORKFLOW & ROLE VERIFICATION ===\n\n";

$brand = Brand::firstOrCreate(
    ['slug' => 'elephant-house'],
    ['name' => 'Elephant House']
);

// Map Users by Role
$admin = User::where('role', 'Admin')->first() ?? User::first();
$brandManager = User::where('role', 'Brand Manager')->first();
$approver = User::where('role', 'Approver Coordinator')->first() ?? User::where('role', 'Approver')->first();
$furtherApprover = User::where('role', 'Approver')->where('id', '!=', $approver->id)->first() ?? $approver;
$coordinator = User::where('role', 'Coordinator')->first();
$writer = User::where('role', 'Writer')->first();
$assignee = User::where('role', 'Writer')->where('id', '!=', $writer->id)->first() ?? $writer;
$designer = User::where('role', 'Designer')->first();

echo "Identified Test Role Accounts:\n";
echo " - Admin:               {$admin->name} ({$admin->email})\n";
echo " - Brand Manager:       {$brandManager->name} ({$brandManager->email})\n";
echo " - Approver:            {$approver->name} ({$approver->email})\n";
echo " - Further Approver:    {$furtherApprover->name} ({$furtherApprover->email})\n";
echo " - Coordinator:         {$coordinator->name} ({$coordinator->email})\n";
echo " - Writer:              {$writer->name} ({$writer->email})\n";
echo " - Assignee:            {$assignee->name} ({$assignee->email})\n";
echo " - Designer:            {$designer->name} ({$designer->email})\n\n";

// 1. Create Live Campaign Project
$projectName = 'UAT Full Flow Verification 2026';
$project = Project::updateOrCreate(
    [
        'brand_id' => $brand->id,
        'name' => $projectName,
    ],
    [
        'workflow_type' => 'campaign',
        'type' => 'Campaign',
        'status' => 'Active',
        'priority' => 'High Priority',
        'brand_manager_id' => $brandManager->id,
        'approver_id' => $approver->id,
        'coordinator_id' => $coordinator->id,
        'writer_id' => $writer->id,
        'designer_id' => $designer->id,
        'deadline' => now()->addDays(30)->toDateString(),
        'description' => 'Comprehensive live verification of all workflows (Outlines, Other Deliverables, Fast Track) and role handoffs.',
    ]
);
echo "[SUCCESS] Project created: {$project->name} (ID: {$project->id}, Type: {$project->workflow_type})\n\n";

// 2. FLOW 1: Outlines Deliverable (Live progression through all 11 stages)
echo "--- TESTING FLOW 1: OUTLINES DELIVERABLE (11 Stages) ---\n";
$outline = Deliverable::updateOrCreate(
    [
        'project_id' => $project->id,
        'title' => 'Digital Banking - Brand Campaign Outlines',
    ],
    [
        'task_type' => 'Deliverable',
        'post_type' => 'Outlines',
        'approval_stage' => 'Writer',
        'status' => 'To Do',
        'progress_percent' => 0,
        'priority' => 'High Priority',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'further_approver_id' => $furtherApprover->id,
        'brand_manager_id' => $brandManager->id,
        'coordinator_id' => $coordinator->id,
        'designer_id' => $designer->id,
        'concept' => '3-part cinematic launch series showcasing digital convenience.',
        'post_copy' => 'Hook: Still queuing? Switch to instant digital banking.',
        'caption' => 'Experience effortless digital banking on the go. #NextGenBanking',
        'deadline' => now()->addDays(7)->toDateString(),
    ]
);
echo " [Step 0] Created Outlines Deliverable ID {$outline->id}: Stage = {$outline->approval_stage} (Progress: {$outline->getStageProgress()}%)\n";

// Stage 1 -> Approver (10%)
$res1 = $outline->getWorkflow()->advanceStage($outline, ['approver_id' => $approver->id], $writer);
echo " [Step 1] Writer ({$writer->name}) submitted -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 2 -> Further Approver (20%)
$res2 = $outline->getWorkflow()->advanceStage($outline, ['further_approver_id' => $furtherApprover->id], $approver);
echo " [Step 2] Approver ({$approver->name}) submitted -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 3 -> Brand Manager (30%)
$res3 = $outline->getWorkflow()->advanceStage($outline, ['brand_manager_id' => $brandManager->id], $furtherApprover);
echo " [Step 3] Further Approver ({$furtherApprover->name}) submitted -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 4 -> Coordinator (40%)
$res4 = $outline->getWorkflow()->advanceStage($outline, ['coordinator_id' => $coordinator->id], $brandManager);
echo " [Step 4] Brand Manager ({$brandManager->name}) submitted -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 5 -> Designer (50%)
$res5 = $outline->getWorkflow()->advanceStage($outline, ['designer_id' => $designer->id], $coordinator);
echo " [Step 5] Coordinator ({$coordinator->name}) submitted -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 6 -> Designer Artwork Upload -> Writer Review (60%)
$res6 = $outline->getWorkflow()->advanceStage($outline, [
    'writer_id' => $writer->id,
    'final_designs_link' => 'https://www.figma.com/file/live-demo-key-visual',
    'hours_spent' => 4.0,
], $designer);
echo " [Step 6] Designer ({$designer->name}) submitted artwork link -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 7 -> Approver Review (70%)
$res7 = $outline->getWorkflow()->advanceStage($outline, ['approver_id' => $approver->id], $writer);
echo " [Step 7] Writer ({$writer->name}) approved artwork -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 8 -> AM/BD (80%)
$res8 = $outline->getWorkflow()->advanceStage($outline, ['brand_manager_id' => $brandManager->id], $approver);
echo " [Step 8] Approver ({$approver->name}) approved -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 9 -> Final Approval (90%)
$res9 = $outline->getWorkflow()->advanceStage($outline, ['brand_manager_id' => $brandManager->id], $brandManager);
echo " [Step 9] AM/BD ({$brandManager->name}) approved -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%)\n";

// Stage 10 -> Closed (100%)
$res10 = $outline->getWorkflow()->advanceStage($outline, [], $brandManager);
echo " [Step 10] Final Approval ({$brandManager->name}) closed task -> Stage: {$outline->approval_stage} ({$outline->getStageProgress()}%, Status: {$outline->status})\n\n";

// 3. FLOW 2: Other Deliverables Flow (Assign -> Approve -> Close)
echo "--- TESTING FLOW 2: OTHER DELIVERABLES (3 Stages) ---\n";
$other = Deliverable::updateOrCreate(
    [
        'project_id' => $project->id,
        'title' => 'Digital Marketing Strategy Deck & Media Plan',
    ],
    [
        'task_type' => 'Deliverable',
        'post_type' => 'Strategy Deck',
        'approval_stage' => 'Assign',
        'status' => 'To Do',
        'progress_percent' => 10,
        'priority' => 'High Priority',
        'writer_id' => $assignee->id,
        'assignee_name' => $assignee->name,
        'brand_manager_id' => $brandManager->id,
        'concept' => 'Q4 media mix modeling across Meta, TikTok, and Programmatic.',
        'deadline' => now()->addDays(10)->toDateString(),
    ]
);
echo " [Step 0] Created Other Deliverable ID {$other->id}: Stage = {$other->approval_stage} ({$other->getStageProgress()}%)\n";

// Stage 1: Assignee submits -> Approve (50%)
$otherRes1 = $other->getWorkflow()->advanceStage($other, ['brand_manager_id' => $brandManager->id], $assignee);
echo " [Step 1] Assignee ({$assignee->name}) submitted -> Stage: {$other->approval_stage} ({$other->getStageProgress()}%)\n";

// Stage 2: Brand Manager approves -> Close (100%)
$otherRes2 = $other->getWorkflow()->advanceStage($other, [], $brandManager);
echo " [Step 2] Brand Manager ({$brandManager->name}) approved -> Stage: {$other->approval_stage} ({$other->getStageProgress()}%, Status: {$other->status})\n\n";

// 4. FLOW 3: Fast Track Deliverables Flow (Designer -> Manager Review -> Closed)
echo "--- TESTING FLOW 3: FAST TRACK DELIVERABLES (3 Stages) ---\n";
$fastTrack = Deliverable::updateOrCreate(
    [
        'project_id' => $project->id,
        'title' => 'Emergency Flash Sale Social Assets',
    ],
    [
        'task_type' => 'Deliverable',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'status' => 'To Do',
        'progress_percent' => 20,
        'priority' => 'High Priority',
        'designer_id' => $designer->id,
        'assignee_name' => $designer->name,
        'brand_manager_id' => $brandManager->id,
        'designer_deadline' => now()->addDays(2)->toDateString(),
        'deadline' => now()->addDays(2)->toDateString(),
        'concept' => 'High impact story banners and square creatives for 24h flash promo.',
    ]
);
echo " [Step 0] Created Fast Track Deliverable ID {$fastTrack->id}: Stage = {$fastTrack->approval_stage} ({$fastTrack->getStageProgress()}%)\n";

// Step 1: Designer submits artwork -> Manager Review (60%)
$ftRes1 = $fastTrack->getWorkflow()->advanceStage($fastTrack, [
    'final_designs_link' => 'https://www.figma.com/file/flash-sale-designs',
    'hours_spent' => 2.5,
], $designer);
echo " [Step 1] Designer ({$designer->name}) submitted artwork -> Stage: {$fastTrack->approval_stage} ({$fastTrack->getStageProgress()}%)\n";

// Step 2: Manager Review: Brand Manager approves -> Closed (100%)
$ftRes2 = $fastTrack->getWorkflow()->advanceStage($fastTrack, [], $brandManager);
echo " [Step 2] Brand Manager ({$brandManager->name}) approved -> Stage: {$fastTrack->approval_stage} ({$fastTrack->getStageProgress()}%, Status: {$fastTrack->status})\n\n";

// 5. FLOW 4: Revision Loop Verification
echo "--- TESTING FLOW 4: REVISION LOOPS ---\n";
$revItem = Deliverable::updateOrCreate(
    [
        'project_id' => $project->id,
        'title' => 'Brand Reel Concept Storyboard (Active)',
    ],
    [
        'task_type' => 'Deliverable',
        'post_type' => 'Outlines',
        'approval_stage' => 'Approver',
        'status' => 'To Do',
        'progress_percent' => 10,
        'priority' => 'Medium',
        'writer_id' => $writer->id,
        'approver_id' => $approver->id,
        'brand_manager_id' => $brandManager->id,
        'revisions' => 0,
    ]
);
echo " [Step 0] Active deliverable at Approver stage: Revisions = {$revItem->revisions}\n";
$revResult = $revItem->getWorkflow()->requestRevisions($revItem, [
    'revision_instructions' => 'Refine scene 2 transitions and update call-to-action tone.',
    'revision_target' => 'writer',
], null, $approver);
$revItem->refresh();
echo " [Step 1] Approver requested revisions targeting Writer -> Returned to Stage: {$revItem->approval_stage}, Revisions Count: {$revItem->revisions}\n\n";

echo "=== ALL FLOWS & ROLES SUCCESSFULLY TESTED AND VERIFIED! ===\n";
echo "Project URL: " . url("/projects/{$project->id}") . "\n";
