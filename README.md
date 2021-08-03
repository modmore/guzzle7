Guzzle7 for MODX
----------------

This is a MODX transport package that installs Guzzle 7 globally on a MODX 2.x installation. The latest 7.x version is installed when the package is installed, so you can easily update to newer versions by using reinstall in the MODX package manager.  

On MODX3, or if it detects Guzzle is already available, it does not initialise the autoloader to try to avoid conflicts. That may mean an older Guzzle version gets loaded if that's included in another package. 

## How to use (for package developers)

If your package depends on Guzzle, either directly or through another dependency, you can use the Guzzle7 package to avoid dependency conflicts. 

There's a few parts to it.

1) Avoid shipping Guzzle yourself, by using the `replace` configuration in your composer.json.
2) Automatically install Guzzle7 as part of your package
3) Handle errors gracefully

When the Guzzle7 package is installed, it is registered as an extension package that autoloads Guzzle, so you can start sending requests without having to require files or calling a service:

```php 
$client = new GuzzleHttp\Client();
$res = $client->request('GET', 'https://api.github.com/user', [
    'auth' => ['user', 'pass']
]);
echo $res->getStatusCode();
```

### 1. Avoid shipping Guzzle yourself

In your composer.json, make sure you do not have `guzzlehttp/guzzle` in your list of required dependencies.

If one of the packages you depend on requires it, add the `replace` option to indicate it is already provided:

```json
{
    ...
    "replace": {
        "guzzlehttp/guzzle": "*"
    }
}
```

Run `composer update` to update your lockfile.

### 2. Install Guzzle7 in your package

You could instruct users to manually install Guzzle7, if you prefer, but it's also nice to automatically install it when your package is installed. 

Wether you use a standard build script, MyComponent, or GPM to build your package: you'll need to add a resolver. 

For example with a standard build script, add the following to `_build/resolvers/dependencies.resolver.php`:

```php
<?php
/**
 * Installs required dependencies packages, in this case guzzle7
 *
 * @var xPDOTransport $transport
 * @var array $options
 */

$modx = $transport->xpdo;
// When using $vehicle->resolve() instead of $builder->package->put(), use this:
//$modx = $object->xpdo;

$success = true;

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        /**
         * Define required packages: name => minimum version
         */
        $packages = [
            'Guzzle7' => '1.0.0-pl',
        ];

        /** @var modTransportProvider|\MODX\Revolution\Transport\modTransportProvider $provider */
        $provider = $modx->getObject('transport.modTransportProvider', [
            'service_url' => 'https://rest.modx.com/extras/',
        ]);
        if (!$provider) {
            $modx->log(modX::LOG_LEVEL_ERROR, "Could not find MODX.com provider; can't install dependencies");
        }

        foreach ($packages as $package_name => $version) {
            $modx->log(modX::LOG_LEVEL_INFO, "Installing dependency <b>{$package_name}</b> v{$version} (or higher)...");

            $installed = $modx->getIterator('transport.modTransportPackage', [
                'package_name' => $package_name,
            ]);
            /** @var modTransportPackage|\MODX\Revolution\Transport\modTransportPackage $package */
            foreach ($installed as $package) {
                if ($package->compareVersion($version, '<=')) {
                    $modx->log(modX::LOG_LEVEL_INFO, "- &check; {$package->get('signature')} already installed");
                    continue(2);
                }
            }


            $latest = $provider->latest($package_name, '>=' . $version);
            if (count($latest) === 0) {
                $modx->log(modX::LOG_LEVEL_ERROR, "- Could not find <b>{$package_name} v{$version}+</b> in package provider {$provider->get('name')}");
                $success = false;
                continue;
            }

            $latest = reset($latest);
            $modx->log(modX::LOG_LEVEL_INFO, "- Downloading <b>{$latest['signature']}</b> from {$provider->get('name')}...");
            $package = $provider->transfer($latest['signature']);

            if (!$package) {
                $modx->log(modX::LOG_LEVEL_ERROR, "- Download failed :(");
                $success = false;
                continue;
            }

            $modx->log(modX::LOG_LEVEL_WARN, "<b>--- Installing {$latest['signature']} ---</b>");
            $stime = microtime(true);
            $installSuccess = $package->install();
            $ttime = microtime(true) - $stime;

            if ($installSuccess) {
                $modx->log(modX::LOG_LEVEL_WARN,"<b>--- Installed {$latest['signature']} in " . number_format($ttime, 2) . "s ---</b>");
            }
            else {
                $modx->log(modX::LOG_LEVEL_ERROR,"- Installation failed. Please refer to the log above for details.");
                $success = false;
            }
        }

}

return $success;
```

And in `_build/build.transport.php`, add the resolver it to a vehicle, for example to a files vehicle:

```php
// ...
$builder = new modPackageBuilder($modx);
// ...
$builder->package->put(
    [
        'source' => $sources['source_core'],
        'target' => "return MODX_CORE_PATH . 'components/';",
    ],
    [
        xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
        'vehicle_class' => 'xPDOFileVehicle',
        'resolve' => [
            [
                'type' => 'php',
                'source' => $sources['resolvers'] . 'dependencies.resolver.php',
            ]
        ],
    ]
);
```

Or, use the following to add the resolver to any other type of vehicle as you may be used to instead:

```php
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'] . 'dependencies.resolver.php',
));
```

> **Important: when using `$vehicle->resolve` instead of `$builder->package->put()` change the line `$modx = $transport->xpdo;` to `$modx = $object->xpdo;` at the top of the resolver file**. 

### 3. Avoid errors

When loading a dependency through another package like this, there is always the risk something goes wrong. Perhaps the installation failed, or the user uninstalled the guzzle7 package because they didn't recognise it. 

To avoid fatal errors, be sure to check the client is available. For example with `class_exists`:

``` php
if (!class_exists(\GuzzleHttp\Client::class)) {
    throw new Exception('Failed loading Guzzle Client.');
}
```

In a controller's process() method, you could return a nice error message:

```php
class ExtraFooManagerController extends modExtraManagerController
{
    public function process(array $scriptProperties = array())
    {
        if (!class_exists(\GuzzleHttp\Client::class)) {
            $this->failure('Please install the guzzle7 package.');
            return;
        }
        
        // regular processing
    }
}
```

In a (base) processor, check it in the initialize() method and return a string to avoid your process() method from being called.

