<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WebsiteMeasurable;
use Piwik\Settings\Setting;
use Piwik\Settings\SettingConfig;


/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new MeasurableSettings($idSite);
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var Setting */
    public $name;

    /** @var Setting */
    public $urls;

    /** @var Setting */
    public $onlyTrackWhitelstedUrls;

    /** @var Setting */
    public $keeppageurlFragments;

    /** @var Setting */
    public $excludedIps;

    /** @var Setting */
    public $excludedParameter;

    protected function init()
    {
    //    if ($this->hasMeasurableType(Type::ID)) {
            $this->makeMeasurableSetting(new SettingConfig('myName', 'mytest'));
      //  }
    }

}
