<?php
/** @var modX $modx */
$modx =& $transport->xpdo;

if (!function_exists('checkVersion')) {
    /**
     * @param string $description
     * @param string $current
     * @param array $definition
     * @param modX $modx
     * @return bool
     */
    function checkVersion($description, $current, array $definition, $modx)
    {
        $pass = true;
        $passGlyph = '✔';
        $failGlyph = '×';
        $warnGlyph = '⚠';

        // Determine the minimum version (the one that we require today) and the recommended version (the version we'll
        // start requiring in 9 months from now).
        $realMinimum = false;
        $recommended = false;
        $recommendedDate = false;
        foreach ($definition as $date => $minVersion) {
            $date = strtotime($date);
            if ($date <= time()) {
                $realMinimum = $minVersion;
            }
            if ($date <= time() + (60 * 60 * 24 * 270)) {
                $recommended = $minVersion;
                $recommendedDate = $date;
            }
        }

        if ($realMinimum) {
            $level = xPDO::LOG_LEVEL_INFO;
            $glyph = $passGlyph;
            $checkMinimumVersion = $realMinimum;
            if (substr_count($realMinimum, '.') < 2) {
                $checkMinimumVersion .= '.0-dev';
            }
            if (version_compare($current, $checkMinimumVersion) <= 0) {
                $level = xPDO::LOG_LEVEL_ERROR;
                $pass = false;
                $glyph = $failGlyph;
            }
            $modx->log($level, "- {$description} {$realMinimum}+ (minimum): {$glyph} {$current}");
        }
        if ($pass && $recommended) {
            $level = xPDO::LOG_LEVEL_INFO;
            $glyph = $passGlyph;
            $checkRecommendedVersion = $recommended;
            if (substr_count($realMinimum, '.') < 2) {
                $checkRecommendedVersion .= '.0-dev';
            }
            if (version_compare($current, $checkRecommendedVersion) <= 0) {
                $level = xPDO::LOG_LEVEL_WARN;
                $glyph = $warnGlyph;
            }
            $recommendedDateFormatted = date('Y-m-d', $recommendedDate);
            $modx->log($level, "- {$description} {$recommended}+ (minimum per {$recommendedDateFormatted}): {$glyph} {$current}");
        }

        return $pass;
    }
}
$success = false;
switch($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        $success = true;

        $modxVersion = $modx->getVersionData();
        if (!checkVersion('MODX', $modxVersion['full_version'], [
            '2019-11-27 12:00:00' => '2.7',
        ], $modx)) {
            $success = false;
        }

        if (!checkVersion('PHP', PHP_VERSION, [
            '2019-07-01 12:00:00' => '7.2',
        ], $modx)) {
            $success = false;
        }

        if (!$success) {
            $modx->log(xPDO::LOG_LEVEL_ERROR, 'At least MODX 2.7 and PHP 7.2 is required to use the Guzzle 7 package.');
        }

        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success = true;
        break;
}
return $success;