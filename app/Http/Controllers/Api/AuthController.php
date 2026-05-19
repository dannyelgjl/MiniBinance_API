<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\WalletResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(fn (): User => User::query()->create([
            'name' => $data['name'] ?? Str::before($data['email'], '@'),
            'email' => $data['email'],
            'password' => $data['password'],
        ]));

        $user->load('wallet');

        return response()->json([
            'access_token' => $user->createToken(config('trading.token_name'))->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'wallet' => new WalletResource($user->wallet),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $user->loadMissing('wallet');

        return response()->json([
            'access_token' => $user->createToken(config('trading.token_name'))->plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'wallet' => new WalletResource($user->wallet),
        ]);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }
}
