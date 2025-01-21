<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Get all colors.
     */
    public function index()
    {
        $colors = Color::all();
        return response()->json($colors);
    }

    /**
     * Create a new color.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:colors',
        ]);

        $color = Color::create([
            'name' => $request->name,
        ]);

        return response()->json($color, 201);
    }

    /**
     * Get a color by ID.
     */
    public function show($id)
    {
        $color = Color::findOrFail($id);
        return response()->json($color);
    }

    /**
     * Update an existing color.
     */
    public function update(Request $request, $id)
    {
        $color = Color::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:colors,name,' . $id,
        ]);

        $color->update([
            'name' => $validated['name'],
        ]);

        return response()->json($color);
    }

    /**
     * Delete a color.
     */
    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        $color->delete();

        return response()->json(['message' => 'Color deleted successfully.']);
    }
}
