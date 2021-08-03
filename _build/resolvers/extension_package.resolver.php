<?php
/**
 * @var xPDOTransport $transport
 * @var array $options
 */

if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_UPGRADE:
        case xPDOTransport::ACTION_INSTALL:
            $modx->addExtensionPackage('guzzle7', '[[++core_path]]components/guzzle7/model/', [
                'serviceName' => 'guzzle7',
                'serviceClass' => 'Guzzle7',
            ]);

            break;

        case xPDOTransport::ACTION_UNINSTALL:
            $modx->removeExtensionPackage('guzzle7');

            break;
    }
}

return true;
