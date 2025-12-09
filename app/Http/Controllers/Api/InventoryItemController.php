<?php

namespace App\Http\Controllers\Api;

use App\Models\InventoryItem;
use App\Traits\ApiPaginationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryItemController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = InventoryItem::with(['category', 'unit']);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter low stock
        if ($request->has('low_stock') && $request->low_stock) {
            $query->lowStock();
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        return $this->paginatedResponse($query, $request, 'Inventory items retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:inventory_items,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,uuid',
            'unit_id' => 'required|exists:units,uuid',
            'current_stock' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'status' => 'required|in:available,unavailable,discontinued',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        // Resolve UUIDs to internal IDs
        $category = \App\Models\Category::where('uuid', $request->category_id)->first();
        if (!$category) {
            return $this->resourceNotFound('category', $request->category_id, 'category_id');
        }

        $unit = \App\Models\Unit::where('uuid', $request->unit_id)->first();
        if (!$unit) {
            return $this->resourceNotFound('unit', $request->unit_id, 'unit_id');
        }

        $item = InventoryItem::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'current_stock' => $request->current_stock,
            'minimum_stock' => $request->minimum_stock,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'location' => $request->location,
            'status' => $request->status,
            'image' => $request->image,
        ]);
        $item->load(['category', 'unit']);

        return $this->successResponse($item, 'Inventory item created successfully', 201);
    }

    public function show(InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->load(['category', 'unit', 'transactions' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return $this->successResponse($inventoryItem);
    }

    public function update(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:inventory_items,code,' . $inventoryItem->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,uuid',
            'unit_id' => 'required|exists:units,uuid',
            'minimum_stock' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'location' => 'nullable|string',
            'status' => 'required|in:available,unavailable,discontinued',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        // Resolve UUIDs to internal IDs
        $category = \App\Models\Category::where('uuid', $request->category_id)->first();
        if (!$category) {
            return $this->resourceNotFound('category', $request->category_id, 'category_id');
        }

        $unit = \App\Models\Unit::where('uuid', $request->unit_id)->first();
        if (!$unit) {
            return $this->resourceNotFound('unit', $request->unit_id, 'unit_id');
        }

        $inventoryItem->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'minimum_stock' => $request->minimum_stock,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'location' => $request->location,
            'status' => $request->status,
            'image' => $request->image,
        ]);
        $inventoryItem->load(['category', 'unit']);

        return $this->successResponse($inventoryItem, 'Inventory item updated successfully');
    }

    public function destroy(InventoryItem $inventoryItem): JsonResponse
    {
        $inventoryItem->delete();

        return $this->successResponse(null, 'Inventory item deleted successfully');
    }

    public function lowStock(Request $request): JsonResponse
    {
        $query = InventoryItem::with(['category', 'unit'])
            ->lowStock();

        return $this->paginatedResponse($query, $request, 'Low stock items retrieved successfully');
    }

    public function outOfStock(Request $request): JsonResponse
    {
        $query = InventoryItem::with(['category', 'unit'])
            ->outOfStock();

        return $this->paginatedResponse($query, $request, 'Out of stock items retrieved successfully');
    }
}
