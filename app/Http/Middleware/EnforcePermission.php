<?php

namespace App\Http\Middleware;

use App\Support\PermissionCatalog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Applies the route-name => permission map from PermissionCatalog to every authenticated route. */
class EnforcePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $permission = PermissionCatalog::ROUTES[$request->route()?->getName()] ?? null;

        abort_if($permission && ! $request->user()?->hasPermission($permission), 403, 'You do not have permission to do that.');

        return $next($request);
    }
}
