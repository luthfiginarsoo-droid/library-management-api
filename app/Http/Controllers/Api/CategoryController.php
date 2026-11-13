<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::withCount('books')->latest()->get();
        return $this->successResponse($categories, 'Categories retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);
        return $this->successResponse($category, 'Category created successfully', 201);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->successResponse($category->load('books'), 'Category detail retrieved');
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);
        return $this->successResponse($category, 'Category updated successfully');
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->books()->exists()) {
            return $this->errorResponse('Cannot delete category that still has associated books', 422);
        }

        $category->delete();
        return $this->successResponse(null, 'Category deleted successfully');
    }
}