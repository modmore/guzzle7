<?php

use Composer\Console\HtmlOutputFormatter;
use Symfony\Component\Console\Output\StreamOutput;

echo "<pre>";
$path = __DIR__ . '/core/components/guzzle7/';

putenv("COMPOSER={$path}composer.json");
putenv("COMPOSER_HOME={$path}.composer");
putenv("COMPOSER_VENDOR_DIR={$path}vendor/");

require "phar://{$path}composer.phar/vendor/autoload.php";

$io = new \Composer\IO\BufferIO('', StreamOutput::VERBOSITY_NORMAL, new HtmlOutputFormatter());
$composer = \Composer\Factory::create($io);
$install = \Composer\Installer::create($io, $composer);
$install
    ->setPreferDist(true)
    ->setDevMode(false)
    ->setOptimizeAutoloader(true)
    ->setUpdate(true)
    ->setPreferStable(true);

try {
    $install->run();
} catch (Exception $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
}

$output = $io->getOutput();
$output = trim($output);
var_dump($output);


echo "Done!\n";
