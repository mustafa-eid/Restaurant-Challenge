<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Class Handler
 *
 * Centralized exception handler for the application.
 * Converts various exception types into consistent JSON API responses.
 *
 * This class extends Laravel’s default ExceptionHandler and customizes
 * the behavior for API requests using the ApiResponseTrait.
 *
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    use ApiResponseTrait;

    /**
     * Register the exception handling callbacks for the application.
     *
     * Defines how different types of exceptions are handled
     * when the request expects a JSON response. It ensures
     * all error types return standardized structured responses.
     *
     * @return void
     */
    public function register(): void
    {
        $this->renderable(
            /**
             * Render exceptions as JSON API responses.
             *
             * This closure determines the type of exception
             * and returns the appropriate standardized response.
             *
             * @param \Throwable $e The thrown exception instance.
             * @param \Illuminate\Http\Request $request The current HTTP request.
             * @return \Illuminate\Http\JsonResponse|null JSON response if applicable, otherwise null.
             */
            function (Throwable $e, $request) {

                // Only handle API (JSON) requests
                if ($request->expectsJson()) {

                    /**
                     * Handle validation errors
                     *
                     * @return \Illuminate\Http\JsonResponse
                     */
                    if ($e instanceof ValidationException) {
                        return $this->validationErrorResponse($e);
                    }

                    /**
                     * Handle missing models (not found in the database)
                     *
                     * @return \Illuminate\Http\JsonResponse
                     */
                    if ($e instanceof ModelNotFoundException) {
                        $model = class_basename($e->getModel());
                        return $this->errorResponse("{$model} not found", 404);
                    }

                    /**
                     * Handle invalid routes or endpoints
                     *
                     * @return \Illuminate\Http\JsonResponse
                     */
                    if ($e instanceof NotFoundHttpException) {
                        return $this->errorResponse('Route not found', 404);
                    }

                    /**
                     * Handle unauthenticated user access
                     *
                     * @return \Illuminate\Http\JsonResponse
                     */
                    if ($e instanceof AuthenticationException) {
                        return $this->errorResponse('Unauthenticated', 401);
                    }

                    /**
                     * Handle unauthorized access (forbidden)
                     *
                     * @return \Illuminate\Http\JsonResponse
                     */
                    if ($e instanceof AuthorizationException) {
                        return $this->errorResponse('Unauthorized', 403);
                    }

                    /**
                     * Handle all other uncaught exceptions
                     *
                     * @return \Illuminate\Http\JsonResponse
                     */
                    return $this->exceptionResponse($e);
                }

                return null;
            }
        );
    }
}
