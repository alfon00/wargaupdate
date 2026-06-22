<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isKelurahan()) {
            abort(403, 'Akses hanya untuk akun kelurahan.');
        }

        return $next($request);
    }
}
