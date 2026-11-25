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

    public function test_artwork_critique_with_specific_artwork_url()
    {
        $user = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Brand Test 4', 'slug' => 'brand-test-4']);
        $user->brands()->attach($brand->id);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Multi Artwork Task',
            'status' => 'To Do',
            'final_designs' => json_encode(['https://via.placeholder.com/art1.png', 'https://via.placeholder.com/art2.png']),
        ]);

        config(['services.gemini.key' => 'fake_gemini_key']);

        Http::fake([
            'via.placeholder.com/art2.png' => Http::response('fake_image_bytes_2', 200, ['Content-Type' => 'image/png']),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'score' => 9.0,
                                        'verdict' => 'Outstanding branding visual.',
                                        'strengths' => ['Brilliant clarity'],
                                        'hierarchy' => 'Excellent priority',
                                        'typography' => 'Crisp text',
                                        'alignment' => 'Exact match to brand',
                                        'suggestions' => ['No changes needed'],
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($user)->json('POST', route('deliverables.critique-artwork', $deliverable), [
            'artwork_url' => 'https://via.placeholder.com/art2.png',
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(9.0, $data['critique']['score']);
        $this->assertEquals('https://via.placeholder.com/art2.png', $data['artwork_url']);
    }

    public function test_artwork_critique_cross_checks_reference_links_and_reference_images()
    {
        $user = User::factory()->create(['role' => 'Designer']);
        $brand = Brand::create(['name' => 'Brand Cross Check', 'slug' => 'brand-cross-check']);
        $user->brands()->attach($brand->id);
        $project = Project::create(['brand_id' => $brand->id, 'name' => 'Campaign Project', 'workflow_type' => 'campaign']);
        $deliverable = Deliverable::create([
            'project_id' => $project->id,
            'title' => 'Deliverable with References',
            'status' => 'To Do',
            'concept' => 'Minimalist modern watch launch',
            'post_copy' => 'Precision crafted timepieces.',
            'caption' => 'Discover timeless elegance.',
            'reference' => 'https://pinterest.com/pin/12345, https://dribbble.com/shots/67890',
            'reference_file' => 'https://via.placeholder.com/ref1.png',
            'final_designs' => 'https://via.placeholder.com/artwork.png',
        ]);

        config(['services.gemini.key' => 'fake_gemini_key']);

        $recordedGeminiPayload = null;

        Http::fake([
            'via.placeholder.com/ref1.png' => Http::response('fake_reference_bytes', 200, ['Content-Type' => 'image/png']),
            'via.placeholder.com/artwork.png' => Http::response('fake_artwork_bytes', 200, ['Content-Type' => 'image/png']),
            'generativelanguage.googleapis.com/*' => function ($request) use (&$recordedGeminiPayload) {
                $recordedGeminiPayload = json_decode($request->body(), true);
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [
                                        'text' => json_encode([
                                            'score' => 9.2,
                                            'verdict' => 'Superb alignment with reference moodboard.',
                                            'strengths' => ['Faithful color palette', 'Elegant typography'],
                                            'hierarchy' => 'Hero timepiece stands out boldly.',
                                            'typography' => 'Sophisticated serif styling.',
                                            'alignment' => 'Fulfills the minimalist concept completely.',
                                            'reference_fidelity' => 'Color grading and lighting mirror the Pinterest reference closely.',
                                            'suggestions' => ['No major changes needed.'],
                                        ])
                                    ]
                                ]
                            ]
                        ]
                    ]
                ], 200);
            },
        ]);

        $response = $this->actingAs($user)->json('POST', route('deliverables.critique-artwork', $deliverable));
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(9.2, $data['critique']['score']);
        $this->assertEquals('Color grading and lighting mirror the Pinterest reference closely.', $data['critique']['reference_fidelity']);

        // Verify payload sent to Gemini Vision
        $this->assertNotNull($recordedGeminiPayload);
        $parts = $recordedGeminiPayload['contents'][0]['parts'] ?? [];

        // Check that prompt contains reference URLs
        $promptPart = $parts[0]['text'] ?? '';
        $this->assertStringContainsString('https://pinterest.com/pin/12345', $promptPart);
        $this->assertStringContainsString('https://dribbble.com/shots/67890', $promptPart);
        $this->assertStringContainsString('1 reference image(s) attached below', $promptPart);

        // Check that reference image inlineData is included
        $foundRefImagePart = false;
        $foundArtworkImagePart = false;

        foreach ($parts as $part) {
            if (isset($part['text']) && str_contains($part['text'], '[REFERENCE IMAGE #1]')) {
                $foundRefImagePart = true;
            }
            if (isset($part['text']) && str_contains($part['text'], '[SUBMITTED FINAL ARTWORK]')) {
                $foundArtworkImagePart = true;
            }
        }

        $this->assertTrue($foundRefImagePart, 'Reference image header was not found in Gemini payload');
        $this->assertTrue($foundArtworkImagePart, 'Artwork header was not found in Gemini payload');
    }
}
