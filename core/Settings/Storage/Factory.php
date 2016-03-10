<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\SettingsServer;

class Factory
{
    public static function make($type, $id)
    {
        switch ($type) {
            case 'plugin':
                $backend = new Backend\Plugin($id);
                break;
            case 'measurable':
                $backend = new Backend\Measurable($id);
                break;
            default:
                $backend = new Backend\Null($id);
        }

        if (SettingsServer::isTrackerApiRequest()) {
            $backend = new Backend\Cache($backend);
        }

        return new Storage($backend);
    }
}
