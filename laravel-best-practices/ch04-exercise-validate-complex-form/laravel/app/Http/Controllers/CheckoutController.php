<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // hand $validated to a service / action — never $request->all()
        return response()->json(['received' => $validated], 201);
    }
}
