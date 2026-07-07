<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = [], ?string $layout = null): string
    {
        $content = self::extract($template, $data);

        if ($layout !== null) {
            return self::extract($layout, array_merge($data, ['content' => $content]));
        }

        return $content;
    }

    public static function extract(string $template, array $data = []): string
    {
        $path = __DIR__ . '/../../views/' . $template . '.php';

        if (!file_exists($path)) {
            throw new \RuntimeException("View not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return ob_get_clean();
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTR, 'UTF-8');
}
