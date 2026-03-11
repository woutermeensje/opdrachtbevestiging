<?php

namespace App\Http\Controllers;

use App\Services\KvkLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class KvkSearchController extends Controller
{
    public function __construct(
        private readonly KvkLookupService $lookupService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        try {
            return response()->json([
                'data' => $this->lookupService->searchCompanies($validated['company_name']),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
