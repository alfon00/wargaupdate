<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsRtStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isRtStaff()) {
            abort(403, 'Akses hanya untuk pengurus RT.');
        }

        return $next($request);
    }
}
