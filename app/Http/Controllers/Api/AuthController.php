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
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: "/register",
        summary: "Register user baru",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Budi"),
                    new OA\Property(property: "email", type: "string", example: "budi@mail.com"),
                    new OA\Property(property: "password", type: "string", minLength: 8, example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Register berhasil",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(
                response: 422,
                description: "Validasi gagal",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]

    public function register(RegisterRequest $request){
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('api')->login($user);
        return $this->success(['user' => $user, 'token' => $token], 'Register Successful', 201);
    }



    #[OA\Post(
        path: "/login",
        summary: "Login dan mendapatkan JWT token",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", example: "budi@mail.com"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login berhasil",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
            new OA\Response(
                response: 401,
                description: "Email atau password salah",
                content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
            ),
        ]
    )]

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

    #[OA\Post(
        path: "/logout",
        summary: "Logout dan invalidate token",
        security: [["bearerAuth" => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout berhasil",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]

    public function logout()
    {
        Auth::guard('api')->logout();

        return $this->success([], 'Logout successful');
    }

    #[OA\Get(
        path: "/me",
        summary: "Info user yang sedang login",
        security: [["bearerAuth" => []]],
        tags: ["Auth"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Data user",
                content: new OA\JsonContent(ref: "#/components/schemas/SuccessResponse")
            ),
        ]
    )]
    
    public function me()
    {
        return $this->success(Auth::guard('api')->user(), 'User retrieved');
    }

}
