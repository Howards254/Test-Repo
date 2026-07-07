<?php

namespace App\Core;

class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');

        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }

        return $token;
    }

    public static function verify(string $token): bool
    {
        $stored = Session::get('_csrf_token');

        if ($stored === null) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public static function regenerate(): void
    {
        $token = bin2hex(random_bytes(32));
        Session::set('_csrf_token', $token);
    }
}
