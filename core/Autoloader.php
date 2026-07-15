<?php
/**
 * 自动加载器
 */

spl_autoload_register(function ($class) {
    $prefixes = [
        'Core\\' => ML_ROOT . '/core/',
        'App\\' => ML_ROOT . '/app/',
        'Api\\' => ML_ROOT . '/api/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
require ML_ROOT . '/core/helpers.php';
