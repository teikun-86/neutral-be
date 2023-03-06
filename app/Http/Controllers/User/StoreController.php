<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class StoreController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'phone' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', 'alphanum', Rules\Password::defaults()],
            'user_type' => ['required', 'in:personal,agent,company'],
            'company_id' => [Rule::requiredIf($request->user_type === 'company'), 'exists:companies,id'],
            'role_id' => ['nullable'],
        ], [], [
            'name' => 'Nama',
            'country_id' => 'Kode Negara',
            'phone' => 'Telepon',
            'email' => 'Email',
            'password' => 'Password',
            'user_type' => 'Account Type'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('validation.failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $md5 = md5(strtolower(trim($request->email)));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => "https://secure.gravatar.com/avatar/$md5?s=200",
            'user_type' => $request->user_type,
            'company_id' => $request->user_type === 'company' ? $request->company_id : null,
            'country_id' => $request->country_id
        ]);

        if ($request->role_id) {
            $user->attachRole([$request->role_id]);
        }

        event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => __('auth.registered'),
        ], 200);
    }
}
