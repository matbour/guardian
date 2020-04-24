<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Windy\Guardian\Guardian;

class AuthController
{
    /**
     * @param Request $request The Illuminate HTTP request.
     *
     * @return string[] The token.
     *
     * @throws AuthenticationException
     */
    public function login(Request $request): array
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw new AuthenticationException();
        }

        $token = Guardian::sign(Auth::user());
        // $token = Guardian::get('login')->sign(Auth::user()); // use a non-default authority

        return ['token' => $token];
    }
}
