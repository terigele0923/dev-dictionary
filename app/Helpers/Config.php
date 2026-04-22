<?php

declare(strict_types=1);

namespace App\Helpers;

final class Config
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset(self::$cache[$file])) {
            $configFile = dirname(__DIR__, 2) . '/config/' . $file . '.php';
            self::$cache[$file] = is_file($configFile) ? require $configFile : [];
        }

        $value = self::$cache[$file];
        if ($path === null) {
            return $value;
        }

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
