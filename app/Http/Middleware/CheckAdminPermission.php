<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $admin = $request->user('admin');
        
        if (!$admin || !$admin->hasPermission($permission)) {
            // If it's an API/AJAX request, return JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'ليس لديك صلاحية لإجراء هذه العملية.'], 403);
            }
            
            abort(403, 'عذراً، لا تمتلك الصلاحية للوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}
