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
        $autoRefresh = new SettingConfig('autoRefresh', 'Auto refresh');
        $autoRefresh->type = SettingConfig::TYPE_BOOL;
        $autoRefresh->uiControlType = SettingConfig::CONTROL_CHECKBOX;
        $autoRefresh->description = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        $autoRefresh->defaultValue = false;

        return $this->makeUserSetting($autoRefresh);
    }

    private function createRefreshIntervalSetting()
    {
        $refreshInterval = new SettingConfig('refreshInterval', 'Refresh Interval');
        $refreshInterval->type  = SettingConfig::TYPE_INT;
        $refreshInterval->uiControlType = SettingConfig::CONTROL_TEXT;
        $refreshInterval->uiControlAttributes = array('size' => 3);
        $refreshInterval->description = 'Defines how often the value should be updated';
        $refreshInterval->inlineHelp  = 'Enter a number which is >= 15';
        $refreshInterval->defaultValue = '30';
        $refreshInterval->validate = function ($value, $setting) {
            if ($value < 15) {
                throw new \Exception('Value is invalid');
            }
        };

        return $this->makeUserSetting($refreshInterval);
    }

    private function createColorSetting()
    {
        $color = new SettingConfig('color', 'Color');
        $color->uiControlType = SettingConfig::CONTROL_RADIO;
        $color->description = 'Pick your favourite color';
        $color->availableValues = array('red' => 'Red', 'blue' => 'Blue', 'green' => 'Green');

        return $this->makeUserSetting($color);
    }

    private function createMetricSetting()
    {
        $metric = new SettingConfig('metric', 'Metric to display');
        $metric->type = SettingConfig::TYPE_STRING;
        $metric->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
        $metric->availableValues = array('nb_visits' => 'Visits', 'nb_actions' => 'Actions', 'visitors' => 'Visitors');
        $metric->introduction = 'Only Super Users can change the following settings:';
        $metric->description = 'Choose the metric that should be displayed in the browser tab';
        $metric->defaultValue = 'nb_visits';

        return $this->makeSystemSetting($metric);
    }

    private function createBrowsersSetting()
    {
        $browsers = new SettingConfig('browsers', 'Supported Browsers');
        $browsers->type = SettingConfig::TYPE_ARRAY;
        $browsers->uiControlType = SettingConfig::CONTROL_MULTI_SELECT;
        $browsers->availableValues = array('firefox' => 'Firefox', 'chromium' => 'Chromium', 'safari' => 'safari');
        $browsers->description = 'The value will be only displayed in the following browsers';
        $browsers->defaultValue = array('firefox', 'chromium', 'safari');

        return $this->makeSystemSetting($browsers);
    }

    private function createDescriptionSetting()
    {
        $description = new SettingConfig('description', 'Description for value');
        $description->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $description->description = 'This description will be displayed next to the value';
        $description->defaultValue = "This is the value: \nAnother line";

        return $this->makeSystemSetting($description);
    }

    private function createPasswordSetting()
    {
        $password = new SettingConfig('password', 'API password');
        $password->uiControlType = SettingConfig::CONTROL_PASSWORD;
        $password->description = 'Password for the 3rd API where we fetch the value';
        $password->transform = function ($value) {
            return sha1($value . 'salt');
        };

        return $this->makeSystemSetting($password);
    }
}
