<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateTrustScores;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'artisan_id' => 'required|exists:artisans,id',
            'customer_id' => 'required|exists:users,id',
            'service_id' => 'nullable|exists:services,id',
            'total_price' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
        ]);

        $order = Order::create($validated);

        return response()->json($order->load(['artisan', 'service']), 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['artisan', 'customer', 'service', 'review'])
            ->findOrFail($id);

        return response()->json($order);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:accepted,in_progress,completed,cancelled_by_customer,cancelled_by_artisan',
        ]);

        $order->update([
            'status' => $validated['status'],
            'completed_date' => $validated['status'] === 'completed' ? now()->toDateString() : $order->completed_date,
        ]);

        // Recalculate trust score when order status changes
        if (in_array($validated['status'], ['completed', 'cancelled_by_artisan'])) {
            RecalculateTrustScores::dispatch($order->artisan_id);
        }

        return response()->json($order->fresh());
    }

    public function artisanOrders(Request $request, int $artisanId): JsonResponse
    {
        $orders = Order::where('artisan_id', $artisanId)
            ->with(['customer', 'service'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($orders);
    }
}
