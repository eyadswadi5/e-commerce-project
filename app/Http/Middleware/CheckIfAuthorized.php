<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckIfAuthorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = JWTAuth::user();
        $permissionName = $request->route()->action["as"];
        if ($user->is_permitted($permissionName))
            return $next($request);
        
        return response()->json([
            "success" => false,
            "message" => "you aren't permitted to perform this action.",
            "errors" => null
        ], 403);
    }
}
