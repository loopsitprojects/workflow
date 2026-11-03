<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoogleAuthToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_admin_can_view_google_auth_toggle_in_settings(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Google Authentication (SSO)');
        $response->assertSee('FEATURE ENABLED');
        $response->assertSee(route('admin.features.toggle-google-auth'));
    }

    public function test_non_admin_cannot_toggle_google_auth(): void
    {
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->post(route('admin.features.toggle-google-auth'));

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_google_auth_on_and_off(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        // Default is enabled
        $this->assertTrue(FeatureManager::isGoogleAuthEnabled());

        // Toggle OFF
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-google-auth'));
        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertFalse(FeatureManager::isGoogleAuthEnabled());

        // Settings view reflects DISABLED
        $settingsView = $this->actingAs($admin)->get(route('admin.settings'));
        $settingsView->assertSee('FEATURE DISABLED');

        // Toggle back ON
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-google-auth'));
        $response->assertRedirect(route('admin.settings'));
        $response->assertSessionHas('success');
        $this->assertTrue(FeatureManager::isGoogleAuthEnabled());

        // Settings view reflects ENABLED
        $settingsView2 = $this->actingAs($admin)->get(route('admin.settings'));
        $settingsView2->assertSee('FEATURE ENABLED');
    }

    public function test_login_page_shows_google_sso_button_when_enabled(): void
    {
        FeatureManager::setGoogleAuthEnabled(true);

        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Sign in with Google');
        $response->assertSee('Or continue with');
        $response->assertSee(route('auth.google.redirect'));
    }

    public function test_login_page_hides_google_sso_button_when_disabled(): void
    {
        FeatureManager::setGoogleAuthEnabled(false);

        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertDontSee('Sign in with Google');
        $response->assertDontSee('Or continue with');
        $response->assertDontSee(route('auth.google.redirect'));
    }

    public function test_google_redirect_blocked_when_disabled(): void
    {
        FeatureManager::setGoogleAuthEnabled(false);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'login' => 'Google sign-in is currently disabled by an administrator.',
        ]);
    }

    public function test_google_callback_blocked_when_disabled(): void
    {
        FeatureManager::setGoogleAuthEnabled(false);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'login' => 'Google sign-in is currently disabled by an administrator.',
        ]);
    }
}
