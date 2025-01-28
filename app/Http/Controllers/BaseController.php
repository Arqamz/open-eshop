<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\MassAssignmentException;

use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    /**
     * Return a success response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function sendSuccess($message, $data = null, $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function sendError($message, $data = null, $statusCode = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Handle exceptions in a standard way.
     *
     * @param \Exception $e
     * @return JsonResponse
     */
    protected function handleException(\Exception $e): JsonResponse
    {
        Log::error($e->getMessage(), ['exception' => $e]);

        if ($e instanceof ModelNotFoundException) {
            return $this->sendError('Resource not found', null, 404);
        }

        if ($e instanceof JWTException) {
            return $this->sendError('Unauthorized', null, 401);
        }

        if ($e instanceof ValidationException) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        }

        if ($e instanceof MassAssignmentException) {
            return $this->sendError('Mass assignment error', null, 422);
        }

        if ($e instanceof QueryException) {
            return $this->sendError('Database error occurred', null, 500);
        }

        return $this->sendError('An unexpected error occurred', null, 500);
    }
}
