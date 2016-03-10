<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Storage\Backend;

/**
 * Base type of all Setting storage implementations.
 */
interface BackendInterface
{

    /**
     * Get an id that identifies the current storage. Eg `Plugin_$pluginName_Settings` could be a storage id
     * for plugin settings. It's kind of like a cache key.
     *
     * @return string
     */
    public function getStorageId();

    /**
     * Saves (persists) the current setting values in the database.
     * @param array $values An array of key value pairs where $settingName => $settingValue.
     *                      Eg array('settingName1' > 'settingValue1')
     */
    public function save($values);

    /**
     * Deletes all saved settings.
     * @return void
     */
    public function delete();

    /**
     * Loads previously saved setting values and returns them (if some were saved)
     *
     * @return array An array of key value pairs where $settingName => $settingValue.
     *               Eg array('settingName1' > 'settingValue1')
     */
    public function load();
}
