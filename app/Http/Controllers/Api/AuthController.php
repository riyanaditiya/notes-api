<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request){
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('api')->login($user);
        return $this->success(['user' => $user, 'token' => $token], 'Register Successful', 201);
    }

    public function Login(LoginRequest $request){
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return $this->error('Invalid email or password', 401);
        }

        return $this->success([
            'user'  => Auth::guard('api')->user(),
            'token' => $token,
        ], 'Login successful');
    }

    public function logout()
    {
        Auth::guard('api')->logout();

        return $this->success([], 'Logout successful');
    }

    public function me()
    {
        return $this->success(Auth::guard('api')->user(), 'User retrieved');
    }

}
