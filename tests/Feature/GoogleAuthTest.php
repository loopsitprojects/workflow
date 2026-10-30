<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_google_redirect_initiates_oauth_flow(): void
    {
        Config::set('services.google.client_id', 'test-client-id');
        Config::set('services.google.client_secret', 'test-client-secret');
        Config::set('services.google.redirect', 'http://localhost/auth/google/callback');
        Config::set('services.google.hosted_domain', 'loops.work');

        $response = $this->get(route('auth.google.redirect'));

        $response->assertStatus(302);
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('accounts.google.com', $targetUrl);
        $this->assertStringContainsString('hd=loops.work', $targetUrl);
    }

    public function test_google_callback_with_error_redirects_to_login(): void
    {
        $response = $this->get(route('auth.google.callback', ['error' => 'access_denied']));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login']);
    }

    public function test_google_callback_rejects_unauthorized_domain(): void
    {
        Config::set('services.google.hosted_domain', 'company.com');

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn('intruder@gmail.com');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_google_callback_rejects_unregistered_employee(): void
    {
        Config::set('services.google.hosted_domain', 'company.com');

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-123456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('unregistered@company.com');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_google_callback_logs_in_existing_user_and_links_google_id(): void
    {
        Config::set('services.google.hosted_domain', 'company.com');

        // Create pre-provisioned user
        $user = User::factory()->create([
            'email'     => 'designer@company.com',
            'role'      => 'Designer',
            'google_id' => null,
            'avatar'    => null,
        ]);

        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('google-999888');
        $socialiteUser->shouldReceive('getEmail')->andReturn('designer@company.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://google.com/photo.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertEquals('google-999888', $user->google_id);
        $this->assertEquals('https://google.com/photo.jpg', $user->avatar);
        $this->assertEquals('https://google.com/photo.jpg', $user->avatar_url);
    }

    public function test_google_callback_blocks_non_admin_during_maintenance_mode(): void
    {
        \Illuminate\Support\Facades\Storage::put('maintenance.json', json_encode([
            'enabled' => true,
            'message' => 'System maintenance active',
        ]));

        try {
            $user = User::factory()->create([
                'email' => 'writer@company.com',
                'role'  => 'Writer',
            ]);

            $socialiteUser = Mockery::mock(SocialiteUser::class);
            $socialiteUser->shouldReceive('getId')->andReturn('google-writer');
            $socialiteUser->shouldReceive('getEmail')->andReturn('writer@company.com');
            $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

            $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
            $provider->shouldReceive('user')->andReturn($socialiteUser);
            Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

            $response = $this->get(route('auth.google.callback'));

            $response->assertRedirect(route('login'));
            $response->assertSessionHasErrors(['login']);
            $this->assertGuest();
        } finally {
            \Illuminate\Support\Facades\Storage::delete('maintenance.json');
        }
    }

    public function test_google_callback_allows_admin_during_maintenance_mode(): void
    {
        \Illuminate\Support\Facades\Storage::put('maintenance.json', json_encode([
            'enabled' => true,
            'message' => 'System maintenance active',
        ]));

        try {
            $admin = User::factory()->create([
                'email' => 'admin@company.com',
                'role'  => 'Admin',
            ]);

            $socialiteUser = Mockery::mock(SocialiteUser::class);
            $socialiteUser->shouldReceive('getId')->andReturn('google-admin');
            $socialiteUser->shouldReceive('getEmail')->andReturn('admin@company.com');
            $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

            $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
            $provider->shouldReceive('user')->andReturn($socialiteUser);
            Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

            $response = $this->get(route('auth.google.callback'));

            $response->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($admin);
        } finally {
            \Illuminate\Support\Facades\Storage::delete('maintenance.json');
        }
    }
}
