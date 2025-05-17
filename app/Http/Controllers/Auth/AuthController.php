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
            $token = auth()->user()->createToken('auth_token')->plainTextToken;
            return $this->sendSuccess($token);
        }

        return $this->sendError('Invalid credentials');
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return $this->sendSuccess('Logged out successfully');
    }
}
