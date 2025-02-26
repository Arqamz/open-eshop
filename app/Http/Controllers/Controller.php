<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
abstract class Controller
{
    /**
     * Return a success response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    abstract protected function sendSuccess($message, $data = null, $statusCode = 200): JsonResponse;

    /**
     * Return an error response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    abstract protected function sendError($message, $data = null, $statusCode = 400): JsonResponse;

    /**
     * Handle exceptions in a standard way.
     *
     * @param \Exception $e
     * @return JsonResponse
     */
    abstract protected function handleException(\Exception $e): JsonResponse;
}
