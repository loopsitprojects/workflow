<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Project;
use App\Models\Deliverable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandDeliverableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to assign
        $admin = User::where('email', 'admin@loops.com')->first();
        $bm1 = User::where('email', 'brandmanager1@loops.com')->first();
        $bm2 = User::where('email', 'brandmanager2@loops.com')->first();
        $designer1 = User::where('email', 'designer1@loops.com')->first();
        $designer2 = User::where('email', 'designer2@loops.com')->first();
        $writer1 = User::where('email', 'writer1@loops.com')->first();
        $writer2 = User::where('email', 'writer2@loops.com')->first();
        $approver1 = User::where('email', 'approver1@loops.com')->first();
        $approver2 = User::where('email', 'approver2@loops.com')->first();

        // --- Brand 1: Lumina Digital ---
        $brand1 = Brand::create([
            'name' => 'Lumina Digital',
            'slug' => 'lumina-digital',
            'location' => 'Dubai, UAE',
            'description' => 'A leading digital solutions provider specializing in AI and cloud infrastructure.',
            'active_projects' => 1,
            'total_members' => 5,
            'health_score' => 'Stable',
        ]);

        // Attach members to brand
        if ($bm1) $brand1->members()->attach($bm1->id);
        if ($designer1) $brand1->members()->attach($designer1->id);
        if ($writer1) $brand1->members()->attach($writer1->id);
        if ($approver1) $brand1->members()->attach($approver1->id);
        if ($admin) $brand1->members()->attach($admin->id);

        $project1 = Project::create([
            'brand_id' => $brand1->id,
            'name' => 'Q2 Creative Campaign',
            'job_number' => 'LD-2024-001',
            'description' => 'Main marketing campaign for the second quarter focusing on cloud acceleration.',
            'status' => 'In Progress',
            'deadline' => now()->addMonths(2),
            'priority' => 'High',
            'workflow_type' => 'retainer',
            'brand_manager_id' => $bm1?->id,
            'designer_id' => $designer1?->id,
            'writer_id' => $writer1?->id,
            'approver_id' => $approver1?->id,
            'lead_id' => $admin?->id,
        ]);

        // Deliverables for Project 1
        Deliverable::create([
            'project_id' => $project1->id,
            'title' => 'Social Media KV Design',
            'description' => 'Primary key visual for LinkedIn and Instagram ads.',
            'status' => 'In Progress',
            'approval_stage' => 'Designer',
            'priority' => 'High',
            'post_type' => 'KV',
            'designer_id' => $designer1?->id,
            'brand_manager_id' => $bm1?->id,
            'progress_percent' => 60,
        ]);

        Deliverable::create([
            'project_id' => $project1->id,
            'title' => 'Campaign Copywriting',
            'description' => 'Ad copies for all social channels.',
            'status' => 'Review',
            'approval_stage' => 'Approver',
            'priority' => 'Medium',
            'post_type' => 'Static Post',
            'writer_id' => $writer1?->id,
            'approver_id' => $approver1?->id,
            'progress_percent' => 80,
        ]);

        Deliverable::create([
            'project_id' => $project1->id,
            'title' => 'Technical Whitepaper',
            'description' => 'Detailed whitepaper on AI-driven cloud optimization.',
            'status' => 'To Do',
            'approval_stage' => 'Writer',
            'priority' => 'Standard',
            'post_type' => 'Ideation/Brainstorm',
            'writer_id' => $writer1?->id,
            'progress_percent' => 10,
        ]);

        // --- Brand 2: Velvet Bloom ---
        $brand2 = Brand::create([
            'name' => 'Velvet Bloom',
            'slug' => 'velvet-bloom',
            'location' => 'Paris, France',
            'description' => 'Luxury fashion brand known for sustainable fabrics and timeless designs.',
            'active_projects' => 1,
            'total_members' => 4,
            'health_score' => 'Excellent',
        ]);

        // Attach members to brand
        if ($bm2) $brand2->members()->attach($bm2->id);
        if ($designer2) $brand2->members()->attach($designer2->id);
        if ($writer2) $brand2->members()->attach($writer2->id);
        if ($admin) $brand2->members()->attach($admin->id);

        $project2 = Project::create([
            'brand_id' => $brand2->id,
            'name' => 'Spring Collection Refresh',
            'job_number' => 'VB-SPR-24',
            'description' => 'Refreshing the brand assets for the upcoming Spring 2024 collection.',
            'status' => 'Planning',
            'deadline' => now()->addMonths(3),
            'priority' => 'Standard',
            'workflow_type' => 'campaign',
            'brand_manager_id' => $bm2?->id,
            'designer_id' => $designer2?->id,
            'writer_id' => $writer2?->id,
            'approver_id' => $approver2?->id,
            'lead_id' => $admin?->id,
        ]);

        // Deliverables for Project 2
        Deliverable::create([
            'project_id' => $project2->id,
            'title' => 'Logo Modernization',
            'description' => 'Subtle refresh of the brand logo for better digital legibility.',
            'status' => 'Completed',
            'approval_stage' => 'Closed',
            'priority' => 'High',
            'post_type' => 'KV',
            'designer_id' => $designer2?->id,
            'progress_percent' => 100,
            'is_ready' => true,
        ]);

        Deliverable::create([
            'project_id' => $project2->id,
            'title' => 'Lookbook Layout',
            'description' => 'Digital lookbook for the Spring collection.',
            'status' => 'In Progress',
            'approval_stage' => 'Designer',
            'priority' => 'Medium',
            'post_type' => 'Carousel',
            'designer_id' => $designer2?->id,
            'progress_percent' => 40,
        ]);

        Deliverable::create([
            'project_id' => $project2->id,
            'title' => 'Social Media Teasers',
            'description' => 'Short video teasers for Instagram Reels.',
            'status' => 'To Do',
            'approval_stage' => 'Writer',
            'priority' => 'Medium',
            'post_type' => 'Reels',
            'writer_id' => $writer2?->id,
            'progress_percent' => 0,
        ]);
    }
}
