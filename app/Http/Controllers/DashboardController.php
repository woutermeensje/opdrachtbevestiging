<?php

namespace App\Http\Controllers;

use App\Models\Confirmation;
use App\Services\LogoResizer;
use App\Services\MollieBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

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

    public function destroyAccount(Request $request, MollieBillingService $billing): RedirectResponse
    {
        $request->validate([
            'delete_password' => ['required', 'current_password'],
        ], [
            'delete_password.current_password' => 'Het wachtwoord is onjuist.',
        ], [
            'delete_password' => 'wachtwoord',
        ]);

        $user = $request->user();

        try {
            $billing->cancelSubscription($user);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('dashboard.profile')
                ->withErrors(['delete_password' => 'Je abonnement kon niet worden opgezegd, daarom is je account niet verwijderd. Probeer het later opnieuw of neem contact met ons op.']);
        }

        $directories = collect(['profiles/'.$user->id])
            ->merge($user->confirmations()->pluck('id')->map(fn (int $id): string => 'confirmations/'.$id))
            ->merge($user->quotes()->pluck('id')->map(fn (int $id): string => 'quotes/'.$id));

        // Eerst uitloggen: logout() slaat het remember-token op en zou een al
        // verwijderde gebruiker anders opnieuw aanmaken.
        Auth::logout();

        // Opdrachtbevestigingen, offertes en opdrachtgevers verdwijnen via cascadeOnDelete.
        $user->delete();

        $directories->each(fn (string $directory) => Storage::disk('local')->deleteDirectory($directory));

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Je account en alle bijbehorende gegevens zijn verwijderd.');
    }

    public function companyProfile(): View
    {
        return view('dashboard.profile.company');
    }

    public function documentProfile(): View
    {
        return view('dashboard.profile.documents');
    }

    public function agreementsProfile(): View
    {
        return view('dashboard.profile.agreements');
    }

    public function brandProfile(): View
    {
        return view('dashboard.profile.brand');
    }

    public function updateCompanyProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_trade_name' => ['nullable', 'string', 'max:255'],
            'kvk_number' => ['required', 'digits:8'],
            'street_name' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'house_number_addition' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['company_trade_name'] = filled($validated['company_trade_name'] ?? null)
            ? trim($validated['company_trade_name'])
            : null;

        $request->user()
            ->forceFill($validated)
            ->save();

        return redirect()
            ->route('dashboard.profile.company')
            ->with('status', 'Je bedrijfsgegevens zijn opgeslagen.');
    }

    public function updateDocumentProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'terms' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $uploadedFiles = array_filter([
            'terms' => $this->storeProfileDocument($request->file('terms'), $request, 'algemene-voorwaarden', 'terms'),
        ]);

        $request->user()
            ->forceFill(collect($uploadedFiles)->collapse()->all())
            ->save();

        return redirect()
            ->route('dashboard.profile.documents')
            ->with('status', 'Je documenten zijn opgeslagen.');
    }

    public function updateAgreementsProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_agreements' => ['nullable', 'string'],
        ] + Confirmation::specificationValidationRules('default_specifications'));

        $request->user()
            ->forceFill([
                'default_agreements' => Confirmation::sanitizeDescription($validated['default_agreements'] ?? null),
                'default_specifications' => Confirmation::normalizeSpecifications($validated['default_specifications'] ?? []),
            ])
            ->save();

        return redirect()
            ->route('dashboard.profile.agreements')
            ->with('status', 'Je basis afspraken zijn opgeslagen.');
    }

    public function updateBrandProfile(Request $request, LogoResizer $logoResizer): RedirectResponse
    {
        $validated = $request->validate([
            'company_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $uploadedFiles = array_filter([
            'company_logo' => $this->storeProfileDocument($request->file('company_logo'), $request, 'logo', 'company_logo'),
        ]);

        $logoResizer->shrinkStored($uploadedFiles['company_logo']['company_logo_path'] ?? null);

        $request->user()
            ->forceFill(array_merge([
                'primary_color' => strtoupper($validated['primary_color']),
                'secondary_color' => strtoupper($validated['secondary_color']),
            ], collect($uploadedFiles)->collapse()->all()))
            ->save();

        return redirect()
            ->route('dashboard.profile.brand')
            ->with('status', 'Je logo en huisstijl zijn opgeslagen.');
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
