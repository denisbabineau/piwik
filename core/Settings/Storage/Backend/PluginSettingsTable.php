<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

use Piwik\Common;
use Piwik\Db;
use Exception;

/**
 * Base setting type class.
 *
 * @api
 */
class PluginSettingsTable implements BackendInterface
{
    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var string
     */
    private $userLogin;

    /**
     * @var Db\AdapterInterface
     */
    private $db;

    public function __construct($pluginName, $userLogin)
    {
        $this->pluginName = $pluginName;
        $this->userLogin = $userLogin;
        $this->db = Db::get();
    }

    public function getStorageId()
    {
        return 'PluginSettings_' . $this->pluginName . '_User_' . $this->userLogin;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        $table = $this->getTableName();

        $existingValues = $this->load();

        foreach ($existingValues as $name => $delete) {
            if (!array_key_exists($name, $values)) {
                $sql  = "DELETE FROM $table WHERE `plugin_name` = ? and `user_login` = ? and `setting_name` = ?";
                $bind = array($this->pluginName, $this->userLogin, $name);

                $this->db->query($sql, $bind);
            }
        }

        foreach ($values as $name => $value) {
            if (!is_array($value)) {
                $value = array($value);
            }

            foreach ($value as $val) {
                $sql  = "INSERT INTO $table (`plugin_name`, `user_login`, `setting_name`, `setting_value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?";
                $bind = array($this->pluginName, $this->userLogin, $name, $val);

                $this->db->query($sql, $bind);
            }
        }
    }

    public function load()
    {
        $sql  = "SELECT `setting_name`, `setting_value` FROM " . $this->getTableName() . " WHERE plugin_name = ? and user_login = ?";
        $bind = array($this->pluginName, $this->userLogin);

        $settings = $this->db->fetchAll($sql, $bind);

        $flat = array();
        foreach ($settings as $setting) {
            $name = $setting['setting_name'];

            if (array_key_exists($name, $flat)) {
                if (!is_array($flat[$name])) {
                    $flat[$name] = array($flat[$name]);
                }
                $flat[$name][] = $setting['setting_value'];
            } else {
                $flat[$name] = $setting['setting_value'];
            }
        }

        return $flat;
    }

    private function getTableName()
    {
        return Common::prefixTable('plugin_setting');
    }

    public function delete()
    {
        $table = $this->getTableName();
        $sql   = "DELETE FROM $table WHERE `plugin_name` = ? and `user_login` = ?";
        $bind  = array($this->pluginName, $this->userLogin);

        $this->db->query($sql, $bind);
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
            throw new Exception('No userLogin specified. Cannot remove all settings for this user');
        }

        $table = Common::prefixTable('plugin_setting');
        Db::get()->query(sprintf('DELETE FROM %s WHERE user_login = ?', $table), array($userLogin));
    }
}
