<?php

// MODX3 (as of alpha4) ships with Guzzle already, so we do absolutely nothing if the PSR ClientInterface exists
if (isset($modx) && $modx->services instanceof \Psr\Container\ContainerInterface && $modx->services->has(\Psr\Http\Client\ClientInterface::class)) {
    return;
}

// Make sure Guzzle is not already loaded through another package, and don't load our version if that's the case
// We check for both the Client and the ClientInterface primarily to load them into memory: otherwise conflicting
// installations may use our loaded Client, but see a different (loaded later) ClientInterface and fail.
if (class_exists(\GuzzleHttp\Client::class) && class_exists(\GuzzleHttp\ClientInterface::class) && class_exists(\GuzzleHttp\Utils::class)) {
    return;
}

// Load the guzzle7 autoloader to make guzzle available
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load the classes; this time from our own location.
$loaded = class_exists(\GuzzleHttp\Client::class) && class_exists(\GuzzleHttp\ClientInterface::class) && class_exists(\GuzzleHttp\Utils::class);
