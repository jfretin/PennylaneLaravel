<?php

require dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('config')) {
    /**
     * Minimal config helper for package unit tests running without a Laravel app.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        return $default;
    }
}
