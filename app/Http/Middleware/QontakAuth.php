<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\MekariAuthController;

class QontakAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('qontak_token')) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        $expires_at = Session::get('qontak_expires_at');
        
        if ($expires_at && now()->greaterThan($expires_at)) {
            $authController = new MekariAuthController();
            $refreshed = $authController->refreshToken();
            
            if (!$refreshed) {
                Session::flush();
                return redirect()->route('login')->with('error', 'Session expired. Please login again.');
            }
        }

        return $next($request);
    }
}