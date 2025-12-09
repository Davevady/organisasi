<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\{
    NotFoundHttpException,
    MethodNotAllowedHttpException,
    AccessDeniedHttpException,
    UnauthorizedHttpException,
    HttpException
};

abstract class BaseApiController extends Controller
{
    /**
     * Return a JSON error response
     */
    protected function errorResponse(string $message, int $statusCode = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a 404 response when resource (data) is not found by UUID
     */
    protected function resourceNotFound(string $resourceType, ?string $uuid = null, ?string $field = null): JsonResponse
    {
        $message = ucfirst($resourceType) . ' not found';
        if ($uuid !== null) {
            $fieldLabel = $field ?: 'UUID';
            $message .= " with {$fieldLabel}: {$uuid}";
        }

        return $this->errorResponse($message, 404, [
            'type' => 'resource_not_found',
            'resource' => $resourceType,
            'uuid' => $uuid,
        ]);
    }

    /**
     * Return a 404 response when endpoint (route) is not found
     */
    protected function endpointNotFound(?string $endpoint = null): JsonResponse
    {
        $message = 'Endpoint not found';
        if ($endpoint !== null) {
            $message .= ": {$endpoint}";
        }

        return $this->errorResponse($message, 404, [
            'type' => 'endpoint_not_found',
            'endpoint' => $endpoint,
        ]);
    }

    /**
     * Return a JSON success response
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle exceptions and return JSON response
     */
    protected function handleException(\Throwable $e): JsonResponse
    {
        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                'Validation failed',
                422,
                $e->errors()
            );
        }

        // Handle model not found (404)
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return $this->errorResponse(
                'Resource not found',
                404
            );
        }

        // Handle not found HTTP exception (404)
        if ($e instanceof NotFoundHttpException) {
            return $this->errorResponse(
                'Endpoint not found',
                404
            );
        }

        // Handle method not allowed (405)
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->errorResponse(
                'Method not allowed',
                405
            );
        }

        // Handle unauthorized (401)
        if ($e instanceof UnauthorizedHttpException || $e instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->errorResponse(
                'Unauthorized. Please authenticate.',
                401
            );
        }

        // Handle forbidden (403)
        if ($e instanceof AccessDeniedHttpException || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return $this->errorResponse(
                'Forbidden. You do not have permission to access this resource.',
                403
            );
        }

        // Handle HTTP exceptions
        if ($e instanceof HttpException) {
            return $this->errorResponse(
                $e->getMessage() ?: 'An error occurred',
                $e->getStatusCode()
            );
        }

        // Handle database connection errors (503)
        if ($e instanceof \Illuminate\Database\QueryException) {
            return $this->errorResponse(
                'Service temporarily unavailable. Please try again later.',
                503
            );
        }

        // Handle general exceptions (500)
        return $this->errorResponse(
            config('app.debug') ? $e->getMessage() : 'Internal server error',
            500
        );
    }
}

