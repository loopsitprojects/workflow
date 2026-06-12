<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('a regular user can mark all their notifications as read', function () {
    $user = User::factory()->create(['role' => 'User']);
    $otherUser = User::factory()->create(['role' => 'User']);

    // Create a notification for $user
    $notif1 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => ['message' => 'Test message'],
        'read_at' => null,
    ]);

    // Create a notification for $otherUser
    $notif2 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => ['message' => 'Other test message'],
        'read_at' => null,
    ]);

    $this->actingAs($user)
        ->postJson(route('notifications.markAllRead'))
        ->assertOk();

    // The user's notification should be read
    expect($notif1->fresh()->read_at)->not->toBeNull();
    // The other user's notification should remain unread
    expect($notif2->fresh()->read_at)->toBeNull();
});

test('an admin user can mark all notifications as read', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $otherUser = User::factory()->create(['role' => 'User']);

    // Create a notification for $admin
    $notif1 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $admin->id,
        'data' => ['message' => 'Test message'],
        'read_at' => null,
    ]);

    // Create a notification for $otherUser
    $notif2 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => ['message' => 'Other test message'],
        'read_at' => null,
    ]);

    $this->actingAs($admin)
        ->postJson(route('notifications.markAllRead'))
        ->assertOk();

    // Both notifications should be read
    expect($notif1->fresh()->read_at)->not->toBeNull();
    expect($notif2->fresh()->read_at)->not->toBeNull();
});

test('a regular user can archive all their notifications', function () {
    $user = User::factory()->create(['role' => 'User']);
    $otherUser = User::factory()->create(['role' => 'User']);

    // Create a notification for $user
    $notif1 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => ['message' => 'Test message'],
    ]);

    // Create a notification for $otherUser
    $notif2 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => ['message' => 'Other test message'],
    ]);

    $this->actingAs($user)
        ->postJson(route('notifications.archiveAll'))
        ->assertOk();

    // The user's notification should be deleted (cannot be found)
    expect(DatabaseNotification::find($notif1->id))->toBeNull();
    // The other user's notification should still exist
    expect(DatabaseNotification::find($notif2->id))->not->toBeNull();
});

test('an admin user can archive all notifications', function () {
    $admin = User::factory()->create(['role' => 'Admin']);
    $otherUser = User::factory()->create(['role' => 'User']);

    // Create a notification for $admin
    $notif1 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $admin->id,
        'data' => ['message' => 'Test message'],
    ]);

    // Create a notification for $otherUser
    $notif2 = DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\DeliverableUpdated',
        'notifiable_type' => User::class,
        'notifiable_id' => $otherUser->id,
        'data' => ['message' => 'Other test message'],
    ]);

    $this->actingAs($admin)
        ->postJson(route('notifications.archiveAll'))
        ->assertOk();

    // Both notifications should be deleted
    expect(DatabaseNotification::find($notif1->id))->toBeNull();
    expect(DatabaseNotification::find($notif2->id))->toBeNull();
});
