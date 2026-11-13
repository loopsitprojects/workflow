<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBrandDefaultInclusionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $owner;
    protected User $opsManager;
    protected User $brandManager;
    protected User $writer;
    protected User $designer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Admin',
        ]);

        $this->owner = User::create([
            'name' => 'System Owner',
            'email' => 'owner@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Owner',
        ]);

        $this->opsManager = User::create([
            'name' => 'Ops Manager',
            'email' => 'ops@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Operations Manager',
        ]);

        $this->brandManager = User::create([
            'name' => 'Brand Manager User',
            'email' => 'bm@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Brand Manager',
        ]);

        $this->writer = User::create([
            'name' => 'Creative Writer',
            'email' => 'writer@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        $this->designer = User::create([
            'name' => 'Visual Designer',
            'email' => 'designer@loops.com',
            'password' => bcrypt('secret123'),
            'role' => 'Designer',
        ]);
    }

    public function test_brand_create_page_does_not_show_admin_or_owner_in_team_member_list(): void
    {
        $response = $this->actingAs($this->brandManager)->get(route('brands.create'));
        $response->assertStatus(200);

        // Assert users passed to view do NOT contain Admin or Owner
        $viewUsers = $response->viewData('users');
        $this->assertFalse($viewUsers->contains('id', $this->admin->id));
        $this->assertFalse($viewUsers->contains('id', $this->owner->id));

        // Assert non-admin team members ARE present
        $this->assertTrue($viewUsers->contains('id', $this->writer->id));
        $this->assertTrue($viewUsers->contains('id', $this->designer->id));
    }

    public function test_brand_edit_page_does_not_show_admin_or_owner_in_team_member_list(): void
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'created_by' => $this->brandManager->id,
        ]);
        $brand->members()->attach([$this->admin->id, $this->writer->id]);

        $response = $this->actingAs($this->brandManager)->get(route('brands.edit', $brand));
        $response->assertStatus(200);

        $viewUsers = $response->viewData('users');
        $this->assertFalse($viewUsers->contains('id', $this->admin->id));
        $this->assertFalse($viewUsers->contains('id', $this->owner->id));
        $this->assertTrue($viewUsers->contains('id', $this->writer->id));
    }

    public function test_brand_index_modal_does_not_show_admin_or_owner_in_team_member_list(): void
    {
        $brand = Brand::create([
            'name' => 'Index Test Brand',
            'slug' => 'index-test-brand',
        ]);

        $response = $this->actingAs($this->admin)->get(route('brands.index'));
        $response->assertStatus(200);

        $viewUsers = $response->viewData('users');
        $this->assertFalse($viewUsers->contains('id', $this->admin->id));
        $this->assertFalse($viewUsers->contains('id', $this->owner->id));
        $this->assertTrue($viewUsers->contains('id', $this->writer->id));
    }

    public function test_brand_store_automatically_includes_admin_owner_and_ops_manager(): void
    {
        // Brand Manager creates brand and only selects Writer
        $response = $this->actingAs($this->brandManager)->post(route('brands.store'), [
            'name' => 'Auto Include Brand',
            'slug' => 'auto-include-brand',
            'members' => [$this->writer->id],
        ]);

        $response->assertRedirect(route('brands.index'));

        $brand = Brand::where('slug', 'auto-include-brand')->first();
        $this->assertNotNull($brand);

        $memberIds = $brand->members->pluck('id')->toArray();

        // Admin and Owner are automatically included in default
        $this->assertContains($this->admin->id, $memberIds);
        $this->assertContains($this->owner->id, $memberIds);
        $this->assertContains($this->opsManager->id, $memberIds);
        $this->assertContains($this->brandManager->id, $memberIds);
        $this->assertContains($this->writer->id, $memberIds);
    }

    public function test_brand_update_preserves_admin_and_owner_even_when_not_in_request_payload(): void
    {
        $brand = Brand::create([
            'name' => 'Update Brand',
            'slug' => 'update-brand',
            'created_by' => $this->brandManager->id,
        ]);
        $brand->members()->attach([$this->admin->id, $this->owner->id, $this->brandManager->id, $this->writer->id]);

        // Brand Manager updates brand and selects only Designer
        $response = $this->actingAs($this->brandManager)->put(route('brands.update', $brand), [
            'name' => 'Updated Brand Name',
            'slug' => 'update-brand',
            'members' => [$this->designer->id],
        ]);

        $response->assertRedirect(route('brands.index'));

        $brand->refresh();
        $memberIds = $brand->members->pluck('id')->toArray();

        // Admin and Owner remain attached
        $this->assertContains($this->admin->id, $memberIds);
        $this->assertContains($this->owner->id, $memberIds);
        $this->assertContains($this->opsManager->id, $memberIds);
        $this->assertContains($this->designer->id, $memberIds);
    }

    public function test_newly_created_admin_user_is_automatically_synced_to_existing_brands(): void
    {
        $brand1 = Brand::create(['name' => 'Brand Alpha', 'slug' => 'brand-alpha']);
        $brand2 = Brand::create(['name' => 'Brand Beta', 'slug' => 'brand-beta']);

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'username' => 'newadmin',
            'email' => 'newadmin@loops.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'Admin',
        ]);

        $response->assertRedirect(route('users.index'));

        $newAdmin = User::where('email', 'newadmin@loops.com')->first();
        $this->assertNotNull($newAdmin);

        $brand1MemberIds = $brand1->fresh()->members->pluck('id')->toArray();
        $brand2MemberIds = $brand2->fresh()->members->pluck('id')->toArray();

        $this->assertContains($newAdmin->id, $brand1MemberIds);
        $this->assertContains($newAdmin->id, $brand2MemberIds);
    }
}
