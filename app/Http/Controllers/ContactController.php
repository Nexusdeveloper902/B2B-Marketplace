<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Contact / request-demo form.
     *
     * The storefront is STATELESS (see .agent/DECISIONS/ADR-013-stateless-no-database.md):
     * there is no database layer, so submissions are written to the application
     * log — stderr on Vercel (captured by Vercel's log drain), storage/logs/
     * laravel.log on Render or a local dev machine. No email delivery, no
     * persistence beyond the log channel (deliberate product decision).
     */
    public function show(): View
    {
        return view('pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'organization' => ['required', 'string', 'min:2', 'max:160'],
            'tier' => ['required', 'string', 'in:starter,campus,enterprise,unsure'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], __('forms.validation'));

        Log::info('contact.request', $validated);

        return redirect()
            ->route('contact.thankYou')
            ->with('contact_email', $validated['email']);
    }
}
