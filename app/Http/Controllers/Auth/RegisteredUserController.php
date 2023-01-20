<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'call_code' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], [
            'name' => 'Nama',
            'call_code' => 'Kode Negara',
            'phone' => 'Telepon',
            'email' => 'Email',
            'password' => 'Password',
        ]);

        $md5 = md5(strtolower(trim($request->email)));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => "{$request->call_code}{$request->phone}",
            'password' => Hash::make($request->password),
            'avatar' => "https://secure.gravatar.com/avatar/$md5?s=200"
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => __('auth.registered'),
        ], 200);
    }
}
