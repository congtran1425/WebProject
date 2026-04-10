<?php

if (!function_exists("load_project_env")) {
    function load_project_env($envPath = null)
    {
        static $loaded = false;

        if ($loaded) {
            return;
        }

        $loaded = true;
        $envPath = $envPath ?: (__DIR__ . "/../.env");
        if (!is_file($envPath) || !is_readable($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === "" || str_starts_with($line, "#")) {
                continue;
            }

            $parts = explode("=", $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if ($name === "" || getenv($name) !== false) {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            putenv($name . "=" . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

if (!function_exists("env_value")) {
    function env_value(array $names, $default = null)
    {
        load_project_env();

        foreach ($names as $name) {
            $value = getenv($name);
            if ($value !== false && $value !== "") {
                return $value;
            }
        }

        return $default;
    }
}

if (!function_exists("env_flag")) {
    function env_flag(array $names, $default = false)
    {
        $value = env_value($names);
        if ($value === null) {
            return (bool)$default;
        }

        $normalized = strtolower(trim((string)$value));
        if (in_array($normalized, ["1", "true", "yes", "on"], true)) {
            return true;
        }
        if (in_array($normalized, ["0", "false", "no", "off"], true)) {
            return false;
        }

        return (bool)$default;
    }
}
