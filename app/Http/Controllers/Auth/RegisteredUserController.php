<?php

namespace App\Http\Controllers\Auth;

use Str;
use App\Models\Plan;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use App\Helpers\Toastr;
use App\Helpers\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Providers\RouteServiceProvider;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        if (request()->filled('plan_id')) {
            $plan = Plan::query()->where('status', 1)
                ->where('id', request('plan_id'))->first();
            session()->put('plan_id', $plan->id);
        }

        SeoMeta::init('seo_register');

        $googleClient = !empty(env('GOOGLE_CLIENT_ID')) ? true : false;
        $facebookClient = !empty(env('FACEBOOK_CLIENT_ID')) ? true : false;
        return Inertia::render('Auth/Register', [
            'authPages' => get_option('auth_pages', true),
            'googleClient' => $googleClient,
            'facebookClient' => $facebookClient
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', Rules\Password::defaults()],
        ]);
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->authkey = Str::random(32) . User::max('id');

        $plan = session('plan_id')
            ? Plan::query()->where('status', 1)->find(session('plan_id'))
            : Plan::query()->where('status', 1)->where('is_trial', 1)->first();

        if ($plan) {
            $user->plan_data = $plan->data;
            $user->plan_id = $plan->id;
            $user->will_expire = $plan->is_trial == 1 ? now()->addDays($plan->trial_days) : null;
            $user->credits = $plan->data['credits']['value'] ?? 0;
            $user->save();

            if (session('plan_id')) {
                Auth::login($user);
                session()->forget('plan_id');
                if ($user->will_expire === null) {
                    return Inertia::location(route('user.subscription.payment', $plan->id));
                }
            }
        }

        $user->save();

        event(new Registered($user));

        Auth::login($user);

        Toastr::success(__('Registration successful!'));
        return Inertia::location(RouteServiceProvider::HOME);
    }
}
