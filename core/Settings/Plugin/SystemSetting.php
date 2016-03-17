<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Plugin;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\Storage;

/**
 * Describes a system wide setting. Only the Super User can change this type of setting and
 * the value of this setting will affect all users.
 *
 * See {@link \Piwik\Plugin\Settings}.
 *
 * @api
 */
class SystemSetting extends Setting
{

    /**
     * Constructor.
     *
     * @param string $name The setting's persisted name.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $pluginName The name of the plugin the system setting belongs to.
     */
    public function __construct($name, $defaultValue, $pluginName)
    {
        parent::__construct($name, $defaultValue, $pluginName);

        $factory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->storage = $factory->getPluginStorage($this->pluginName, $userLogin = '');

        $this->setIsWritableByCurrentUser(Piwik::hasUserSuperUserAccess());
    }

    /**
     * Returns `true` if this setting is writable for the current user, `false` if otherwise. In case it returns
     * writable for the current user it will be visible in the Plugin settings UI.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        if ($this->hasConfigValue()) {
            return false;
        }

        return parent::isWritableByCurrentUser();
    }

    public function getValue()
    {
        $defaultValue = parent::getValue(); // we access value first to make sure permissions are checked

        $configValue = $this->getValueFromConfig();

        if (isset($configValue)) {
            $defaultValue = $configValue;
            settype($defaultValue, $this->config->type);
        }

        return $defaultValue;
    }

    private function hasConfigValue()
    {
        $value = $this->getValueFromConfig();
        return isset($value);
    }

    private function getValueFromConfig()
    {
        $config = Config::getInstance()->{$this->pluginName};

        if (!empty($config) && array_key_exists($this->key, $config)) {
            return $config[$this->key];
        }
    }

}
