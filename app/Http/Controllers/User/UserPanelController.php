<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserPanelController extends Controller
{
    use Uploader;

    public function accountSetting()
    {
        PageHeader::set(
            title: "Edit Profile"
        );
        $user = Auth::user();
        return Inertia::render('User/AccountSettings', [
            'user' => $user,
        ]);
    }

    public function accountSettingUpdate(Request $request)
    {
        if (env('DEMO_MODE')) {
            return back()->with('danger', __('Permission disabled for demo mode..!'));
        }
        $request->validate([
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [request()->user()->provider_id ? 'nullable' : 'required', 'email', 'max:255', 'unique:users,email,' . request()->user()->id],
            'current_password' => [request()->user()->provider_id ? 'nullable' : 'required', 'current_password'],
        ]);

        /** @var \App\Models\User **/
        $user = Auth::user();

        $updateData = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
        ];

        if ($request->hasFile('avatar')) {
            $this->removeFile($user->avatar);
            $updateData['avatar'] = $this->saveFile($request, 'avatar');
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Profile Updated Successfully');
    }
    public function regenerateKey()
    {
        /** @var \App\Models\User **/
        $user = Auth::user();
        $user->authkey = Str::random(32) . User::max('id');
        $user->save();

        return redirect()->back()->with('success', 'Key Regenerated Successfully');
    }
    public function changePassword()
    {
        PageHeader::set(
            title: "Change Password"
        );

        if (request()->user()->provider_id) {
            return back();
        }
        return Inertia::render('User/PasswordChange');
    }

    public function updatePassword(Request $request)
    {
        if (env('DEMO_MODE')) {
            return back()->with('danger', __('Permission disabled for demo mode..!'));
        }
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed'],
        ]);

        /** @var \App\Models\User **/
        $user = Auth::user();
        $user->password = Hash::make($request->get('new_password'));
        $user->save();

        Toastr::success('Password Changed Successfully');

        return back();
    }


    // destroy the user | soft deleted
    public function destroy()
    {
        /** @var \App\Models\User **/
        $user = Auth::user();
        $user->deleted_at = now();
        $user->save();

        Auth::logout();

        Toastr::success('Account Deleted Successfully');

        return Inertia::location('/');
    }

    public function userNotifications()
    {
        return
            request()
                ->user()
                ->hasMany(Notification::class)
                ->where('is_admin', 1)
                ->limit(5)
                ->get()->map(function ($item) {
                    $item->title_short = Str::limit($item->title, 30, '...');
                    $item->comment_short = Str::limit($item->comment, 35, '...');
                    return $item;
                });
    }

    public function userNotificationsRead(Notification $notification)
    {
        $notification->seen = 1;
        $notification->save();
        return response()->json([
            'success' => true,
        ]);
    }
}
