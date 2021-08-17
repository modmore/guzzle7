<?php

// MODX3 (as of alpha4) ships with Guzzle already, so we do absolutely nothing if the PSR ClientInterface exists
if (isset($modx) && $modx->services instanceof \Psr\Container\ContainerInterface && $modx->services->has(\Psr\Http\Client\ClientInterface::class)) {
    return;
}

$classes = [
    \GuzzleHttp\ClientInterface::class,
    \GuzzleHttp\Client::class,
    \GuzzleHttp\Utils::class,
    \GuzzleHttp\HandlerStack::class
];

// Make sure Guzzle is not already available from another package. We do this with class_exists checks, which also
// "pre-loads" the classes so that if there is a dependency conflict these key classes are always loaded from the
// already available version.
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
    if (!$loaded) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[guzzle7] Failed loading ' . $className);
    }
}
