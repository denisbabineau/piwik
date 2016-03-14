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
 * $settings = new PluginSettings();
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class PluginSettings extends \Piwik\Settings\Plugin\PluginSettings
{
    /** @var Setting */
    public $autoRefresh;

    /** @var Setting */
    public $refreshInterval;

    /** @var Setting */
    public $color;

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
        // User setting --> checkbox converted to bool
        $this->autoRefresh = $this->createAutoRefreshSetting();

        // User setting --> textbox converted to int defining a validator and filter
        $this->refreshInterval = $this->createRefreshIntervalSetting();

        // User setting --> radio
        $this->color = $this->createColorSetting();

        // System setting --> allows selection of a single value
        $this->metric = $this->createMetricSetting();

        // System setting --> allows selection of multiple values
        $this->browsers = $this->createBrowsersSetting();

        // System setting --> textarea
        $this->description = $this->createDescriptionSetting();

        // System setting --> textarea
        $this->password = $this->createPasswordSetting();
    }

    private function createAutoRefreshSetting()
    {
        return $this->makeUserSetting('autoRefresh', $default = false, function (SettingConfig $config) {
            $config->title = 'Auto refresh';
            $config->type = SettingConfig::TYPE_BOOL;
            $config->uiControlType = SettingConfig::CONTROL_CHECKBOX;
            $config->description = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        });
    }

    private function createRefreshIntervalSetting()
    {
        return $this->makeUserSetting('refreshInterval', $default = '30', function (SettingConfig $config) {
                $config->title = 'Refresh Interval';
                $config->type  = SettingConfig::TYPE_INT;
                $config->uiControlType = SettingConfig::CONTROL_TEXT;
                $config->uiControlAttributes = array('size' => 3);
                $config->description = 'Defines how often the value should be updated';
                $config->inlineHelp  = 'Enter a number which is >= 15';
                $config->validate = function ($value, $setting) {
                if ($value < 15) {
                    throw new \Exception('Value is invalid');
                }
            };
        });
    }

    private function createColorSetting()
    {
        return $this->makeUserSetting('color', $default = null, function (SettingConfig $config) {
            $config->title = 'Color';
            $config->uiControlType = SettingConfig::CONTROL_RADIO;
            $config->description = 'Pick your favourite color';
            $config->availableValues = array('red' => 'Red', 'blue' => 'Blue', 'green' => 'Green');
        });
    }

    private function createMetricSetting()
    {
        return $this->makeSystemSetting('metric', $default = 'nb_visits', function (SettingConfig $config) {
            $config->title = 'Metric to display';
            $config->type = SettingConfig::TYPE_STRING;
            $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
            $config->availableValues = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
            $config->introduction = 'Only Super Users can change the following settings:';
            $config->description = 'Choose the metric that should be displayed in the browser tab';
        });
    }

    private function createBrowsersSetting()
    {
        $default = array('firefox', 'chromium', 'safari');

        return $this->makeSystemSetting('browsers', $default, function (SettingConfig $config) {
            $config->title = 'Supported Browsers';
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_MULTI_SELECT;
            $config->availableValues = array('firefox' => 'Firefox', 'chromium' => 'Chromium', 'safari' => 'safari');
            $config->description = 'The value will be only displayed in the following browsers';
        });
    }

    private function createDescriptionSetting()
    {
        $default = "This is the value: \nAnother line";

        return $this->makeSystemSetting('description', $default, function (SettingConfig $config) {
            $config->title = 'Description for value';
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->description = 'This description will be displayed next to the value';
        });
    }

    private function createPasswordSetting()
    {
        return $this->makeSystemSetting('password', $default = null, function (SettingConfig $config) {
            $config->title = 'API password';
            $config->uiControlType = SettingConfig::CONTROL_PASSWORD;
            $config->description = 'Password for the 3rd API where we fetch the value';
            $config->transform = function ($value) {
                return sha1($value . 'salt');
            };
        });
    }
}
