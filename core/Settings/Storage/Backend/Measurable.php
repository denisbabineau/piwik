<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings\Storage\Backend;

use Piwik\API\Request;
use Piwik\Db;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\SitesManager\Model;
use Piwik\Site;

/**
 * Base setting type class.
 *
 * @api
 */
class Measurable implements BackendInterface
{
    /**
     * @var int
     */
    private $idSite;

    public function __construct($idSite)
    {
        $this->idSite = $idSite;
    }

    public function getStorageId()
    {
        return 'Measurable_' . $this->idSite;
    }

    /**
     * Saves (persists) the current setting values in the database.
     */
    public function save($values)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = implode(',', $value);
            }
        }

        // TODO handle urls?

        $model = new Model();
        $model->updateSite($values, $this->idSite);
    }

    public function load()
    {
        if (!empty($this->idSite)) {
            $site = Site::getSite($this->idSite);

            $urls = new Model();
            $site['urls'] = $urls->getSiteUrlsFromId($this->idSite);

            return $site;
        }
    }

    public function delete()
    {

    }

}
