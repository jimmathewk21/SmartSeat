<?php

namespace App\Http\Middleware;

use App\Support\FrontendPageAccessSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFrontendPageAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (FrontendPageAccessSettings::isAccessible($request)) {
            return $next($request);
        }

        return redirect()->guest(route('login'))
            ->with('message', 'Please sign in to access this page.');
    }
}
