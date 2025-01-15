<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    /**
     * Upload or replace an image in a specific position.
     */
    public function uploadOrReplace(Request $request, $position)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|file|mimes:jpeg,png,svg|max:2048', // Max 2MB
        ]);

        if ($position < 1 || $position > 10) {
            return response()->json([
                'message' => 'Invalid position. Position must be between 1 and 10.',
            ], 400);
        }

        // Store the image file
        $filePath = $request->file('image')->store('sliders', 'public');

        // Create or update slider at the specific position
        $slider = Slider::updateOrCreate(
            ['position' => $position], // Match by position
            ['name' => $request->name, 'path' => $filePath]
        );

        return response()->json([
            'message' => 'Slider image saved successfully.',
            'slider' => $slider,
        ]);
    }

    /**
     * Get all slider images sorted by position.
     */
    public function getAll()
    {
        $sliders = Slider::orderBy('position')->get();

        return response()->json([
            'message' => 'Slider images fetched successfully.',
            'sliders' => $sliders,
        ]);
    }

    /**
     * Delete an image from a specific position.
     */
    public function deleteById($position)
    {
        $slider = Slider::where('position', $position)->first();

        if (!$slider) {
            return response()->json([
                'message' => 'Slider image not found.',
            ], 404);
        }

        // Delete the image file
        Storage::disk('public')->delete($slider->path);

        // Delete the slider entry
        $slider->delete();

        return response()->json([
            'message' => 'Slider image deleted successfully.',
        ]);
    }
}
