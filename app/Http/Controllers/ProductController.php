<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{

    /**
     * Get all products.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**
     * Get a product by ID.
     */
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    /**
     * Get a product by slug.
     */
    public function getBySlug($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        return response()->json($product);
    }

    /**
     * Get all products by product group ID.
     */
    public function getByGroupId($groupId)
    {
        $products = Product::where('product_group_id', $groupId)->get();
        return response()->json($products);
    }

    /**
     * Create a new product.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'long_description' => 'nullable|string',
            'image1' => 'nullable|file|mimes:jpeg,png,jpg',
            'image2' => 'nullable|file|mimes:jpeg,png,jpg',
            'image3' => 'nullable|file|mimes:jpeg,png,jpg',
            'image4' => 'nullable|file|mimes:jpeg,png,jpg',
            'image5' => 'nullable|file|mimes:jpeg,png,jpg',
            'status' => 'boolean',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'weight' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'color_id' => 'nullable|exists:colors,id',
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
        return response()->json($product, 201);
    }

    /**
     * Update an existing product.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'long_description' => 'nullable|string',
            'image1' => 'nullable|file|mimes:jpeg,png,jpg',
            'image2' => 'nullable|file|mimes:jpeg,png,jpg',
            'image3' => 'nullable|file|mimes:jpeg,png,jpg',
            'image4' => 'nullable|file|mimes:jpeg,png,jpg',
            'image5' => 'nullable|file|mimes:jpeg,png,jpg',
            'status' => 'boolean',
            'stock' => 'sometimes|integer',
            'price' => 'sometimes|numeric',
            'weight' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'color_id' => 'nullable|exists:colors,id',
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
        return response()->json($product);
    }

    /**
     * Delete a product.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete associated images
        foreach (['image1', 'image2', 'image3', 'image4', 'image5'] as $imageField) {
            if ($product->$imageField) {
                Storage::disk('public')->delete($product->$imageField);
            }
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully.']);
    }

    /**
     * Update the price of a product.
     */
    public function updatePrice(Request $request, $id)
    {
        $validated = $request->validate(['price' => 'required|numeric']);
        $product = Product::findOrFail($id);
        $product->update(['price' => $validated['price']]);
        return response()->json($product);
    }

    /**
     * Update the stock of a product.
     */
    public function updateStock(Request $request, $id)
    {
        $validated = $request->validate(['stock' => 'required|integer']);
        $product = Product::findOrFail($id);
        $product->update(['stock' => $validated['stock']]);
        return response()->json($product);
    }

    /**
     * Assign a group ID to a product.
     */
    public function assignGroup(Request $request, $id)
    {
        $validated = $request->validate(['product_group_id' => 'required|integer']);
        $product = Product::findOrFail($id);
        $product->update(['product_group_id' => $validated['product_group_id']]);
        return response()->json($product);
    }

    /**
     * Set the status of a product.
     */
    public function setStatus(Request $request, $id)
    {
        $validated = $request->validate(['status' => 'required|boolean']);
        $product = Product::findOrFail($id);
        $product->update(['status' => $validated['status']]);
        return response()->json($product);
    }
}
