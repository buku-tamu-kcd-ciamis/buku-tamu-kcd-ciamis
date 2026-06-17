<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Trust all proxies (ngrok, load balancer, etc.) agar URL generation benar
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Static fallback pages are used so error screens still render even
        // when the app cannot access database/services during heavy failures.
        $exceptions->render(function (\Throwable $exception, Request $request) {
            // Keep Laravel's default unauthenticated behavior (redirect to login page).
            if ($exception instanceof AuthenticationException) {
                return null;
            }

            $acceptHeader = strtolower((string) $request->header('Accept', ''));
            $isHtmlRequest = str_contains($acceptHeader, 'text/html') || $acceptHeader === '';
            $isPageRequest = in_array($request->method(), ['GET', 'HEAD'], true);

            // Keep fallback static pages only for regular browser page loads.
            // For upload/ajax/livewire/json flows, defer to Laravel default error handling.
            if (
                $request->expectsJson()
                || $request->wantsJson()
                || $request->ajax()
                || ! $isPageRequest
                || ! $isHtmlRequest
            ) {
                return null;
            }

            $statusCode = 500;

            if ($exception instanceof HttpExceptionInterface) {
                $statusCode = $exception->getStatusCode();
            }

            $supported = [400, 401, 403, 404, 419, 429, 500, 501, 502, 503, 504];

            if (in_array($statusCode, $supported, true)) {
                $target = (string) $statusCode;
            } else {
                $target = str_starts_with((string) $statusCode, '4') ? '4xx' : '5xx';
            }

            $filePath = public_path("system-errors/{$target}.html");
            $classFallbackFilePath = public_path(
                str_starts_with((string) $statusCode, '4')
                    ? 'system-errors/4xx.html'
                    : 'system-errors/5xx.html'
            );

            if (! is_file($filePath) && is_file($classFallbackFilePath)) {
                $filePath = $classFallbackFilePath;
            }

            if (! is_file($filePath)) {
                return null;
            }

            $html = @file_get_contents($filePath);

            if ($html === false) {
                return null;
            }

            return response($html, $statusCode, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Cache-Control' => 'no-cache, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        });
    })->create();
