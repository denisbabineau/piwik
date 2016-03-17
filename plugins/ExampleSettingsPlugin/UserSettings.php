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
 * $settings = new UserSettings();
 * $settings->autoRefresh->getValue();
 * $settings->color->getValue();
 */
class UserSettings extends \Piwik\Settings\Plugin\UserSettings
{
    /** @var Setting */
    public $autoRefresh;

    /** @var Setting */
    public $refreshInterval;

    /** @var Setting */
    public $color;

    protected function init()
    {
        // User setting --> checkbox converted to bool
        $this->autoRefresh = $this->createAutoRefreshSetting();

        // User setting --> textbox converted to int defining a validator and filter
        $this->refreshInterval = $this->createRefreshIntervalSetting();

        // User setting --> radio
        $this->color = $this->createColorSetting();
    }

    private function createAutoRefreshSetting()
    {
        return $this->makeSetting('autoRefresh', $default = false, function (SettingConfig $config) {
            $config->title = 'Auto refresh';
            $config->type = SettingConfig::TYPE_BOOL;
            $config->uiControl = SettingConfig::UI_CONTROL_CHECKBOX;
            $config->description = 'If enabled, the value will be automatically refreshed depending on the specified interval';
        });
    }

    private function createRefreshIntervalSetting()
    {
        return $this->makeSetting('refreshInterval', $default = '30', function (SettingConfig $config) {
                $config->title = 'Refresh Interval';
                $config->type  = SettingConfig::TYPE_INT;
                $config->uiControl = SettingConfig::UI_CONTROL_TEXT;
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
        return $this->makeSetting('color', $default = 'red', function (SettingConfig $config) {
            $config->title = 'Color';
            $config->uiControl = SettingConfig::UI_CONTROL_RADIO;
            $config->description = 'Pick your favourite color';
            $config->availableValues = array('red' => 'Red', 'blue' => 'Blue', 'green' => 'Green');
        });
    }

}
