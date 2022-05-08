<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use TymonJWTAuthExceptionsJWTException;

class CheckActive
{

    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::guard('api')->check()) {
            $user = JWTAuth::user();
            if ($user->active == 0) {
                return response()->json(msg($request, failed(), trans('lang.not_active')));
            }
            if ($user->suspend == 1) {
                return response()->json(msg($request, failed(), trans('lang.suspended')));
            }
        }
        return $next($request);
    }
}
