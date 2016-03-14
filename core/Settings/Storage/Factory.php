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
    // cache prevents multiple loading of storage
    private $cache = array();

    public function getPluginStorage($pluginName)
    {
        return $this->make('plugin', 'Plugin_' . $pluginName . '_Settings');
    }

    public function getMeasurableSettingsStorage($idSite)
    {
        return $this->make('measurable_settings', $idSite);
    }

    public function getMeasurableStorage($idSite)
    {
        return $this->make('measurable', $idSite);
    }

    public function getNonPersistentStorage($key)
    {
        return $this->make('null', $key);
    }

    /**
     * @param string $type
     * @param string $id
     * @return Storage
     * @throws \Exception
     */
    private function make($type, $id)
    {
        $cacheId = $type . $id;
        if (!isset($this->cache[$cacheId])) {
            switch ($type) {
                case 'plugin':
                    $backend = new Backend\OptionTable($id);
                    break;
                case 'measurable_settings':
                    $backend = new Backend\MeasurableSettingsTable($id);
                    break;
                case 'measurable':
                    $backend = new Backend\Measurable($id);
                    break;
                case 'null':
                    $backend = new Backend\Null($id);
                    break;
                default:
                    throw new \Exception('Invalid backend type');
            }

            if (SettingsServer::isTrackerApiRequest()) {
                $backend = new Backend\Cache($backend);
            }

            $this->cache[$cacheId] = new Storage($backend);
        }

        return $this->cache[$cacheId];
    }
}
