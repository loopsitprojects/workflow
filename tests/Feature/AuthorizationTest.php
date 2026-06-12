<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_all_restricted_buttons()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Test Project',
            'workflow_type' => 'retainer',
            'priority' => 'Medium',
        ]);

        $response = $this->actingAs($admin)->get(route('brands.index'));
        $response->assertStatus(200);
        $response->assertSee('Create New Brand');
        $response->assertSee('Edit Brand');

        $response = $this->actingAs($admin)->get(route('brands.show', $brand));
        $response->assertStatus(200);
        $response->assertSee('Edit');
        $response->assertSee('New Project');

        $response = $this->actingAs($admin)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Settings');

        $response = $this->actingAs($admin)->get(route('projects.edit', $project));
        $response->assertStatus(200);
        $response->assertSee('Delete Project');
    }

    public function test_brand_manager_can_see_project_settings_but_not_brand_edit_or_project_delete()
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Test Project',
            'workflow_type' => 'retainer',
            'priority' => 'Medium',
        ]);

        $response = $this->actingAs($manager)->get(route('brands.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Create New Brand');
        $response->assertDontSee('Edit Brand');

        $response = $this->actingAs($manager)->get(route('brands.show', $brand));
        $response->assertStatus(200);
        $response->assertDontSee('Edit'); // Brand edit
        $response->assertSee('New Project');

        $response = $this->actingAs($manager)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertSee('Settings');

        $response = $this->actingAs($manager)->get(route('projects.edit', $project));
        $response->assertStatus(200);
        $response->assertDontSee('Delete Project');
    }

    public function test_writer_cannot_see_settings_brand_edit_new_project_or_project_delete()
    {
        $writer = User::factory()->create(['role' => 'Writer']);
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Test Project',
            'workflow_type' => 'retainer',
            'priority' => 'Medium',
        ]);

        $response = $this->actingAs($writer)->get(route('brands.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Create New Brand');
        $response->assertDontSee('Edit Brand');

        $response = $this->actingAs($writer)->get(route('brands.show', $brand));
        $response->assertStatus(200);
        $response->assertDontSee('Edit');
        $response->assertDontSee('New Project');

        $response = $this->actingAs($writer)->get(route('projects.show', $project));
        $response->assertStatus(200);
        $response->assertDontSee('Settings');

        // Accessing edit directly should fail
        $response = $this->actingAs($writer)->get(route('projects.edit', $project));
        $response->assertStatus(403);
    }
}
