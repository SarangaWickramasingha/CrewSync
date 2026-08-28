<?php

class Env {
    private static array $vars = [];

    public static function load(string $path = null): void {
        if ($path === null) {
            $path = dirname(__DIR__, 2) . '/.env';
        }

        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove surrounding quotes
                if (strlen($value) >= 2 &&
                    (($value[0] === '"' && substr($value, -1) === '"') ||
                     ($value[0] === "'" && substr($value, -1) === "'"))) {
                    $value = substr($value, 1, -1);
                }

                self::$vars[$key] = $value;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        return self::$vars[$key] ?? $default;
    }

    public static function required(string $key): string {
        if (!isset(self::$vars[$key])) {
            throw new RuntimeException("Missing required environment variable: {$key}");
        }
        return self::$vars[$key];
    }
}
