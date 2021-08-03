Guzzle7 for MODX
----------------

This is a MODX transport package that installs Guzzle 7 globally on a MODX 2.x installation. The latest 7.x version is installed when the package is installed, so you can easily update to newer versions by using reinstall in the MODX package manager.  

On MODX3, or if it detects Guzzle is already available, it does not initialise the autoloader to try to avoid conflicts. That may mean an older Guzzle version gets loaded if that's included in another package. 
