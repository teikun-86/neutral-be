<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectAdmin
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
        if ($request->header('origin') === config('app.admin_url') || $request->has('__sso_admin') || $request->header('x-trp-app') === '1') {
            config(['app.is_admin' => true]);
        }

        return $next($request);
    }
}
