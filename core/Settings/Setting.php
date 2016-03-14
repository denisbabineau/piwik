<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings;

use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Settings\Storage\Storage;

/**
 * Base setting type class.
 *
 * @api
 */
abstract class Setting
{

    /**
     * Defines whether a user can change the value and whether a user is allowed to actually see the value
     * of this setting. Eg via UI or API.
     * @var bool
     * @internal
     */
    protected $isWritableByCurrentUser = false;

    /**
     * @internal
     * @ignore
     * @var string
     */
    protected $key;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $pluginName;

    /**
     * @var SettingConfig
     */
    protected $config;

    protected $configureCallback;

    protected $name;

    /**
     * Constructor.
     *
     * @param string $name    The setting's persisted name. Only alphanumeric characters are allowed, eg,
     *                        `'refreshInterval'`.
     * @param string $title   The setting's display name, eg, `'Refresh Interval'`.
     */
    public function __construct(SettingConfig $config, $pluginName)
    {
        $this->setDefaultTypeAndFieldIfNeeded($config);

        $this->config = $config;
        $this->key = $config->getName();
        $this->pluginName = $pluginName;
    }

    public function getName()
    {
        return $this->configure()->getName();
    }

    public function configure()
    {
        if ($this->configureCallback) {
            call_user_func($this->configureCallback, $this->config);
        }

        return $this->config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns `true` if this setting is writable for the current user, `false` if otherwise. In case it returns
     * writable for the current user it will be visible in the Plugin settings UI.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        return $this->isWritableByCurrentUser;
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

    public function save()
    {
        if (isset($this->storage)) {
            $this->storage->save();
        }
    }

    /**
     * Sets the object used to persist settings. Meant for tests only.
     *
     * @internal
     * @ignore
     * @param Storage $storage
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Returns the previously persisted setting value. If no value was set, the default value
     * is returned.
     *
     * @return mixed
     * @throws \Exception If the current user is not allowed to change the value of this setting.
     */
    public function getValue()
    {
        return $this->storage->getValue($this->key, $this->config->defaultValue);
    }

    /**
     * Returns the previously persisted setting value. If no value was set, the default value
     * is returned.
     *
     * @return mixed
     * @throws \Exception If the current user is not allowed to change the value of this setting.
     */
    public function removeValue()
    {
        $this->checkHasEnoughWritePermission();

        $this->storage->deleteValue($this->key);
    }

    /**
     * Sets and persists this setting's value overwriting any existing value.
     *
     * @param mixed $value
     * @throws \Exception If the current user is not allowed to change the value of this setting.
     */
    public function setValue($value)
    {
        $this->validateValue($value);

        if ($this->config->transform && $this->config->transform instanceof \Closure) {
            $value = call_user_func($this->config->transform, $value, $this);
        } elseif (isset($this->type)) {
            settype($value, $this->type);
        }

        $this->storage->setValue($this->key, $value);
    }

    private function validateValue($value)
    {
        $this->checkHasEnoughWritePermission();

        if ($this->config->validate && $this->config->validate instanceof \Closure) {
            call_user_func($this->config->validate, $value, $this);
        } elseif (is_array($this->config->availableValues)) {

            // TODO move error message creation to a subclass, eg in MeasurableSettings we do not want to mention plugin name
            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingsValueNotAllowed',
                                         array($this->config->title, $this->pluginName));

            if (is_array($value) && $this->config->type === SettingConfig::TYPE_ARRAY) {
                foreach ($value as $val) {
                    if (!array_key_exists($val, $this->config->availableValues)) {
                        throw new \Exception($errorMsg);
                    }
                }
            } else {
                if (!array_key_exists($value, $this->config->availableValues)) {
                    throw new \Exception($errorMsg);
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function checkHasEnoughWritePermission()
    {
        // When the request is a Tracker request, allow plugins to write settings
        if (SettingsServer::isTrackerApiRequest()) {
            return;
        }

        if (!$this->isWritableByCurrentUser()) {
            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingChangeNotAllowed', array($this->config->getName(), $this->pluginName));
            throw new \Exception($errorMsg);
        }
    }

    private function setDefaultTypeAndFieldIfNeeded(SettingConfig $config)
    {
        if (empty($config) || !$config instanceof SettingConfig) {
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);exit;
        }
        if (!isset($config->type)) {
            $config->type = $config->getDefaultType($config->uiControlType);
        }

        if (!isset($config->uiControlType)) {
            $config->uiControlType = $config->getDefaultUiControl($config->type);
        }
    }

}
