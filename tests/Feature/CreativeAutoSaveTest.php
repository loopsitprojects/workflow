<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreativeAutoSaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_saving_concept_copy_caption_persists_content(): void
    {
        $writer = User::factory()->create(['role' => 'Writer', 'name' => 'Test Writer']);
        $brand = Brand::create(['name' => 'AutoSave Brand', 'slug' => 'autosave-brand']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'AutoSave Project',
            'workflow_type' => 'campaign',
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Initial Title',
            'approval_stage' => 'Writer',
            'writer_id' => $writer->id,
            'concept' => 'Initial Concept',
            'post_copy' => 'Initial Copy',
            'caption' => 'Initial Caption',
        ]);

        $response = $this->actingAs($writer)->postJson(route('deliverables.submit', $deliverable), [
            'action' => 'save_only',
            'concept' => '<p>Updated Concept via auto-save</p>',
            'post_copy' => '<p>Updated Copy via auto-save</p>',
            'caption' => '<p>Updated Caption via auto-save</p>',
            'title' => 'Updated Title via auto-save',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Deliverable content saved successfully.',
        ]);

        $fresh = $deliverable->fresh();
        $this->assertEquals('<p>Updated Concept via auto-save</p>', $fresh->concept);
        $this->assertEquals('<p>Updated Copy via auto-save</p>', $fresh->post_copy);
        $this->assertEquals('<p>Updated Caption via auto-save</p>', $fresh->caption);
        $this->assertEquals('Updated Title via auto-save', $fresh->title);
    }

    public function test_view_has_autosave_indicator_and_hidden_save_button(): void
    {
        $writer = User::factory()->create(['role' => 'Writer', 'name' => 'Test Writer']);
        $brand = Brand::create(['name' => 'AutoSave Brand', 'slug' => 'autosave-brand']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'AutoSave Project',
            'workflow_type' => 'campaign',
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'View Test Deliverable',
            'approval_stage' => 'Writer',
            'writer_id' => $writer->id,
        ]);

        $resShow = $this->actingAs($writer)->get(route('deliverables.show', $deliverable));
        $resShow->assertOk();
        $resShow->assertSee('id="creativeAutoSaveStatus"', false);
        $resShow->assertSee('triggerAutoSaveCreativeContent', false);
        $resShow->assertSee('display:none !important;">Save Content<', false);

        $resProject = $this->actingAs($writer)->get(route('projects.show', $project));
        $resProject->assertOk();
        $resProject->assertSee('id="creativeAutoSaveStatus"', false);
        $resProject->assertSee('triggerAutoSaveCreativeContent', false);
        $resProject->assertSee('display:none !important;">Save Content<', false);
    }
}
