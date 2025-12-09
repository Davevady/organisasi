<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Traits\ApiPaginationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends BaseApiController
{
    use ApiPaginationTrait;

    public function index(Request $request): JsonResponse
    {
        $query = Category::with('parent');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        return $this->paginatedResponse($query, $request, 'Categories retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:inventory,cash',
            'parent_id' => 'nullable|exists:categories,uuid',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        // Resolve parent UUID to internal ID
        $parentId = null;
        if ($request->has('parent_id') && $request->parent_id) {
            $parent = Category::where('uuid', $request->parent_id)->first();
            if (!$parent) {
                return $this->resourceNotFound('parent category', $request->parent_id, 'parent_id');
            }
            $parentId = $parent->id;
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'type' => $request->type,
            'parent_id' => $parentId,
            'is_active' => $request->is_active ?? true,
        ]);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children']);
        return $this->successResponse($category);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:inventory,cash',
            'parent_id' => 'nullable|exists:categories,uuid',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        // Resolve parent UUID to internal ID
        $parentId = null;
        if ($request->has('parent_id') && $request->parent_id) {
            $parent = Category::where('uuid', $request->parent_id)->first();
            if (!$parent) {
                return $this->resourceNotFound('parent category', $request->parent_id, 'parent_id');
            }
            $parentId = $parent->id;
        }

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'type' => $request->type,
            'parent_id' => $parentId,
            'is_active' => $request->is_active ?? true,
        ]);

        return $this->successResponse($category, 'Category updated successfully');
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
