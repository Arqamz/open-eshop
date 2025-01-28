<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;
use Exception;

class ColorController extends BaseController
{
    /**
     * Get all colors.
     */
    public function index()
    {
        try{
            $colors = Color::all();
            return $this->sendSuccess("Colors fetched successfully", $colors);

        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a new color.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:colors',
            ]);

            $color = Color::create([
                'name' => $request->name,
            ]);

            return $this->sendSuccess('Color created successfully', $color, 201);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a color by ID.
     */
    public function show($id)
    {
        try {
            $color = Color::findOrFail($id);
            return $this->sendSuccess('Color fetched successfully', $color);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update an existing color.
     */
    public function update(Request $request, $id)
    {
        try {
            $color = Color::findOrFail($id);

            $request->validate([
                'name' => 'required|string|unique:colors,name,' . $id,
            ]);

            $color->update([
                'name' => $request->name,
            ]);

            return $this->sendSuccess('Color updated successfully', $color);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete a color.
     */
    public function destroy($id)
    {
        try {
            $color = Color::findOrFail($id);
            $color->delete();

            return $this->sendSuccess('Color deleted successfully', null, 204);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
