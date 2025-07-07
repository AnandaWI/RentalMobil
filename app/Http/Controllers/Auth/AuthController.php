<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        if (auth()->attempt($data)) {
            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;
            $token_type = 'bearer'; // sesuai format contoh response

            // Mendapatkan roles user, asumsi relasi 'roles' sudah didefinisikan di model User
            $roles = $user->roles()->select('id', 'name')->get();

            return response()->json([
                'access_token' => $token,
                'token_type' => $token_type,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $roles,
                ],
            ]);
        }

        return $this->sendError('Invalid credentials');
    }


    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->sendSuccess('Logged out successfully');
    }
}
