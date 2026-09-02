<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PagesTest extends TestCase
{
    #[Test]
    public function all_five_pages_render_successfully(): void
    {
        foreach (['/', '/product', '/pricing', '/enterprise', '/contact', '/contact/thank-you'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $response->assertSee('Presence', false);
        }
    }

    #[Test]
    public function every_page_is_reachable_through_shared_navigation(): void
    {
        foreach (['/', '/product', '/pricing', '/enterprise', '/contact'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $response->assertSee(__('nav.product'));
            $response->assertSee(__('nav.pricing'));
            $response->assertSee(__('nav.enterprise'));
            $response->assertSee(__('nav.contact'));
        }
    }

    #[Test]
    public function pages_render_in_english_by_default(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('lang="en"', false);
        $response->assertSee('Every card tap becomes a record you can trust.');
    }

    #[Test]
    public function locale_toggle_switches_to_spanish_and_persists(): void
    {
        $this->get('/lang/es');
        $this->get('/lang/es')->assertRedirect();

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('lang="es"', false);
        $response->assertSee('Cada toque de tarjeta se convierte en un registro confiable.');

        // Persists across pages
        $pricing = $this->get('/pricing');
        $pricing->assertOk();
        $pricing->assertSee('Tres paquetes, una plataforma');
    }

    #[Test]
    public function english_copy_is_reachable_again_after_switching(): void
    {
        $this->get('/lang/en');
        $this->get('/lang/en');

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('lang="en"', false);
        $response->assertSee('Every card tap becomes a record you can trust.');
    }

    #[Test]
    public function unsupported_locale_is_ignored(): void
    {
        $this->get('/lang/fr');

        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('lang="en"', false);
    }

    #[Test]
    public function pricing_page_shows_all_three_tiers_with_distinct_features(): void
    {
        $response = $this->get('/pricing');

        $response->assertOk();
        $response->assertSee('Starter');
        $response->assertSee('Campus');
        $response->assertSee('Enterprise');
        $response->assertSee('1 reader at your main entrance');
        $response->assertSee('PAE meal tracking at meal service points');
        $response->assertSee('API access to your event stream');
        $response->assertSee('Custom quote');
    }

    #[Test]
    public function enterprise_page_explains_custom_event_tracking(): void
    {
        $response = $this->get('/enterprise');

        $response->assertOk();
        $response->assertSee('If a moment of presence can be named, the platform can count it.');
        $response->assertSee('shift.begin');
        $response->assertSee('zone.enter');
        $response->assertSee('asset.out');
        $response->assertSee('visitor.in');
    }

    #[Test]
    public function no_authentication_cart_or_checkout_ui_exists(): void
    {
        foreach (['/login', '/register', '/cart', '/checkout', '/vendor'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }
}
