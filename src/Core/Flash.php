<?php

namespace App\Core;

class Flash
{
    private const KEY = '_flash_messages';

    public static function set(string $key, string $message): void
    {
        $_SESSION[self::KEY][$key] = $message;
    }

    public static function get(string $key): ?string
    {
        $messages = $_SESSION[self::KEY] ?? [];
        $message = $messages[$key] ?? null;

        unset($_SESSION[self::KEY][$key]);

        return $message;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[self::KEY][$key]);
    }

    public static function all(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);

        return $messages;
    }
}
