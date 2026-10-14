<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use App\Enums\UserRole;

class AuthController extends Controller 
{

    protected function register(Request $request,string $role)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);


        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => $role,
            ]);

        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');
        $token = $guard->login($user);

         return $this->respondWithToken($guard,
        $guard->login($user),
        $guard->user(),
        201);
    }

    public function registerCustomer(Request $request)
    {
        return $this->register($request,UserRole::CUSTOMER->value);
    }
    
    public function registerAdmin(Request $request)
    {
        return $this->register($request,UserRole::ADMIN->value);
    }

    public function login(Request $request)
    {
         $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');

        if(!$guard->validate($credentials))
            {
                return response()->json(['message' => __('auth.invalid_credentials')], 401);
            }

        /** @var \App\Models\User $user */
        $user = $guard->getLastAttempted();

         return $this->respondWithToken($guard,
        $guard->login($user),
        $guard->user());
    }

    public function me()
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');

        return response()->json([
            'user' => $guard->user(),
        ]);
    }

    public function refresh()
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');

        return $this->respondWithToken($guard,
        $guard->refresh(),
        $guard->user());
    }

    public function logout()
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');
        $guard->logout();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }


    /**
     * Format the token and user response structure.
     *
     * @param \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard
     * @param string $token
     * @param \App\Models\User $user
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($guard, $token, $user, $statusCode = 200)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user' => $user,
        ], $statusCode);
    }
}
