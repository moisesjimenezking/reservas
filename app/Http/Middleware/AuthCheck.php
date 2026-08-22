<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->has('auth_user_id')) {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
