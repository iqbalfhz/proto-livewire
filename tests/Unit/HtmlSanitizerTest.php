<?php

use App\Support\HtmlSanitizer;

test('formatting produced by the editor survives', function (string $html) {
    expect(HtmlSanitizer::clean($html))->toContain($html);
})->with([
    '<p>A plain paragraph.</p>',
    '<h2>A heading</h2>',
    '<strong>bold</strong>',
    '<em>italic</em>',
    '<blockquote>quoted</blockquote>',
    '<pre>code block</pre>',
    '<ul><li>one</li><li>two</li></ul>',
    '<ol start="3"><li>three</li></ol>',
    '<table><tbody><tr><td>cell</td></tr></tbody></table>',
    '<p class="ql-align-center">centred</p>',
    '<a href="https://example.com" title="Example">a link</a>',
    '<a href="/projects">an internal link</a>',
    '<a href="mailto:hi@example.com">email me</a>',
    '<img src="/storage/posts/content/photo.jpg" alt="A photo">',
    '<span style="color: rgb(255, 0, 0)">red</span>',
]);

test('script tags are removed with their contents', function () {
    $clean = HtmlSanitizer::clean('<p>Before</p><script>alert("xss")</script><p>After</p>');

    expect($clean)->toBe('<p>Before</p><p>After</p>');
});

test('event handler attributes are stripped', function () {
    $clean = HtmlSanitizer::clean('<p onclick="steal()" onmouseover="steal()">Text</p>');

    expect($clean)->toBe('<p>Text</p>');
});

test('an image with an onerror payload keeps only its safe attributes', function () {
    $clean = HtmlSanitizer::clean('<img src="/storage/a.jpg" onerror="alert(1)" alt="ok">');

    expect($clean)->toContain('src="/storage/a.jpg"')
        ->toContain('alt="ok"')
        ->not->toContain('onerror');
});

test('javascript and data urls are dropped', function (string $url) {
    $clean = HtmlSanitizer::clean('<a href="'.$url.'">click</a>');

    expect($clean)->toBe('<a>click</a>');
})->with([
    'javascript:alert(1)',
    'JaVaScRiPt:alert(1)',
    'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
    'vbscript:msgbox(1)',
]);

test('style declarations that can execute code are filtered out', function () {
    $clean = HtmlSanitizer::clean('<p style="color: red; background: url(javascript:alert(1)); position: fixed">Text</p>');

    expect($clean)->toBe('<p style="color: red">Text</p>');
});

test('iframes and forms are removed entirely', function () {
    $clean = HtmlSanitizer::clean('<p>Keep</p><iframe src="https://evil.test"></iframe><form action="/steal"><input name="pw"></form>');

    expect($clean)->toBe('<p>Keep</p>');
});

test('unknown tags are unwrapped but their text is kept', function () {
    $clean = HtmlSanitizer::clean('<p>Hello <marquee>world</marquee></p>');

    expect($clean)->toBe('<p>Hello world</p>');
});

test('html comments are removed', function () {
    expect(HtmlSanitizer::clean('<p>Visible</p><!-- hidden -->'))->toBe('<p>Visible</p>');
});

test('links opening a new tab get the opener severed', function () {
    $clean = HtmlSanitizer::clean('<a href="https://example.com" target="_blank">out</a>');

    expect($clean)->toContain('rel="noopener noreferrer"');
});

test('empty input stays empty', function () {
    expect(HtmlSanitizer::clean(null))->toBe('')
        ->and(HtmlSanitizer::clean('   '))->toBe('');
});

test('unicode content is not mangled', function () {
    expect(HtmlSanitizer::clean('<p>Halo — apa kabar? 日本語 ✅</p>'))
        ->toBe('<p>Halo — apa kabar? 日本語 ✅</p>');
});

test('white backgrounds pasted in from other sites are dropped', function (string $value) {
    expect(HtmlSanitizer::clean('<p><span style="background-color: '.$value.'">Text</span></p>'))
        ->toBe('<p><span>Text</span></p>');
})->with([
    'rgb(255, 255, 255)',
    'rgb(249, 250, 251)',
    '#fff',
    '#FFFFFF',
    'white',
    'initial',
    'transparent',
]);

test('a deliberate highlight colour is kept', function () {
    $clean = HtmlSanitizer::clean('<p><span style="background-color: rgb(255, 230, 0)">Text</span></p>');

    expect($clean)->toContain('background-color: rgb(255, 230, 0)');
});

test('text colour is never mistaken for a background', function () {
    $clean = HtmlSanitizer::clean('<p><span style="color: rgb(255, 255, 255)">Text</span></p>');

    expect($clean)->toContain('color: rgb(255, 255, 255)');
});
