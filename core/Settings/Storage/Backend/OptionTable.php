<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

use Piwik\Option;

/**
 * Base setting type class.
 *
 * @api
 */
class OptionTable implements BackendInterface
{
    private $storageId;

    public function __construct($storageId)
    {
        $this->storageId = $storageId;
    }

    public function getStorageId()
    {
        return $this->storageId;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        Option::set($this->storageId, serialize($values));
    }

    public function delete()
    {
        Option::delete($this->storageId);
    }

    public function load()
    {
        $values = Option::get($this->storageId);

        if (!empty($values)) {
            return unserialize($values);
        }

        return array();
    }
}
