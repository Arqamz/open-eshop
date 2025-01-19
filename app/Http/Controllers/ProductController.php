<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        $products = Product::with('category', 'variations')->get();
        return response()->json($products);
    }

    // Show a specific product
    public function show($id)
    {
        $product = Product::with('category', 'variations')->findOrFail($id);
        return response()->json($product);
    }

    // Create a new product
    public function store(Request $request)
    {

        Log::info($request);

        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'long_description' => 'nullable|string',
            'image1' => 'nullable|string',
            'image2' => 'nullable|string',
            'image3' => 'nullable|string',
            'image4' => 'nullable|string',
            'image5' => 'nullable|string',
            'status' => 'boolean',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'weight' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Log::info("validated or not");

        Log::info($validated);

        $product = Product::create($validated);

        Log::info($product);

        return response()->json($product, 201);
    }

    // Update an existing product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string',
            'description' => 'string',
            'long_description' => 'nullable|string',
            'image1' => 'nullable|string',
            'image2' => 'nullable|string',
            'image3' => 'nullable|string',
            'image4' => 'nullable|string',
            'image5' => 'nullable|string',
            'status' => 'boolean',
            'stock' => 'integer',
            'price' => 'numeric',
            'weight' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
