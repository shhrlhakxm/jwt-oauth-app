<?php

namespace App\Http\Controllers;

use PHPOpenSourceSaver\JWTAuth\JWTGuard;

abstract class Controller
{
    protected function jwtGuard(): JWTGuard
    {
        $guard = auth('api');

        if (! $guard instanceof JWTGuard) {
            throw new \RuntimeException('The api authentication guard must use JWT.');
        }

        return $guard;
    }
}
