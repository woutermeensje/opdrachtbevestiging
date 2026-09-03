<?php

namespace Tests\Unit;

use App\Models\Confirmation;
use Tests\TestCase;

class ConfirmationRichTextSanitizerTest extends TestCase
{
    public function test_it_preserves_supported_rich_text_and_removes_unsafe_markup(): void
    {
        $html = '<h2 onclick="alert(1)">Scope</h2>'
            .'<p>Bekijk <a href="https://example.com/offerte?x=1&amp;y=2" onclick="alert(1)">de offerte</a>.</p>'
            .'<p><a href="javascript:alert(1)">Onveilige link</a></p>'
            .'<blockquote cite="https://example.com">Akkoord na betaling.</blockquote>'
            .'<iframe src="https://example.com"></iframe>'
            .'<script>alert(1)</script>';

        $clean = Confirmation::sanitizeDescription($html);

        $this->assertSame(
            '<h2>Scope</h2><p>Bekijk <a href="https://example.com/offerte?x=1&amp;y=2" target="_blank" rel="noopener noreferrer">de offerte</a>.</p><p><a>Onveilige link</a></p><blockquote>Akkoord na betaling.</blockquote>',
            $clean,
        );
    }

    public function test_it_treats_empty_editor_markup_as_empty(): void
    {
        $this->assertNull(Confirmation::sanitizeDescription('<p><br></p>'));
        $this->assertNull(Confirmation::sanitizeDescription('<p>&nbsp;</p>'));
    }

    public function test_it_sanitizes_plain_footer_notes(): void
    {
        $note = '<p>Betaling binnen 14 dagen.</p>'
            .'<script>alert(1)</script>'
            .'<p>Meerwerk alleen na schriftelijk akkoord.</p>';

        $this->assertSame(
            "Betaling binnen 14 dagen.\nMeerwerk alleen na schriftelijk akkoord.",
            Confirmation::sanitizeFooterNote($note),
        );
        $this->assertNull(Confirmation::sanitizeFooterNote('<script>alert(1)</script>'));
    }
}
