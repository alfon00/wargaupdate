<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsKelurahan
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isKelurahan() && ! $request->user()?->isSuperAdmin()) {
            abort(403, 'Akses hanya untuk petugas kelurahan.');
        }

        return $next($request);
    }
}
