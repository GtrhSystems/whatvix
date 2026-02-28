<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class GoogleAuthController extends Controller
{
    public function redirectTo()
    {
        /** @var User $user */
        $user = Auth::user();
        $credentials = $user->aiCredentials()->where('provider', 'gemini')->first();
        $metaData = $credentials?->meta;

        if (!$metaData) {
            // abort(403, 'Google credentials not found for this user.');
            return redirect()->route('user.ai-training.index')->with('error', 'Google credentials not found for this user.');
        }

        $query = http_build_query([
            'client_id' => $metaData['client_id'],
            'redirect_uri' => $metaData['redirect'],
            'response_type' => 'code',
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/cloud-platform',
                'https://www.googleapis.com/auth/generative-language.tuning',
                'https://www.googleapis.com/auth/drive.file',
            ]),
            'access_type' => 'offline',
            'include_granted_scopes' => 'true',
            'prompt' => 'consent',
        ]);

        return redirect("https://accounts.google.com/o/oauth2/v2/auth?$query");
    }

    public function handleCallback(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $credentials = $user->aiCredentials()->where('provider', 'gemini')->first();
        $metaData = $credentials?->meta;

        if (!$request->has('code')) {
            abort(400, 'Authorization code not provided.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $request->code,
            'client_id' => $metaData['client_id'],
            'client_secret' => $metaData['client_secret'],
            'redirect_uri' => $metaData['redirect'],
            'grant_type' => 'authorization_code',
        ]);

        if (!$response->ok()) {
            abort(500, 'Failed to fetch token from Google.');
        }

        $tokens = $response->json();

        $metaData['refresh_token'] = $tokens['refresh_token'];
        $credentials->update([
            'token' => $tokens['access_token'],
            'meta' => $metaData
        ]);

        return redirect()->route('user.ai-training.index')->with('success', 'Google connected successfully.');
    }
}
