<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Create a new category.
     */
    public function create(Request $request)
    {
        Log::info('Create category called', ['data' => $request->all()]);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'active' => 'required|boolean',
        ]);

        $category = Category::create($request->only('name', 'parent_id', 'active'));

        return response()->json([
            'message' => 'Category created successfully.',
            'category' => $category,
        ], 201);
    }

    /**
     * Disable a category.
     */
    public function disable($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['active' => false]);

        return response()->json([
            'message' => 'Category disabled successfully.',
        ]);
    }

    /**
     * Enable a category.
     */
    public function enable($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['active' => true]);

        return response()->json([
            'message' => 'Category enabled successfully.',
        ]);
    }

    /**
     * Delete a category and its subcategories.
     */
    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    /**
     * Edit a category.
     */
    public function edit(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'active' => 'nullable|boolean',
        ]);

        $category = Category::findOrFail($id);
        $category->update($request->only('name', 'parent_id', 'active'));

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category,
        ]);
    }

    /**
     * Get category by ID.
     */
    public function getById($id)
    {
        $category = Category::with('subcategories')->findOrFail($id);

        return response()->json($category);
    }

    /**
     * Get all subcategories of a parent category.
     */
    public function getSubcategories($id)
    {
        $category = Category::findOrFail($id);
        $subcategories = $category->subcategories;

        return response()->json($subcategories);
    }

    /**
     * Get all categories with nested subcategories.
     */
    public function getAll()
    {
        $categories = Category::whereNull('parent_id')
            ->with('subcategories')
            ->get();

        return response()->json($categories);
    }
}
