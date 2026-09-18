<?php

use App\Support\Text;

test('blank lines become paragraphs', function () {
    expect(Text::paragraphs("First para.\n\nSecond para."))
        ->toBe('<p>First para.</p><p>Second para.</p>');
});

test('single newlines stay as line breaks inside a paragraph', function () {
    expect(Text::paragraphs("Line one\nLine two"))
        ->toBe("<p>Line one<br />\nLine two</p>");
});

test('windows line endings are handled', function () {
    expect(Text::paragraphs("First.\r\n\r\nSecond."))
        ->toBe('<p>First.</p><p>Second.</p>');
});

test('html in the source text is escaped', function () {
    expect(Text::paragraphs('<script>alert(1)</script>'))
        ->toBe('<p>&lt;script&gt;alert(1)&lt;/script&gt;</p>');
});

test('empty input produces nothing', function () {
    expect(Text::paragraphs(null))->toBe('')
        ->and(Text::paragraphs("  \n\n  "))->toBe('');
});

test('runs of blank lines do not create empty paragraphs', function () {
    expect(Text::paragraphs("One.\n\n\n\n\nTwo."))
        ->toBe('<p>One.</p><p>Two.</p>');
});
