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
use Piwik\Settings\Setting;
use Piwik\Settings\Settings;
use Piwik\Settings\Storage;
use Piwik\Site;

/**
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\ExampleSettingsPlugin} plugin.
 *
 * @api
 */
abstract class MeasurableSettings extends Settings
{
    /**
     * @var int
     */
    protected $idSite;

    /**
     * Constructor.
     */
    public function __construct($idSite)
    {
        parent::__construct();

        $this->idSite = (int) $idSite;
        $this->storage = Storage\Factory::make('measurable', $idSite);

        $this->init();
    }

    protected function hasSiteTypeId($typeId)
    {
        return $typeId === Site::getTypeFor($this->idSite);
    }

    public function addSetting(Setting $setting)
    {
        if (!$setting instanceof MeasurableSetting) {
            throw new \Exception('Only a MeasurableSetting can be added to MeasurableSettings');
        }

        parent::addSetting($setting);
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
