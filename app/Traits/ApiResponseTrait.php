<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Trait ApiResponseTrait
 *
 * This trait provides a standardized JSON response structure
 * for API endpoints across the application.
 *
 * It includes helper methods for handling:
 *  - Successful responses
 *  - Validation errors
 *  - Not found errors
 *  - General errors
 *  - Unhandled exceptions
 *
 * @package App\Traits
 */
trait ApiResponseTrait
{
    /**
     * Generate a success JSON response.
     *
     * @param  mixed|null  $data     The response data (can be array, object, etc.)
     * @param  string      $message  A descriptive success message
     * @param  int         $status   HTTP status code (default: 200)
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Request successful', int $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Generate a general error JSON response.
     *
     * @param  string     $message  Error message to return
     * @param  int        $status   HTTP status code (default: 400)
     * @param  mixed|null $errors   Additional error details (optional)
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Something went wrong', int $status = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    /**
     * Generate a not found (404) JSON response.
     *
     * @param  string  $message  Message to describe the missing resource
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => null,
        ], 404);
    }

    /**
     * Generate a validation error JSON response (422).
     *
     * @param  ValidationException  $exception  Validation exception instance
     * @return JsonResponse
     */
    protected function validationErrorResponse(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $exception->errors(),
        ], 422);
    }

    /**
     * Generate a global exception JSON response (500).
     *
     * Logs the exception details and returns a standardized error message.
     * In debug mode, the actual error message is included for easier debugging.
     *
     * @param  \Throwable  $exception  The caught exception
     * @param  int         $status     HTTP status code (default: 500)
     * @return JsonResponse
     */
    protected function exceptionResponse(\Throwable $exception, int $status = 500): JsonResponse
    {
        // Log error details for debugging and monitoring
        Log::error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Internal server error',
            'error'   => config('app.debug') ? $exception->getMessage() : null,
        ], $status);
    }
}
