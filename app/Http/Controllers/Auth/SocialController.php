<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    /**
     * Redirect the user to the Third Party authentication page.
     */
    public function redirectToProvider(string $provider): \Illuminate\Http\RedirectResponse
    {
        if (! $this->_validateProvider($provider)) abort(404);

        $url = config("services.{$provider}.redirect") . "?lang=" . app()->getLocale();
        if (config('app.is_admin')) {
            $url .= '&__sso_admin=1';
        }

        config([
            "services.{$provider}.redirect" => $url,
        ]);
        
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Third Party.
     */
    public function handleProviderCallback($provider): \Illuminate\Http\RedirectResponse
    {
        if (! $this->_validateProvider($provider)) abort(404);

        $url = config("services.{$provider}.redirect") . "?lang=" . app()->getLocale();
        if (config('app.is_admin')) {
            $url .= '&__sso_admin=1';
        }

        config([
            "services.{$provider}.redirect" => $url,
        ]);

        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('email', $socialUser->getEmail())
            ->orWhere("{$provider}_id", $socialUser->getId())
            ->first();

        if (! $user) {
            $avatar = $socialUser->getAvatar();

            if ($provider === 'facebook') {
                // replace the size of the avatar to large
                $avatar = str_replace('type=normal', 'type=large', $avatar);
            } else {
                // replace the size of the avatar to 500x500
                $avatar = str_replace('s96-c', 's500-c', $avatar);
            }
            
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'password' => bcrypt($socialUser->getId()),
                'avatar' => $avatar,
                "{$provider}_id" => $socialUser->getId(),
                "email_verified_at" => now(),
            ]);
        } else {
            $toUpdate = [
                "{$provider}_id" => $socialUser->getId(),
            ];

            if (! $user->email_verified_at) $toUpdate['email_verified_at'] = now();
            
            $user->update($toUpdate);
        }

        return redirect($this->_buildRedirect($provider, "{$socialUser->getId()}"));
    }

    /**
     * Handle the login request.
     */
    public function handleLogin(string $provider, Request $request): \Illuminate\Http\JsonResponse | \Illuminate\Http\Response
    {
        
        if (! $this->_validateProvider($provider)) abort(404);

        $user = User::where("{$provider}_id", $request->input('user_id'))->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Validate the provider.
     */
    private function _validateProvider(string $provider): bool
    {
        return in_array($provider, ['apple', 'facebook', 'google'], true);
    }

    /**
     * Build the redirect url.
     */
    private function _buildRedirect(string $provider, string $provider_id): string
    {
        $admin = config('app.is_admin');
        $fe = $admin ? config('app.admin_url') : config('app.frontend_url');
        $locale = app()->getLocale();

        $locale = $admin ? '' : ($locale === 'en' ? 'en-US' : 'id-ID');
        
        return "{$fe}/$locale?social={$provider}&user_id={$provider_id}";
    }
}