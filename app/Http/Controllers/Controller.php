<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $authUser = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->check()) {
                /**
                 * @var \App\Models\User
                 */
                $user = auth()->user();
                $this->authUser = $user;
            }
            return $next($request);
        });
    }
}
