<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirectTo($provider)
    {
        session(['user_role' => request()->r]);
        config(["services.$provider.redirect" => route('oauth.callback', $provider)]);
        return Socialite::driver($provider)->redirect();
    }

    public function handleCallback($provider)
    {

        config(["services.$provider.redirect" => route('oauth.callback', $provider)]);

        try {
            $user = Socialite::driver($provider)->user();
            $findUser = User::where('provider_id', $user->id)
                ->where('provider', $provider)
                ->where('email', $user->email)->first();

            $uuid = (string) Str::uuid();

            if ($findUser) {
                $findUser->update(['password' => Hash::make($uuid . $user->id)]);
                Auth::login($findUser);
                return redirect('/user/dashboard');
            } else {

                $defaultPlan = Plan::query()->where('status', 1)->where('is_trial', 1)->first();

                $plan = Plan::query()->where('status', 1)->find(session('plan_id', $defaultPlan->id));
               
                DB::transaction(function () use ($provider, $user, $plan) {

                    $willExpire = null;

                    if ($plan) {
                        $willExpire = $plan && $plan->is_trial ? now()->addDays($plan->trial_days) : null;
                    }
                   

                    $newUser = User::create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'provider_id' => $user->id,
                        'provider' => $provider,
                        'plan_data' => $plan->data ?? null,
                        'plan_id' => $plan->id ?? null,
                        'will_expire' => $willExpire,
                        'password' => Hash::make(Str::uuid() . $user->id),
                        'email_verified_at' => now(),
                        'role' => 'user',
                    ]);

                    Auth::login($newUser);
                });

                if (Auth::user()->will_expire === null) {
                    return inertia_location(route('user.subscription.payment', Auth::user()->plan_id));
                }

                return inertia_location('/user/dashboard');
            }
        } catch (Exception $e) {
            return to_route('login')->with('danger', $e->getMessage());
        }
    }
}
