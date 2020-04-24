<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Windy\Guardian\Guardian;

class AuthController
{
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw new AuthenticationException();
        }

        $token = Guardian::sign(Auth::user());

        return ['token' => $token];
    }
}
