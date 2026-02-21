<?php

declare(strict_types=1);

namespace FunnelKit\SCE;

final class Autoloader
{
    private const PREFIX = __NAMESPACE__ . '\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $class_name): void
    {
        if (strpos($class_name, self::PREFIX) !== 0) {
            return;
        }

        $relative_class = substr($class_name, strlen(self::PREFIX));
        $relative_path  = str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
        $file_path      = SCE_PLUGIN_DIR . 'src' . DIRECTORY_SEPARATOR . $relative_path;

        if (is_readable($file_path)) {
            require_once $file_path;
        }
    }
}

