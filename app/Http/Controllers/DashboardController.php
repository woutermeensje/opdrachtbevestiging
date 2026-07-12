<?php

namespace App\Http\Controllers;

use App\Models\Confirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $confirmations = $user->confirmations()->latest()->take(5)->get();

        return view('dashboard.index', [
            'metrics' => [
                'total' => $user->confirmations()->count(),
                'drafts' => $user->confirmations()->where('status', 'concept')->count(),
                'signed' => $user->confirmations()->where('status', 'getekend')->count(),
                'value' => (float) $user->confirmations()->sum('total_value'),
            ],
            'contactCount' => $user->contacts()->count(),
            'recentConfirmations' => $confirmations,
        ]);
    }

    public function profile(): View
    {
        return view('dashboard.profile');
    }

    public function updateAccountProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone_number' => ['required', 'string', 'max:30'],
        ]);

        $request->user()
            ->forceFill($validated)
            ->save();

        return redirect()
            ->route('dashboard.profile')
            ->with('status', 'Je accountgegevens zijn opgeslagen.');
    }

    public function updateAccountPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()
            ->forceFill(['password' => $validated['password']])
            ->save();

        return redirect()
            ->route('dashboard.profile')
            ->with('status', 'Je wachtwoord is gewijzigd.');
    }

    public function companyProfile(): View
    {
        return view('dashboard.profile.company');
    }

    public function documentProfile(): View
    {
        return view('dashboard.profile.documents');
    }

    public function updateCompanyProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'kvk_number' => ['required', 'digits:8'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'house_number_addition' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()
            ->forceFill($validated)
            ->save();

        return redirect()
            ->route('dashboard.profile.company')
            ->with('status', 'Je bedrijfsgegevens zijn opgeslagen.');
    }

    public function updateDocumentProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
            'terms' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'default_agreements' => ['nullable', 'string'],
        ]);

        $profileData = [
            'default_agreements' => Confirmation::sanitizeDescription($validated['default_agreements'] ?? null),
        ];

        $uploadedFiles = array_filter([
            'company_logo' => $this->storeProfileDocument($request->file('company_logo'), $request, 'logo', 'company_logo'),
            'terms' => $this->storeProfileDocument($request->file('terms'), $request, 'algemene-voorwaarden', 'terms'),
        ]);

        $request->user()
            ->forceFill(array_merge($profileData, collect($uploadedFiles)->collapse()->all()))
            ->save();

        return redirect()
            ->route('dashboard.profile.documents')
            ->with('status', 'Je vaste documentgegevens zijn opgeslagen.');
    }

    /**
     * @return array<string, string|null>
     */
    private function storeProfileDocument(mixed $file, Request $request, string $directory, string $fieldPrefix): array
    {
        if (! $file instanceof UploadedFile) {
            return [];
        }

        $path = $file->store('profiles/'.$request->user()->id.'/'.$directory, 'local');

        if ($path === false) {
            throw new RuntimeException('Het uploadbestand kon niet worden opgeslagen.');
        }

        return [
            $fieldPrefix.'_path' => $path,
            $fieldPrefix.'_original_name' => $file->getClientOriginalName(),
            $fieldPrefix.'_mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
        ];
    }
}
