<?php

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define version */
define('PKG_NAME', 'Guzzle7');
define('PKG_NAMESPACE', 'guzzle7');
define('PKG_VERSION', '7.8.0');
define('PKG_RELEASE', 'pl');

/* load modx */
require_once dirname(__DIR__) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
$targetDirectory = dirname(__DIR__) . '/_packages/';

$root = dirname(__DIR__).'/';
$sources = [
    'root' => $root,
    'build' => $root .'_build/',
    'events' => $root . '_build/events/',
    'resolvers' => $root . '_build/resolvers/',
    'validators' => $root . '_build/validators/',
    'data' => $root . '_build/data/',
    'plugins' => $root.'_build/elements/plugins/',
    'snippets' => $root.'_build/elements/snippets/',
    'source_core' => $root.'core/components/'.PKG_NAMESPACE,
    'source_assets' => $root.'assets/components/'.PKG_NAMESPACE,
    'lexicon' => $root . 'core/components/'.PKG_NAMESPACE.'/lexicon/',
    'docs' => $root.'core/components/'.PKG_NAMESPACE.'/docs/',
    'model' => $root.'core/components/'.PKG_NAMESPACE.'/model/',
];

$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->directory = $targetDirectory;
$builder->createPackage(PKG_NAMESPACE,PKG_VERSION,PKG_RELEASE);
$builder->registerNamespace(PKG_NAMESPACE,false,true,'{core_path}components/'.PKG_NAMESPACE.'/', '{assets_path}components/'.PKG_NAMESPACE.'/');

$modx->log(modX::LOG_LEVEL_INFO,'Packaged in namespace.'); flush();

// Prevent including the vendor dir or composer.lock in the package
if (file_exists($root.'core/components/'.PKG_NAMESPACE.'/vendor/')) {
    rename($root.'core/components/'.PKG_NAMESPACE.'/vendor/', $root.'core/components/.vendor/');
}
if (file_exists($root.'core/components/'.PKG_NAMESPACE.'/.composer/')) {
    rename($root.'core/components/'.PKG_NAMESPACE.'/.composer/', $root.'core/components/.composer/');
}
if (file_exists($root.'core/components/'.PKG_NAMESPACE.'/composer.lock')) {
    unlink($root.'core/components/'.PKG_NAMESPACE.'/composer.lock');
}

$builder->package->put(
    [
        'source' => $sources['source_core'],
        'target' => "return MODX_CORE_PATH . 'components/';",
    ],
    [
        xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
        'vehicle_class' => 'xPDOFileVehicle',
        'validate' => [
            [
                'type' => 'php',
                'source' => $sources['validators'] . 'requirements.script.php'
            ]
        ],
        'resolve' => [
            [
                'type' => 'php',
                'source' => $sources['resolvers'] . 'composer.resolver.php',
            ],
            [
                'type' => 'php',
                'source' => $sources['resolvers'] . 'extension_package.resolver.php',
            ],
        ]
    ]
);
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in core, requirements validator, and composer installer resolver.'); flush();

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes([
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
]);
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in package attributes.'); flush();

$modx->log(modX::LOG_LEVEL_INFO,'Zipping up the package...'); flush();
$builder->pack();


if (file_exists($root.'core/components/.vendor/')) {
    rename($root.'core/components/.vendor/', $root.'core/components/'.PKG_NAMESPACE.'/vendor/');
}
if (file_exists($root.'core/components/.composer/')) {
    rename($root.'core/components/.composer/', $root.'core/components/'.PKG_NAMESPACE.'/.composer/');
}


$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\nPackage Built.\nExecution time: {$totalTime}\n");

