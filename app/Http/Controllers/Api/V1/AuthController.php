<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Authentication
 *
 * Endpoints for registering, logging in and managing the current session.
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * Returns the newly created user and a Sanctum Bearer token.
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: Enzo Alzueta
     * @bodyParam email string required A unique email address. Example: enzo@crm.test
     * @bodyParam password string required Minimum 8 characters. Example: password
     * @bodyParam password_confirmation string required Must match password. Example: password
     *
     * @response 201 {"user":{"id":1,"name":"Enzo Alzueta","email":"enzo@crm.test","created_at":"2026-04-13T00:00:00.000000Z"},"token":"1|abc..."}
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user  = User::create($validated);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login.
     *
     * Returns the authenticated user and a fresh Sanctum Bearer token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Example: demo@crm.test
     * @bodyParam password string required Example: password
     *
     * @response {"user":{"id":1,"name":"Demo User","email":"demo@crm.test","created_at":"2026-04-13T00:00:00.000000Z"},"token":"1|abc..."}
     * @response 422 {"message":"The provided credentials are incorrect.","errors":{"email":["The provided credentials are incorrect."]}}
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Get authenticated user.
     *
     * @response {"id":1,"name":"Demo User","email":"demo@crm.test","created_at":"2026-04-13T00:00:00.000000Z"}
     */
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Logout.
     *
     * Revokes the current access token.
     *
     * @response {"message":"Logged out successfully"}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
