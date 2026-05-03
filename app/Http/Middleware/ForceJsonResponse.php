<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Forcer JSON response pour les requêtes API
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }
        
        return $next($request);
    }
}
