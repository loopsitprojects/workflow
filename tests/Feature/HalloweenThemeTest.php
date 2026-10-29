<?php

use App\Models\User;
use App\Services\FeatureManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

test('halloween theme is disabled by default in FeatureManager', function () {
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeFalse();
});

test('admin can toggle halloween theme feature via endpoint', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    // Initially false
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeFalse();

    // Toggle ON
    $response = $this->actingAs($admin)->post(route('admin.features.toggle-halloween-theme'));
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeTrue();

    // Toggle OFF
    $response = $this->actingAs($admin)->post(route('admin.features.toggle-halloween-theme'));
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeFalse();
});

test('non-admin cannot toggle halloween theme', function () {
    $writer = User::factory()->create(['role' => 'Writer']);

    $response = $this->actingAs($writer)->post(route('admin.features.toggle-halloween-theme'));
    $response->assertStatus(403);
});

test('admin can update active theme via theme setting endpoint', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    // Set to dark
    $response = $this->actingAs($admin)->post(route('admin.settings.theme'), ['theme' => 'dark']);
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::getActiveTheme())->toBe('dark');
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeFalse();

    // Set to light
    $response = $this->actingAs($admin)->post(route('admin.settings.theme'), ['theme' => 'light']);
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::getActiveTheme())->toBe('light');
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeFalse();

    // Set to halloween
    $response = $this->actingAs($admin)->post(route('admin.settings.theme'), ['theme' => 'halloween']);
    $response->assertRedirect(route('admin.settings'));
    $response->assertSessionHas('success');
    expect(FeatureManager::getActiveTheme())->toBe('halloween');
    expect(FeatureManager::isHalloweenThemeEnabled())->toBeTrue();
});

test('admin settings page displays theme settings section and option cards', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    FeatureManager::setActiveTheme('halloween');
    $response = $this->actingAs($admin)->get(route('admin.settings'));
    $response->assertOk();
    $response->assertSee('Platform Theme', false);
    $response->assertSee('Halloween Spooky Mode', false);
    $response->assertSee('Standard Dark Theme', false);
    $response->assertSee('Standard Light Theme', false);
    $response->assertSee('HALLOWEEN MODE ACTIVE', false);

    FeatureManager::setActiveTheme('dark');
    $response = $this->actingAs($admin)->get(route('admin.settings'));
    $response->assertOk();
    $response->assertSee('DARK THEME ACTIVE', false);

    // Restore to halloween
    FeatureManager::setActiveTheme('halloween');
});

test('layout renders theme store with halloweenAvailable state and pumpkin icon template', function () {
    $user = User::factory()->create(['role' => 'Writer']);

    FeatureManager::setHalloweenThemeEnabled(true);
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('class="dark halloween"', false);
    $response->assertSee('const halloweenEnabled = true;', false);
    $response->assertSee('🎃', false);

    FeatureManager::setHalloweenThemeEnabled(false);
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertDontSee('class="dark halloween"', false);
    $response->assertSee('const halloweenEnabled = false;', false);

    // Restore to true
    FeatureManager::setHalloweenThemeEnabled(true);
});

test('layout includes halloween light mode and dark mode styles and watermarks', function () {
    $user = User::factory()->create(['role' => 'Writer']);

    FeatureManager::setHalloweenThemeEnabled(true);
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('html.halloween:not(.dark) body', false);
    $response->assertSee('html.dark.halloween body', false);
    $response->assertSee('html.halloween:not(.dark) .card-shadow::after', false);
    $response->assertSee('html.dark.halloween .card-shadow::after', false);
    $response->assertSee('Switch to Halloween Light Mode', false);
});

test('halloween redesign tokens and enhanced components render on dashboard', function () {
    $user = User::factory()->create(['role' => 'Writer']);

    FeatureManager::setHalloweenThemeEnabled(true);
    $response = $this->actingAs($user)->get(route('dashboard'));
    $response->assertOk();

    // Dark Mode tokens ("Obsidian Midnight")
    $response->assertSee('#0B0A0F', false); // Background
    $response->assertSee('rgba(22, 19, 31, 0.75)', false); // Glassmorphic card surface
    $response->assertSee('rgba(255, 255, 255, 0.08)', false); // Sharp 1px border
    $response->assertSee('#F97316', false); // Warm Burnished Gold-Orange
    $response->assertSee('#8B5CF6', false); // Royal Violet / Deep Amethyst

    // Light Mode tokens ("Warm Obsidian & Linen")
    $response->assertSee('#FBF9F6', false); // Warm Editorial Linen
    $response->assertSee('#EAE6DF', false); // Ultra-fine border
    $response->assertSee('#121118', false); // Deep Ink Black
    $response->assertSee('#C2410C', false); // Burnt Copper
    $response->assertSee('#581C87', false); // Deep Plum

    // Modern KPI Gradient Borders
    $response->assertSee('linear-gradient(90deg, #8B5CF6', false);
    $response->assertSee('linear-gradient(90deg, #F97316', false);

    // Enhanced Spooky Badge & Empty State
    $response->assertSee('🎃 SPOOKY', false);
    $response->assertSee('halloween-empty-state', false);
});
