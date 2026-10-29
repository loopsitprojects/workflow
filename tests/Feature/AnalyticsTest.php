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

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_admin_can_view_analytics_toggle_in_settings(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('admin.settings'));

        $response->assertStatus(200);
        $response->assertSee('Analytics', false);
        $response->assertSee('FEATURE ENABLED');
        $response->assertSee(route('admin.features.toggle-analytics'));
    }

    public function test_non_admin_cannot_toggle_analytics(): void
    {
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->post(route('admin.features.toggle-analytics'));

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_analytics_on_and_off(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        // Default is enabled
        $this->assertTrue(FeatureManager::isAnalyticsEnabled());

        // Toggle OFF
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-analytics'));
        $response->assertRedirect(route('admin.settings'));
        $this->assertFalse(FeatureManager::isAnalyticsEnabled());

        // Toggle back ON
        $response = $this->actingAs($admin)->post(route('admin.features.toggle-analytics'));
        $response->assertRedirect(route('admin.settings'));
        $this->assertTrue(FeatureManager::isAnalyticsEnabled());
    }

    public function test_analytics_tab_is_visible_in_nav_when_enabled(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('analytics.index'));
        $response->assertSee('Analytics');
    }

    public function test_analytics_tab_is_hidden_in_nav_when_disabled(): void
    {
        FeatureManager::setAnalyticsEnabled(false);
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee(route('analytics.index'));
    }

    public function test_authenticated_user_can_access_analytics_when_enabled(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $user = User::factory()->create(['role' => 'Brand Manager']);
        $brand = Brand::create([
            'name' => 'Acme Agency',
            'slug' => 'acme-agency',
        ]);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Social Campaign',
            'workflow_type' => 'campaign',
            'status' => 'In Progress',
        ]);
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Instagram Reel Deliverable',
            'status' => 'In Progress',
            'approval_stage' => 'Assignee',
            'priority' => 'High',
            'deadline' => now()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Analytics');
        $response->assertSee('Active');
        $response->assertSee('Instagram Reel Deliverable');
    }

    public function test_regular_user_cannot_access_analytics_when_disabled(): void
    {
        FeatureManager::setAnalyticsEnabled(false);
        $user = User::factory()->create(['role' => 'Brand Manager']);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_access_analytics_even_when_feature_disabled_for_preview(): void
    {
        FeatureManager::setAnalyticsEnabled(false);
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Analytics Tab is currently DISABLED for standard users');
    }

    public function test_analytics_csv_export(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $user = User::factory()->create(['role' => 'Brand Manager']);
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Test Project', 'workflow_type' => 'campaign']);
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Hero Banner Video',
            'status' => 'Done',
            'priority' => 'Urgent',
        ]);

        $response = $this->actingAs($user)->get(route('analytics.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Hero Banner Video', $content);
        $this->assertStringContainsString('Test Brand', $content);
        $this->assertStringContainsString('Deliverable ID', $content);
        $this->assertStringContainsString('Open Date', $content);
        $this->assertStringContainsString('Due Date', $content);
        $this->assertStringContainsString('Closed Date', $content);
    }

    public function test_managers_can_see_brand_wise_and_project_wise_reports(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Brand Manager']);

        $brand = Brand::create(['name' => 'Omega Watches', 'slug' => 'omega-watches']);
        $project = Project::create([
            'brand_id' => $brand->id,
            'name' => 'Summer Campaign 2026',
            'workflow_type' => 'campaign',
        ]);
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Watch Hero Reel',
            'status' => 'In Progress',
            'approval_stage' => 'Designer',
            'priority' => 'High',
        ]);

        $response = $this->actingAs($manager)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Brand-Wise Report');
        $response->assertSee('Project-Wise Report');
        $response->assertSee('Omega Watches');
        $response->assertSee('Summer Campaign 2026');
        $response->assertSee('Analytics & Reports');
    }

    public function test_individual_contributors_see_individual_reports_scoped_to_their_work(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $writer = User::factory()->create(['role' => 'Writer', 'name' => 'Alice Writer']);
        $otherWriter = User::factory()->create(['role' => 'Writer', 'name' => 'Bob Other']);

        $brand = Brand::create(['name' => 'Alpha Brand', 'slug' => 'alpha-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Alpha Project', 'workflow_type' => 'campaign']);

        // Alice's deliverable
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Alice Exclusive Article',
            'writer_id' => $writer->id,
            'status' => 'In Progress',
            'approval_stage' => 'Writer',
        ]);

        // Bob's deliverable
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Bob Secret Task',
            'writer_id' => $otherWriter->id,
            'status' => 'In Progress',
            'approval_stage' => 'Writer',
        ]);

        $response = $this->actingAs($writer)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('My Performance', false);
        $response->assertSee('Alice Exclusive Article');
        $response->assertDontSee('Bob Secret Task');
        $response->assertDontSee('Brand-Wise Report');
        $response->assertDontSee('Project-Wise Report');
    }

    public function test_individual_contributors_cannot_snoop_other_users_via_query_params(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $designer = User::factory()->create(['role' => 'Designer', 'name' => 'David Designer']);
        $otherUser = User::factory()->create(['role' => 'Designer', 'name' => 'Eve Designer']);

        $brand = Brand::create(['name' => 'Beta Brand', 'slug' => 'beta-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Beta Project', 'workflow_type' => 'campaign']);

        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'David Artwork Concept',
            'designer_id' => $designer->id,
            'status' => 'In Progress',
        ]);

        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Eve Confidential Visual',
            'designer_id' => $otherUser->id,
            'status' => 'In Progress',
        ]);

        // David tries to query Eve's user_id
        $response = $this->actingAs($designer)->get(route('analytics.index', ['user_id' => $otherUser->id]));

        $response->assertStatus(200);
        $response->assertSee('My Performance', false);
        $response->assertSee('David Artwork Concept');
        $response->assertDontSee('Eve Confidential Visual');
    }

    public function test_reports_show_simplified_active_completed_and_closed_states(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Delta Brand', 'slug' => 'delta-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Delta Project', 'workflow_type' => 'campaign']);

        // 1 Active
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Active Deliverable',
            'status' => 'In Progress',
            'approval_stage' => 'Assignee',
        ]);

        // 1 Completed
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Completed Deliverable',
            'status' => 'Done',
            'approval_stage' => 'Designer',
        ]);

        // 1 Closed
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Closed Deliverable',
            'status' => 'Done',
            'approval_stage' => 'Closed',
        ]);

        $response = $this->actingAs($manager)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Active');
        $response->assertSee('Completed');
        $response->assertSee('Closed');
        $response->assertSee('Active Deliverable');
        $response->assertSee('Completed Deliverable');
        $response->assertSee('Closed Deliverable');
    }

    public function test_reports_filter_by_date(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Epsilon Brand', 'slug' => 'epsilon-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Epsilon Project', 'workflow_type' => 'campaign']);

        // Old deliverable created 60 days ago
        $old = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Old Ancient Task',
            'status' => 'Done',
        ]);
        $old->timestamps = false;
        $old->created_at = now()->subDays(60);
        $old->save();

        // Recent deliverable created 2 days ago
        $recent = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Recent Fresh Task',
            'status' => 'In Progress',
        ]);
        $recent->timestamps = false;
        $recent->created_at = now()->subDays(2);
        $recent->save();

        // Filter for last 7 days
        $response = $this->actingAs($manager)->get(route('analytics.index', ['period' => '7d']));

        $response->assertStatus(200);
        $response->assertSee('Recent Fresh Task');
        $response->assertDontSee('Old Ancient Task');
    }

    public function test_view_button_in_deliverables_matrix_opens_details_and_does_not_link_to_project_workflow(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Details Brand', 'slug' => 'details-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Details Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Specific Detail Test Item',
            'status' => 'In Progress',
            'approval_stage' => 'Assign',
            'priority' => 'High',
            'concept' => 'Master Concept Idea',
            'caption' => 'Exciting social caption',
            'post_copy' => 'Full compelling post copy body',
        ]);

        $response = $this->actingAs($manager)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Specific Detail Test Item');
        // Ensure View button uses viewDeliverableDetails and not the project deliverable route
        $response->assertSee('viewDeliverableDetails(' . $deliverable->id . ')', false);
        $response->assertDontSee('href="' . route('deliverables.show', $deliverable->id) . '"', false);
    }

    public function test_analytics_deliverable_show_json_returns_complete_details(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Omega Brand', 'slug' => 'omega-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Omega Project', 'workflow_type' => 'campaign']);
        $writer = User::factory()->create(['role' => 'Writer', 'name' => 'Alice Writer']);
        $designer = User::factory()->create(['role' => 'Designer', 'name' => 'Bob Designer']);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Grand Reveal Carousel',
            'status' => 'In Progress',
            'approval_stage' => 'Designer',
            'priority' => 'Urgent',
            'writer_id' => $writer->id,
            'designer_id' => $designer->id,
            'concept' => 'Innovative Launch Concept',
            'caption' => 'Watch this space for the big reveal!',
            'post_copy' => 'Exclusive copy previewing the brand transformation.',
            'notes' => 'Ensure vibrant brand colors.',
            'progress_percent' => 50,
        ]);

        $response = $this->actingAs($manager)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(route('analytics.deliverables.show', $deliverable->id));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $deliverable->id,
            'title' => 'Grand Reveal Carousel',
            'state_label' => 'Active',
            'priority' => 'Urgent',
            'brand' => [
                'name' => 'Omega Brand',
            ],
            'project' => [
                'name' => 'Omega Project',
                'workflow_type' => 'campaign',
            ],
            'team' => [
                'writer' => [
                    'name' => 'Alice Writer',
                ],
                'designer' => [
                    'name' => 'Bob Designer',
                ],
            ],
            'content' => [
                'concept' => 'Innovative Launch Concept',
                'caption' => 'Watch this space for the big reveal!',
                'post_copy' => 'Exclusive copy previewing the brand transformation.',
            ],
        ]);
    }

    public function test_analytics_deliverable_show_html_renders_read_only_details_view(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'HTML Brand', 'slug' => 'html-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'HTML Project', 'workflow_type' => 'pitch']);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Pitch Deck Deliverable',
            'status' => 'Done',
            'approval_stage' => 'Closed',
            'priority' => 'Medium',
            'concept' => 'Pitch winning concept',
        ]);

        $response = $this->actingAs($manager)->get(route('analytics.deliverables.show', $deliverable->id));

        $response->assertStatus(200);
        $response->assertSee('Pitch Deck Deliverable');
        $response->assertSee('HTML Brand');
        $response->assertSee('Pitch winning concept');
        $response->assertSee('Closed');
        // Must NOT render workflow action submission forms
        $response->assertDontSee('submitStageForm');
    }

    public function test_unauthorized_user_cannot_view_analytics_deliverable_details(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $unrelatedUser = User::factory()->create(['role' => 'Writer', 'name' => 'Stranger']);
        $brand = Brand::create(['name' => 'Secret Brand', 'slug' => 'secret-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Secret Project', 'workflow_type' => 'retainer']);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Confidential Deliverable',
            'status' => 'In Progress',
            'approval_stage' => 'Writer',
        ]);

        $response = $this->actingAs($unrelatedUser)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(route('analytics.deliverables.show', $deliverable->id));

        $response->assertStatus(403);
    }

    public function test_reports_show_open_date_due_date_and_closed_date(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Date Brand', 'slug' => 'date-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Date Project', 'workflow_type' => 'campaign']);

        // Closed deliverable with specific deadline
        $closedTask = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Finished Task With All Dates',
            'status' => 'Done',
            'approval_stage' => 'Closed',
            'deadline' => '2026-11-15',
        ]);
        $closedTask->timestamps = false;
        $closedTask->created_at = \Carbon\Carbon::parse('2026-10-01 10:00:00');
        $closedTask->updated_at = \Carbon\Carbon::parse('2026-10-20 16:30:00');
        $closedTask->save();

        // Active deliverable with deadline
        $activeTask = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Ongoing Task With Due Date',
            'status' => 'In Progress',
            'approval_stage' => 'Assign',
            'deadline' => '2026-12-01',
        ]);
        $activeTask->timestamps = false;
        $activeTask->created_at = \Carbon\Carbon::parse('2026-10-10 09:00:00');
        $activeTask->save();

        // Verify HTML matrix table renders headers and dates
        $response = $this->actingAs($manager)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Open Date');
        $response->assertSee('Due Date');
        $response->assertSee('Closed Date');

        // Formatted date assertions for closed deliverable
        $response->assertSee('Oct 01, 2026'); // Open date
        $response->assertSee('Nov 15, 2026'); // Due date
        $response->assertSee('Oct 20, 2026'); // Closed date

        // Formatted date assertions for active deliverable
        $response->assertSee('Oct 10, 2026'); // Open date
        $response->assertSee('Dec 01, 2026'); // Due date

        // Check JSON details endpoint
        $jsonResponse = $this->actingAs($manager)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(route('analytics.deliverables.show', $closedTask->id));

        $jsonResponse->assertStatus(200);
        $jsonResponse->assertJson([
            'schedule' => [
                'open_date' => 'Oct 01, 2026',
                'due_date' => 'Nov 15, 2026',
                'closed_date' => 'Oct 20, 2026',
            ],
        ]);
    }

    public function test_table_removes_stage_priority_assignee_columns_and_renders_charts(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Chart Brand', 'slug' => 'chart-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Chart Project', 'workflow_type' => 'campaign']);
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Sample Graphic Task',
            'post_type' => 'Carousel',
            'status' => 'In Progress',
            'approval_stage' => 'Assign',
            'priority' => 'High',
            'assignee_name' => 'John Designer',
        ]);

        $response = $this->actingAs($manager)->get(route('analytics.index'));

        $response->assertStatus(200);

        // Verify the 3 columns are removed from the deliverables list table header
        $response->assertDontSee('<th class="py-3 px-3">Stage</th>', false);
        $response->assertDontSee('<th class="py-3 px-3">Priority</th>', false);
        $response->assertDontSee('<th class="py-3 px-3">Assignee</th>', false);

        // Verify the retained columns exist
        $response->assertSee('Deliverable');
        $response->assertSee('Brand & Project', false);
        $response->assertSee('State');
        $response->assertSee('Open Date');
        $response->assertSee('Due Date');
        $response->assertSee('Closed Date');

        // Verify analytic diagrams, graphs and pie charts are present
        $response->assertSee('Analytics Diagrams & Charts', false);
        $response->assertSee('Work Status Distribution');
        $response->assertSee('Activity Timeline & Velocity', false);
        $response->assertSee('Deliverable Formats & Types', false);
        $response->assertSee('Brand Workload & Completion', false);
        $response->assertSee('id="statusPieChart"', false);
        $response->assertSee('id="timelineActivityChart"', false);
        $response->assertSee('id="typesPieChart"', false);
        $response->assertSee('id="brandBarChart"', false);
    }

    public function test_reports_and_details_do_not_display_work_hours(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Effort Brand', 'slug' => 'effort-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Effort Project', 'workflow_type' => 'campaign']);

        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Deliverable Without Work Hours',
            'status' => 'In Progress',
            'approval_stage' => 'AM/BD',
            'work_hours' => 12.5,
        ]);

        // Check index view
        $indexResponse = $this->actingAs($manager)->get(route('analytics.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee('Work Hours Logged');
        $indexResponse->assertDontSee('hours logged');

        // Check deliverable standalone detail view
        $detailResponse = $this->actingAs($manager)->get(route('analytics.deliverables.show', $deliverable->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertDontSee('Work Hours Logged');
        $detailResponse->assertSee('Quality & Assets', false);
    }

    public function test_closed_deliverables_count_as_completed_in_pie_chart_and_reports(): void
    {
        FeatureManager::setAnalyticsEnabled(true);
        $manager = User::factory()->create(['role' => 'Admin']);
        $brand = Brand::create(['name' => 'Completion Brand', 'slug' => 'completion-brand']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Completion Project', 'workflow_type' => 'campaign']);

        // 13 Active deliverables
        for ($i = 1; $i <= 13; $i++) {
            Deliverable::create([
                'project_id' => $project->id,
                'title' => "Active Task {$i}",
                'status' => 'In Progress',
                'approval_stage' => 'AM/BD',
            ]);
        }

        // 1 Closed deliverable
        Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Closed Task 1',
            'status' => 'Done',
            'approval_stage' => 'Closed',
        ]);

        $response = $this->actingAs($manager)->get(route('analytics.index'));
        $response->assertStatus(200);

        /** @var \App\Services\AnalyticsService $service */
        $service = app(\App\Services\AnalyticsService::class);
        $data = $service->getAnalyticsData([], $manager);

        // Status Pie must reflect Active (13) and Completed (1) where close means completed
        $this->assertEquals(['Active', 'Completed'], $data['charts']['status_pie']['labels']);
        $this->assertEquals([13, 1], $data['charts']['status_pie']['data']);
        $this->assertEquals([93, 7], $data['charts']['status_pie']['percentages']);
        $this->assertEquals(14, $data['charts']['status_pie']['total']);

        // KPIs must count the 1 closed deliverable under Completed
        $this->assertEquals(13, $data['kpis']['active_deliverables']);
        $this->assertEquals(1, $data['kpis']['completed_deliverables']);
    }
}
