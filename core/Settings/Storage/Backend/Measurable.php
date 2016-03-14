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

    private $commaSeparatedArrayFields = array(
        'sitesearch_keyword_parameters',
        'sitesearch_category_parameters',
        'excluded_user_agents',
        'excluded_parameters',
        'excluded_ips'
    );

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
        $model = $this->getModel();

        foreach ($values as $key => $value) {
            if (is_array($value) && in_array($key, $this->commaSeparatedArrayFields)) {
                $values[$key] = implode(',', $value);
            }
        }

        if (!empty($values['urls'])) {
            $values['urls'] = array_unique($values['urls']);
            $urls = $values['urls'];
            $values['main_url'] = array_shift($urls);
            unset($values['urls']);

            $model->deleteSiteAliasUrls($this->idSite);
            foreach ($urls as $url) {
                $model->insertSiteUrl($this->idSite, $url);
            }
        }

        $model->updateSite($values, $this->idSite);
    }

    public function load()
    {
        if (!empty($this->idSite)) {
            $site = Site::getSite($this->idSite);

            $urls = $this->getModel();
            $site['urls'] = $urls->getSiteUrlsFromId($this->idSite);

            foreach ($this->commaSeparatedArrayFields as $field) {
                if (!empty($site[$field]) && is_string($site[$field])) {
                    $site[$field] = explode(',', $site[$field]);
                }
            }

            return $site;
        }
    }

    private function getModel()
    {
        return new Model();
    }

    public function delete()
    {

    }

}
