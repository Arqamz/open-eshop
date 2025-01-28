<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class SliderController extends BaseController
{
    /**
     * Upload or replace an image in a specific position.
     */
    public function uploadOrReplace(Request $request, $position)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'required|file|mimes:jpeg,png,svg|max:2048', // Max 2MB
            ]);

            if ($position < 1 || $position > 10) {
                return $this->sendError('Invalid position. Position must be between 1 and 10.', 400);
            }

            // Store the image file
            $filePath = $request->file('image')->store('sliders', 'public');

            // Create or update slider at the specific position
            $slider = Slider::updateOrCreate(
                ['position' => $position],
                ['name' => $request->name, 'path' => $filePath]
            );

            return $this->sendSuccess('Slider image saved successfully', $slider);

        } catch (Exception $e) {
            return $this->handleException($e);
        }    
    }

    /**
     * Get all slider images sorted by position.
     */
    public function getAll()
    {
        try {
            $sliders = Slider::orderBy('position')->get();
            return $this->sendSuccess('Slider images fetched successfully', $sliders);
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete an image from a specific position.
     */
    public function deleteByPosition($position)
    {
        try {
            if ($position < 1 || $position > 10) {
                return $this->sendError('Invalid position. Position must be between 1 and 10.', 400);
            }

            $slider = Slider::where('position', $position)->first();

            if (!$slider) {
                return $this->sendError('Slider image not found.', 404);
            }

            // Delete the image file
            Storage::disk('public')->delete($slider->path);

            // Delete the slider entry
            $slider->delete();

            return $this->sendSuccess('Slider image deleted successfully', null, 204);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
        
        // Delete the image file
        Storage::disk('public')->delete($slider->path);

        // Delete the slider entry
        $slider->delete();

        return $this->sendSuccess('Slider image deleted successfully');
    }
}
