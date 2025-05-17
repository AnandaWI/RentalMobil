<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Auth\AddOwnerRequest;
use App\Models\Owner;
use App\Models\User;

class AddOwnerController extends BaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(AddOwnerRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('owner');

        Owner::create([
            'user_id' => $user->id,
        ]);

        return $this->sendSuccess($user);
    }
}
