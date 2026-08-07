<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KasirRedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'kasir') {
            // Kasir hanya boleh akses rute pos.*
            if (!$request->routeIs('pos.*') && !$request->routeIs('logout')) {
                return redirect()->route('pos.index');
            }
        }

        return $next($request);
    }
}
