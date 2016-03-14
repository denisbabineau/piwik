<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WebsiteMeasurable;
use Piwik\Date;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Settings\Setting;
use Piwik\Settings\SettingConfig;
use Piwik\Plugins\SitesManager;
use Piwik\SettingsServer;

/**
 * Defines Settings for ExampleSettingsPlugin.
 *
 * Usage like this:
 * $settings = new MeasurableSettings($idSite);
 * $settings->autoRefresh->getValue();
 * $settings->metric->getValue();
 */
class MeasurableSettings extends \Piwik\Settings\Measurable\MeasurableSettings
{
    /** @var Setting */
    public $name;

    /** @var Setting */
    public $urls;

    /** @var Setting */
    public $onlyTrackWhitelstedUrls;

    /** @var Setting */
    public $keeppageurlFragments;

    /** @var Setting */
    public $excludedIps;

    /** @var Setting */
    public $excludedParameter;

    /**
     * @var SitesManager\API
     */
    private $sitesManagerApi;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(SitesManager\API $api, Plugin\Manager $pluginManager, $idSite, $idMeasurableType)
    {
        $this->sitesManagerApi = $api;
        $this->pluginManager = $pluginManager;

        parent::__construct($idSite, $idMeasurableType);
    }

    protected function init()
    {
        $sitesManagerApi = $this->sitesManagerApi;

        $this->makeMeasurableProperty('urls', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_Urls';
            $config->inlineHelp = 'SitesManager_AliasUrlHelp';
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '25', 'rows' => '3');
        });

        $default = array("http://siteUrl.com/", "http://siteUrl2.com/");

        $this->makeMeasurableProperty('exclude_unknown_urls', $default, function (SettingConfig $config) {
            $config->title = 'SitesManager_OnlyMatchedUrlsAllowed';
            $config->inlineHelp = array('SitesManager_OnlyMatchedUrlsAllowedHelp', 'SitesManager_OnlyMatchedUrlsAllowedHelpExamples');
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_CHECKBOX;
            $config->transform = function ($value) {
                $values = explode($value, "\n");
                $values = array_map('trim', $values);
                return $values;
            };
        });

        $this->makeMeasurableProperty('keep_url_fragment', $default = '0', function (SettingConfig $config) use ($sitesManagerApi) {
            $config->title = 'SitesManager_KeepURLFragmentsLong';
            $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;

            if ($sitesManagerApi->getKeepURLFragmentsGlobal()) {
                $default = Piwik::translate('General_Yes');
            } else {
                $default = Piwik::translate('General_No');
            }

            $config->availableValues = array(
                '0' => $default . ' (' . Piwik::translate('General_Default') . ')',
                '1' => 'General_Yes',
                '2' => 'General_No'
            );
        });

        $this->makeMeasurableProperty('excluded_ips', $default = array(), function (SettingConfig $config) {
            $ip = IP::getIpFromHeader();

            $config->title = 'SitesManager_ExcludedIps';
            $config->inlineHelp = array(Piwik::translate('SitesManager_HelpExcludedIps', array('1.2.3.*', '1.2.*.*')),
                                        '',
                                        Piwik::translate('SitesManager_YourCurrentIpAddressIs', array('<i>' . $ip . '</i>')));
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        });

        $this->makeMeasurableProperty('excluded_parameters', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_ExcludedParameters';
            $config->inlineHelp = array('SitesManager_ListOfQueryParametersToExclude',
                                        '',
                                        Piwik::translate('SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters', array('phpsessid, sessionid, ...')));
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        });

        $this->makeMeasurableProperty('excluded_user_agents', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_ExcludedUserAgents';
            $config->inlineHelp = array('SitesManager_GlobalExcludedUserAgentHelp1',
                '',
                'SitesManager_GlobalListExcludedUserAgents_Desc',
                'SitesManager_GlobalExcludedUserAgentHelp2');
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        });


        /**
         * SiteSearch
         */
        $this->makeMeasurableProperty('sitesearch', $default = '1', function (SettingConfig $config) {
            $config->title = 'Actions_SubmenuSitesearch';
            $config->inlineHelp = 'SitesManager_SiteSearchUse';
            $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
            $config->availableValues = array(
                '1' => 'SitesManager_EnableSiteSearch',
                '0' => 'SitesManager_DisableSiteSearch'
            );
        });

        $property = $this->makeMeasurableProperty('use_default_site_search_params', $default = null, function (SettingConfig $config) {

            if (Piwik::hasUserSuperUserAccess()) {
                $title = Piwik::translate('SitesManager_SearchUseDefault', array("<a href='#globalSettings'>","</a>"));
            } else {
                $title = Piwik::translate('SitesManager_SearchUseDefault', array('', ''));
            }

            $config->title = $title;
            $config->type = SettingConfig::TYPE_BOOL;
            $config->uiControlType = SettingConfig::CONTROL_CHECKBOX;
            $config->showIf = 'sitesearch';
        });
        $property->setIsWritableByCurrentUser(!empty($searchKeywordsGlobal));

        $this->makeMeasurableProperty('default_value_info', $default = null, function (SettingConfig $config) use ($sitesManagerApi) {

            $searchKeywordsGlobal = $sitesManagerApi->getSearchKeywordParametersGlobal();
            $searchCategoryGlobal = $sitesManagerApi->getSearchCategoryParametersGlobal();

            $config->title  = Piwik::translate('SitesManager_SearchKeywordLabel');
            $config->title .= Piwik::translate('General_Default') . ': ';
            $config->title .= implode(',', $searchKeywordsGlobal) . ' & ';
            $config->title .= Piwik::translate('SitesManager_SearchCategoryLabel') . ': ';
            $config->title .= implode(',', $searchCategoryGlobal);
            $config->uiControlType = SettingConfig::CONTROL_HIDDEN;

            $hasParams = 'false';
            if (!empty($searchKeywordsGlobal)) {
                $hasParams = 'true';
            }

            $config->showIf = $hasParams . ' && sitesearch && use_default_site_search_params';
        });

        $this->makeMeasurableProperty('sitesearch_keyword_parameters', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_SearchKeywordLabel';
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->inlineHelp = 'SitesManager_SearchKeywordParametersDesc';
            $config->showIf = 'sitesearch && !use_default_site_search_params';
        });

        $property = $this->makeMeasurableProperty('sitesearch_category_parameters', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_SearchCategoryLabel';
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
            $config->inlineHelp = array('Goals_Optional', 'SitesManager_SearchCategoryParametersDesc');
            $config->showIf = 'sitesearch';
        });
        $property->setIsWritableByCurrentUser($this->pluginManager->isPluginActivated('CustomVariables'));
        /**
         * SiteSearch End
         */


        $this->makeMeasurableProperty('ecommerce', $default = '0', function (SettingConfig $config) {
            $config->title = 'Goals_Ecommerce';
            $config->inlineHelp = array('SitesManager_EcommerceHelp',
                                        Piwik::translate('SitesManager_PiwikOffersEcommerceAnalytics',
                                                         array("<a href='http://piwik.org/docs/ecommerce-analytics/' target='_blank'>", '</a>')));
            $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
            $config->availableValues = array(
                '0' => 'SitesManager_NotAnEcommerceSite',
                '1' => 'SitesManager_EnableEcommerce'
            );
        });

        $this->makeMeasurableProperty('timezone', $default = 'UTC', function (SettingConfig $config) use ($sitesManagerApi) {
            $config->title = 'SitesManager_Timezone';

            $inlineHelp = array();
            if (SettingsServer::isTimezoneSupportEnabled()) {
                $inlineHelp[] = 'SitesManager_ChooseCityInSameTimezoneAsYou';
            } else {
                $inlineHelp[] = 'SitesManager_AdvancedTimezoneSupportNotFound';
            }

            $inlineHelp[] = Piwik::translate('SitesManager_UTCTimeIs', array(Date::now()->getDatetime()));
            $inlineHelp[] = 'SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward';

            $config->inlineHelp = $inlineHelp;
            $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
            $config->availableValues = $sitesManagerApi->getTimezonesList();
        });

    }

}
