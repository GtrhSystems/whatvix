<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Traits\Notifications;
use App\Models\User;
use Inertia\Inertia;

class NotifyController extends Controller
{

    use Notifications;

    public function __construct()
    {
        $this->middleware('permission:notification');
    }
    public function index(Request $request)
    {

        $notifications = Notification::query()->filterOn(['title', 'comment', 'user_email', 'seen'])
            ->with('user')->where('for_admin', 1)->latest()->paginate(20);
        $type = $request->type ?? 'email';

        $totalNotifications = Notification::count();
        $readNotifications = Notification::where('seen', 1)->count();
        $unreadNotifications = Notification::where('seen', 0)->count();
        PageHeader::set(title: 'Notifications', overviews: [
            [
                'icon' => "bx:user-voice",
                'title' => 'Total Notifications',
                'value' => $totalNotifications,
            ],
            [
                'icon' => "bx:envelope-open",
                'title' => 'Read Notifications',
                'value' => $readNotifications,
            ],
            [
                'icon' => "bx:envelope",
                'title' => 'Unread Notifications',
                'value' => $unreadNotifications,
            ],

        ])->addModal(__('Create Notification'), 'addNewNotificationModal', 'bx:plus');


        return Inertia::render('Admin/Notification/Index', [
            'notifications' => $notifications,
            'request' => $request,
            'type' => $type,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:100',
            'email' => 'required|email|exists:users,email',
            'description' => 'required',
            'url' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        $title = $request->title;
        $notification['user_id'] = $user->id;
        $notification['title'] = $title;
        $notification['comment'] = $request->description;
        $notification['url'] = $request->url;

        $this->createNotification($notification);

        return back();
    }

    public function destroy($id)
    {
        $row = Notification::findOrFail($id);
        $row->delete();

        return redirect()->back();
    }
}
