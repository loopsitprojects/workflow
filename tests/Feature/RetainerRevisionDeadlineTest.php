<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetainerRevisionDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_can_request_revision_with_deadline_in_retainer_workflow()
    {
        $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
        $writer = User::factory()->create(['role' => 'Writer']);
        $designer = User::factory()->create(['role' => 'Designer']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Retainer Project',
            'workflow_type' => 'retainer',
            'writer_id' => $writer->id,
            'designer_id' => $designer->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Carousel 1',
            'approval_stage' => 'Writer Review',
            'status' => 'To Do',
            'writer_id' => $writer->id,
            'designer_id' => $designer->id,
            'deadline' => '2026-10-29 00:00:00',
        ]);

        $this->actingAs($writer);

        $response = $this->post(route('deliverables.revisions', $deliverable), [
            'revision_instructions' => 'Change colors and layout',
            'revision_target' => 'designer',
            'revision_deadline' => '2026-10-27',
        ]);

        $response->assertSessionHasNoErrors();
        $deliverable->refresh();

        $this->assertEquals('Designer', $deliverable->approval_stage);
        $this->assertEquals('2026-10-27 00:00:00', $deliverable->revision_deadline->format('Y-m-d H:i:s'));
        $this->assertEquals(1, $deliverable->revisions);
        $this->assertEquals('designer', $deliverable->revision_target);

        $latestRev = $deliverable->revisionsHistory()->latest()->first();
        $this->assertNotNull($latestRev);
        $this->assertEquals('2026-10-27 00:00:00', $latestRev->revision_deadline->format('Y-m-d H:i:s'));
        $this->assertEquals('designer', $latestRev->target);
        $this->assertEquals('designer', $latestRev->target_role);
    }

    public function test_writer_can_update_revision_deadline_endpoint()
    {
        $brand = Brand::create(['name' => 'Brand Test 2', 'slug' => 'brand-test-2']);
        $writer = User::factory()->create(['role' => 'Writer']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Retainer Project 2',
            'workflow_type' => 'retainer',
            'writer_id' => $writer->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Post 1',
            'approval_stage' => 'Designer',
            'status' => 'To Do',
            'writer_id' => $writer->id,
        ]);

        $this->actingAs($writer);

        $response = $this->postJson(route('deliverables.update-revision-deadline', $deliverable), [
            'revision_deadline' => '2026-10-28',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $deliverable->refresh();
        $this->assertEquals('2026-10-28 00:00:00', $deliverable->revision_deadline->format('Y-m-d H:i:s'));
    }

    public function test_revision_deadline_renders_in_project_show_table()
    {
        $brand = Brand::create(['name' => 'Brand Test 3', 'slug' => 'brand-test-3']);
        $writer = User::factory()->create(['role' => 'Writer']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Retainer Project 3',
            'workflow_type' => 'retainer',
            'writer_id' => $writer->id,
        ]);

        $parent = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Batch 1',
            'approval_stage' => 'Designer',
            'status' => 'To Do',
            'deadline' => '2026-10-29 00:00:00',
        ]);

        $subtask = Deliverable::create([
            'project_id' => $project->id,
            'parent_deliverable_id' => $parent->id,
            'title' => 'Carousel 1',
            'approval_stage' => 'Designer',
            'status' => 'To Do',
            'deadline' => '2026-10-29 00:00:00',
            'revision_deadline' => '2026-10-27 00:00:00',
            'revision_instructions' => 'Fix artwork',
            'revisions' => 1,
        ]);

        $this->actingAs($writer);

        $response = $this->get(route('projects.show', $project));
        $response->assertOk();
        $response->assertSee('Rev: Oct 27, 2026');
    }

    public function test_revision_targeting_writer_persists_target_and_shows_in_table()
    {
        $brand = Brand::create(['name' => 'Brand Test 4', 'slug' => 'brand-test-4']);
        $writer = User::factory()->create(['role' => 'Writer']);
        $approver = User::factory()->create(['role' => 'Approver']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Retainer Project 4',
            'workflow_type' => 'retainer',
            'writer_id' => $writer->id,
            'approver_id' => $approver->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Carousel 2',
            'approval_stage' => 'Approver Review',
            'status' => 'To Do',
            'writer_id' => $writer->id,
            'approver_id' => $approver->id,
            'deadline' => '2026-10-29 00:00:00',
        ]);

        $this->actingAs($approver);

        $response = $this->post(route('deliverables.revisions', $deliverable), [
            'revision_instructions' => 'Update headline hook',
            'revision_target' => 'writer',
        ]);

        $response->assertSessionHasNoErrors();
        $deliverable->refresh();

        $this->assertEquals('Writer Review', $deliverable->approval_stage);
        $this->assertEquals('writer', $deliverable->revision_target);

        $latestRev = $deliverable->revisionsHistory()->latest()->first();
        $this->assertNotNull($latestRev);
        $this->assertEquals('writer', $latestRev->target);
        $this->assertEquals('writer', $latestRev->target_role);

        $pageResponse = $this->get(route('projects.show', $project));
        $pageResponse->assertOk();
        $pageResponse->assertSee('Revision Requested (Writer)');
    }
}
