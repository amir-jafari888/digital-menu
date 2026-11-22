<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Plain PHP templates. A template is rendered into $content, which the layout
 * (when given) may print. All output must pass through e().
 */
final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $template, array $data = [], ?string $layout = null): void
    {
        echo self::capture($template, $data, $layout);
    }

    /** @param array<string, mixed> $data */
    public static function capture(string $template, array $data = [], ?string $layout = null): string
    {
        $file = self::resolve($template);

        $content = (static function (string $__file, array $__data): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            try {
                require $__file;

                return (string) ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        })($file, $data);

        if ($layout === null) {
            return $content;
        }

        $layoutFile = self::resolve('layouts/' . $layout);

        return (static function (string $__file, array $__data, string $content): string {
            extract($__data, EXTR_SKIP);
            ob_start();
            try {
                require $__file;

                return (string) ob_get_clean();
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            }
        })($layoutFile, $data, $content);
    }

    private static function resolve(string $template): string
    {
        if (!preg_match('#^[a-zA-Z0-9_\-/]+$#', $template) || str_contains($template, '..')) {
            throw new \InvalidArgumentException('Invalid view name.');
        }

        $file = ROOT_PATH . '/app/Views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $template, 500);
        }

        return $file;
    }
}
