<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class ProductController extends BaseController
{

    /**
     * Get all products.
     */
    public function index()
    {
        try {
            $products = Product::all();
            return $this->sendSuccess('Products retrieved successfully.', $products);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a product by ID.
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return $this->sendSuccess('Product retrieved successfully.', $product);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a product by slug.
     */
    public function getBySlug($slug)
    {
        try {
            $product = Product::where('slug', $slug)->firstOrFail();
            return $this->sendSuccess('Product retrieved successfully.', $product);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get all products by product group ID.
     */
    public function getByGroupId($groupId)
    {
        try{
            $products = Product::where('product_group_id', $groupId)->get();
            return $this->sendSuccess('Products retrieved successfully.', $products);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        try {

            $request->merge([
                'status' => filter_var($request->input('status'), FILTER_VALIDATE_BOOLEAN),
            ]);
    
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'long_description' => 'nullable|string',
                'image1' => 'required|file|mimes:jpeg,png,jpg',
                'image2' => 'nullable|file|mimes:jpeg,png,jpg',
                'image3' => 'nullable|file|mimes:jpeg,png,jpg',
                'image4' => 'nullable|file|mimes:jpeg,png,jpg',
                'image5' => 'nullable|file|mimes:jpeg,png,jpg',
                'status' => 'required|boolean',
                'stock' => 'required|integer',
                'price' => 'required|numeric',
                'weight' => 'required|numeric',
                'category_id' => 'required|exists:categories,id',
                'color_id' => 'required|exists:colors,id',
                'size' => 'nullable|string',
                'seo_keywords' => 'nullable|string',
                'product_group_id' => 'nullable|integer',
            ]);
    
            // Generate a unique slug
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            $validated['slug'] = $slug;
    
            // Handle image uploads
            foreach (['image1', 'image2', 'image3', 'image4', 'image5'] as $imageField) {
                if ($request->hasFile($imageField)) {
                    $filePath = $request->file($imageField)->storeAs(
                        'images/products',
                        $slug . '-' . $imageField . '.' . $request->file($imageField)->getClientOriginalExtension(),
                        'public'
                    );
                    $validated[$imageField] = $filePath;
                }
            }
    
            $product = Product::create($validated);

            return $this->sendSuccess('Product created successfully.', $product, 201);
        } catch (Exception $e) {
            return $this->handleException($e);
        }        
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
    
            $validated = $request->validate([
                'name' => 'required|string',
                'description' => 'required|string',
                'long_description' => 'nullable|string',
                'image1' => 'required|file|mimes:jpeg,png,jpg',
                'image2' => 'nullable|file|mimes:jpeg,png,jpg',
                'image3' => 'nullable|file|mimes:jpeg,png,jpg',
                'image4' => 'nullable|file|mimes:jpeg,png,jpg',
                'image5' => 'nullable|file|mimes:jpeg,png,jpg',
                'status' => 'required|boolean',
                'stock' => 'required|integer',
                'price' => 'required|numeric',
                'weight' => 'required|numeric',
                'category_id' => 'required|exists:categories,id',
                'color_id' => 'required|exists:colors,id',
                'size' => 'nullable|string',
                'seo_keywords' => 'nullable|string',
                'product_group_id' => 'nullable|integer',
            ]);
    
            // Handle image uploads
            foreach (['image1', 'image2', 'image3', 'image4', 'image5'] as $imageField) {
                if ($request->hasFile($imageField)) {
                    // Delete the old file if exists
                    if ($product->$imageField) {
                        Storage::disk('public')->delete($product->$imageField);
                    }
    
                    $filePath = $request->file($imageField)->storeAs(
                        'images/products',
                        $product->slug . '-' . $imageField . '.' . $request->file($imageField)->getClientOriginalExtension(),
                        'public'
                    );
                    $validated[$imageField] = $filePath;
                }
            }
    
            $product->update($validated);
            return $this->sendSuccess('Product updated successfully.', $product);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete a product.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
    
            // Delete associated images
            foreach (['image1', 'image2', 'image3', 'image4', 'image5'] as $imageField) {
                if ($product->$imageField) {
                    Storage::disk('public')->delete($product->$imageField);
                }
            }
    
            $product->delete();
            return $this->sendSuccess("Product deleted successfully", null, 204);
        }  catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the price of a product.
     */
    public function updatePrice(Request $request, $id)
    {

        try {
            $validated = $request->validate(['price' => 'required|numeric']);
            $product = Product::findOrFail($id);
            $product->update(['price' => $validated['price']]);
            return $this->sendSuccess('Product price updated successfully.', $product);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update the stock of a product.
     */
    public function updateStock(Request $request, $id)
    {
        try {
            $validated = $request->validate(['stock' => 'required|integer']);
            $product = Product::findOrFail($id);
            $product->update(['stock' => $validated['stock']]);
            return $this->sendSuccess('Product stock updated successfully.', $product);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Assign a group ID to a product.
     */
    public function assignGroup(Request $request, $id)
    {
        try {
            $validated = $request->validate(['product_group_id' => 'required|integer']);
            $product = Product::findOrFail($id);
            $product->update(['product_group_id' => $validated['product_group_id']]);
            return $this->sendSuccess('Product group assigned successfully.', $product);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Set the status of a product.
     */
    public function setStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate(['status' => 'required|boolean']);
            $product = Product::findOrFail($id);
            $product->update(['status' => $validated['status']]);
            return $this->sendSuccess("Product status updated successfully", $product, 200);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
