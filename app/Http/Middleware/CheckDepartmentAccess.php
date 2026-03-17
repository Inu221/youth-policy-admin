<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartmentAccess
{
    /**
     * Handle an incoming request.
     * Checks if department_head can access resource from their department
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }

        $user = auth()->user();

        // Director and analyst can access all departments
        if ($user->isDirector() || $user->isAnalyst()) {
            return $next($request);
        }

        // Department head must have department assigned
        if ($user->isDepartmentHead() && !$user->department_id) {
            abort(403, 'Department not assigned. Contact administrator.');
        }

        return $next($request);
    }
}
