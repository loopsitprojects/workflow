<?php

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('designer dashboard displays main deliverable deadline as data and tracks designer deadline in countdown', function () {
    $brand = Brand::create(['name' => 'Brand Design Test', 'slug' => 'brand-design-test']);
    $designer = User::factory()->create(['name' => 'Bravin Designer', 'role' => 'Designer']);
    $manager = User::factory()->create(['role' => 'Brand Manager']);
    $brand->members()->attach([$designer->id, $manager->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Design Project 1',
        'workflow_type' => 'campaign',
    ]);

    // 1. Deliverable with main deadline in 10 days, but designer deadline tomorrow (1 day left)
    $mainDate = now()->addDays(10)->format('Y-m-d');
    $desDate = now()->addDays(1)->format('Y-m-d');
    $taskWithDesDeadline = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable With Designer Deadline',
        'flow_type' => 'direct_design',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'deadline' => $mainDate,
        'designer_deadline' => $desDate,
        'progress_percent' => 25,
    ]);

    // 2. Deliverable assigned to designer without a designer deadline (only main deadline in 2 days)
    $taskWithoutDesDeadline = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable Without Designer Deadline',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'deadline' => now()->addDays(2)->format('Y-m-d'),
        'progress_percent' => 25,
    ]);

    // 3. Deliverable under revision with revision deadline today
    $taskWithRevDeadline = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable In Revision',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'deadline' => now()->addDays(5)->format('Y-m-d'),
        'designer_deadline' => now()->addDays(3)->format('Y-m-d'),
        'revision_deadline' => now()->format('Y-m-d'),
        'revision_instructions' => 'Change text color',
        'revisions' => 1,
        'progress_percent' => 25,
    ]);

    // 4. Deliverable where designer deadline equals deliverable deadline (e.g. Direct Design fast-track)
    $sameDate = now()->addDays(4)->format('Y-m-d');
    $taskSameDate = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Deliverable With Same Date',
        'approval_stage' => 'Designer',
        'designer_id' => $designer->id,
        'deadline' => $sameDate,
        'designer_deadline' => $sameDate,
        'progress_percent' => 25,
    ]);

    $response = $this->actingAs($designer)->get(route('dashboard'));
    $response->assertOk();

    // Verify stage is NOT shown on deliverable cards
    $response->assertDontSee('Stage: DESIGNER');
    $response->assertDontSee('Stage: ASSIGN');

    // Verify main deliverable deadline is visible as date metadata when different
    $response->assertSee('Due:');
    $response->assertSee(\Carbon\Carbon::parse($mainDate)->format('M d, Y'));

    // Verify countdown tracks designer deadline (Des: 1d left), NOT the 10d main deadline
    $response->assertSee('Des: 1d left');

    // Verify deliverable without designer deadline falls back to deliverable countdown (2d left) instead of "No date"
    $response->assertSee('2d left');

    // Verify deliverable in revision tracks revision deadline (Rev: Due Today)
    $response->assertSee('Rev: Due Today');
});

test('writer dashboard tracks revision deadline when under revision and deliverable deadline otherwise', function () {
    $brand = Brand::create(['name' => 'Brand Writer Test', 'slug' => 'brand-writer-test']);
    $writer = User::factory()->create(['name' => 'Wendy Writer', 'role' => 'Writer']);
    $brand->members()->attach([$writer->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Writer Project',
        'workflow_type' => 'campaign',
    ]);

    // Deliverable without revision (3 days left)
    $taskNormal = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Writer Normal Deliverable',
        'approval_stage' => 'Assignee',
        'writer_id' => $writer->id,
        'deadline' => now()->addDays(3)->format('Y-m-d'),
        'progress_percent' => 10,
    ]);

    // Deliverable with revision (1 day left)
    $taskRev = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Writer Revision Deliverable',
        'approval_stage' => 'Assignee',
        'writer_id' => $writer->id,
        'deadline' => now()->addDays(10)->format('Y-m-d'),
        'revision_deadline' => now()->addDays(1)->format('Y-m-d'),
        'revision_instructions' => 'Revise paragraph 2',
        'revisions' => 1,
        'progress_percent' => 10,
    ]);

    $response = $this->actingAs($writer)->get(route('dashboard'));
    $response->assertOk();

    // Normal deliverable tracks main deadline (3d left)
    $response->assertSee('3d left');

    // Deliverable in revision tracks revision deadline (Rev: 1d left)
    $response->assertSee('Rev: 1d left');

    // Both deliverables have their Due: date data shown
    $response->assertSee('Due:');
});

