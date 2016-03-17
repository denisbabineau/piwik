<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleSettingsPlugin;

use Piwik\Settings\Setting;
use Piwik\Settings\SettingConfig;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new SystemSettings();
 * $settings->metric->getValue();
 * $settings->description->getValue();
 */
class SystemSettings extends \Piwik\Settings\Plugin\SystemSettings
{
    /** @var Setting */
    public $metric;

    /** @var Setting */
    public $browsers;

    /** @var Setting */
    public $description;

    /** @var Setting */
    public $password;

    protected function init()
    {
        // System setting --> allows selection of a single value
        $this->metric = $this->createMetricSetting();

        // System setting --> allows selection of multiple values
        $this->browsers = $this->createBrowsersSetting();

        // System setting --> textarea
        $this->description = $this->createDescriptionSetting();

        // System setting --> textarea
        $this->password = $this->createPasswordSetting();
    }

    private function createMetricSetting()
    {
        return $this->makeSetting('metric', $default = 'nb_visits', function (SettingConfig $config) {
            $config->title = 'Metric to display';
            $config->type = SettingConfig::TYPE_STRING;
            $config->uiControl = SettingConfig::UI_CONTROL_SINGLE_SELECT;
            $config->availableValues = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
            $config->introduction = 'Only Super Users can change the following settings:';
            $config->description = 'Choose the metric that should be displayed in the browser tab';
        });
    }

    private function createBrowsersSetting()
    {
        $default = array('firefox', 'chromium', 'safari');

        return $this->makeSetting('browsers', $default, function (SettingConfig $config) {
            $config->title = 'Supported Browsers';
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_MULTI_SELECT;
            $config->availableValues = array('firefox' => 'Firefox', 'chromium' => 'Chromium', 'safari' => 'safari');
            $config->description = 'The value will be only displayed in the following browsers';
        });
    }

    private function createDescriptionSetting()
    {
        $default = "This is the value: \nAnother line";

        return $this->makeSetting('description', $default, function (SettingConfig $config) {
            $config->title = 'Description for value';
            $config->uiControl = SettingConfig::UI_CONTROL_TEXTAREA;
            $config->description = 'This description will be displayed next to the value';
        });
    }

    private function createPasswordSetting()
    {
        return $this->makeSetting('password', $default = null, function (SettingConfig $config) {
            $config->title = 'API password';
            $config->uiControl = SettingConfig::UI_CONTROL_PASSWORD;
            $config->description = 'Password for the 3rd API where we fetch the value';
            $config->transform = function ($value) {
                return sha1($value . 'salt');
            };
        });
    }
}
