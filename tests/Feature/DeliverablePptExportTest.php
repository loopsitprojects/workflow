<?php

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ppt export omits image links when artwork is visible and displays compact web and video links', function () {
    $brand = Brand::create(['name' => 'Seylan Bank', 'slug' => 'seylan-bank']);
    $user = User::factory()->create(['role' => 'Brand Manager']);
    $brand->members()->attach([$user->id]);

    $project = Project::create([
        'brand_id' => $brand->id,
        'name' => 'Seylan Q4',
        'workflow_type' => 'retainer',
    ]);

    // Create a temporary dummy image so pptLocalImagePath resolves it as a visible image
    $tempImage = tempnam(sys_get_temp_dir(), 'test_art_') . '.png';
    $img = imagecreatetruecolor(100, 100);
    imagepng($img, $tempImage);
    imagedestroy($img);

    $deliverable = Deliverable::create([
        'project_id' => $project->id,
        'title' => 'Carousel 1',
        'concept' => 'Seylan Diwali Campaign',
        'caption' => 'Wishing you light and prosperity',
        'post_copy' => 'Celebrate Diwali with Seylan',
        'final_designs' => json_encode([$tempImage]),
        'final_designs_link' => 'https://www.google.com/',
        'reference' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'approval_stage' => 'Final Approval',
        'progress_percent' => 90,
    ]);

    $response = $this->actingAs($user)->get(route('deliverables.export.ppt', $deliverable));
    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

    // Also verify batch PPT export
    $subtask = Deliverable::create([
        'project_id' => $project->id,
        'parent_deliverable_id' => $deliverable->id,
        'title' => 'Slide 1',
        'concept' => 'Slide 1 Concept',
        'final_designs' => json_encode([$tempImage]),
        'final_designs_link' => 'https://figma.com/file/12345',
        'approval_stage' => 'Final Approval',
        'progress_percent' => 90,
    ]);

    $batchResponse = $this->actingAs($user)->get(route('deliverables.export-batch.ppt', $deliverable));
    $batchResponse->assertOk();
    $batchResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

    // Clean up temporary image
    @unlink($tempImage);
});

test('ppt slide contains web links, video links, and no redundant image link', function () {
    $tempImage = tempnam(sys_get_temp_dir(), 'test_art_') . '.png';
    $img = imagecreatetruecolor(100, 100);
    imagepng($img, $tempImage);
    imagedestroy($img);

    $deliverable = new Deliverable([
        'title' => 'Carousel Test',
        'concept' => 'Concept text',
        'caption' => 'Caption text',
        'post_copy' => 'Copy text',
        'final_designs' => json_encode([$tempImage]),
        'final_designs_link' => 'https://www.google.com/',
        'reference' => json_encode(['https://www.youtube.com/watch?v=12345', 'https://example.com/ref']),
    ]);

    $prs = new \PhpOffice\PhpPresentation\PhpPresentation();
    $controller = app(\App\Http\Controllers\DeliverableController::class);

    // Call private buildPptSlide via reflection
    $refMethod = new ReflectionMethod(\App\Http\Controllers\DeliverableController::class, 'buildPptSlide');
    $refMethod->invoke($controller, $prs->getActiveSlide(), $deliverable);

    $slide = $prs->getActiveSlide();
    $shapeTexts = [];
    $hyperlinkUrls = [];
    $hasImageShape = false;

    foreach ($slide->getShapeCollection() as $shape) {
        if ($shape instanceof \PhpOffice\PhpPresentation\Shape\Drawing\File) {
            $hasImageShape = true;
        } elseif ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
            foreach ($shape->getParagraphs() as $p) {
                foreach ($p->getRichTextElements() as $elem) {
                    if ($elem instanceof \PhpOffice\PhpPresentation\Shape\RichText\Run) {
                        $shapeTexts[] = $elem->getText();
                        if ($elem->getHyperlink() && $elem->getHyperlink()->getUrl() !== '') {
                            $hyperlinkUrls[] = $elem->getHyperlink()->getUrl();
                            // Check that link font size is small (9pt)
                            expect($elem->getFont()->getSize())->toBe(9);
                        }
                    }
                }
            }
        }
    }

    // 1. Artwork image shape was created
    expect($hasImageShape)->toBeTrue();

    // 2. Redundant image URL was NOT added to text
    expect(in_array($tempImage, $shapeTexts))->toBeFalse();
    expect(in_array('FINAL ARTWORK FILE', $shapeTexts))->toBeFalse();
    expect(in_array('FINAL ARTWORK FILES', $shapeTexts))->toBeFalse();

    // 3. Web links and video links are present
    expect($shapeTexts)->toContain('FINAL DESIGNS LINK');
    expect($shapeTexts)->toContain('https://www.google.com/');
    expect($shapeTexts)->toContain('VIDEO LINK');
    expect($shapeTexts)->toContain('https://www.youtube.com/watch?v=12345');
    expect($shapeTexts)->toContain('REFERENCE LINK');
    expect($shapeTexts)->toContain('https://example.com/ref');

    // 4. Verify headings are bold black (FF0F172A)
    foreach ($slide->getShapeCollection() as $shape) {
        if ($shape instanceof \PhpOffice\PhpPresentation\Shape\RichText) {
            foreach ($shape->getParagraphs() as $p) {
                foreach ($p->getRichTextElements() as $elem) {
                    if ($elem instanceof \PhpOffice\PhpPresentation\Shape\RichText\Run) {
                        if (in_array($elem->getText(), ['CONCEPT', 'CAPTION', 'COPY', 'FINAL DESIGNS LINK', 'VIDEO LINK', 'REFERENCE LINK', 'FINAL ARTWORK'])) {
                            expect($elem->getFont()->getColor()->getARGB())->toBe('FF0F172A');
                        }
                    }
                }
            }
        }
    }

    // 5. Clickable hyperlinks were attached
    expect($hyperlinkUrls)->toContain('https://www.google.com/');
    expect($hyperlinkUrls)->toContain('https://www.youtube.com/watch?v=12345');
    expect($hyperlinkUrls)->toContain('https://example.com/ref');

    @unlink($tempImage);
});
