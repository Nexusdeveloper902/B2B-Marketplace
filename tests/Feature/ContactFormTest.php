<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    private array $validPayload = [
        'name' => 'Ana Torres',
        'email' => 'ana@escuelariverside.edu',
        'organization' => 'Escuela Riverside',
        'tier' => 'starter',
        'message' => 'We need attendance tracking for our primary school.',
    ];

    #[Test]
    public function valid_submission_is_logged_and_redirects_to_thank_you(): void
    {
        Log::spy();

        $response = $this->post('/contact', $this->validPayload);

        $response->assertRedirect(route('contact.thankYou'));

        Log::shouldHaveReceived('info')->once()->withArgs(
            fn (string $channel, array $context) => $channel === 'contact.request'
                && $context['name'] === 'Ana Torres'
                && $context['email'] === 'ana@escuelariverside.edu'
                && $context['organization'] === 'Escuela Riverside'
                && $context['tier'] === 'starter'
        );

        $thankYou = $this->get('/contact/thank-you');
        $thankYou->assertOk();
        $thankYou->assertSee('Request received.');
    }

    #[Test]
    public function all_tiers_are_accepted(): void
    {
        foreach (['starter', 'campus', 'enterprise', 'unsure'] as $tier) {
            $this->post('/contact', array_merge($this->validPayload, ['tier' => $tier]))
                ->assertRedirect(route('contact.thankYou'));
        }
    }

    #[Test]
    public function invalid_submission_is_rejected_with_errors_and_old_input(): void
    {
        Log::spy();

        $response = $this->from('/contact')->post('/contact', [
            'name' => 'X',
            'email' => 'not-an-email',
            'organization' => '',
            'tier' => 'bogus',
            'message' => 'short',
        ]);

        $response->assertRedirect('/contact');
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Your name needs at least 2 characters.')
            ->assertSee('That does not look like a valid email address.')
            ->assertSee('Enter your organization', false)
            ->assertSee('Choose a package from the list.')
            ->assertSee('A sentence or two is enough');

        // Rejected submissions must not reach the log channel either.
        Log::shouldNotHaveReceived('info');
    }

    #[Test]
    public function validation_errors_are_shown_in_spanish_when_locale_is_spanish(): void
    {
        $response = $this->withSession(['locale' => 'es'])
            ->from('/contact')
            ->post('/contact', [
                'name' => 'X',
                'email' => 'no-es-correo',
                'organization' => '',
                'tier' => 'bogus',
                'message' => 'corto',
            ]);

        $response->assertRedirect('/contact');
        $this->get('/contact')
            ->assertOk()
            ->assertSee('Su nombre necesita al menos 2 caracteres.')
            ->assertSee('Eso no parece un correo válido.')
            ->assertSee('Escriba el nombre de su organización.')
            ->assertSee('Elija un paquete de la lista.');
    }

    #[Test]
    public function message_is_required_and_cannot_exceed_two_thousand_characters(): void
    {
        $this->from('/contact')
            ->post('/contact', array_merge($this->validPayload, ['message' => '']))
            ->assertSessionHasErrors('message');

        $this->from('/contact')
            ->post('/contact', array_merge($this->validPayload, ['message' => str_repeat('a', 2001)]))
            ->assertSessionHasErrors('message');
    }

    #[Test]
    public function the_shipped_log_config_cannot_filter_out_contact_leads(): void
    {
        // REGRESSION GUARD (audit): the ONLY record of a lead is a
        // Log::info('contact.request', ...) line (ADR-013). The repo once
        // shipped LOG_LEVEL=warning in .env.example, which filtered info
        // records out at the Monolog handler — every lead silently
        // evaporated while the form still looked fine. Pin the shipped
        // config: either no explicit level (framework default = debug)
        // or an explicit level at or below info.
        $envExample = file_get_contents(base_path('.env.example'));

        if (preg_match('/^LOG_LEVEL=(\S+)/m', $envExample, $m)) {
            $levels = ['debug' => 100, 'info' => 200, 'notice' => 250, 'warning' => 300];
            $shipped = strtolower(trim($m[1]));
            $this->assertArrayHasKey(
                $shipped,
                $levels,
                "LOG_LEVEL in .env.example must be a known Monolog level, got [{$shipped}]"
            );
            $this->assertLessThanOrEqual(
                200,
                $levels[$shipped],
                "LOG_LEVEL={$shipped} in .env.example filters out Log::info — contact leads would be silently lost"
            );
        }
    }

    #[Test]
    public function security_headers_ride_every_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    #[Test]
    public function locale_switch_ignores_cross_site_referers(): void
    {
        // Open-redirect guard: /lang/* used to follow the raw Referer
        // header, so an attacker page could hand visitors off elsewhere.
        $response = $this->get('/lang/es', ['Referer' => 'https://attacker.example/phish']);

        $response->assertRedirect(route('landing'));
        $this->assertSame('es', session('locale'));
    }
}
