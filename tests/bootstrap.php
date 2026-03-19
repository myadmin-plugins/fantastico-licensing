<?php

/**
 * PHPUnit bootstrap file.
 *
 * Locates the Composer autoloader from either the package's own vendor
 * directory or from a parent project when installed as a dependency.
 */

$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

$loaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    fwrite(STDERR, 'Could not find Composer autoloader. Run "composer install" first.' . PHP_EOL);
    exit(1);
}
