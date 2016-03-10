<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Plugin;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Settings\Settings;
use Piwik\Settings\Storage;

/**
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\ExampleSettingsPlugin} plugin.
 *
 * @api
 */
abstract class PluginSettings extends Settings
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->storage = Storage\Factory::make('plugin', $this->pluginName);

        $this->init();
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        parent::save();

        /**
         * Triggered after a plugin settings have been updated.
         *
         * **Example**
         *
         *     Piwik::addAction('PluginSettings.updated', function (PluginSettings $settings) {
         *         if ($settings->getPluginName() === 'PluginName') {
         *             $value = $settings->someSetting->getValue();
         *             // Do something with the new setting value
         *         }
         *     });
         *
         * @param Settings $settings The plugin settings object.
         */
        Piwik::postEvent('PluginSettings.updated', array($this));
    }
}
