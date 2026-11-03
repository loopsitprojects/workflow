<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\DeliverableReassignment;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliverableReassignmentTest extends TestCase
{
    use RefreshDatabase;

    protected Brand $brand;
    protected Project $project;
    protected Deliverable $deliverable;
    protected User $coordinator;
    protected User $brandManager;
    protected User $operationsManager;
    protected User $admin;
    protected User $writer1;
    protected User $writer2;
    protected User $designer1;
    protected User $designer2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Admin User', 'role' => 'Admin']);
        $this->coordinator = User::factory()->create(['name' => 'Coord User', 'role' => 'Coordinator']);
        $this->brandManager = User::factory()->create(['name' => 'BM User', 'role' => 'Brand Manager']);
        $this->operationsManager = User::factory()->create(['name' => 'Ops User', 'role' => 'Operations Manager']);

        $this->writer1 = User::factory()->create(['name' => 'Writer One', 'role' => 'Writer']);
        $this->writer2 = User::factory()->create(['name' => 'Writer Two', 'role' => 'Writer']);
        $this->designer1 = User::factory()->create(['name' => 'Designer One', 'role' => 'Designer']);
        $this->designer2 = User::factory()->create(['name' => 'Designer Two', 'role' => 'Designer']);

        $this->brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $this->project = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Test Project',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $this->brandManager->id,
            'coordinator_id' => $this->coordinator->id,
        ]);

        $this->deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Test Deliverable',
            'approval_stage' => 'Writer',
            'writer_id' => $this->writer1->id,
            'designer_id' => $this->designer1->id,
        ]);
    }

    public function test_coordinator_can_reassign_designer(): void
    {
        $response = $this->actingAs($this->coordinator)->postJson(
            route('deliverables.reassign-designer', $this->deliverable),
            [
                'designer_id' => $this->designer2->id,
                'designer_deadline' => '2026-12-15',
                'reason' => 'Designer 1 is on leave',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'designer_name' => $this->designer2->name,
        ]);

        $this->assertEquals($this->designer2->id, $this->deliverable->fresh()->designer_id);
        $this->assertEquals('2026-12-15', $this->deliverable->fresh()->designer_deadline->format('Y-m-d'));

        $this->assertDatabaseHas('deliverable_reassignments', [
            'deliverable_id' => $this->deliverable->id,
            'role' => 'designer',
            'from_user_id' => $this->designer1->id,
            'to_user_id' => $this->designer2->id,
            'reassigned_by_user_id' => $this->coordinator->id,
            'reason' => 'Designer 1 is on leave',
        ]);
    }

    public function test_brand_manager_can_reassign_writer(): void
    {
        $response = $this->actingAs($this->brandManager)->postJson(
            route('deliverables.reassign-writer', $this->deliverable),
            [
                'writer_id' => $this->writer2->id,
                'deadline' => '2026-12-10',
                'reason' => 'Workload rebalance',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'writer_name' => $this->writer2->name,
        ]);

        $this->assertEquals($this->writer2->id, $this->deliverable->fresh()->writer_id);

        $this->assertDatabaseHas('deliverable_reassignments', [
            'deliverable_id' => $this->deliverable->id,
            'role' => 'writer',
            'from_user_id' => $this->writer1->id,
            'to_user_id' => $this->writer2->id,
            'reassigned_by_user_id' => $this->brandManager->id,
            'reason' => 'Workload rebalance',
        ]);
    }

    public function test_operations_manager_and_admin_can_reassign(): void
    {
        // Operations Manager
        $resOps = $this->actingAs($this->operationsManager)->postJson(
            route('deliverables.reassign-writer', $this->deliverable),
            ['writer_id' => $this->writer2->id]
        );
        $resOps->assertStatus(200);

        // Admin reassigns designer
        $resAdminDesigner = $this->actingAs($this->admin)->postJson(
            route('deliverables.reassign-designer', $this->deliverable),
            ['designer_id' => $this->designer2->id, 'reason' => 'Admin override']
        );
        $resAdminDesigner->assertStatus(200);
        $this->assertEquals($this->designer2->id, $this->deliverable->fresh()->designer_id);

        // Admin reassigns writer
        $resAdminWriter = $this->actingAs($this->admin)->postJson(
            route('deliverables.reassign-writer', $this->deliverable),
            ['writer_id' => $this->writer1->id, 'reason' => 'Admin reassigned back']
        );
        $resAdminWriter->assertStatus(200);
        $this->assertEquals($this->writer1->id, $this->deliverable->fresh()->writer_id);
    }

    public function test_admin_sees_reassign_controls_in_deliverable_and_project_views(): void
    {
        $resDeliverable = $this->actingAs($this->admin)->get(route('deliverables.show', $this->deliverable));
        $resDeliverable->assertStatus(200);
        $resDeliverable->assertSee('<div id="reassignWriterModalOverlay"', false);
        $resDeliverable->assertSee('<div id="reassignDesignerModalOverlay"', false);
        $resDeliverable->assertSee('<button type="button" id="btnOpenReassignWriterModal"', false);
        $resDeliverable->assertSee('<button type="button" id="btnOpenReassignDesignerModal"', false);

        $resProject = $this->actingAs($this->admin)->get(route('projects.show', $this->project));
        $resProject->assertStatus(200);
        $resProject->assertSee('<div id="reassignWriterModalOverlay"', false);
        $resProject->assertSee('<div id="reassignDesignerModalOverlay"', false);
        $resProject->assertSee('<button type="button" id="btnOpenReassignWriterModal"', false);
        $resProject->assertSee('<button type="button" id="btnOpenReassignDesignerModal"', false);
    }

    public function test_writer_cannot_reassign(): void
    {
        $response = $this->actingAs($this->writer1)->postJson(
            route('deliverables.reassign-writer', $this->deliverable),
            ['writer_id' => $this->writer2->id]
        );

        $response->assertStatus(403);
    }

    public function test_designer_cannot_reassign(): void
    {
        $response = $this->actingAs($this->designer1)->postJson(
            route('deliverables.reassign-designer', $this->deliverable),
            ['designer_id' => $this->designer2->id]
        );

        $response->assertStatus(403);
    }

    public function test_reassigning_same_writer_or_designer_returns_422(): void
    {
        $resWriter = $this->actingAs($this->coordinator)->postJson(
            route('deliverables.reassign-writer', $this->deliverable),
            ['writer_id' => $this->writer1->id]
        );
        $resWriter->assertStatus(422);

        $resDesigner = $this->actingAs($this->coordinator)->postJson(
            route('deliverables.reassign-designer', $this->deliverable),
            ['designer_id' => $this->designer1->id]
        );
        $resDesigner->assertStatus(422);
    }

    public function test_reassign_with_subtasks_updates_children(): void
    {
        $subtask = Deliverable::create([
            'project_id' => $this->project->id,
            'parent_deliverable_id' => $this->deliverable->id,
            'title' => 'Subtask 1',
            'writer_id' => $this->writer1->id,
            'designer_id' => $this->designer1->id,
        ]);

        // Reassign writer on parent with reassign_subtasks = true
        $res = $this->actingAs($this->coordinator)->postJson(
            route('deliverables.reassign-writer', $this->deliverable),
            [
                'writer_id' => $this->writer2->id,
                'reassign_subtasks' => true,
            ]
        );
        $res->assertStatus(200);

        $this->assertEquals($this->writer2->id, $this->deliverable->fresh()->writer_id);
        $this->assertEquals($this->writer2->id, $subtask->fresh()->writer_id);

        // Reassign designer on parent with reassign_subtasks = true
        $res2 = $this->actingAs($this->coordinator)->postJson(
            route('deliverables.reassign-designer', $this->deliverable),
            [
                'designer_id' => $this->designer2->id,
                'reassign_subtasks' => true,
            ]
        );
        $res2->assertStatus(200);

        $this->assertEquals($this->designer2->id, $this->deliverable->fresh()->designer_id);
        $this->assertEquals($this->designer2->id, $subtask->fresh()->designer_id);
    }

    public function test_deliverable_view_shows_reassign_modals_for_coordinator(): void
    {
        $response = $this->actingAs($this->coordinator)->get(route('deliverables.show', $this->deliverable));

        $response->assertStatus(200);
        $response->assertSee('<div id="reassignWriterModalOverlay"', false);
        $response->assertSee('<div id="reassignDesignerModalOverlay"', false);
        $response->assertSee('<button type="button" id="btnOpenReassignWriterModal"', false);
        $response->assertSee('<button type="button" id="btnOpenReassignDesignerModal"', false);
        $response->assertDontSee('Writer Due Date');
        $response->assertDontSee('reassignWriterDeadline');
    }

    public function test_deliverable_view_does_not_show_reassign_modals_for_writer(): void
    {
        $response = $this->actingAs($this->writer1)->get(route('deliverables.show', $this->deliverable));

        $response->assertStatus(200);
        $response->assertDontSee('<div id="reassignWriterModalOverlay"', false);
        $response->assertDontSee('<div id="reassignDesignerModalOverlay"', false);
        $response->assertDontSee('<button type="button" id="btnOpenReassignWriterModal"', false);
        $response->assertDontSee('<button type="button" id="btnOpenReassignDesignerModal"', false);
    }
}
