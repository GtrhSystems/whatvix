<?php

namespace App\Http\Middleware;

use App\Helpers\Toastr;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfWorkspacesAreEmpty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()->allWorkspaces()->count() > 0) {
            return $next($request);
        }

        Toastr::warning("Please create a workspace first");

        return redirect()->route('user.workspaces.create');
    }
}
