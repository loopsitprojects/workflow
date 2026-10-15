<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Project;
use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectTypeToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset feature flags settings file
        Storage::disk('local')->delete('settings.json');
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->delete('settings.json');
        parent::tearDown();
    }

    public function test_admin_can_view_project_types_configuration_in_settings()
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Project Types Configuration');
        $response->assertSee('Retainer');
        $response->assertSee('Campaign');
        $response->assertSee('Pitch');
        $response->assertSee('3 of 3 Active');
    }

    public function test_non_admin_cannot_toggle_project_types()
    {
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->post(route('admin.features.toggle-project-type'), [
            'project_type' => 'pitch',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_project_type_on_and_off()
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        // Pitch is initially enabled
        $this->assertTrue(FeatureManager::isProjectTypeEnabled('pitch'));

        // Toggle pitch OFF
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-project-type'), [
            'project_type' => 'pitch',
        ]);

        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertFalse(FeatureManager::isProjectTypeEnabled('pitch'));

        // Toggle pitch back ON
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-project-type'), [
            'project_type' => 'pitch',
        ]);

        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertTrue(FeatureManager::isProjectTypeEnabled('pitch'));
    }

    public function test_safeguard_prevents_disabling_all_project_types()
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        // Disable pitch
        FeatureManager::setProjectTypeEnabled('pitch', false);
        // Disable campaign
        FeatureManager::setProjectTypeEnabled('campaign', false);

        $this->assertTrue(FeatureManager::isProjectTypeEnabled('retainer'));
        $this->assertCount(1, FeatureManager::getEnabledProjectTypes());

        // Attempt to disable retainer (the last remaining type)
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-project-type'), [
            'project_type' => 'retainer',
        ]);

        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('error', 'At least one project type must remain enabled.');
        $this->assertTrue(FeatureManager::isProjectTypeEnabled('retainer'));
    }

    public function test_create_project_view_hides_disabled_project_types()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

        // Disable pitch
        FeatureManager::setProjectTypeEnabled('pitch', false);

        $response = $this->actingAs($admin)->get(route('projects.create', ['brand_id' => $brand->id]));

        $response->assertStatus(200);
        $response->assertSee('id="option-retainer"', false);
        $response->assertSee('id="option-campaign"', false);
        $response->assertDontSee('id="option-pitch"', false);
    }

    public function test_create_project_dynamically_defaults_to_first_enabled_type()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

        // Disable retainer
        FeatureManager::setProjectTypeEnabled('retainer', false);

        $response = $this->actingAs($admin)->get(route('projects.create', ['brand_id' => $brand->id]));

        $response->assertStatus(200);
        $response->assertDontSee('id="option-retainer"', false);
        $response->assertSee('id="option-campaign"', false);
        $response->assertSee('id="option-pitch"', false);
        $response->assertSee('value="campaign"', false);
    }

    public function test_store_project_validation_fails_for_disabled_project_type()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

        // Disable pitch
        FeatureManager::setProjectTypeEnabled('pitch', false);

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'brand_id' => $brand->id,
            'name' => 'New Pitch Project',
            'workflow_type' => 'pitch',
            'type' => 'Pitch Deck',
            'priority' => 'High',
            'status' => 'Not Started',
        ]);

        $response->assertSessionHasErrors(['workflow_type']);
        $errors = session('errors')->get('workflow_type');
        $this->assertContains('The selected project type is currently disabled by administrator.', $errors);
    }

    public function test_store_project_succeeds_for_enabled_project_type()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

        FeatureManager::setProjectTypeEnabled('retainer', true);

        $response = $this->actingAs($admin)->post(route('projects.store'), [
            'brand_id' => $brand->id,
            'name' => 'Valid Retainer Project',
            'workflow_type' => 'retainer',
            'type' => 'Social Media',
            'priority' => 'Medium',
            'status' => 'Not Started',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', [
            'name' => 'Valid Retainer Project',
            'workflow_type' => 'retainer',
        ]);
    }

    public function test_existing_project_with_disabled_type_can_still_be_edited_and_updated()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Acme Corp', 'slug' => 'acme-corp']);

        // Existing pitch project
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Existing Pitch',
            'workflow_type' => 'pitch',
            'type' => 'Pitch Deck',
            'priority' => 'High',
            'status' => 'In Progress',
        ]);

        // Pitch is disabled later
        FeatureManager::setProjectTypeEnabled('pitch', false);

        // Edit view still displays the current project's option
        $response = $this->actingAs($admin)->get(route('projects.edit', $project));
        $response->assertStatus(200);
        $response->assertSee('id="option-pitch"', false);

        // Update succeeds keeping pitch
        $response = $this->actingAs($admin)->put(route('projects.update', $project), [
            'brand_id' => $brand->id,
            'name' => 'Existing Pitch Updated',
            'workflow_type' => 'pitch',
            'type' => 'Pitch Deck',
            'priority' => 'High',
            'status' => 'In Progress',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Existing Pitch Updated',
            'workflow_type' => 'pitch',
        ]);
    }
}
