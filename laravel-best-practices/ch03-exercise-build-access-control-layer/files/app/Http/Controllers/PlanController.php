<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate(['plan' => ['required', 'string', 'in:standard,premium']]);

        return response()->json(['ok' => true, 'plan' => $data['plan']], 200);
    }
}
