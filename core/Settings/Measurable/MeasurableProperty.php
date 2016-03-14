<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Measurable;

use Piwik\Piwik;
use Piwik\Settings\Storage;

/**
 * Describes a Type setting for a website, mobile app, ...
 *
 * See {@link \Piwik\Plugin\Settings}.
 */
class MeasurableProperty extends \Piwik\Settings\Setting
{

    /**
     * Constructor.
     *
     * @param string $name The persisted name of the setting.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $pluginName The name of the plugin the setting belongs to.
     * @param int $idSite The idSite this setting belongs to.
     */
    public function __construct($name, $defaultValue, $pluginName, $idSite)
    {
        parent::__construct($name, $defaultValue, $pluginName);

        $this->idSite = $idSite;

        if (!empty($idSite)) {
            $this->isWritableByCurrentUser = Piwik::isUserHasAdminAccess($idSite);
        } else {
            // when no idSite is set yet, likely a site is created and this requires SuperUserAccess
            $this->isWritableByCurrentUser = Piwik::hasUserSuperUserAccess();
        }

        $storageFactory = new Storage\Factory();

        if (!empty($idSite)) {
            $this->storage = $storageFactory->getMeasurableStorage($idSite);
        } else {
            $this->storage = $storageFactory->getNonPersistentStorage('site' . $idSite);
        }
    }
}
