<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedTrainOwner
{
    public function handle(Request $request, Closure $next, string $permission = 'train_management_access')
    {
        abort_unless(
            $request->user()?->hasApprovedTrainOwnerAccess($permission),
            Response::HTTP_FORBIDDEN,
            'Train owner access is not approved.'
        );

        return $next($request);
    }
}
