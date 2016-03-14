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

    public function __construct(SitesManager\API $api, $idSite, $idMeasurableType)
    {
        $this->sitesManagerApi = $api;

        parent::__construct($idSite, $idMeasurableType);
    }

    protected function init()
    {
        $config = new SettingConfig('urls', 'SitesManager_Urls');
        $config->inlineHelp = 'SitesManager_AliasUrlHelp';
        $config->defaultValue = '';
        $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $config->uiControlAttributes = array('cols' => '25', 'rows' => '3');
        $this->makeMeasurableProperty($config);


        $config = new SettingConfig('exclude_unknown_urls', 'SitesManager_OnlyMatchedUrlsAllowed');
        $config->inlineHelp = array('SitesManager_OnlyMatchedUrlsAllowedHelp', 'SitesManager_OnlyMatchedUrlsAllowedHelpExamples');
        $config->defaultValue = array("http://siteUrl.com/", "http://siteUrl2.com/");
        $config->type = SettingConfig::TYPE_ARRAY;
        $config->transform = function ($value) {
            $values = explode($value, "\n");
            $values = array_map('trim', $values);
            return $values;
        };

        $config->uiControlType = SettingConfig::CONTROL_CHECKBOX;
        $this->makeMeasurableProperty($config);


        $keepUrlFragmentsGlobal = $this->sitesManagerApi->getKeepURLFragmentsGlobal();

        $config = new SettingConfig('keep_url_fragment', 'SitesManager_KeepURLFragmentsLong');
        $config->defaultValue = '0';
        $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;

        $default = (bool) $keepUrlFragmentsGlobal ? Piwik::translate('General_Yes') : Piwik::translate('General_No');

        $config->availableValues = array(
            '0' => $default . ' (' . Piwik::translate('General_Default') . ')',
            '1' => 'General_Yes',
            '2' => 'General_No'
        );
        $this->makeMeasurableProperty($config);


        $config = new SettingConfig('excluded_ips', 'SitesManager_ExcludedIps');
        $config->inlineHelp = array(Piwik::translate('SitesManager_HelpExcludedIps', array('1.2.3.*', '1.2.*.*')),
                                    '',
                                    Piwik::translate('SitesManager_YourCurrentIpAddressIs', array('<i>' . IP::getIpFromHeader() . '</i>')));
        $config->defaultValue = '';
        $config->type = SettingConfig::TYPE_ARRAY;
        $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        $this->makeMeasurableProperty($config);

        $config = new SettingConfig('excluded_parameters', 'SitesManager_ExcludedParameters');
        $config->inlineHelp = array('SitesManager_ListOfQueryParametersToExclude',
                                    '',
                                    Piwik::translate('SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters', array('phpsessid, sessionid, ...')));
        $config->defaultValue = '';
        $config->type = SettingConfig::TYPE_ARRAY;
        $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        $this->makeMeasurableProperty($config);


        $config = new SettingConfig('excluded_user_agents', 'SitesManager_ExcludedUserAgents');
        $config->inlineHelp = array('SitesManager_GlobalExcludedUserAgentHelp1',
                                    '',
                                    'SitesManager_GlobalListExcludedUserAgents_Desc',
                                    'SitesManager_GlobalExcludedUserAgentHelp2');
        $config->defaultValue = '';
        $config->type = SettingConfig::TYPE_ARRAY;
        $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        $this->makeMeasurableProperty($config);


        /**
         * SiteSearch
         */
        $config = new SettingConfig('sitesearch', 'Actions_SubmenuSitesearch');
        $config->defaultValue = '1';
        $config->inlineHelp = 'SitesManager_SiteSearchUse';
        $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
        $config->availableValues = array(
            '1' => 'SitesManager_EnableSiteSearch',
            '0' => 'SitesManager_DisableSiteSearch'
        );

        $this->makeMeasurableProperty($config);

        $searchKeywordsGlobal = $this->sitesManagerApi->getSearchKeywordParametersGlobal();
        $searchCategoryGlobal = $this->sitesManagerApi->getSearchCategoryParametersGlobal();

        if (Piwik::hasUserSuperUserAccess()) {
            $title = Piwik::translate('SitesManager_SearchUseDefault', array("<a href='#globalSettings'>","</a>"));
        } else {
            $title = Piwik::translate('SitesManager_SearchUseDefault', array('', ''));
        }

        $config = new SettingConfig('use_default_site_search_params', $title);
        $config->type = SettingConfig::TYPE_BOOL;
        $config->uiControlType = SettingConfig::CONTROL_CHECKBOX;
        $prop = $this->makeMeasurableProperty($config);
        $prop->setIsWritableByCurrentUser(!empty($searchKeywordsGlobal));

        $config->showIf = 'sitesearch';

        $title  = Piwik::translate('SitesManager_SearchKeywordLabel');
        $title .= Piwik::translate('General_Default') . ': ';
        $title .= implode(',', $searchKeywordsGlobal) . ' & ';
        $title .= Piwik::translate('SitesManager_SearchCategoryLabel') . ': ';
        $title .= implode(',', $searchCategoryGlobal);

        $config = new SettingConfig('test', $title);
        $config->uiControlType = SettingConfig::CONTROL_HIDDEN;

        $prop = $this->makeMeasurableProperty($config);
        $prop->setIsWritableByCurrentUser($this->sitesManagerApi->getSearchKeywordParametersGlobal());

        $config->showIf = 'sitesearch && use_default_site_search_params';

        $config = new SettingConfig('sitesearch_keyword_parameters', 'SitesManager_SearchKeywordLabel');
        $config->type = SettingConfig::TYPE_ARRAY;
        $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $config->inlineHelp = 'SitesManager_SearchKeywordParametersDesc';
        $config->showIf = 'sitesearch && !use_default_site_search_params';
        $this->makeMeasurableProperty($config);

        $config = new SettingConfig('sitesearch_category_parameters', 'SitesManager_SearchCategoryLabel');
        $config->type = SettingConfig::TYPE_ARRAY;
        $config->uiControlType = SettingConfig::CONTROL_TEXTAREA;
        $config->inlineHelp = array('Goals_Optional', 'SitesManager_SearchCategoryParametersDesc');
        $config->showIf = 'sitesearch';
        $prop = $this->makeMeasurableProperty($config);
        $prop->setIsWritableByCurrentUser(Plugin\Manager::getInstance()->isPluginActivated('CustomVariables'));
        /**
         * SiteSearch End
         */


        $config = new SettingConfig('timezone', 'SitesManager_Timezone');

        $inlineHelp = array();
        if (SettingsServer::isTimezoneSupportEnabled()) {
            $inlineHelp[] = 'SitesManager_ChooseCityInSameTimezoneAsYou';
        } else {
            $inlineHelp[] = 'SitesManager_AdvancedTimezoneSupportNotFound';
        }

        $inlineHelp[] = Piwik::translate('SitesManager_UTCTimeIs', array(Date::now()->getDatetime()));
        $inlineHelp[] = 'SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward';

        $config->inlineHelp = $inlineHelp;
        $config->defaultValue = 'UTC';
        $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
        $config->availableValues = $this->sitesManagerApi->getTimezonesList();

        $this->makeMeasurableProperty($config);



        $config = new SettingConfig('ecommerce', 'Goals_Ecommerce');

        $config->inlineHelp = array('SitesManager_EcommerceHelp',
                                    Piwik::translate('SitesManager_PiwikOffersEcommerceAnalytics', array("<a href='http://piwik.org/docs/ecommerce-analytics/' target='_blank'>", '</a>')));
        $config->defaultValue = '0';
        $config->uiControlType = SettingConfig::CONTROL_SINGLE_SELECT;
        $config->availableValues = array(
            '0' => 'SitesManager_NotAnEcommerceSite',
            '1' => 'SitesManager_EnableEcommerce'
        );

        $this->makeMeasurableProperty($config);
    }

}
