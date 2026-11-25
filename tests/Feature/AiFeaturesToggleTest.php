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

class AiFeaturesToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_can_view_ai_features_toggle_in_settings(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('AI Creative Suite & Intelligence', false);
        $response->assertSee('FEATURE ENABLED');
        $response->assertSee(route('admin.features.toggle-ai-features'));
    }

    public function test_non_admin_cannot_toggle_ai_features(): void
    {
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->post(route('admin.features.toggle-ai-features'));

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_ai_features_on_and_off(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        // Default is enabled
        $this->assertTrue(FeatureManager::isAiFeaturesEnabled());

        // Toggle OFF
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-ai-features'));
        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertFalse(FeatureManager::isAiFeaturesEnabled());

        // Toggle back ON
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-ai-features'));
        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertTrue(FeatureManager::isAiFeaturesEnabled());
    }

    public function test_grammar_check_is_blocked_when_ai_features_are_disabled(): void
    {
        $user = User::factory()->create(['role' => 'Writer']);
        FeatureManager::setAiFeaturesEnabled(false);

        $response = $this->actingAs($user)->json('POST', route('deliverables.check-grammar'), [
            'fields' => ['copy' => 'Some test copy text'],
        ]);

        $response->assertStatus(403);
        $data = $response->json();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('disabled in system settings', $data['message']);
    }

    public function test_artwork_critique_is_blocked_when_ai_features_are_disabled(): void
    {
        $user = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Brand AI Test', 'slug' => 'brand-ai-test']);
        $user->brands()->attach($brand->id);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Artwork Test',
            'status' => 'To Do',
            'final_designs' => 'sample_artwork.png',
        ]);

        FeatureManager::setAiFeaturesEnabled(false);

        $response = $this->actingAs($user)->json('POST', route('deliverables.critique-artwork', $deliverable));

        $response->assertStatus(403);
        $data = $response->json();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('disabled in system settings', $data['message']);
    }
}
