<?php

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use App\Models\Deliverable;
use App\Services\Workflows\RetainerWorkflowService;
use App\Services\Workflows\CampaignWorkflowService;
use App\Services\Workflows\OtherDeliverableWorkflowService;
use App\Services\Workflows\WorkflowManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('deliverable correctly resolves isolated workflow strategy based on project workflow_type', function () {
    $brand = Brand::create(['name' => 'Brand A', 'slug' => 'brand-a']);
    
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

    $pitchProject = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Pitch Project',
        'workflow_type' => 'pitch',
    ]);

    $retainerTask = Deliverable::create([
        'project_id' => $retainerProject->id,
        'title' => 'Retainer Task',
        'approval_stage' => 'Writer',
    ]);

    $campaignOutlineTask = Deliverable::create([
        'project_id' => $campaignProject->id,
        'title' => 'Campaign Outline Task',
        'post_type' => 'Outlines',
        'approval_stage' => 'Assignee',
    ]);

    $campaignOtherTask = Deliverable::create([
        'project_id' => $campaignProject->id,
        'title' => 'Campaign Other Task',
        'post_type' => 'Reels',
        'approval_stage' => 'Assign',
    ]);

    $pitchTask = Deliverable::create([
        'project_id' => $pitchProject->id,
        'title' => 'Pitch Task',
        'post_type' => 'Outlines',
        'approval_stage' => 'Assignee',
    ]);

    expect($retainerTask->getWorkflow())->toBeInstanceOf(RetainerWorkflowService::class)
        ->and($campaignOutlineTask->getWorkflow())->toBeInstanceOf(CampaignWorkflowService::class)
        ->and($campaignOtherTask->getWorkflow())->toBeInstanceOf(OtherDeliverableWorkflowService::class)
        ->and($pitchTask->getWorkflow())->toBeInstanceOf(CampaignWorkflowService::class);

    // Verify stage isolation
    expect($retainerTask->getStages())->toBe(RetainerWorkflowService::STAGES)
        ->and(count($retainerTask->getStages()))->toBe(11)
        ->and($campaignOutlineTask->getStages())->toBe(CampaignWorkflowService::STAGES)
        ->and(count($campaignOutlineTask->getStages()))->toBe(11)
        ->and($campaignOtherTask->getStages())->toBe(OtherDeliverableWorkflowService::STAGES)
        ->and(count($campaignOtherTask->getStages()))->toBe(3);
});

test('retainer workflow accurately calculates 11-stage progress milestones', function () {
    $brand = Brand::create(['name' => 'Brand B', 'slug' => 'brand-b']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Retainer Project',
        'workflow_type' => 'retainer',
    ]);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Retainer Milestone Test',
        'approval_stage' => 'Writer',
    ]);

    expect($deliverable->getStageProgress())->toBe(0);

    $deliverable->approval_stage = 'Approver';
    expect($deliverable->getStageProgress())->toBe(9);

    $deliverable->approval_stage = 'Designer';
    expect($deliverable->getStageProgress())->toBe(45);

    $deliverable->approval_stage = 'Closed';
    expect($deliverable->getStageProgress())->toBe(100);
});

test('campaign outline workflow accurately calculates 11-stage progress milestones', function () {
    $brand = Brand::create(['name' => 'Brand C', 'slug' => 'brand-c']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Project',
        'workflow_type' => 'campaign',
    ]);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Campaign Milestone Test',
        'post_type' => 'Outlines',
        'approval_stage' => 'Writer',
    ]);

    expect($deliverable->getStageProgress())->toBe(0);

    $deliverable->approval_stage = 'Approver';
    expect($deliverable->getStageProgress())->toBe(10);

    $deliverable->approval_stage = 'Designer';
    expect($deliverable->getStageProgress())->toBe(50);

    $deliverable->approval_stage = 'AM/BD';
    expect($deliverable->getStageProgress())->toBe(80);

    $deliverable->approval_stage = 'Final Approval';
    expect($deliverable->getStageProgress())->toBe(90);

    $deliverable->approval_stage = 'Closed';
    expect($deliverable->getStageProgress())->toBe(100);
});

test('other deliverables workflow accurately calculates 3-stage progress milestones', function () {
    $brand = Brand::create(['name' => 'Brand C2', 'slug' => 'brand-c2']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Campaign Project',
        'workflow_type' => 'campaign',
    ]);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Other Deliverable Milestone Test',
        'post_type' => 'Radio script',
        'approval_stage' => 'Assign',
    ]);

    expect($deliverable->getStageProgress())->toBe(10);

    $deliverable->approval_stage = 'Approve';
    expect($deliverable->getStageProgress())->toBe(50);

    $deliverable->approval_stage = 'Close';
    expect($deliverable->getStageProgress())->toBe(100);
});

test('retainer workflow handles Further Approver routing independently', function () {
    $brand = Brand::create(['name' => 'Brand D', 'slug' => 'brand-d']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Retainer FA Test',
        'workflow_type' => 'retainer',
    ]);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'FA Task',
        'approval_stage' => 'Approver',
    ]);

    $service = new RetainerWorkflowService();

    // When further approver is selected, route to 'Further Approver'
    $nextWithFA = $service->getNextStage($deliverable, ['further_approver_id' => 99]);
    expect($nextWithFA)->toBe('Further Approver');

    // When further approver is NOT selected, bypass directly to 'Brand Manager'
    $nextWithoutFA = $service->getNextStage($deliverable, []);
    expect($nextWithoutFA)->toBe('Brand Manager');
});

test('retainer workflow revision targeting routes to Designer or Writer as specified', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $designer = User::factory()->create(['role' => 'Designer']);
    $writer = User::factory()->create(['role' => 'Writer']);

    $brand = Brand::create(['name' => 'Brand E', 'slug' => 'brand-e']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Retainer Rev Test',
        'workflow_type' => 'retainer',
    ]);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Revision Target Task',
        'approval_stage' => 'Final Approval',
        'designer_id' => $designer->id,
        'writer_id' => $writer->id,
    ]);

    $service = new RetainerWorkflowService();

    // Test revision targeting designer
    $service->requestRevisions($deliverable, [
        'revision_instructions' => 'Fix color scheme',
        'revision_target' => 'designer',
    ], null, $admin);

    expect($deliverable->fresh()->approval_stage)->toBe('Designer')
        ->and($deliverable->fresh()->revisions)->toBe(1);

    // Now test revision targeting writer
    $deliverable->approval_stage = 'Final Approval';
    $deliverable->save();

    $service->requestRevisions($deliverable, [
        'revision_instructions' => 'Fix headline copy',
        'revision_target' => 'writer',
    ], null, $admin);

    expect($deliverable->fresh()->approval_stage)->toBe('Writer')
        ->and($deliverable->fresh()->revisions)->toBe(2);
});

test('campaign deliverable can be created without top fields and inherits title from first post', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $brand = Brand::create(['name' => 'Brand Campaign', 'slug' => 'brand-campaign']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Q4 Launch Campaign',
        'workflow_type' => 'campaign',
    ]);

    // Submit without top fields (title, writer_id, deadline omitted)
    $response = $this->actingAs($admin)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'subtasks' => [
            [
                'title' => 'Teaser Video Post',
                'post_type' => 'Reels',
                'concept' => 'Exciting teaser',
            ]
        ]
    ]);

    $response->assertRedirect();

    $deliverable = Deliverable::where('project_id', $project->id)->first();
    expect($deliverable)->not->toBeNull()
        ->and($deliverable->title)->toBe('Teaser Video Post')
        ->and($deliverable->approval_stage)->toBe('Assign')
        ->and($deliverable->status)->toBe('To Do');
});

test('campaign deliverable with outlines stores concept, caption, and post copy', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $brand = Brand::create(['name' => 'Brand Outlines', 'slug' => 'brand-outlines']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Outlines Campaign',
        'workflow_type' => 'campaign',
    ]);

    $response = $this->actingAs($admin)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'subtasks' => [
            [
                'title' => 'Outline Post #1',
                'post_type' => 'Outlines',
                'concept' => 'Brand narrative concept',
                'caption' => 'Exciting caption for outline',
                'post_copy' => 'Body copy outline line 1\nline 2',
                'reference' => 'https://example.com/reference',
            ]
        ]
    ]);

    $response->assertRedirect();

    $deliverable = Deliverable::where('project_id', $project->id)->first();
    expect($deliverable)->not->toBeNull()
        ->and($deliverable->title)->toBe('Outline Post #1')
        ->and($deliverable->post_type)->toBe('Outlines')
        ->and($deliverable->approval_stage)->toBe('Writer')
        ->and($deliverable->concept)->toBe('Brand narrative concept')
        ->and($deliverable->caption)->toBe('Exciting caption for outline')
        ->and($deliverable->post_copy)->toContain('Body copy outline')
        ->and($deliverable->reference)->toBe('https://example.com/reference');
});

test('campaign deliverable with non-outlines post type stores brief and reference file', function () {
    \Illuminate\Support\Facades\Storage::fake('public');

    $admin = User::factory()->create(['role' => 'Admin']);
    $brand = Brand::create(['name' => 'Brand NonOutlines', 'slug' => 'brand-non-outlines']);
    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Brief Campaign',
        'workflow_type' => 'campaign',
    ]);

    $file = \Illuminate\Http\UploadedFile::fake()->create('brief_document.pdf', 100);

    $response = $this->actingAs($admin)->post(route('deliverables.store'), [
        'project_id' => $project->id,
        'status' => 'To Do',
        'task_type' => 'Deliverable',
        'progress_percent' => 0,
        'subtasks' => [
            [
                'title' => '30s Radio Script',
                'post_type' => 'Radio script',
                'brief' => 'Full audio script brief with sound effects cues and voiceover lines.',
                'reference_file' => $file,
            ]
        ]
    ]);

    $response->assertRedirect();

    $deliverable = Deliverable::where('project_id', $project->id)->first();
    expect($deliverable)->not->toBeNull()
        ->and($deliverable->title)->toBe('30s Radio Script')
        ->and($deliverable->post_type)->toBe('Radio script')
        ->and($deliverable->concept)->toBe('Full audio script brief with sound effects cues and voiceover lines.')
        ->and($deliverable->reference_file)->not->toBeNull();
});

