<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role, $permission=null)
    {
        if (Auth::guest()) {
            return redirect('/login');
        }

        if($request->user()->hasRole('admin') || $request->user()->hasRole('superadmin')) {
            return $next($request);
        }

        if (!is_null($role) && !$request->user()->hasRole($role)) {
            abort(403);
        }

        if (!is_null($permission) && !$request->user()->can($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
