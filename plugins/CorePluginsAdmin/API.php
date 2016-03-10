<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Settings\Plugin\SystemSetting;
use Exception;
use Piwik\Settings\Plugin\UserSetting;

/**
 * API for plugin CorePluginsAdmin
 *
 * @method static \Piwik\Plugins\CorePluginsAdmin\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var PluginsSettings
     */
    private $pluginsSettings;

    /**
     * @var SettingsMetadata
     */
    private $settingsMetadata;

    public function __construct(PluginsSettings $pluginsSettings, SettingsMetadata $settingsMetadata)
    {
        $this->pluginsSettings = $pluginsSettings;
        $this->settingsMetadata = $settingsMetadata;
    }

    public function setSystemSettings($settingValues)
    {
        Piwik::checkUserHasSuperUserAccess();

        $pluginsSettings = $this->pluginsSettings->getPluginSettingsForCurrentUser();

        $this->settingsMetadata->setPluginSettings($pluginsSettings, $settingValues, function ($setting) {
            return $setting instanceof SystemSetting;
        });
    }

    public function setUserSettings($settingValues)
    {
        Piwik::checkUserIsNotAnonymous();

        $pluginsSettings = $this->pluginsSettings->getPluginSettingsForCurrentUser();

        $this->settingsMetadata->setPluginSettings($pluginsSettings, $settingValues, function ($setting) {
            return $setting instanceof UserSetting;
        });
    }
    public function getSystemSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $systemSettings = $this->pluginsSettings->getAllWritableSystemSettings();

        return $this->settingsMetadata->formatSettings($systemSettings);
    }

    public function getUserSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $userSettings = $this->pluginsSettings->getAllWritableUserSettings();

        return $this->settingsMetadata->formatSettings($userSettings);
    }

}
