<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateTrustScores;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'artisan_id' => 'required|exists:artisans,id',
            'customer_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = Review::create($validated);

        // Trigger trust recalculation
        RecalculateTrustScores::dispatch($validated['artisan_id']);

        return response()->json($review->load('customer'), 201);
    }

    public function artisanReviews(Request $request, int $artisanId): JsonResponse
    {
        $reviews = Review::where('artisan_id', $artisanId)
            ->where('status', 'published')
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($reviews);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'status' => 'sometimes|in:pending,published,flagged',
        ]);

        $review->update($validated);

        // Recalculate trust if rating changed
        if (isset($validated['rating'])) {
            RecalculateTrustScores::dispatch($review->artisan_id);
        }

        return response()->json($review);
    }
}
