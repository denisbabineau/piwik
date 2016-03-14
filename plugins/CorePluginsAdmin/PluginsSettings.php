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
        $settings = $this->settingsProvider->getAllPluginSettings();

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

    public function getAllWritableSystemSettings()
    {
        $pluginSettings = $this->settingsProvider->getAllPluginSettings();

        $systemSettings = array();

        foreach ($pluginSettings as $pluginName => $settings) {
            foreach ($settings->getSettingsWritableByCurrentUser() as $writableSetting) {
                if ($writableSetting instanceof SystemSetting) {
                    if (!isset($systemSettings[$pluginName])) {
                        $systemSettings[$pluginName] = array();
                    }

                    $systemSettings[$pluginName][] = $writableSetting;
                }
            }
        }

        return $systemSettings;
    }

    public function getAllWritableUserSettings()
    {
        $pluginSettings = $this->settingsProvider->getAllPluginSettings();

        $userSettings = array();

        foreach ($pluginSettings as $pluginName => $settings) {
            foreach ($settings->getSettingsWritableByCurrentUser() as $writableSetting) {
                if ($writableSetting instanceof UserSetting) {
                    if (!isset($userSettings[$pluginName])) {
                        $userSettings[$pluginName] = array();
                    }

                    $userSettings[$pluginName][] = $writableSetting;
                }
            }
        }

        return $userSettings;
    }

    public function getPluginNamesHavingSystemSettings()
    {
        return array_keys($this->getAllWritableSystemSettings());
    }

    /**
     * Detects whether there are system settings for activated plugins available that the current user can change.
     *
     * @return bool
     */
    public function hasSystemPluginsSettingsForCurrentUser()
    {
        $settings = $this->getAllWritableSystemSettings();

        return !empty($settings);
    }
}
