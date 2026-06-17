<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectPengurusToPanel
{
    /** @var list<string> */
    private const PUBLIC_ROUTE_NAMES = [
        'home',
        'activities.index',
        'profile.index',
        'profile.show',
        'services.index',
        'services.show',
        'services.apply',
        'services.apply.store',
        'services.apply.success',
        'services.pendataan-ulang',
        'services.pendataan-ulang.store',
        'security',
        'track.form',
        'track.show',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role?->isPengurus()) {
            $routeName = $request->route()?->getName();

            if ($routeName && in_array($routeName, self::PUBLIC_ROUTE_NAMES, true)) {
                return redirect()->to($user->dashboardRoute());
            }
        }

        return $next($request);
    }
}
