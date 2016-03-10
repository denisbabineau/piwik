<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings;

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
abstract class Settings
{
    /**
     * An array containing all available settings: Array ( [setting-name] => [setting] )
     *
     * @var Setting[]
     */
    private $settings = array();

    protected $pluginName;

    public function __construct()
    {
        if (!isset($this->pluginName)) {
            $classname = get_class($this);
            $parts     = explode('\\', $classname);

            if (count($parts) >= 3) {
                $this->pluginName = $parts[2];
            } else {
                throw new \Exception(sprintf('Plugin Settings must have a plugin name specified in %s, could not detect plugin name', $classname));
            }
        }
    }

    /**
     * @ignore
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }

    /**
     * @ignore
     * @return Setting
     */
    public function getSetting($name)
    {
        if (array_key_exists($name, $this->settings)) {
            return $this->settings[$name];
        }
    }

    /**
     * Implemented by descendants. This method should define plugin settings (via the
     * {@link addSetting()}) method and set the introduction text (via the
     * {@link setIntroduction()}).
     */
    abstract protected function init();

    /**
     * Returns the settings that can be displayed for the current user.
     *
     * @return Setting[]
     */
    public function getSettingsWritableByCurrentUser()
    {
        return array_filter($this->getSettings(), function (Setting $setting) {
            return $setting->isWritableByCurrentUser();
        });
    }

    /**
     * Returns all available settings. This will include settings that are not available
     * to the current user (such as settings available only to the Super User).
     *
     * @return Setting[]
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Makes a new plugin setting available.
     *
     * @param Setting $setting
     * @throws \Exception       If there is a setting with the same name that already exists.
     *                          If the name contains non-alphanumeric characters.
     */
    protected function addSetting(Setting $setting)
    {
        $name = $setting->getConfig()->getName();

        if (array_key_exists($name, $this->settings)) {
            throw new \Exception(sprintf('A setting with name "%s" does already exist for plugin "%s"', $name, $this->pluginName));
        }

        $this->settings[$name] = $setting;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        foreach ($this->settings as $setting) {
            $setting->save();
        }
    }

}
