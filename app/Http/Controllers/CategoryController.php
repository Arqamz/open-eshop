<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Exception;

class CategoryController extends BaseController
{
    /**
     * Create a new category.
     */
    public function create(Request $request)
    {
        try {
            
            $request->validate([
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'active' => 'required|boolean',
            ]);
    
            $category = Category::create($request->only('name', 'parent_id', 'active'));
    
            return $this->sendSuccess('Category created successfully', $category, 201);

        }  catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Disable a category.
     */
    public function disable($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->update(['active' => false]);
    
            return $this->sendSuccess('Category disabled successfully');
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Enable a category.
     */
    public function enable($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->update(['active' => true]);
    
            return $this->sendSuccess('Category enabled successfully');
        } catch (Exception $e) {
            return $this->handleException($e);

        }
    }

    /**
     * Delete a category and its subcategories.
     */
    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
    
            return $this->sendSuccess('Category deleted successfully', null, 204);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Edit a category.
     */
    public function edit(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'active' => 'nullable|boolean',
            ]);
    
            $category = Category::findOrFail($id);
            $category->update($request->only('name', 'parent_id', 'active'));
    
            return $this->sendSuccess('Category updated successfully', $category);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get category by ID.
     */
    public function getById($id)
    {
        try {
            $category = Category::with('subcategories')->findOrFail($id);
    
            return $this->sendSuccess('Category fetched successfully', $category);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get all subcategories of a parent category.
     */
    public function getSubcategories($id)
    {
        try {
            $category = Category::findOrFail($id);
            $subcategories = $category->subcategories;
    
            return $this->sendSuccess('Subcategories fetched successfully', $subcategories);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get all categories with nested subcategories.
     */
    public function getAll()
    {
        try {
            $categories = Category::whereNull('parent_id')
                ->with('subcategories')
                ->get();
    
            return $this->sendSuccess('Categories fetched successfully', $categories);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getAllProductsForCategory($categoryId)
    {
        try {
            $category = Category::with('subcategories')->findOrFail($categoryId);
            
            $descendantCategories = $this->getDescendantCategories($category);
            
            $categoryIds = $descendantCategories->pluck('id')->toArray();
            $categoryIds[] = $category->id;
            
            $products = Product::whereIn('category_id', $categoryIds)->get();
            
            return $this->sendSuccess('Products retrieved successfully for the category.', $products);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    private function getDescendantCategories($category)
    {
        $descendants = collect();
        
        foreach ($category->subcategories as $subcategory) {
            $descendants->push($subcategory);
            $descendants = $descendants->merge($this->getDescendantCategories($subcategory));
        }

        return $descendants;
    }


}
