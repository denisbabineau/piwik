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

/**
 * Base setting type class.
 *
 * @api
 */
class MeasurableSettingsTable implements BackendInterface
{
    /**
     * @var int
     */
    private $idSite;

    /**
     * @var Db\AdapterInterface
     */
    private $db;

    public function __construct($idSite)
    {
        $this->idSite = $idSite;
        $this->db = Db::get();
    }

    public function getStorageId()
    {
        return 'Measurable_' . $this->idSite . '_Settings';
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        $table = $this->getTableName();

        $existingValues = $this->load();

        //TODO this does not work unless we request per pluginname!
        foreach ($existingValues as $name => $delete) {
            if (!array_key_exists($name, $values)) {
                $sql  = "DELETE FROM $table WHERE `idsite` = ? and `setting_name` = ?";
                $bind = array($this->idSite, $name);

                $this->db->query($sql, $bind);
            }
        }

        foreach ($values as $name => $value) {
            $value = serialize($value);

            $sql  = "INSERT INTO $table (`idsite`, `setting_name`, `setting_value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?";
            $bind = array($this->idSite, $name, $value, $value);

            $this->db->query($sql, $bind);
        }
    }

    public function load()
    {
        $sql  = "SELECT `setting_name`, `setting_value` FROM " . $this->getTableName() . " WHERE idsite = ?";
        $bind = array($this->idSite);

        $settings =$this->db->fetchAll($sql, $bind);

        $flat = array();
        foreach ($settings as $setting) {
            $flat[$setting['setting_name']] = unserialize($setting['setting_value']);
        }

        return $flat;
    }

    private function getTableName()
    {
        return Common::prefixTable('site_setting');
    }

    public function delete()
    {
        $table = $this->getTableName();
        $sql   = "DELETE FROM $table WHERE `idsite` = ?";
        $bind  = array($this->idSite);

        $this->db->query($sql, $bind);
    }

}
