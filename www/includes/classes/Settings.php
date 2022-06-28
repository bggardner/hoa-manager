<?php

namespace HOA;

class Settings
{
    public const FILE = __DIR__ . '/../settings.php';

    protected static $settings;

    public static function get($key = NULL)
    {
        if (!is_array(static::$settings)) {
            $web_root = rtrim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
            static::$settings = [
                'title' => 'HOA',
                'web_root' => $web_root
            ];
            if (file_exists(static::FILE)) {
                static::$settings = array_replace_recursive(
                    static::$settings,
                    require_once static::FILE
                );
            }
        }
        if (!is_null($key)) {
            return static::$settings[$key] ?? null;
        }
        return static::$settings;
    }
}
