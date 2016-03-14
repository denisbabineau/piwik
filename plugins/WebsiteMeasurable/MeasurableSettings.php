<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WebsiteMeasurable;
use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Settings\Setting;
use Piwik\Settings\SettingConfig;
use Piwik\Plugins\SitesManager;
use Exception;
use Piwik\UrlHelper;

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
    public $urls;

    /** @var Setting */
    public $onlyTrackWhitelstedUrls;

    /** @var Setting */
    public $keepPageUrlFragments;

    /** @var Setting */
    public $excludeKnownUrls;

    /** @var Setting */
    public $excludedUserAgents;

    /** @var Setting */
    public $excludedIps;

    /** @var Setting */
    public $siteSearch;

    /** @var Setting */
    public $useDefaultSiteSearchParams;

    /** @var Setting */
    public $siteSearchKeywords;

    /** @var Setting */
    public $siteSearchCategory;

    /** @var Setting */
    public $excludedParameters;

    /** @var Setting */
    public $ecommerce;

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

        $default = array("http://siteUrl.com/", "http://siteUrl2.com/");

        $this->urls = $this->makeMeasurableProperty('urls', $default, function (SettingConfig $config) {
            $config->title = 'SitesManager_Urls';
            $config->inlineHelp = 'SitesManager_AliasUrlHelp';
            $config->uiControl = SettingConfig::UI_CONTROL_TEXTAREA;
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControlAttributes = array('cols' => '25', 'rows' => '3');

            $config->validate = function ($urls) {
                if (!is_array($urls)) {
                    $urls = array($urls);
                }

                foreach ($urls as $url) {
                    if (!UrlHelper::isLookLikeUrl($url)) {
                        throw new Exception(sprintf(Piwik::translate('SitesManager_ExceptionInvalidUrl'), $url));
                    }
                }
            };

            $config->transform = function ($urls) {
                if (!is_array($urls)) {
                    $urls = array($urls);
                }

                $urls = array_filter($urls);
                $urls = array_map('urldecode', $urls);

                foreach ($urls as &$url) {
                    // if there is a final slash, we take the URL without this slash (expected URL format)
                    if (strlen($url) > 5
                        && $url[strlen($url) - 1] == '/'
                    ) {
                        $url = substr($url, 0, strlen($url) - 1);
                    }

                    $scheme = parse_url($url, PHP_URL_SCHEME);
                    if (empty($scheme)
                        && strpos($url, '://') === false
                    ) {
                        $url = 'http://' . $url;
                    }
                    $url = trim($url);
                    $url = Common::sanitizeInputValue($url);
                }

                $urls = array_unique($urls);
                return $urls;
            };
        });

        $this->excludeKnownUrls = $this->makeMeasurableProperty('exclude_unknown_urls', $default = false, function (SettingConfig $config) {
            $config->title = 'SitesManager_OnlyMatchedUrlsAllowed';
            $config->inlineHelp = array('SitesManager_OnlyMatchedUrlsAllowedHelp', 'SitesManager_OnlyMatchedUrlsAllowedHelpExamples');
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_CHECKBOX;
            $config->transform = function ($value) {
                $values = explode($value, "\n");
                $values = array_map('trim', $values);
                return $values;
            };
        });

        $this->keepPageUrlFragments = $this->makeMeasurableProperty('keep_url_fragment', $default = '0', function (SettingConfig $config) use ($sitesManagerApi) {
            $config->title = 'SitesManager_KeepURLFragmentsLong';
            $config->uiControl = SettingConfig::UI_CONTROL_SINGLE_SELECT;

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

        $this->excludedIps = $this->makeMeasurableProperty('excluded_ips', $default = array(), function (SettingConfig $config) {
            $ip = IP::getIpFromHeader();

            $config->title = 'SitesManager_ExcludedIps';
            $config->inlineHelp = array(Piwik::translate('SitesManager_HelpExcludedIps', array('1.2.3.*', '1.2.*.*')),
                                        '',
                                        Piwik::translate('SitesManager_YourCurrentIpAddressIs', array('<i>' . $ip . '</i>')));
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        });

        $this->excludedParameters = $this->makeMeasurableProperty('excluded_parameters', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_ExcludedParameters';
            $config->inlineHelp = array('SitesManager_ListOfQueryParametersToExclude',
                                        '',
                                        Piwik::translate('SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters', array('phpsessid, sessionid, ...')));
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        });

        $this->excludedUserAgents = $this->makeMeasurableProperty('excluded_user_agents', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_ExcludedUserAgents';
            $config->inlineHelp = array('SitesManager_GlobalExcludedUserAgentHelp1',
                '',
                'SitesManager_GlobalListExcludedUserAgents_Desc',
                'SitesManager_GlobalExcludedUserAgentHelp2');
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_TEXTAREA;
            $config->uiControlAttributes = array('cols' => '20', 'rows' => '4');
        });


        /**
         * SiteSearch
         */
        $this->siteSearch = $this->makeMeasurableProperty('sitesearch', $default = '1', function (SettingConfig $config) {
            $config->title = 'Actions_SubmenuSitesearch';
            $config->inlineHelp = 'SitesManager_SiteSearchUse';
            $config->uiControl = SettingConfig::UI_CONTROL_SINGLE_SELECT;
            $config->availableValues = array(
                '1' => 'SitesManager_EnableSiteSearch',
                '0' => 'SitesManager_DisableSiteSearch'
            );
        });

        $this->useDefaultSiteSearchParams = $this->makeMeasurableSetting('use_default_site_search_params', $default = true, function (SettingConfig $config) use ($sitesManagerApi) {

            if (Piwik::hasUserSuperUserAccess()) {
                $title = Piwik::translate('SitesManager_SearchUseDefault', array("<a href='#globalSettings'>","</a>"));
            } else {
                $title = Piwik::translate('SitesManager_SearchUseDefault', array('', ''));
            }

            $config->title = $title;
            $config->type = SettingConfig::TYPE_BOOL;
            $config->uiControl = SettingConfig::UI_CONTROL_CHECKBOX;

            $searchKeywordsGlobal = $sitesManagerApi->getSearchKeywordParametersGlobal();

            $hasParams = (int) !empty($searchKeywordsGlobal);
            $config->showIf = $hasParams . ' && sitesearch';

            $searchKeywordsGlobal = $sitesManagerApi->getSearchKeywordParametersGlobal();
            $searchCategoryGlobal = $sitesManagerApi->getSearchCategoryParametersGlobal();

            $config->description  = Piwik::translate('SitesManager_SearchKeywordLabel');
            $config->description .= ' (' . Piwik::translate('General_Default') . ')';
            $config->description .= ': ';
            $config->description .= $searchKeywordsGlobal;
            $config->description .= ' & ';
            $config->description .= Piwik::translate('SitesManager_SearchCategoryLabel');
            $config->description .= ': ';
            $config->description .= $searchCategoryGlobal;
        });

        $this->siteSearchKeywords = $this->makeMeasurableProperty('sitesearch_keyword_parameters', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_SearchKeywordLabel';
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_TEXT;
            $config->inlineHelp = 'SitesManager_SearchKeywordParametersDesc';
            $config->showIf = 'sitesearch && !use_default_site_search_params';
        });

        $siteSearchKeywords = $this->siteSearchKeywords->getValue();
        $this->useDefaultSiteSearchParams->setDefaultValue(empty($siteSearchKeywords));

        $this->siteSearchCategory = $this->makeMeasurableProperty('sitesearch_category_parameters', $default = array(), function (SettingConfig $config) {
            $config->title = 'SitesManager_SearchCategoryLabel';
            $config->type = SettingConfig::TYPE_ARRAY;
            $config->uiControl = SettingConfig::UI_CONTROL_TEXT;
            $config->inlineHelp = array('Goals_Optional', 'SitesManager_SearchCategoryParametersDesc');
            $config->showIf = 'sitesearch && !use_default_site_search_params';
        });
        $this->siteSearchCategory->setIsWritableByCurrentUser($this->pluginManager->isPluginActivated('CustomVariables'));

        /**
         * SiteSearch End
         */

        $this->ecommerce = $this->makeMeasurableProperty('ecommerce', $default = '0', function (SettingConfig $config) {
            $config->title = 'Goals_Ecommerce';
            $config->inlineHelp = array('SitesManager_EcommerceHelp',
                                        Piwik::translate('SitesManager_PiwikOffersEcommerceAnalytics',
                                                         array("<a href='http://piwik.org/docs/ecommerce-analytics/' target='_blank'>", '</a>')));
            $config->uiControl = SettingConfig::UI_CONTROL_SINGLE_SELECT;
            $config->availableValues = array(
                '0' => 'SitesManager_NotAnEcommerceSite',
                '1' => 'SitesManager_EnableEcommerce'
            );
        });
    }

}
