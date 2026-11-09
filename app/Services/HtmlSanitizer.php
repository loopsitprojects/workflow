<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags.
     */
    protected static array $allowedTags = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li',
        'a', 'span', 'blockquote', 'pre', 'code', 'sub', 'sup',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'div', 'hr'
    ];

    /**
     * Allowed attributes per tag.
     */
    protected static array $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel'],
        'span' => ['class', 'style'],
        'p' => ['class', 'style'],
        'div' => ['class', 'style'],
        'h1' => ['class', 'style'],
        'h2' => ['class', 'style'],
        'h3' => ['class', 'style'],
        'h4' => ['class', 'style'],
        'h5' => ['class', 'style'],
        'h6' => ['class', 'style'],
        'li' => ['class', 'style'],
        'table' => ['class', 'style', 'border', 'cellpadding', 'cellspacing'],
        'th' => ['class', 'style', 'colspan', 'rowspan'],
        'td' => ['class', 'style', 'colspan', 'rowspan'],
    ];

    /**
     * Clean untrusted HTML string, retaining safe formatting and stripping malicious elements/scripts.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Fast path: if no HTML tags exist, return escaped or as-is
        if (!str_contains($html, '<')) {
            return $html;
        }

        // Normalize encoding
        $prevErrors = libxml_use_internal_errors(true);
        $doc = new DOMDocument();

        // Wrap in UTF-8 container to preserve full multibyte and fragment structure
        $wrappedHtml = '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="__sanitizer_root__">' . $html . '</div></body></html>';
        
        $doc->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevErrors);

        $root = $doc->getElementById('__sanitizer_root__');
        if (!$root) {
            return strip_tags($html);
        }

        self::sanitizeNode($root);

        // Extract inner HTML of root container
        $cleanHtml = '';
        foreach ($root->childNodes as $child) {
            $cleanHtml .= $doc->saveHTML($child);
        }

        return $cleanHtml;
    }

    /**
     * Recursively sanitize DOM nodes.
     */
    protected static function sanitizeNode(DOMNode $node): void
    {
        $toRemove = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                /** @var DOMElement $child */
                $tagName = strtolower($child->tagName);

                // Disallow blacklisted/non-whitelisted tags
                if (!in_array($tagName, self::$allowedTags, true)) {
                    $toRemove[] = $child;
                    continue;
                }

                // Sanitize attributes
                $allowedAttrs = self::$allowedAttributes[$tagName] ?? [];
                $attrsToRemove = [];

                if ($child->hasAttributes()) {
                    foreach ($child->attributes as $attr) {
                        $attrName = strtolower($attr->name);

                        // Block all event handlers (on*)
                        if (str_starts_with($attrName, 'on')) {
                            $attrsToRemove[] = $attrName;
                            continue;
                        }

                        // Check attribute whitelist
                        if (!in_array($attrName, $allowedAttrs, true)) {
                            $attrsToRemove[] = $attrName;
                            continue;
                        }

                        // Validate attribute values for dangerous protocols
                        $attrValue = trim($attr->value);
                        if ($attrName === 'href' || $attrName === 'src') {
                            $lowerVal = strtolower($attrValue);
                            // Reject javascript:, data:, vbscript:, file:
                            if (preg_match('/^(javascript|vbscript|data|file):/i', $lowerVal)) {
                                $attrsToRemove[] = $attrName;
                                continue;
                            }
                        }

                        // Sanitize style attribute
                        if ($attrName === 'style') {
                            $cleanStyle = self::cleanStyle($attrValue);
                            if ($cleanStyle !== '') {
                                $child->setAttribute('style', $cleanStyle);
                            } else {
                                $attrsToRemove[] = $attrName;
                            }
                        }
                    }

                    foreach ($attrsToRemove as $attrName) {
                        $child->removeAttribute($attrName);
                    }
                }

                // For <a> tags with target="_blank", ensure rel="noopener noreferrer"
                if ($tagName === 'a' && $child->getAttribute('target') === '_blank') {
                    $child->setAttribute('rel', 'noopener noreferrer');
                }

                // Recursively sanitize children
                self::sanitizeNode($child);
            }
        }

        // Remove disallowed elements (replace with text content or unwrap)
        foreach ($toRemove as $disallowed) {
            $tagName = strtolower($disallowed->tagName);
            if (in_array($tagName, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'], true)) {
                // Completely remove executable and embedded script tags and their contents
                $node->removeChild($disallowed);
            } else {
                // For other tags, unwrap by moving children before removing
                while ($disallowed->hasChildNodes()) {
                    $disallowed->parentNode->insertBefore($disallowed->firstChild, $disallowed);
                }
                $node->removeChild($disallowed);
            }
        }
    }

    /**
     * Clean inline CSS style properties.
     */
    protected static function cleanStyle(string $style): string
    {
        // Reject dangerous CSS expressions or url(...)
        if (preg_match('/expression|url\s*\(|behavior|javascript|vbscript|-moz-binding/i', $style)) {
            return '';
        }

        return htmlspecialchars($style, ENT_QUOTES, 'UTF-8');
    }
}
