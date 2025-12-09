<?php

namespace App\Http\Controllers\Api;

use App\Models\Unit;
use App\Traits\ApiPaginationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UnitController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Unit::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        return $this->paginatedResponse($query, $request, 'Units retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|unique:units,symbol|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $unit = Unit::create($request->all());

        return $this->successResponse($unit, 'Unit created successfully', 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        return $this->successResponse($unit);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|unique:units,symbol,' . $unit->id . '|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $unit->update($request->all());

        return $this->successResponse($unit, 'Unit updated successfully');
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();

        return $this->successResponse(null, 'Unit deleted successfully');
    }
}
