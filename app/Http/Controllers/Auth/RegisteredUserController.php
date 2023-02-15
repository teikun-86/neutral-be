<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'phone' => ['required', 'string', 'max:255', 'unique:' . User::class],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', 'alphanum', Rules\Password::defaults()],
            'type' => ['required', 'in:personal,agent,company'],
            'company.name' => ['required_if:type,company', 'string', 'min:5'],
            'company.ppiu' => ['required_if:type,company', 'string', 'min:5'],
            'company.email' => ['required_if:type,company', 'email'],
            'company.phone' => ['required_if:type,company'],
            'company.image' => ['nullable', 'image', 'max:2048'],
        ], [], [
            'name' => 'Nama',
            'country_id' => 'Kode Negara',
            'phone' => 'Telepon',
            'email' => 'Email',
            'password' => 'Password',
            'type' => 'Account Type'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('validation.failed'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $md5 = md5(strtolower(trim($request->email)));

        
        if ($request->type === 'company') {
            $company = Company::where('ppiu_number', $request->company['ppiu'])->first();
            if (!$company) {
                $company = Company::create([
                    'name' => $request->company['name'],
                    'ppiu_number' => $request->company['ppiu'],
                    'email' => $request->company['email'],
                    'phone' => $request->company['phone'],
                    'image' => $request->company['image'] ? $request->company['image']->store('companies') : null,
                ]);
            }
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => "https://secure.gravatar.com/avatar/$md5?s=200",
            'user_type' => $request->type,
            'company_id' => $request->type === 'company' ? $company->id : null,
            'country_id' => $request->country_id
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => __('auth.registered'),
        ], 200);
    }
}
