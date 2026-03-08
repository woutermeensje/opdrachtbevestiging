<?php

namespace App\Http\Controllers;

use App\Services\KvkLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class KvkLookupController extends Controller
{
    public function __construct(
        private readonly KvkLookupService $lookupService,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kvk_number' => ['required', 'digits:8'],
        ]);

        try {
            return response()->json([
                'data' => $this->lookupService->lookup($validated['kvk_number']),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }
}
