<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\Settings\Storage\Backend\BackendInterface;
use Piwik\SettingsServer;

class Factory
{
    // cache prevents multiple loading of storage
    private $cache = array();

    /**
     * @param $pluginName
     * @param $userLogin
     * @return Storage
     */
    public function getPluginStorage($pluginName, $userLogin)
    {
        $id = $pluginName . '#' . $userLogin;

        if (!$this->cache[$id]) {
            $backend = new Backend\PluginSettingsTable($pluginName, $userLogin);
            $this->cache[$id] = $this->makeStorage($backend);
        }

        return $this->cache[$id];
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

            $this->cache[$cacheId] = $this->makeStorage($backend);
        }

        return $this->cache[$cacheId];
    }

    private function makeStorage(BackendInterface $backend)
    {
        if (SettingsServer::isTrackerApiRequest()) {
            $backend = new Backend\Cache($backend);
        }

        return new Storage($backend);
    }
}
