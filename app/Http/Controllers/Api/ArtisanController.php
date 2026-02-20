<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Services\RecommendationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtisanController extends Controller
{
    public function __construct(
        private RecommendationEngine $recommendation,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'max_price' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:trust_score,ranking,rating,price_asc,price_desc',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $results = $this->recommendation->search(
            category: $validated['category'] ?? null,
            location: $validated['location'] ?? null,
            minRating: isset($validated['min_rating']) ? (float) $validated['min_rating'] : null,
            maxPrice: isset($validated['max_price']) ? (float) $validated['max_price'] : null,
            sortBy: $validated['sort_by'] ?? 'trust_score',
            perPage: $validated['per_page'] ?? 20,
        );

        return response()->json($results);
    }

    public function show(int $id): JsonResponse
    {
        $artisan = Artisan::with(['services', 'trustCache'])
            ->withCount('reviews', 'orders')
            ->findOrFail($id);

        $artisan->trust_tier = $artisan->trustTier;

        return response()->json($artisan);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:artisans,email',
            'phone' => 'nullable|string|max:20',
            'service_category' => 'required|string|max:100',
            'specialty' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $artisan = Artisan::create($validated);

        return response()->json($artisan, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $artisan = Artisan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'service_category' => 'sometimes|string|max:100',
            'specialty' => 'nullable|string|max:255',
            'location' => 'sometimes|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        $artisan->update($validated);

        return response()->json($artisan);
    }

    public function index(Request $request): JsonResponse
    {
        $artisans = Artisan::with('trustCache')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json($artisans);
    }

    public function destroy(int $id): JsonResponse
    {
        $artisan = Artisan::findOrFail($id);
        $artisan->delete();

        return response()->json(['message' => 'Artisan deleted successfully.']);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:artisans,email',
            'phone' => 'required|string|max:20',
            'service_category' => 'required|string|max:100',
            'specialty' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'bio' => 'nullable|string|max:1000',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'services' => 'nullable|array|max:5',
            'services.*.name' => 'required_with:services|string|max:255',
            'services.*.description' => 'nullable|string|max:500',
            'services.*.base_price' => 'nullable|numeric|min:0',
            'services.*.duration_estimate' => 'nullable|integer|min:0',
        ]);

        $artisanData = collect($validated)->except(['bio', 'experience_years', 'services'])->toArray();
        if (!empty($validated['experience_years'])) {
            $base = $validated['specialty'] ?? $validated['service_category'];
            $artisanData['specialty'] = $base . ' (' . $validated['experience_years'] . ' years experience)';
        }

        $artisan = Artisan::create($artisanData);

        // Create services if provided
        if (!empty($validated['services'])) {
            foreach ($validated['services'] as $service) {
                $artisan->services()->create([
                    'category' => $validated['service_category'],
                    'name' => $service['name'],
                    'description' => $service['description'] ?? null,
                    'base_price' => $service['base_price'] ?? null,
                    'duration_estimate' => $service['duration_estimate'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Your profile has been created successfully!',
            'artisan' => $artisan->load('services'),
        ], 201);
    }

    public function featured(): JsonResponse
    {
        $featured = $this->recommendation->getFeatured();

        return response()->json($featured);
    }

    public function categories(): JsonResponse
    {
        $categories = $this->recommendation->getCategories();

        return response()->json($categories);
    }
}
