<?php

namespace Tests\Feature;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TASK-014 marketplace layer: catalog presentation, the comparison matrix,
 * the quote-list controls and the ?tier= handoff to the request form.
 */
class StorefrontCatalogTest extends TestCase
{
    #[Test]
    public function home_is_a_catalog_built_from_the_existing_copy(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(__('store.categories.title'));
        foreach (__('pricing.tiers') as $tier) {
            $response->assertSee($tier['name']);
            $response->assertSee($tier['price']);
        }
        foreach (__('enterprise.cases.items') as $case) {
            $response->assertSee($case['title']);
        }
        // The motion layer's hooks survive the restructure.
        foreach (['id="demo-tap"', 'id="demo-pipeline"', 'id="package-widget"', 'class="ledger-table"'] as $hook) {
            $response->assertSee($hook, false);
        }
    }

    #[Test]
    public function pricing_compares_packages_using_published_feature_lines(): void
    {
        $response = $this->get('/pricing');

        $response->assertOk();
        $response->assertSee(__('store.compare.title'));
        $response->assertSee('id="tier-campus"', false);
        $response->assertSee(__('pricing.tiers.1.features.3'));
        $response->assertSee(__('pricing.tiers.2.features.3'));
        $response->assertSee(route('contact.show', ['tier' => 'enterprise']), false);
    }

    #[Test]
    public function product_page_buy_box_submits_the_chosen_package_to_the_form(): void
    {
        $response = $this->get('/product');

        $response->assertOk();
        $response->assertSee('action="'.route('contact.show').'"', false);
        $response->assertSee('name="tier" value="starter"', false);
        $response->assertSee(__('product.hero.headline'));
    }

    #[Test]
    public function quote_controls_ship_hidden_so_no_js_visitors_never_see_dead_buttons(): void
    {
        $html = $this->get('/pricing')->getContent();

        preg_match_all('/<button[^>]*data-quote-add="[^"]+"[^>]*>/', $html, $buttons);
        $this->assertNotEmpty($buttons[0]);
        foreach ($buttons[0] as $button) {
            $this->assertStringContainsString(' hidden', $button);
        }
        // The header quote button degrades to a real link.
        $this->assertStringContainsString('href="'.route('contact.show').'" data-quote-open', $html);
    }

    #[Test]
    public function contact_form_preselects_a_package_from_the_query_string(): void
    {
        $this->get('/contact?tier=campus')
            ->assertOk()
            ->assertSee('<option value="campus" selected>', false);
    }

    #[Test]
    public function contact_form_ignores_unknown_package_values(): void
    {
        $html = $this->get('/contact?tier=platinum')->getContent();

        $this->assertStringNotContainsString('platinum', $html);
        $this->assertDoesNotMatchRegularExpression('/<option value="(starter|campus|enterprise|unsure)" selected>/', $html);
    }

    #[Test]
    public function catalog_renders_in_spanish(): void
    {
        $this->get('/lang/es');

        $this->get('/pricing')->assertOk()->assertSee('Compare los paquetes');
        $this->get('/')->assertOk()->assertSee('Explore el catálogo');
    }

    #[Test]
    public function storefront_chrome_strings_have_identical_keys_in_both_languages(): void
    {
        $en = array_keys(Arr::dot(require lang_path('en/store.php')));
        $es = array_keys(Arr::dot(require lang_path('es/store.php')));

        $this->assertSame($en, $es);
    }
}
