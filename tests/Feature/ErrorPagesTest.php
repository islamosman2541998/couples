<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_page_shows_the_branded_404_with_a_way_back(): void
    {
        $this->get('/games/does-not-exist')->assertNotFound()
            ->assertSee('الصفحة دي مش موجودة')
            ->assertSee('href="'.url('/').'"', false)
            ->assertSee('كل الألعاب');
    }

    public function test_error_views_render_without_the_database(): void
    {
        foreach ([403, 404, 419, 500, 503] as $code) {
            $html = view('errors.'.$code)->render();
            $this->assertStringContainsString('<html lang="ar" dir="rtl">', $html, "errors.$code");
            $this->assertStringNotContainsString('/build/assets/', $html, "errors.$code must not depend on Vite");
        }
    }
}
