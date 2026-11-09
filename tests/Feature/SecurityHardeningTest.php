<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\PasswordOtp;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $assignedUser;
    private User $unrelatedUser;
    private Brand $brand;
    private Project $project;
    private Deliverable $deliverable;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin_test',
            'email' => 'admin_sec@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Admin',
        ]);

        $this->assignedUser = User::create([
            'name' => 'Assigned Writer',
            'username' => 'writer_assigned',
            'email' => 'assigned_sec@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        $this->unrelatedUser = User::create([
            'name' => 'Unrelated User',
            'username' => 'unrelated_sec',
            'email' => 'unrelated_sec@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        $this->brand = Brand::create([
            'name' => 'Security Brand',
            'slug' => 'sec-brand',
        ]);
        $this->brand->members()->attach($this->assignedUser->id);

        $this->project = Project::create([
            'brand_id' => $this->brand->id,
            'name' => 'Sec Project',
            'status' => 'In Progress',
            'priority' => 'Medium',
            'type' => 'Standard',
            'workflow_type' => 'retainer',
            'created_by' => $this->assignedUser->id,
        ]);

        $this->deliverable = Deliverable::create([
            'project_id' => $this->project->id,
            'title' => 'Sec Deliverable',
            'writer_id' => $this->assignedUser->id,
            'status' => 'To Do',
            'approval_stage' => 'Writer',
            'task_type' => 'Standard',
            'priority' => 'Medium',
            'progress_percent' => 0,
        ]);
    }

    public function test_unrelated_user_cannot_access_project_show_idor()
    {
        $this->actingAs($this->unrelatedUser);

        $response = $this->get(route('projects.show', $this->project));
        $response->assertStatus(403);
    }

    public function test_assigned_user_and_admin_can_access_project_show()
    {
        $this->actingAs($this->assignedUser);
        $response = $this->get(route('projects.show', $this->project));
        $response->assertStatus(200);

        $this->actingAs($this->admin);
        $response = $this->get(route('projects.show', $this->project));
        $response->assertStatus(200);
    }

    public function test_unrelated_user_cannot_access_deliverable_show_or_exports_idor()
    {
        $this->actingAs($this->unrelatedUser);

        $response = $this->get(route('deliverables.show', $this->deliverable));
        $response->assertStatus(403);

        $response = $this->get(route('deliverables.export.docx', $this->deliverable));
        $response->assertStatus(403);

        $response = $this->get(route('deliverables.export.ppt', $this->deliverable));
        $response->assertStatus(403);
    }

    public function test_assigned_user_and_admin_can_access_deliverable_show()
    {
        $this->actingAs($this->assignedUser);
        $response = $this->get(route('deliverables.show', $this->deliverable));
        $response->assertStatus(200);

        $this->actingAs($this->admin);
        $response = $this->get(route('deliverables.show', $this->deliverable));
        $response->assertStatus(200);
    }

    public function test_generate_presigned_url_rejects_unauthorized_extensions()
    {
        $this->actingAs($this->assignedUser);

        $response = $this->postJson(route('deliverables.presigned-url'), [
            'filename' => 'malicious_shell.exe',
            'folder' => 'artwork',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'File extension not permitted.']);
    }

    public function test_login_route_is_rate_limited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', [
                'login' => 'nonexistent@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 11th request should be throttled
        $response = $this->post('/login', [
            'login' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    public function test_forgot_password_otp_is_stored_hashed()
    {
        $user = User::create([
            'name' => 'OTP User',
            'username' => 'otp_user',
            'email' => 'otp-test@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'Writer',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'otp-test@example.com',
        ]);

        $response->assertRedirect(route('password.otp.show'));

        $otpRecord = PasswordOtp::where('email', 'otp-test@example.com')->latest()->first();
        $this->assertNotNull($otpRecord);
        $this->assertDoesNotMatchRegularExpression('/^\d{6}$/', $otpRecord->otp);
        $this->assertTrue(str_starts_with($otpRecord->otp, '$2y$'));
    }
}
