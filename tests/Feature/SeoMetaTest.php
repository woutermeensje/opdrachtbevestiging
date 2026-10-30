<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoMetaTest extends TestCase
{
    public function test_every_sitemap_page_has_unique_title_and_description(): void
    {
        preg_match_all('#<loc>(.*?)</loc>#', $this->get('/sitemap.xml')->getContent(), $matches);

        $this->assertNotEmpty($matches[1]);

        $titles = [];
        $descriptions = [];

        foreach ($matches[1] as $url) {
            $html = $this->get(parse_url($url, PHP_URL_PATH) ?: '/')->assertOk()->getContent();

            preg_match('#<title>(.*?)</title>#s', $html, $title);
            preg_match('#<meta name="description" content="(.*?)">#s', $html, $description);

            $title = html_entity_decode($title[1] ?? '');
            $description = html_entity_decode($description[1] ?? '');

            $this->assertNotSame('', $title, "Geen titel op {$url}");
            $this->assertLessThanOrEqual(60, mb_strlen($title), "Titel te lang op {$url}");
            $this->assertGreaterThanOrEqual(70, mb_strlen($description), "Beschrijving te kort op {$url}");
            $this->assertLessThanOrEqual(160, mb_strlen($description), "Beschrijving te lang op {$url}");
            $this->assertStringContainsString('<html lang="nl">', $html, "Verkeerde taal op {$url}");

            $titles[$url] = $title;
            $descriptions[$url] = $description;
        }

        $this->assertSame(array_unique($titles), $titles, 'Dubbele titels in de sitemap');
        $this->assertSame(array_unique($descriptions), $descriptions, 'Dubbele beschrijvingen in de sitemap');
    }

    public function test_old_pricing_page_redirects_to_tariffs(): void
    {
        $this->get('/prijzen')->assertStatus(301)->assertRedirect('/tarieven');

        $this->get('/sitemap.xml')->assertDontSee('/prijzen', false);
    }

    public function test_register_page_is_indexable(): void
    {
        $this->get('/registreren')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index,follow">', false);

        $this->get('/robots.txt')->assertDontSee('Disallow: /registreren');
        $this->get('/sitemap.xml')->assertSee(route('register'), false);
    }
}
