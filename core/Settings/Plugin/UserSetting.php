<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Plugin;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\SettingsProvider;
use Piwik\Settings\Setting;
use Piwik\Settings\Storage;

/**
 * Describes a per user setting. Each user will be able to change this setting for themselves,
 * but not for other users.
 */
class UserSetting extends Setting
{
    private $userLogin = null;

    /**
     * Null while not initialized, bool otherwise.
     * @var null|bool
     */
    private $hasWritePermission = null;

    /**
     * Constructor.
     *
     * @param string $name The setting's persisted name.
     * @param mixed $defaultValue  Default value for this setting if no value was specified.
     * @param string $pluginName The name of the plugin the setting belongs to
     * @param string $userLogin The name of the user the value should be set or get for
     */
    public function __construct($name, $defaultValue, $pluginName, $userLogin)
    {
        parent::__construct($name, $defaultValue, $pluginName);

        $factory = StaticContainer::get('Piwik\Settings\Storage\Factory');
        $this->storage = $factory->getPluginStorage($this->pluginName);

        $this->setUserLogin($userLogin);
    }

    /**
     * Returns `true` if this setting can be displayed for the current user, `false` if otherwise.
     *
     * @return bool
     */
    public function isWritableByCurrentUser()
    {
        if (isset($this->hasWritePermission)) {
            return $this->hasWritePermission;
        }

        // performance improvement, do not detect this in __construct otherwise likely rather "big" query to DB.
        $this->hasWritePermission = Piwik::isUserHasSomeViewAccess();

        return $this->hasWritePermission;
    }

    /**
     * Set whether setting is writable or not. For example to hide setting from the UI set it to false.
     *
     * @param bool $isWritable
     */
    public function setIsWritableByCurrentUser($isWritable)
    {
        $this->hasWritePermission = (bool) $isWritable;
    }

    /**
     * Sets the name of the user this setting will be set for.
     *
     * @param $userLogin
     * @throws \Exception If the current user does not have permission to set the setting value
     *                    of `$userLogin`.
     */
    public function setUserLogin($userLogin)
    {
        if (!Piwik::hasUserSuperUserAccessOrIsTheUser($userLogin)) {
            throw new \Exception('You do not have the permission to read the settings of a different user');
        }

        $this->userLogin = $userLogin;
        $this->key       = $this->buildUserSettingName($this->name, $userLogin);
    }

    private function buildUserSettingName($name, $userLogin)
    {
        // the asterisk tag is indeed important here and better than an underscore. Imagine a plugin has the settings
        // "api_password" and "api". A user having the login "_password" could otherwise under circumstances change the
        // setting for "api" although he is not allowed to. It is not so important at the moment because only alNum is
        // currently allowed as a name this might change in the future.
        $appendix = '#' . $userLogin . '#';

        if (Common::stringEndsWith($name, $appendix)) {
            return $name;
        }

        return $name . $appendix;
    }

    /**
     * Unsets all settings for a user. The settings will be removed from the database. Used when
     * a user is deleted.
     *
     * @param string $userLogin
     * @throws \Exception If the `$userLogin` is empty.
     */
    public static function removeAllUserSettingsForUser($userLogin)
    {
        if (empty($userLogin)) {
            throw new \Exception('No userLogin specified');
        }

        $settings = new SettingsProvider(\Piwik\Plugin\Manager::getInstance());
        $pluginsSettings = $settings->getAllPluginSettings();

        foreach ($pluginsSettings as $pluginSettings) {
            $settings = $pluginSettings->getSettings();

            foreach ($settings as $setting) {
                if ($setting instanceof UserSetting) {
                    $setting->setUserLogin($userLogin);
                    $setting->removeValue();
                }
            }

            $pluginSettings->save();
        }
    }
}
