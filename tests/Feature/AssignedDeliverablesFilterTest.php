<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignedDeliverablesFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $coordinator;
    protected User $designer;
    protected User $writer;
    protected User $approver;
    protected User $otherUser;
    protected Brand $brand;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coordinator = User::factory()->create(['name' => 'Coord User', 'role' => 'Coordinator']);
        $this->designer = User::factory()->create(['name' => 'Des User', 'role' => 'Designer']);
        $this->writer = User::factory()->create(['name' => 'Writer User', 'role' => 'Writer']);
        $this->approver = User::factory()->create(['name' => 'Approver User', 'role' => 'Approver']);
        $this->otherUser = User::factory()->create(['name' => 'Other User', 'role' => 'Coordinator']);

        $this->brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
        $this->project = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Retainer Project',
            'workflow_type' => 'retainer',
        ]);
    }

    public function test_is_assigned_to_user_detects_role_assignments(): void
    {
        $deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Single Task',
            'approval_stage' => 'Coordinator',
            'coordinator_id' => $this->coordinator->id,
            'designer_id' => $this->designer->id,
            'writer_id' => $this->writer->id,
            'approver_id' => $this->approver->id,
        ]);

        $this->assertTrue($deliverable->isAssignedToUser($this->coordinator));
        $this->assertTrue($deliverable->isAssignedToUser($this->designer));
        $this->assertTrue($deliverable->isAssignedToUser($this->writer));
        $this->assertTrue($deliverable->isAssignedToUser($this->approver));
        $this->assertFalse($deliverable->isAssignedToUser($this->otherUser));
    }

    public function test_parent_batch_is_assigned_if_any_subtask_is_assigned(): void
    {
        $batch = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Batch 1',
            'approval_stage' => 'Writer',
        ]);

        $subtask = Deliverable::create([
            'project_id' => $this->project->id,
            'parent_deliverable_id' => $batch->id,
            'title' => 'Post 1',
            'approval_stage' => 'Designer',
            'designer_id' => $this->designer->id,
        ]);

        // Reload relationships
        $batch->load('subtasks');

        // Parent should be considered assigned to designer because subtask is assigned to designer
        $this->assertTrue($batch->isAssignedToUser($this->designer));
        $this->assertFalse($batch->isAssignedToUser($this->otherUser));
    }

    public function test_project_show_renders_only_assigned_switch_and_data_attributes(): void
    {
        $deliverable1 = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Task for Coordinator',
            'approval_stage' => 'Coordinator',
            'coordinator_id' => $this->coordinator->id,
        ]);

        $deliverable2 = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Task for Other',
            'approval_stage' => 'Coordinator',
            'coordinator_id' => $this->otherUser->id,
        ]);

        // Logged in as Coordinator
        $response = $this->actingAs($this->coordinator)->get(route('projects.show', $this->project));
        $response->assertStatus(200);

        // Check for the "Only Assigned to Me" switch
        $response->assertSee('Only Assigned to Me');
        $response->assertSee('assigned-filter-wrapper');
        $response->assertSee('assigned-filter-switch');

        // Check data-assigned attributes on deliverable rows
        $content = $response->getContent();
        $this->assertStringContainsString('id="deliverable-row-' . $deliverable1->id . '" data-deliverable-id="' . $deliverable1->id . '" data-assigned="1"', $content);
        $this->assertStringContainsString('id="deliverable-row-' . $deliverable2->id . '" data-deliverable-id="' . $deliverable2->id . '" data-assigned="0"', $content);

        // Check that the empty state placeholder exists in the DOM
        $response->assertSee('id="cd-assigned-empty-state"', false);
    }

    public function test_direct_design_section_renders_assigned_filter_and_data_attributes(): void
    {
        $dd1 = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Direct Design for Designer',
            'flow_type' => 'direct_design',
            'approval_stage' => 'Designer',
            'designer_id' => $this->designer->id,
        ]);

        $dd2 = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Direct Design Other',
            'flow_type' => 'direct_design',
            'approval_stage' => 'Designer',
            'designer_id' => $this->otherUser->id,
        ]);

        $response = $this->actingAs($this->designer)->get(route('projects.show', $this->project));
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="deliverable-row-' . $dd1->id . '" data-deliverable-id="' . $dd1->id . '" data-assigned="1"', $content);
        $this->assertStringContainsString('id="deliverable-row-' . $dd2->id . '" data-deliverable-id="' . $dd2->id . '" data-assigned="0"', $content);
        $response->assertSee('id="dd-assigned-empty-state"', false);
    }

    public function test_campaign_project_renders_assigned_filter_and_data_attributes(): void
    {
        $campaignProject = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Campaign Project',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $this->approver->id,
        ]);

        $task1 = Deliverable::create([
            'project_id' => $campaignProject->id,
            'title' => 'Campaign Task 1',
            'approval_stage' => 'Assignee',
            'writer_id' => $this->writer->id,
            'status' => 'To Do',
        ]);

        $task2 = Deliverable::create([
            'project_id' => $campaignProject->id,
            'title' => 'Campaign Task 2',
            'approval_stage' => 'Assignee',
            'writer_id' => $this->otherUser->id,
            'status' => 'To Do',
        ]);

        $response = $this->actingAs($this->writer)->get(route('projects.show', $campaignProject));
        $response->assertStatus(200);

        $response->assertSee('Only Assigned to Me');

        $content = $response->getContent();
        // Check Kanban cards have data-assigned attribute
        $this->assertStringContainsString('id="kanban-card-' . $task1->id . '"' . "\n" . '     data-id="' . $task1->id . '"' . "\n" . '     data-assigned="1"', $content);
        $this->assertStringContainsString('id="kanban-card-' . $task2->id . '"' . "\n" . '     data-id="' . $task2->id . '"' . "\n" . '     data-assigned="0"', $content);
    }
}
