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
    public function uploadOrReplace(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|file|mimes:jpeg,png,svg|max:2048', // Max 2MB
        ]);

        if ($id < 1 || $id > 10) {
            return response()->json([
                'message' => 'Invalid position. ID must be between 1 and 10.',
            ], 400);
        }

        $filePath = $request->file('image')->store('sliders', 'public');

        $slider = Slider::updateOrCreate(
            ['id' => $id], // Match by ID (position)
            ['name' => $request->name, 'path' => $filePath]
        );

        return response()->json([
            'message' => 'Slider image saved successfully.',
            'slider' => $slider,
        ]);
    }

    /**
     * Get all slider images sorted by ID.
     */
    public function getAll()
    {
        $sliders = Slider::orderBy('id')->get();

        return response()->json($sliders);
    }

    /**
     * Delete an image from a specific position.
     */
    public function deleteById($id)
    {
        $slider = Slider::findOrFail($id);

        // Delete the image file
        Storage::disk('public')->delete($slider->path);

        // Delete the slider entry
        $slider->delete();

        return response()->json([
            'message' => 'Slider image deleted successfully.',
        ]);
    }
}

