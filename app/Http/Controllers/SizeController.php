<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;
use Exception;

class SizeController extends BaseController
{
    /**
     * Get all sizes.
     */
    public function index()
    {
        try{
            $sizes = Size::all();
            return $this->sendSuccess("sizes fetched successfully", $sizes);

        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Create a new size.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:sizes',
            ]);

            $size = Size::create([
                'name' => $request->name,
            ]);

            return $this->sendSuccess('size created successfully', $size, 201);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get a size by ID.
     */
    public function show($id)
    {
        try {
            $size = Size::findOrFail($id);
            return $this->sendSuccess('size fetched successfully', $size);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update an existing size.
     */
    public function update(Request $request, $id)
    {
        try {
            $size = Size::findOrFail($id);

            $request->validate([
                'name' => 'required|string|unique:sizes,name,' . $id,
            ]);

            $size->update([
                'name' => $request->name,
            ]);

            return $this->sendSuccess('size updated successfully', $size);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Delete a size.
     */
    public function destroy($id)
    {
        try {
            $size = Size::findOrFail($id);
            $size->delete();

            return $this->sendSuccess('size deleted successfully', null, 204);

        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
}
