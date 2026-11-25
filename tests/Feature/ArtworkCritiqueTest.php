<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArtworkCritiqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_request_artwork_critique()
    {
        $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Sample Task',
            'status' => 'To Do',
        ]);

        $response = $this->json('POST', route('deliverables.critique-artwork', $deliverable));
        $response->assertStatus(401);
    }

    public function test_artwork_critique_returns_helpful_message_when_gemini_key_is_missing()
    {
        $user = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Brand Test 2', 'slug' => 'brand-test-2']);
        $user->brands()->attach($brand->id);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Sample Task',
            'status' => 'To Do',
            'final_designs' => 'test_artwork.png',
        ]);

        config(['services.gemini.key' => null]);

        $response = $this->actingAs($user)->json('POST', route('deliverables.critique-artwork', $deliverable));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('GEMINI_API_KEY', $data['message']);
    }

    public function test_artwork_critique_succeeds_with_gemini_vision_mock()
    {
        $user = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Brand Test 3', 'slug' => 'brand-test-3']);
        $user->brands()->attach($brand->id);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Sample Task',
            'status' => 'To Do',
            'final_designs' => 'https://via.placeholder.com/300.png',
        ]);

        config(['services.gemini.key' => 'fake_gemini_key']);

        Http::fake([
            'via.placeholder.com/*' => Http::response('fake_image_bytes', 200, ['Content-Type' => 'image/png']),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'score' => 8.5,
                                        'verdict' => 'Strong composition with good visual flow.',
                                        'strengths' => ['Clear focal point', 'Good brand contrast'],
                                        'hierarchy' => 'Eye moves naturally from headline to product.',
                                        'typography' => 'Clean sans-serif typography.',
                                        'alignment' => 'Directly matches the creative brief.',
                                        'suggestions' => [
                                            'Increase margin around CTA button',
                                            'Boost subtitle contrast slightly',
                                        ],
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($user)->json('POST', route('deliverables.critique-artwork', $deliverable));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(8.5, $data['critique']['score']);
        $this->assertCount(2, $data['critique']['suggestions']);
    }
}
