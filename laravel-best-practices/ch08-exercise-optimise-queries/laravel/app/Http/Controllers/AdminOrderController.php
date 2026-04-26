<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->select(['id', 'user_id', 'status', 'total', 'created_at'])
            ->where('status', 'paid')
            ->orderByDesc('created_at')
            ->limit(50)
            ->with(['user:id,name,email'])
            ->get();

        $payload = $orders->map(static fn (Order $order) => [
            'id' => $order->id,
            'total' => (float) $order->total,
            'status' => $order->status,
            'created_at' => $order->created_at,
            'customer' => [
                'name' => $order->user?->name,
                'email' => $order->user?->email,
            ],
        ]);

        return response()->json($payload);
    }
}
