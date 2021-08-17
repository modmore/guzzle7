<?php

$classes = [
    \GuzzleHttp\ClientInterface::class,
    \GuzzleHttp\Client::class,
    \GuzzleHttp\Utils::class,
    \GuzzleHttp\HandlerStack::class
];

// Make sure Guzzle is not already available from another package, or the core in MODX3 alpha4+.
// We check for this with class_exists checks, which will use any available autoloader to try and find the classes.
// This also "pre-loads" the classes; if there is in fact a dependency conflict this greatly improves the chance it'll
// work correctly as key dependent classes are found from the same location.
$skip = true;
foreach ($classes as $className) {
    if (!class_exists($className)) {
        $skip = false;
    }
}
if ($skip) {
    return;
}

// Load the guzzle7 autoloader to make guzzle available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Pre-load the same classes we checked before, now from our own package
foreach ($classes as $className) {
    $loaded = class_exists($className);
}
