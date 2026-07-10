<?php

namespace App\Http\Controllers;

use App\Models\Confirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

    public function updateProfile(Request $request): RedirectResponse
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
            'company_logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
            'terms' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'default_agreements' => ['nullable', 'string'],
        ]);

        $profileData = collect($validated)
            ->except(['company_logo', 'terms'])
            ->all();

        $profileData['default_agreements'] = Confirmation::sanitizeDescription($validated['default_agreements'] ?? null);

        $uploadedFiles = array_filter([
            'company_logo' => $this->storeProfileDocument($request->file('company_logo'), $request, 'logo', 'company_logo'),
            'terms' => $this->storeProfileDocument($request->file('terms'), $request, 'algemene-voorwaarden', 'terms'),
        ]);

        $request->user()
            ->forceFill(array_merge($profileData, collect($uploadedFiles)->collapse()->all()))
            ->save();

        return redirect()
            ->route('dashboard.profile')
            ->with('status', 'Je bedrijfsgegevens zijn opgeslagen.');
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
