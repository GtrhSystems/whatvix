<?php

namespace App\Http\Controllers\User;

use App\Helpers\Toastr;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Uploader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    use Uploader;
    public function settings()
    {
        $segments = request()->segments();
        $buttons = [
            [
                'name' => __('Back to dashboard'),
                'url' => url('user/dashboard'),
            ]
        ];
        $user = User::findOrFail(Auth::id());
        return Inertia::render('User/Profile/Edit', [
            'editUser' => $user,
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
                'user.first_name' => ['required', 'string', 'max:100'],
                'user.last_name' => ['required', 'string', 'max:100'],
                'user.email' => 'required|email|unique:users,email,' . Auth::id(),
                'user.phone' => 'required|numeric|unique:users,phone,' . Auth::id(),
                'user.address_line' => ['required', 'string', 'max:150'],
                'user.avatar' => ['nullable', 'image', 'max:1024'],
            ]);

            $user->first_name = $validatedData['user']['first_name'];
            $user->last_name = $validatedData['user']['last_name'];
            $user->email = $validatedData['user']['email'];
            $user->phone = $validatedData['user']['phone'];
            $user->address_line = $validatedData['user']['address_line'];


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
