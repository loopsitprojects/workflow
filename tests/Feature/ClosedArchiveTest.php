<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClosedArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('closed.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_closed_archive(): void
    {
        $user = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($user)->get(route('closed.index'));
        $response->assertStatus(200);
        $response->assertSee('Closed Work Archive');
        $response->assertSee('Closed Projects');
        $response->assertSee('Closed Deliverables');
    }

    public function test_closed_projects_tab_displays_completed_projects_only(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Nike Global', 'slug' => 'nike-global']);

        $closedProject = Project::create([
            'brand_id'      => $brand->id,
            'name'          => 'Nike Winter Campaign 2026',
            'workflow_type' => 'campaign',
            'status'        => 'Completed',
            'priority'      => 'High',
            'type'          => 'Campaign',
        ]);

        $activeProject = Project::create([
            'brand_id'      => $brand->id,
            'name'          => 'Nike Summer Retainer 2027',
            'workflow_type' => 'retainer',
            'status'        => 'In Progress',
            'priority'      => 'Medium',
            'type'          => 'Retainer',
        ]);

        $response = $this->actingAs($admin)->get(route('closed.index', ['tab' => 'projects']));
        $response->assertStatus(200);
        $response->assertSee('Nike Winter Campaign 2026');
        $response->assertDontSee('Nike Summer Retainer 2027');
    }

    public function test_closed_deliverables_tab_displays_closed_deliverables(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Adidas Originals', 'slug' => 'adidas-originals']);

        $project = Project::create([
            'brand_id'      => $brand->id,
            'name'          => 'Adidas Superstar Launch',
            'workflow_type' => 'campaign',
            'status'        => 'In Progress',
            'priority'      => 'High',
            'type'          => 'Campaign',
        ]);

        $closedDeliverable = Deliverable::create([
            'project_id'       => $project->id,
            'title'            => 'Superstar Hero Carousel Post',
            'post_type'        => 'Carousel',
            'status'           => 'Done',
            'approval_stage'   => 'Closed',
            'priority'         => 'High',
            'progress_percent' => 100,
        ]);

        $activeDeliverable = Deliverable::create([
            'project_id'       => $project->id,
            'title'            => 'Superstar Reel Teaser (Drafting)',
            'post_type'        => 'Reel',
            'status'           => 'In Progress',
            'approval_stage'   => 'Designer',
            'priority'         => 'Medium',
            'progress_percent' => 50,
        ]);

        $response = $this->actingAs($admin)->get(route('closed.index', ['tab' => 'deliverables']));
        $response->assertStatus(200);
        $response->assertSee('Superstar Hero Carousel Post');
        $response->assertDontSee('Superstar Reel Teaser (Drafting)');
        $response->assertSee('detailsModalOpen');
        $response->assertSee('openDetails');
    }

    public function test_manager_can_toggle_project_status(): void
    {
        $manager = User::factory()->create(['role' => 'Brand Manager']);
        $brand = Brand::create(['name' => 'Apple Inc', 'slug' => 'apple-inc']);

        $project = Project::create([
            'brand_id'      => $brand->id,
            'name'          => 'iPhone 18 Keynote',
            'workflow_type' => 'pitch',
            'status'        => 'In Progress',
            'priority'      => 'High',
            'type'          => 'Pitch',
        ]);

        // Mark as closed
        $response = $this->actingAs($manager)->post(route('projects.toggle-close', $project));
        $response->assertRedirect();

        $project->refresh();
        $this->assertEquals('Completed', $project->status);

        // Reopen project
        $response = $this->actingAs($manager)->post(route('projects.toggle-close', $project));
        $response->assertRedirect();

        $project->refresh();
        $this->assertEquals('In Progress', $project->status);
    }

    public function test_admin_can_toggle_closed_tab_in_settings(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        \App\Services\FeatureManager::setClosedTabEnabled(true);
        $this->assertTrue(\App\Services\FeatureManager::isClosedTabEnabled());

        // Toggle off
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-closed-tab'));
        $response->assertRedirect();
        $this->assertFalse(\App\Services\FeatureManager::isClosedTabEnabled());

        // Toggle on
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-closed-tab'));
        $response->assertRedirect();
        $this->assertTrue(\App\Services\FeatureManager::isClosedTabEnabled());
    }

    public function test_non_admin_cannot_toggle_closed_tab(): void
    {
        $writer = User::factory()->create(['role' => 'Writer']);

        $response = $this->actingAs($writer)->post(route('admin.features.toggle-closed-tab'));
        $response->assertStatus(403);
    }

    public function test_closed_tab_is_hidden_in_nav_when_disabled(): void
    {
        $user = User::factory()->create(['role' => 'Writer']);

        \App\Services\FeatureManager::setClosedTabEnabled(true);
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertSee(route('closed.index'));

        \App\Services\FeatureManager::setClosedTabEnabled(false);
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertDontSee(route('closed.index'));

        // Reset
        \App\Services\FeatureManager::setClosedTabEnabled(true);
    }

    public function test_regular_user_cannot_access_closed_tab_when_disabled(): void
    {
        $writer = User::factory()->create(['role' => 'Writer']);

        \App\Services\FeatureManager::setClosedTabEnabled(false);

        $response = $this->actingAs($writer)->get(route('closed.index'));
        $response->assertStatus(403);

        // Reset
        \App\Services\FeatureManager::setClosedTabEnabled(true);
    }

    public function test_admin_can_access_closed_tab_even_when_disabled(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        \App\Services\FeatureManager::setClosedTabEnabled(false);

        $response = $this->actingAs($admin)->get(route('closed.index'));
        $response->assertStatus(200);
        $response->assertSee('Notice: Closed Tab is currently DISABLED for standard users.');

        // Reset
        \App\Services\FeatureManager::setClosedTabEnabled(true);
    }
}
