<?php

use App\Services\HtmlSanitizer;

test('it preserves legitimate formatting from Quill editor', function () {
    $input = '<p>Hello <strong>World</strong>, this is an <em>important</em> brief.</p><ul><li>Item 1</li><li>Item 2</li></ul>';
    $output = HtmlSanitizer::clean($input);

    expect($output)->toContain('<p>Hello <strong>World</strong>')
        ->toContain('<em>important</em>')
        ->toContain('<ul><li>Item 1</li><li>Item 2</li></ul>');
});

test('it strips script tags and their contents', function () {
    $input = '<p>Normal text</p><script>alert("XSS")</script>';
    $output = HtmlSanitizer::clean($input);

    expect($output)->not->toContain('<script>')
        ->not->toContain('alert("XSS")')
        ->toContain('<p>Normal text</p>');
});

test('it strips inline event handlers like onerror and onload', function () {
    $input = '<p><img src="x" onerror="alert(1)">Text</p><b onclick="evil()">Click</b>';
    $output = HtmlSanitizer::clean($input);

    expect($output)->not->toContain('onerror')
        ->not->toContain('onclick')
        ->not->toContain('alert(1)')
        ->toContain('Text')
        ->toContain('<b>Click</b>');
});

test('it strips javascript protocol in links', function () {
    $input = '<p><a href="javascript:alert(1)">Click me</a> and <a href="https://example.com" target="_blank">Safe</a></p>';
    $output = HtmlSanitizer::clean($input);

    expect($output)->not->toContain('javascript:alert(1)')
        ->toContain('Click me')
        ->toContain('href="https://example.com"')
        ->toContain('rel="noopener noreferrer"');
});

test('it handles empty or plaintext safely', function () {
    expect(HtmlSanitizer::clean(''))->toBe('')
        ->and(HtmlSanitizer::clean(null))->toBe('')
        ->and(HtmlSanitizer::clean('Just a plain string'))->toBe('Just a plain string');
});
