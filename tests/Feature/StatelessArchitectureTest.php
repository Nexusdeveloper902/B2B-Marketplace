<?php

namespace Tests\Feature;

use App\Models\ContactRequest;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StatelessArchitectureTest extends TestCase
{
    #[Test]
    public function the_debug_endpoint_is_gone(): void
    {
        // Regression guard for the TASK-011 security fix: /__debug was an
        // unauthenticated endpoint that disclosed env and filesystem details.
        $this->get('/__debug')->assertNotFound();
        $this->get('/__debug?token=debug')->assertNotFound();

        $this->assertFalse(Route::has('debug'), 'A route named [debug] is registered again.');
    }

    #[Test]
    public function no_database_artifacts_exist(): void
    {
        // ADR-013: the storefront must remain stateless. If any of these
        // assertions fail, the database layer was reintroduced without a
        // superseding decision record.
        $this->assertFalse(
            class_exists(ContactRequest::class),
            'The ContactRequest model must not exist (ADR-013).'
        );

        $this->assertDirectoryDoesNotExist(database_path('migrations'));
        $this->assertDirectoryDoesNotExist(database_path('seeders'));
    }

    #[Test]
    public function contact_form_is_rate_limited(): void
    {
        $payload = [
            'name' => 'Ana Torres',
            'email' => 'ana@escuelariverside.edu',
            'organization' => 'Escuela Riverside',
            'tier' => 'starter',
            'message' => 'We need attendance tracking for our primary school.',
        ];

        // throttle:5,1 — the first five submissions in a minute pass.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $payload)->assertRedirect(route('contact.thankYou'));
        }

        // The sixth within the same window is rejected by the limiter.
        $this->post('/contact', $payload)->assertTooManyRequests();
    }
}
