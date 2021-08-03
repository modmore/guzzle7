Guzzle 7
--------

Installs the common Guzzle package into MODX, which is used for sending HTTP requests.

The package will on each install the latest release of Guzzle 7. To update to a newer version of Guzzle, reinstall the package in the MODX package manager. Guzzle 7 requires at least PHP 7.2.

If the package detects Guzzle is already available, it does not load the autoloader to avoid conflicts. It also does not load the autoloader on MODX3, as that ships with Guzzle 7 out of the box (since alpha4).

To learn how to use this package in your own packages to avoid dependency conflicts, see https://github.com/modmore/guzzle7
