<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        PageHeader::set('Dashboard');

        if (!$user->getCurrentWorkspace() && $user->allWorkspaces()->count()) {
            $user->setCurrentWorkspace($user->allWorkspaces()->value('id'));
        }
        return Inertia::render('User/Dashboard', [
            'systemTimezone' => config('app.timezone'),
        ]);
    }
}
