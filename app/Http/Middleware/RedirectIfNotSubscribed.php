<?php

namespace App\Http\Middleware;

use App\Helpers\Toastr;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotSubscribed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = activeWorkspaceOwner();

        if (!$user->plan_data) {
            return redirect('/user/subscription');
        }

        if ($user->will_expire == null) {
            Toastr::danger(__('Your subscription payment is not completed'));
            $redirect_url = '/user/subscription';
            return inertia()->location($redirect_url);
        }

        if ($user->will_expire < now()) {
            Toastr::danger(__('Your subscription payment was expired please renew the subscription'));
            return redirect('/user/subscription');
        }

        return $next($request);
    }
}
