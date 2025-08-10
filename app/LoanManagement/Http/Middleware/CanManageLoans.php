<?php

namespace App\LoanManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CanManageLoans
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'loan_officer'])) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'You do not have permission to manage loans.');
    }
}