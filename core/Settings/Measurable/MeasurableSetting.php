<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Measurable;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Settings\Storage;

/**
 * Describes a Type setting for a website, mobile app, ...
 *
 * See {@link \Piwik\Plugin\Settings}.
 */
class MeasurableSetting extends \Piwik\Settings\Setting
{
    private $idSite = 0;

    /**
     * Constructor.
     *
     * @param string $name The persisted name of the setting.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $pluginName The name of the plugin the setting belongs to
     * @param int $idSite The idSite this setting belongs to.
     */
    public function __construct($name, $defaultValue, $pluginName, $idSite)
    {
        parent::__construct($name, $defaultValue, $pluginName);

        $this->idSite = $idSite;

        $storageFactory = StaticContainer::get('Piwik\Settings\Storage\Factory');

        if (!empty($idSite)) {
            $this->isWritableByCurrentUser = Piwik::isUserHasAdminAccess($idSite);
            $this->storage = $storageFactory->getMeasurableSettingsStorage($idSite);
        } else {
            // when no idSite is set yet, likely a site is created and this requires SuperUserAccess
            $this->isWritableByCurrentUser = Piwik::hasUserSuperUserAccess();
            $this->storage = $storageFactory->getNonPersistentStorage('site' . $idSite);
        }

        $this->updateKey($pluginName);
    }

    public function updateKey($pluginName)
    {
        // the asterisk tag is indeed important here and better than an underscore. Imagine a plugin has the settings
        // "api_password" and "api". A user having the login "_password" could otherwise under circumstances change the
        // setting for "api" although he is not allowed to. It is not so important at the moment because only alNum is
        // currently allowed as a name this might change in the future.
        $appendix = '#' . $pluginName . '#';

        if (!Common::stringEndsWith($this->key, $appendix)) {
            $this->key = $this->name . $appendix;
        }
    }
}
