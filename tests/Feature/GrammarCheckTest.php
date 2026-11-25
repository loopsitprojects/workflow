<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GrammarCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_check_grammar()
    {
        $response = $this->json('POST', route('deliverables.check-grammar'), [
            'fields' => ['copy' => 'Sample test'],
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_check_grammar_with_languagetool()
    {
        $user = User::factory()->create([
            'role' => 'Writer',
        ]);

        // Mock LanguageTool API response
        Http::fake([
            'api.languagetool.org/*' => Http::response([
                'matches' => [
                    [
                        'message' => 'Possible spelling mistake found.',
                        'shortMessage' => 'Spelling mistake',
                        'offset' => 0,
                        'length' => 4,
                        'replacements' => [['value' => 'This']],
                        'rule' => [
                            'issueType' => 'misspelling',
                            'category' => ['id' => 'TYPOS', 'name' => 'Possible Typo'],
                        ],
                        'sentence' => 'Thiss is a test.',
                    ],
                    [
                        'message' => 'Grammar error found.',
                        'shortMessage' => 'Grammar error',
                        'offset' => 5,
                        'length' => 3,
                        'replacements' => [['value' => 'are']],
                        'rule' => [
                            'issueType' => 'grammar',
                            'category' => ['id' => 'GRAMMAR', 'name' => 'Grammar'],
                        ],
                        'sentence' => 'Thiss are a test.',
                    ]
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->json('POST', route('deliverables.check-grammar'), [
            'fields' => [
                'concept' => '<p>Thiss are a test.</p>',
            ],
            'language' => 'en-US',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertEquals(2, $data['total_issues']);
        $this->assertArrayHasKey('concept', $data['fields']);
        $this->assertCount(2, $data['fields']['concept']);
        $this->assertEquals('spelling', $data['fields']['concept'][0]['category']);
        $this->assertEquals('This', $data['fields']['concept'][0]['replacements'][0]);
        $this->assertEquals('grammar', $data['fields']['concept'][1]['category']);
    }

    public function test_authenticated_user_can_check_grammar_with_gemini()
    {
        $user = User::factory()->create([
            'role' => 'Writer',
        ]);

        config(['services.gemini.key' => 'fake_gemini_api_key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'issues' => [
                                            [
                                                'category' => 'spelling',
                                                'category_name' => 'Spelling',
                                                'message' => 'Typo in brand copy',
                                                'original' => 'campain',
                                                'replacements' => ['campaign'],
                                                'sentence' => 'Join our new campain.',
                                            ],
                                            [
                                                'category' => 'style',
                                                'category_name' => 'Copy Polish',
                                                'message' => 'Use active voice for better impact',
                                                'original' => 'was made by us',
                                                'replacements' => ['we created'],
                                                'sentence' => 'This was made by us.',
                                            ]
                                        ]
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($user)->json('POST', route('deliverables.check-grammar'), [
            'fields' => [
                'copy' => 'Join our new campain. This was made by us.',
            ],
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertEquals('gemini', $data['engine']);
        $this->assertEquals(2, $data['total_issues']);
        $this->assertEquals('campaign', $data['fields']['copy'][0]['replacements'][0]);
        $this->assertEquals('Copy Polish', $data['fields']['copy'][1]['category_name']);
    }
}
