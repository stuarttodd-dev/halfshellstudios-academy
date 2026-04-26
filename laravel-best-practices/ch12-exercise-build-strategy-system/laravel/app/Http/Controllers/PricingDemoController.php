<?php

namespace App\Http\Controllers;

use App\Contracts\DiscountStrategy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingDemoController
{
    public function __invoke(Request $request, DiscountStrategy $discount): JsonResponse
    {
        $subtotal = (int) $request->query('subtotal', '0');
        $out = $discount->apply($subtotal);

        return response()->json(['subtotal_pence' => $subtotal, 'total_pence' => $out]);
    }
}
