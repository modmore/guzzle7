<?php
/* Get the core config */
$componentPath = dirname(__DIR__);
if (!file_exists($componentPath.'/config.core.php')) {
    die('ERROR: missing '.$componentPath.'/config.core.php file defining the MODX core path.');
}

echo "<pre>";
/* Boot up MODX */
echo "Loading modX...\n";
require_once $componentPath . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
echo "Initializing manager...\n";
$modx->initialize('mgr');
$modx->getService('error','error.modError', '', '');
$modx->setLogTarget('HTML');



/* Namespace */
if (!createObject('modNamespace',array(
    'name' => 'guzzle7',
    'path' => $componentPath.'/core/components/guzzle7/',
    'assets_path' => $componentPath.'/assets/components/guzzle7/',
),'name', false)) {
    echo "Error creating namespace guzzle7.\n";
}

/* Path settings */
if (!createObject('modSystemSetting', array(
    'key' => 'guzzle7.core_path',
    'value' => $componentPath.'/core/components/guzzle7/',
    'xtype' => 'textfield',
    'namespace' => 'guzzle7',
    'area' => 'Paths',
    'editedon' => time(),
), 'key', false)) {
    echo "Error creating guzzle7.core_path setting.\n";
}

$modx->addExtensionPackage('guzzle7', $componentPath.'/core/components/guzzle7/model/', [
    'serviceName' => 'guzzle7',
    'serviceClass' => 'Guzzle7',
]);


// Clear the cache
$modx->cacheManager->refresh();

echo "Done.";


/**
 * Creates an object.
 *
 * @param string $className
 * @param array $data
 * @param string $primaryField
 * @param bool $update
 * @return bool
 */
function createObject ($className = '', array $data = array(), $primaryField = '', $update = true) {
    global $modx;
    /* @var xPDOObject $object */
    $object = null;

    /* Attempt to get the existing object */
    if (!empty($primaryField)) {
        if (is_array($primaryField)) {
            $condition = array();
            foreach ($primaryField as $key) {
                $condition[$key] = $data[$key];
            }
        }
        else {
            $condition = array($primaryField => $data[$primaryField]);
        }
        $object = $modx->getObject($className, $condition);
        if ($object instanceof $className) {
            if ($update) {
                $object->fromArray($data);
                return $object->save();
            } else {
                $condition = $modx->toJSON($condition);
                echo "Skipping {$className} {$condition}: already exists.\n";
                return true;
            }
        }
    }

    /* Create new object if it doesn't exist */
    if (!$object) {
        $object = $modx->newObject($className);
        $object->fromArray($data, '', true);
        return $object->save();
    }

    return false;
}
