<?php

$autoloadPaths = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(__DIR__, 2) . '/pennylane_sap_sync/vendor/autoload.php',
];

foreach ($autoloadPaths as $autoloadPath) {
    if (is_file($autoloadPath)) {
        require $autoloadPath;
        break;
    }
}

if (!class_exists(\GuzzleHttp\Client::class)) {
    throw new RuntimeException('Unable to locate Composer autoload for PennylaneLaravel tests.');
}

if (class_exists(\Illuminate\Container\Container::class) && class_exists(\Illuminate\Config\Repository::class)) {
    $container = new \Illuminate\Container\Container();
    \Illuminate\Container\Container::setInstance($container);
    $GLOBALS['__pennylane_test_container'] = $container;

    $configRepository = new \Illuminate\Config\Repository([
        'pennylane-laravel' => require dirname(__DIR__) . '/config/config.php',
    ]);

    $container->instance('app', $container);
    $container->instance('config', $configRepository);

    if (class_exists(\Illuminate\Events\Dispatcher::class)) {
        $container->instance('events', new \Illuminate\Events\Dispatcher($container));
    }
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Ashraam\\PennylaneLaravel\\Tests\\' => dirname(__DIR__) . '/tests/',
        'Ashraam\\PennylaneLaravel\\' => dirname(__DIR__) . '/src/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strpos($class, $prefix) !== 0) {
            continue;
        }

        $relativePath = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $path = $baseDir . $relativePath;

        if (is_file($path)) {
            require_once $path;
        }
    }
}, true, true);

if (!function_exists('config_set')) {
    /**
     * Store config values for package unit tests running without a Laravel app.
     *
     * @param array<string, mixed> $values
     * @return void
     */
    function config_set(array $values): void
    {
        $GLOBALS['__pennylane_test_config'] = $values;

        if (isset($GLOBALS['__pennylane_test_container']) && $GLOBALS['__pennylane_test_container']->bound('config')) {
            $GLOBALS['__pennylane_test_container']->instance('config', new \Illuminate\Config\Repository($values));
        }
    }
}

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
        $config = $GLOBALS['__pennylane_test_config'] ?? [];

        if ($key === null) {
            return $config;
        }

        if (array_key_exists($key, $config)) {
            return $config[$key];
        }

        $segments = explode('.', (string) $key);
        $value = $config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}

config_set([
    'pennylane-laravel' => require dirname(__DIR__) . '/config/config.php',
]);
