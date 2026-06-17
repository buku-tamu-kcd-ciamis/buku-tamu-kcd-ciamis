<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');

        // Fix charset to lowercase 'utf-8' if it's UTF-8
        $contentType = $response->headers->get('Content-Type');
        if ($contentType) {
            // Replace UTF-8 with utf-8
            if (str_contains($contentType, 'charset=UTF-8')) {
                $response->headers->set('Content-Type', str_replace('charset=UTF-8', 'charset=utf-8', $contentType));
            } elseif (!str_contains(strtolower($contentType), 'charset=')) {
                // If it is text/html or application/json without charset, append it
                $lowerContentType = strtolower($contentType);
                if (str_contains($lowerContentType, 'text/html') || str_contains($lowerContentType, 'application/json')) {
                    $response->headers->set('Content-Type', $contentType . '; charset=utf-8');
                }
            }
        }

        // Clean up deprecated / not recommended headers
        $response->headers->remove('Pragma');
        $response->headers->remove('Expires');

        // Standardize Cache-Control for dynamic pages
        // Avoid 'must-revalidate' and 'no-store' if they are flagged by the audit tool
        $cacheControl = $response->headers->get('Cache-Control');
        if ($cacheControl) {
            $directives = explode(',', $cacheControl);
            $directives = array_map('trim', $directives);
            
            // If it is a non-cacheable page, change to 'no-cache, max-age=0'
            if (in_array('no-store', $directives, true) || in_array('must-revalidate', $directives, true) || in_array('no-cache', $directives, true)) {
                $response->headers->set('Cache-Control', 'no-cache, max-age=0');
            }
        } else {
            // Set default Cache-Control if missing
            $response->headers->set('Cache-Control', 'no-cache, max-age=0');
        }

        // Remove or clean up 'Server' header (and PHP version header)
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
        
        // Remove Pragma and Expires headers via PHP built-in if sent directly
        if (function_exists('header_remove')) {
            header_remove('Pragma');
            header_remove('Expires');
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
