<?php

namespace Tests\Feature;

use App\Models\ContactRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [
        'name' => 'Ana Torres',
        'email' => 'ana@escuelariverside.edu',
        'organization' => 'Escuela Riverside',
        'tier' => 'starter',
        'message' => 'We need attendance tracking for our primary school.',
    ];

    #[Test]
    public function valid_submission_is_persisted_and_redirects_to_thank_you(): void
    {
        $response = $this->post('/contact', $this->validPayload);

        $response->assertRedirect(route('contact.thankYou'));

        $this->assertDatabaseHas('contact_requests', [
            'name' => 'Ana Torres',
            'email' => 'ana@escuelariverside.edu',
            'organization' => 'Escuela Riverside',
            'tier' => 'starter',
        ]);

        $thankYou = $this->get('/contact/thank-you');
        $thankYou->assertOk();
        $thankYou->assertSee('Request received.');
    }

    #[Test]
    public function all_tiers_are_accepted(): void
    {
        foreach (['starter', 'campus', 'enterprise', 'unsure'] as $tier) {
            $this->post('/contact', array_merge($this->validPayload, ['tier' => $tier]));

            $this->assertDatabaseHas('contact_requests', ['tier' => $tier]);
            ContactRequest::where('tier', $tier)->delete();
        }
    }

    #[Test]
    public function invalid_submission_is_rejected_with_errors_and_old_input(): void
    {
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

        $this->assertDatabaseCount('contact_requests', 0);
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

        $this->assertDatabaseCount('contact_requests', 0);
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

        $this->assertDatabaseCount('contact_requests', 0);
    }
}
