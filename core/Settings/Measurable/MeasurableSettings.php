<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Measurable;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Settings\SettingConfig;
use Piwik\Settings\Settings;
use Piwik\Settings\Storage;
use Piwik\Site;
use Exception;

/**
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\ExampleSettingsPlugin} plugin.
 *
 * $MeasurableSettings->settingFooBar->getValue($id)
 * $MeasurableSettings->settingFooBar->setValue($idSite, $test)
 *
 * $settingProvider->getMeasruableSettingForPlugin($pluginname, $idsite);
 * @api
 */
abstract class MeasurableSettings extends Settings
{
    /**
     * @var int
     */
    protected $idSite;

    /**
     * @var string
     */
    protected $idMeasurableType;

    /**
     * Constructor.
     * @param int $idSite If creating settings for a new site that is not created yet, use idSite = 0
     * @param string|null $idMeasurableType If null, idType will be detected from idSite
     * @throws Exception
     */
    public function __construct($idSite, $idMeasurableType = null)
    {
        parent::__construct();

        $this->idSite = (int) $idSite;

        if (!empty($idMeasurableType)) {
            $this->idMeasurableType = $idMeasurableType;
        } elseif (!empty($idSite)) {
            $this->idMeasurableType = Site::getTypeFor($idSite);
        } else {
            throw new Exception('No idType specified for ' . get_class($this));
        }

        $this->init();
    }

    protected function hasMeasurableType($typeId)
    {
        return $typeId === $this->idMeasurableType;
    }

    protected function makeMeasurableSetting($name, $defaultValue, $configureCallback)
    {
        $setting = new MeasurableSetting($name, $defaultValue, $this->pluginName, $this->idSite);
        $setting->setConfigureCallback($configureCallback);

        $this->addSetting($setting);

        return $setting;
    }

    protected function makeMeasurableProperty($name, $defaultValue, $configureCallback)
    {
        $setting = new MeasurableProperty($name, $defaultValue, $this->pluginName, $this->idSite);
        $setting->setConfigureCallback($configureCallback);

        $this->addSetting($setting);

        return $setting;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save()
    {
        parent::save();

        /**
         * Triggered after a plugin settings have been updated.
         *
         * **Example**
         *
         *     Piwik::addAction('MeasurableSettings.updated', function (Settings $settings) {
         *         $value = $settings->someSetting->getValue();
         *         // Do something with the new setting value
         *     });
         *
         * @param Settings $settings The plugin settings object.
         */
        Piwik::postEvent('MeasurableSettings.updated', array($this, $this->idSite));
    }
}
