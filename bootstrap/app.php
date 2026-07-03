<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use App\Http\Middleware\{HandleAppearance, HandleInertiaRequests};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure API routes always return JSON responses
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusCode = 500;
                $message = 'Internal server error';
                $errors = null;

                // Handle validation exceptions
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $statusCode = 422;
                    $message = 'Validation failed';
                    $errors = $e->errors();
                }
                // Handle model not found (404) - Data tidak ditemukan berdasarkan UUID/ID
                elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $statusCode = 404;
                    $model = class_basename($e->getModel());
                    $ids = $e->getIds();
                    $id = is_array($ids) ? ($ids[0] ?? null) : $ids;

                    // Cek apakah model menggunakan UUID sebagai route key
                    // Karena semua model menggunakan HasUuid trait, default ke 'uuid'
                    $routeKeyName = 'uuid';
                    try {
                        $modelClass = $e->getModel();
                        if (method_exists($modelClass, 'getRouteKeyName')) {
                            $tempInstance = new $modelClass;
                            $routeKeyName = $tempInstance->getRouteKeyName();
                        }
                    } catch (\Exception $ex) {
                        // Fallback ke uuid jika tidak bisa membuat instance
                        $routeKeyName = 'uuid';
                    }

                    $fieldLabel = $routeKeyName === 'uuid' ? 'UUID' : 'ID';
                    $message = ucfirst(strtolower($model)) . ' not found';
                    if ($id !== null) {
                        $message .= " with {$fieldLabel}: {$id}";
                    }
                    $errors = [
                        'type' => 'resource_not_found',
                        'resource' => strtolower($model),
                        $routeKeyName => $id,
                    ];
                }
                // Handle not found HTTP exception (404) - Endpoint tidak ditemukan
                elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    $statusCode = 404;
                    $message = 'Endpoint not found';
                    $errors = [
                        'type' => 'endpoint_not_found',
                        'endpoint' => $request->path(),
                        'method' => $request->method(),
                    ];
                }
                // Handle method not allowed (405)
                elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    $statusCode = 405;
                    $message = 'Method not allowed';
                }
                // Handle unauthorized (401)
                elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException || $e instanceof \Illuminate\Auth\AuthenticationException) {
                    $statusCode = 401;
                    $message = 'Unauthorized. Please authenticate.';
                }
                // Handle forbidden (403)
                elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException || $e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    $statusCode = 403;
                    $message = 'Forbidden. You do not have permission to access this resource.';
                }
                // Handle HTTP exceptions
                elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $statusCode = $e->getStatusCode();
                    $message = $e->getMessage() ?: 'An error occurred';
                }
                // Handle database connection errors (503)
                elseif ($e instanceof \Illuminate\Database\QueryException) {
                    $statusCode = 503;
                    $message = 'Service temporarily unavailable. Please try again later.';
                }
                // Handle general exceptions (500)
                else {
                    $statusCode = 500;
                    $message = config('app.debug') ? $e->getMessage() : 'Internal server error';
                }

                $response = [
                    'success' => false,
                    'message' => $message,
                ];

                if ($errors !== null) {
                    $response['errors'] = $errors;
                }

                return response()->json($response, $statusCode);
            }
        });
        
        // Web / Inertia Response
    $exceptions->respond(function (Response $response, Throwable $e, Request $request) {

        if ($request->is('api/*') || $request->expectsJson()) {
            return $response;
        }

        $status = $response->getStatusCode();

        if (in_array($status, [403, 404, 500, 503])) {

            return Inertia::render('Errors/Error', [
                'status' => $status,
            ])->toResponse($request)->setStatusCode($status);

        }

        return $response;
    });
    })->create();
