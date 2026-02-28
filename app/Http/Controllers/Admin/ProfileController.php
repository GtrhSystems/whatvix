<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use Uploader;
    public function settings()
    {
        PageHeader::set(__('Edit Profile'));

        $user = User::findOrFail(Auth::id());
        return Inertia::render('Admin/Profile/Edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, $type)
    {
        $user = User::findOrFail(Auth::id());

        if ($type == 'password') {
            $validatedData = $request->validate([
                'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
                'oldpassword' => ['required', 'string'],
            ]);

            $check = Hash::check($request->oldpassword, auth()->user()->password);
            if ($check == true) {
               
                $user->password = Hash::make($request->password);
                $user->save();
            } else {
                Toastr::danger(__('Old password does not match'));
                return back();
            }

            $message = __('Password Updated Successfully');
            Toastr::success($message);
            return back();
        } elseif ($type == 'auth-key') {
            $user->authkey = $this->generateAuthKey();
            $user->save();

            $message = __('Auth Key Updated Successfully');
            Toastr::success($message);
            return back();
        } else {
            $validatedData = $request->validate([
                'user.name' => ['required', 'string', 'max:100'],
                'user.email' => 'required|email|unique:users,email,' . Auth::id(),
                'user.address' => ['nullable', 'string', 'max:150'],
                'user.avatar' => ['nullable', 'image', 'max:1024'],
            ]);

            $user->name = $validatedData['user']['name'];
            $user->email = $validatedData['user']['email'];
            $user->address = $validatedData['user']['address'];


            if ($request->hasFile('user.avatar')) {
                $avatar = $this->saveFile($request, 'user.avatar');
                $user->avatar = $avatar;
            }
        }

        $user->save();

        Toastr::success(__('Profile Updated Successfully'));

        return redirect()->back();
    }
}
