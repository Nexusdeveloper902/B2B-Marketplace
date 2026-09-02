<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Contact / request-demo form. Persisted to SQLite, no email integration
     * (out of scope by design — see PROJECT.md).
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

        ContactRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'organization' => $validated['organization'],
            'tier' => $validated['tier'],
            'message' => $validated['message'],
        ]);

        return redirect()
            ->route('contact.thankYou')
            ->with('contact_email', $validated['email']);
    }
}
