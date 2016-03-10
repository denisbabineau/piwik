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
     * @param string $name The persisted name of the setting.
     * @param string $title The display name of the setting.
     */
    public function __construct($name, $title)
    {
        parent::__construct($name, $title);

        $this->setIsWritableByCurrentUser(Piwik::hasUserSuperUserAccess());
    }

    public function setPluginName($pluginName)
    {
        parent::setPluginName($pluginName);

        $factory = new Storage\Factory();
        $this->storage = $factory->getPluginStorage($this->pluginName);
    }

    /**
     * Set whether setting is writable or not. For example to hide setting from the UI set it to false.
     *
     * @param bool $isWritable
     */
    public function setIsWritableByCurrentUser($isWritable)
    {
        $this->isWritableByCurrentUser = (bool) $isWritable;
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

    /**
     * Returns the display order. System settings are displayed before user settings.
     *
     * @return int
     */
    public function getOrder()
    {
        return 30;
    }

    public function getValue()
    {
        $defaultValue = parent::getValue(); // we access value first to make sure permissions are checked

        $configValue = $this->getValueFromConfig();

        if (isset($configValue)) {
            $defaultValue = $configValue;
            settype($defaultValue, $this->type);
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

        if (!empty($config) && array_key_exists($this->name, $config)) {
            return $config[$this->name];
        }
    }

}
