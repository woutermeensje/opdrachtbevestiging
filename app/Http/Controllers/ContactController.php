<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $contacts = $request->user()
            ->contacts()
            ->latest()
            ->get();

        return view('dashboard.contacts', [
            'contacts' => $contacts,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.contacts-create', [
            'contact' => null,
            'formAction' => route('dashboard.contacts.store'),
            'formMethod' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $request->user()->contacts()->create($validated);

        return redirect()
            ->route('dashboard.contacts')
            ->with('status', 'Contact toegevoegd. Je kunt dit bedrijf nu kiezen bij een nieuwe opdrachtbevestiging.');
    }

    public function edit(Request $request, int $contact): View
    {
        $contact = $request->user()
            ->contacts()
            ->findOrFail($contact);

        return view('dashboard.contacts-create', [
            'contact' => $contact,
            'formAction' => route('dashboard.contacts.update', $contact),
            'formMethod' => 'PATCH',
        ]);
    }

    public function update(Request $request, int $contact): RedirectResponse
    {
        $contact = $request->user()
            ->contacts()
            ->findOrFail($contact);

        $contact->update($request->validate($this->rules()));

        return redirect()
            ->route('dashboard.contacts')
            ->with('status', 'Opdrachtgever bijgewerkt.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'kvk_number' => ['nullable', 'string', 'max:8'],
            'street_name' => ['required', 'string', 'max:255'],
            'house_number' => ['required', 'string', 'max:20'],
            'house_number_addition' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_last_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
        ];
    }
}
