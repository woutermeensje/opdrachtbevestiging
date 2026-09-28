<?php

namespace Tests\Feature;

use Tests\TestCase;

class OpstellenPagesTest extends TestCase
{
    public function test_every_opstellen_page_renders(): void
    {
        foreach (config('opstellen.types') as $slug => $name) {
            $this->get('/opstellen/'.$slug)
                ->assertOk()
                ->assertSee('<title>'.$name.' opstellen - Opdrachtbevestiging.nl</title>', false)
                ->assertSee('<link rel="canonical" href="'.route('opstellen.show', $slug).'">', false);
        }
    }

    public function test_unknown_opstellen_type_returns_404(): void
    {
        $this->get('/opstellen/bestaat-niet')->assertNotFound();
    }

    public function test_homepage_links_to_every_opstellen_page(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (array_keys(config('opstellen.types')) as $slug) {
            $response->assertSee(route('opstellen.show', $slug), false);
        }
    }

    public function test_sitemap_lists_opstellen_pages(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('opstellen.show', 'plaatsingsbevestiging'), false);
    }
}
