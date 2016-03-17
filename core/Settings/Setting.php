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
use Exception;

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
     * @var mixed
     */
    protected $defaultValue;

    protected $type;

    /**
     * Constructor.
     *
     * @param string $name    The setting's persisted name. Only alphanumeric characters are allowed, eg,
     *                        `'refreshInterval'`.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $type Eg an array, int, ... see SettingConfig::TYPE_* constants
     * @param string $pluginName   The name of the plugin the setting belongs to
     * @throws Exception
     */
    public function __construct($name, $defaultValue, $type, $pluginName)
    {
        if (!ctype_alnum(str_replace('_', '', $name))) {
            $msg = sprintf('The setting name "%s" in plugin "%s" is invalid. Only underscores, alpha and numerical characters are allowed', $name, $pluginName);
            throw new Exception($msg);
        }

        $this->name = $name;
        $this->key = $name;
        $this->type = $type;
        $this->pluginName = $pluginName;
        $this->setDefaultValue($defaultValue);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setConfigureCallback($callback)
    {
        $this->configureCallback = $callback;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    public function configure()
    {
        if ($this->configureCallback && !$this->config) {
            $this->config = new SettingConfig();
            call_user_func($this->configureCallback, $this->config);
            $this->setUiControlIfNeeded($this->config);
            $this->checkType($this->config);
        } else if (!$this->config) {
            return new SettingConfig();
        }

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
     * Returns the previously persisted setting value. If no value was set, the default value
     * is returned.
     *
     * @return mixed
     * @throws \Exception If the current user is not allowed to change the value of this setting.
     */
    public function getValue()
    {
        return $this->storage->getValue($this->key, $this->defaultValue, $this->type);
    }

    /**
     * Sets and persists this setting's value overwriting any existing value.
     *
     * @param mixed $value
     * @throws \Exception If the current user is not allowed to change the value of this setting.
     */
    public function setValue($value)
    {
        $config = $this->configure();

        $this->validateValue($value);

        if ($config->transform && $config->transform instanceof \Closure) {
            $value = call_user_func($config->transform, $value, $this);
        } elseif (isset($this->type)) {
            settype($value, $this->type);
        }

        $this->storage->setValue($this->key, $value);
    }

    private function validateValue($value)
    {
        $this->checkHasEnoughWritePermission();

        $config = $this->configure();

        if ($config->validate && $config->validate instanceof \Closure) {
            call_user_func($config->validate, $value, $this);
        } elseif (is_array($config->availableValues)) {

            // TODO move error message creation to a subclass, eg in MeasurableSettings we do not want to mention plugin name
            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingsValueNotAllowed',
                                         array($config->title, $this->pluginName));

            if (is_array($value) && $config->type === SettingConfig::TYPE_ARRAY) {
                foreach ($value as $val) {
                    if (!array_key_exists($val, $config->availableValues)) {
                        throw new \Exception($errorMsg);
                    }
                }
            } else {
                if (!array_key_exists($value, $config->availableValues)) {
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
            $errorMsg = Piwik::translate('CoreAdminHome_PluginSettingChangeNotAllowed', array($this->name, $this->pluginName));
            throw new \Exception($errorMsg);
        }
    }

    private function setUiControlIfNeeded(SettingConfig $config)
    {
        if (!isset($config->uiControl)) {
            $defaultControlTypes = array(
                SettingConfig::TYPE_INT    => SettingConfig::UI_CONTROL_TEXT,
                SettingConfig::TYPE_FLOAT  => SettingConfig::UI_CONTROL_TEXT,
                SettingConfig::TYPE_STRING => SettingConfig::UI_CONTROL_TEXT,
                SettingConfig::TYPE_BOOL   => SettingConfig::UI_CONTROL_CHECKBOX,
                SettingConfig::TYPE_ARRAY  => SettingConfig::UI_CONTROL_MULTI_SELECT,
            );

            if (isset($defaultControlTypes[$this->type])) {
                $config->uiControl = $defaultControlTypes[$this->type];
            } else {
                $config->uiControl = SettingConfig::UI_CONTROL_TEXT;
            }
        }
    }

    private function checkType(SettingConfig $config)
    {
        if ($config->uiControl === SettingConfig::UI_CONTROL_MULTI_SELECT &&
            $this->type !== SettingConfig::TYPE_ARRAY) {
            throw new Exception('Type must be an array when using a multi select');
        }

        $types = array(
            SettingConfig::TYPE_INT,
            SettingConfig::TYPE_FLOAT,
            SettingConfig::TYPE_STRING,
            SettingConfig::TYPE_BOOL,
            SettingConfig::TYPE_ARRAY
        );

        if (!in_array($this->type, $types)) {
            throw new Exception('Type does not exist');
        }
    }

}
