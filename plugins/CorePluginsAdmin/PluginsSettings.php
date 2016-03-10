<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Plugin\SettingsProvider;
use Piwik\Settings\Plugin\PluginSettings;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Plugin\UserSetting;

/**
 * Settings manager.
 *
 */
class PluginsSettings
{
    /**
     * @var SettingsProvider
     */
    private $settingsProvider;

    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Gets all plugins settings that have at least one settings a user is allowed to change. Only the settings for
     * activated plugins are returned.
     *
     * @return \Piwik\Settings\Plugin\PluginSettings[]   An array containing array([pluginName] => [setting instance]).
     */
    public function getPluginSettingsForCurrentUser()
    {
        $settings = $this->settingsProvider->getAllPluginsSettings();

        return array_filter($settings, function ($pluginSettings) {
            /** @var PluginSettings $pluginSettings */
            $forUser = $pluginSettings->getSettingsWritableByCurrentUser();
            return !empty($forUser);
        });
    }

    public function hasSystemPluginSettingsForCurrentUser($pluginName)
    {
        $pluginNames = $this->getPluginNamesHavingSystemSettings();

        return in_array($pluginName, $pluginNames);
    }

    /**
     * Detects whether there are user settings for activated plugins available that the current user can change.
     *
     * @return bool
     */
    public function hasUserPluginsSettingsForCurrentUser()
    {
        $settings = $this->getPluginSettingsForCurrentUser();

        foreach ($settings as $setting) {
            foreach ($setting->getSettingsWritableByCurrentUser() as $set) {
                if ($set instanceof UserSetting) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPluginNamesHavingSystemSettings()
    {
        $settings = $this->getPluginSettingsForCurrentUser();
        $plugins  = array();

        foreach ($settings as $pluginName => $setting) {
            foreach ($setting->getSettingsWritableByCurrentUser() as $set) {
                if ($set instanceof SystemSetting) {
                    $plugins[] = $pluginName;
                    break;
                }
            }
        }

        return array_unique($plugins);
    }

    /**
     * Detects whether there are system settings for activated plugins available that the current user can change.
     *
     * @return bool
     */
    public function hasSystemPluginsSettingsForCurrentUser()
    {
        $settings = $this->getPluginNamesHavingSystemSettings();

        return !empty($settings);
    }
}
