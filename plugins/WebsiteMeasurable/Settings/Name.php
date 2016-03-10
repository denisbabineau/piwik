<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WebsiteMeasurable;

use Piwik\Settings\Measurable\MeasurableProperty;
use Piwik\Settings\Settings;
use Piwik\Settings\Storage;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new PluginSettings('ExampleSettingsPlugin');
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class Name extends SettingsConfig
{
    public function __construct($idSite, $pluginName)
    {
        $this->type  = Settings::TYPE_STRING;
        $this->uiControlType = Settings::CONTROL_TEXT;
        $this->uiControlAttributes = array('size' => 3);
        $this->description     = 'Defines how often the value should be updated';
        $this->inlineHelp      = 'Enter a number which is >= 15';
        $this->defaultValue    = '';
        $this->validate = function ($value, $setting) {
            if ($value < 15) {
                throw new \Exception('Value is invalid');
            }
        };
    }

}
