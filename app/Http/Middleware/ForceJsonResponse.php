<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Force JSON response for API routes
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
            
            // Suppress deprecation warnings for API routes
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
        }

        $response = $next($request);

        // Ensure API responses are JSON
        if ($request->is('api/*') && !$response->headers->get('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}