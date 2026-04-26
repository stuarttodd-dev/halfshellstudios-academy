<?php

namespace App\Http\Controllers;

use App\Actions\Sales\CreateLead;
use App\Data\CreateLeadData;
use App\Http\Requests\StoreLeadRequest;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request, CreateLead $action): JsonResponse
    {
        $lead = $action->handle(CreateLeadData::fromValidated($request->validated()));

        return response()->json(['id' => $lead->id], 201);
    }
}
