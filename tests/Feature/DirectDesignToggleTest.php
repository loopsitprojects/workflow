<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DirectDesignToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_admin_can_view_direct_design_toggle_in_settings(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Direct Design Flow');
        $response->assertSee('FEATURE ENABLED');
        $response->assertSee(route('admin.features.toggle-direct-design'));
    }

    public function test_non_admin_cannot_toggle_direct_design(): void
    {
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->post(route('admin.features.toggle-direct-design'));

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_direct_design_on_and_off(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        // Default is enabled
        $this->assertTrue(FeatureManager::isDirectDesignEnabled());

        // Toggle OFF
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-direct-design'));
        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertFalse(FeatureManager::isDirectDesignEnabled());

        // View reflects DISABLED
        $settingsView = $this->actingAs($admin)->get(route('admin.settings'));
        $settingsView->assertSee('FEATURE DISABLED');

        // Toggle back ON
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-direct-design'));
        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertTrue(FeatureManager::isDirectDesignEnabled());

        // View reflects ENABLED
        $settingsView2 = $this->actingAs($admin)->get(route('admin.settings'));
        $settingsView2->assertSee('FEATURE ENABLED');
    }

    public function test_project_show_hides_direct_design_buttons_when_disabled(): void
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $brand = Brand::create(['name' => 'Acme Brand', 'slug' => 'acme-brand']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Campaign Project',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $manager->id,
        ]);

        // When enabled, Direct Design button is visible
        FeatureManager::setDirectDesignEnabled(true);
        $resEnabled = $this->actingAs($manager)->get(route('projects.show', $project->id));
        $resEnabled->assertOk();
        $resEnabled->assertSee('Direct Design');

        // When disabled, Direct Design button is hidden
        FeatureManager::setDirectDesignEnabled(false);
        $resDisabled = $this->actingAs($manager)->get(route('projects.show', $project->id));
        $resDisabled->assertOk();
        $resDisabled->assertDontSee("flow=design", false);
    }

    public function test_direct_design_create_page_aborts_403_when_disabled(): void
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $brand = Brand::create(['name' => 'Acme Brand', 'slug' => 'acme-brand-2']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Campaign Project 2',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $manager->id,
        ]);

        FeatureManager::setDirectDesignEnabled(false);

        $response = $this->actingAs($manager)->get(route('deliverables.create', [
            'project_id' => $project->id,
            'flow' => 'design',
        ]));

        $response->assertStatus(403);
    }

    public function test_direct_design_store_aborts_403_when_disabled(): void
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $designer = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Acme Brand', 'slug' => 'acme-brand-3']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Campaign Project 3',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $manager->id,
        ]);

        FeatureManager::setDirectDesignEnabled(false);

        $response = $this->actingAs($manager)->post(route('deliverables.store'), [
            'project_id' => $project->id,
            'flow_type' => 'direct_design',
            'title' => 'Attempted Direct Design',
            'designer_id' => $designer->id,
            'status' => 'To Do',
            'task_type' => 'Deliverable',
            'progress_percent' => 20,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('deliverables', [
            'title' => 'Attempted Direct Design',
        ]);
    }

    public function test_adding_to_direct_design_batch_aborts_403_when_disabled(): void
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $designer = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Acme Brand', 'slug' => 'acme-brand-4']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Campaign Project 4',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $manager->id,
        ]);

        $parent = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Existing Direct Design Batch',
            'flow_type' => 'direct_design',
            'approval_stage' => 'Designer',
            'designer_id' => $designer->id,
            'brand_manager_id' => $manager->id,
        ]);

        FeatureManager::setDirectDesignEnabled(false);

        $response = $this->actingAs($manager)->post(route('deliverables.addToBatch', $parent->id), [
            'title' => 'New Subtask Attempt',
            'post_type' => 'Static Post',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('deliverables', [
            'title' => 'New Subtask Attempt',
        ]);
    }

    public function test_existing_direct_design_deliverables_remain_accessible_when_feature_disabled(): void
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $designer = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Acme Brand', 'slug' => 'acme-brand-5']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Campaign Project 5',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $manager->id,
        ]);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Existing Deliverable',
            'flow_type' => 'direct_design',
            'approval_stage' => 'Designer',
            'designer_id' => $designer->id,
            'brand_manager_id' => $manager->id,
        ]);

        FeatureManager::setDirectDesignEnabled(false);

        // Project view still loads successfully with the existing deliverable rendered
        $response = $this->actingAs($manager)->get(route('projects.show', $project->id));
        $response->assertOk();
        $response->assertSee('Existing Deliverable');

        // Deliverable detail still accessible
        $detailRes = $this->actingAs($manager)->get(route('deliverables.show', $deliverable->id));
        $detailRes->assertOk();
        $detailRes->assertSee('Existing Deliverable');
    }
}
