<?php

// MODX3 (as of alpha4) ships with Guzzle already, so we do absolutely nothing.
// Technically this breaks the package on alpha versions before 3
if (isset($modx) && $modx->services instanceof \Psr\Container\ContainerInterface) {
    return;
}

// Make sure Guzzle is not already loaded through another package, and don't load our version if that's the case
if (class_exists(\GuzzleHttp\Client::class)) {
    return;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

