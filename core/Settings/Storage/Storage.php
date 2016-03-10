<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage;

use Piwik\Settings\Setting;
use Piwik\Settings\Storage\Backend;

/**
 * A storage stores values for multiple settings. Storing multiple settings here saves having to do
 * a "get" for each individual setting. A storage is usually stared between all individual setting instances
 * within a plugin.
 */
class Storage
{
    /**
     * Array containing all plugin settings values: Array( [setting-key] => [setting-value] ).
     *
     * @var array
     */
    protected $settingsValues = array();

    // for lazy loading of setting values
    private $settingValuesLoaded = false;

    /**
     * @var Backend\BackendInterface
     */
    private $backend;

    public function __construct(Backend\BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        $this->loadSettingsIfNotDoneYet();

        $this->backend->save($this->settingsValues);

        $this->clearBackendCache();
    }

    /**
     * Removes all settings for this plugin from the database. Useful when uninstalling
     * a plugin.
     */
    public function deleteAllValues()
    {
        $this->backend->delete();
        $this->clearBackendCache();

        $this->settingsValues = array();
        $this->settingValuesLoaded = false;
    }

    /**
     * Returns the current value for a setting. If no value is stored, the default value
     * is be returned.
     *
     * @param Setting $setting
     * @return mixed
     * @throws \Exception If the setting does not exist or if the current user is not allowed to change the value
     *                    of this setting.
     */
    public function getValue(Setting $setting)
    {
        $this->loadSettingsIfNotDoneYet();

        if (array_key_exists($setting->getKey(), $this->settingsValues)) {
            return $this->settingsValues[$setting->getKey()];
        }

        return $setting->defaultValue;
    }

    /**
     * Sets (overwrites) the value of a setting in memory. To persist the change, {@link save()} must be
     * called afterwards, otherwise the change has no effect.
     *
     * Before the setting is changed, the {@link Piwik\Settings\Setting::$validate} and
     * {@link Piwik\Settings\Setting::$transform} closures will be invoked (if defined). If there is no validation
     * filter, the setting value will be casted to the appropriate data type.
     *
     * @param Setting $setting
     * @param string $value
     * @throws \Exception If the setting does not exist or if the current user is not allowed to change the value
     *                    of this setting.
     */
    public function setValue(Setting $setting, $value)
    {
        $this->loadSettingsIfNotDoneYet();

        $this->settingsValues[$setting->getKey()] = $value;
    }

    /**
     * Unsets a setting value in memory. To persist the change, {@link save()} must be
     * called afterwards, otherwise the change has no effect.
     *
     * @param Setting $setting
     */
    public function deleteValue(Setting $setting)
    {
        $this->loadSettingsIfNotDoneYet();

        $key = $setting->getKey();

        if (array_key_exists($key, $this->settingsValues)) {
            unset($this->settingsValues[$key]);
        }
    }

    private function clearBackendCache()
    {
        Backend\Cache::clearCache();
    }

    private function loadSettingsIfNotDoneYet()
    {
        if ($this->settingValuesLoaded) {
            return;
        }

        $this->settingValuesLoaded = true;
        $this->settingsValues = $this->backend->load();
    }
}
