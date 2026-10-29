<?php

use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

test('admin can toggle christmas theme feature via endpoint', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    // Set initial theme to dark
    FeatureManager::setActiveTheme('dark');
    expect(FeatureManager::isChristmasThemeEnabled())->toBeFalse();

    // Toggle ON
    $response = $this->actingAs($admin)->post(route('admin.features.toggle-christmas-theme'));
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::isChristmasThemeEnabled())->toBeTrue();
    expect(FeatureManager::getActiveTheme())->toBe('christmas');

    // Toggle OFF (restores default 'dark')
    $response = $this->actingAs($admin)->post(route('admin.features.toggle-christmas-theme'));
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::isChristmasThemeEnabled())->toBeFalse();
    expect(FeatureManager::getActiveTheme())->toBe('dark');
});

test('non-admin cannot toggle christmas theme', function () {
    $writer = User::factory()->create(['role' => 'Writer']);

    $response = $this->actingAs($writer)->post(route('admin.features.toggle-christmas-theme'));
    $response->assertStatus(403);
});

test('admin can update active theme to christmas via theme setting endpoint', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    // Set to christmas
    $response = $this->actingAs($admin)->post(route('admin.settings.theme'), ['theme' => 'christmas']);
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::getActiveTheme())->toBe('christmas');
    expect(FeatureManager::isChristmasThemeEnabled())->toBeTrue();
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeFalse();

    // Switch back to dark
    $response = $this->actingAs($admin)->post(route('admin.settings.theme'), ['theme' => 'dark']);
    $response->assertRedirect(route('admin.settings'));
    expect(FeatureManager::getActiveTheme())->toBe('dark');
    expect(FeatureManager::isChristmasThemeEnabled())->toBeFalse();
});

test('admin settings page displays christmas theme card, switches, and active badge', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    FeatureManager::setActiveTheme('christmas');
    $response = $this->actingAs($admin)->get(route('admin.settings'));
    $response->assertOk();
    $response->assertSee('Platform Theme', false);
    $response->assertSee('Christmas Festive Mode', false);
    $response->assertSee('CHRISTMAS MODE ACTIVE', false);
    $response->assertSee('🎄 Christmas:', false);

    FeatureManager::setActiveTheme('dark');
    $response = $this->actingAs($admin)->get(route('admin.settings'));
    $response->assertOk();
    $response->assertSee('DARK THEME ACTIVE', false);

    // Reset back
    FeatureManager::setActiveTheme('dark');
});

test('layout renders christmas theme state, festive decorations, and classes', function () {
    $user = User::factory()->create(['role' => 'Writer']);

    FeatureManager::setActiveTheme('christmas');
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('class="dark christmas"', false);
    $response->assertSee('const christmasEnabled = true;', false);
    $response->assertSee('🎄 FESTIVE', false);
    $response->assertSee('christmas-snow-layer', false);
    $response->assertSee('christmas-bauble', false);
    $response->assertSee('christmas-skyline', false);
});

test('christmas dual mode styling tokens and watermarks render on dashboard', function () {
    $user = User::factory()->create(['role' => 'Writer']);

    FeatureManager::setActiveTheme('christmas');
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();

    // Dark Mode tokens & styles
    $response->assertSee('html.dark.christmas body', false);
    $response->assertSee('#06131A', false); // Nordic Midnight background
    $response->assertSee('#0E2330', false); // Frosted Card surface
    $response->assertSee('#1B3D54', false); // Frosted Border
    $response->assertSee('#EF4444', false); // Festive Crimson
    $response->assertSee('#10B981', false); // Evergreen Pine

    // Light Mode tokens & styles
    $response->assertSee('html.christmas:not(.dark) body', false);
    $response->assertSee('#F8FAFC', false); // Crisp snow white
    $response->assertSee('#DC2626', false); // Holiday Red
    $response->assertSee('#047857', false); // Forest Pine

    // Holiday Empty States & Greeting Badges
    $response->assertSee('christmas-empty-state', false);
    $response->assertSee('Holiday Season', false);
    $response->assertSee('🎅', false);
});
