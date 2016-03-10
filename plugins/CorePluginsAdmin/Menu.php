<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Db;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuUser;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    /**
     * @var PluginsSettings
     */
    private $pluginsSettings;

    public function __construct(PluginsSettings $pluginsSettings)
    {
        $this->pluginsSettings = $pluginsSettings;
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $hasSuperUserAcess    = Piwik::hasUserSuperUserAccess();
        $isAnonymous          = Piwik::isUserIsAnonymous();
        $isMarketplaceEnabled = CorePluginsAdmin::isMarketplaceEnabled();

        $pluginsUpdateMessage = '';

        if ($hasSuperUserAcess && $isMarketplaceEnabled) {
            $marketplace = new Marketplace();
            $pluginsHavingUpdate = $marketplace->getPluginsHavingUpdate($themesOnly = false);
            $themesHavingUpdate  = $marketplace->getPluginsHavingUpdate($themesOnly = true);

            if (!empty($pluginsHavingUpdate)) {
                $pluginsUpdateMessage = sprintf(' (%d)', count($pluginsHavingUpdate) + count($themesHavingUpdate));
            }
        }

        if (!$isAnonymous) {
            $menu->addPlatformItem(null, "", $order = 7);
        }

        if ($hasSuperUserAcess) {
            $menu->addManageItem(Piwik::translate('General_Plugins') . $pluginsUpdateMessage,
                                   $this->urlForAction('plugins', array('activated' => '')),
                                   $order = 4);


            if ($this->pluginsSettings->hasSystemPluginsSettingsForCurrentUser()) {
                $menu->addSettingsItem('CoreAdminHome_PluginSettings',
                    $this->urlForAction('adminPluginSettings'),
                    $order = 7);
            }
            if (CorePluginsAdmin::isMarketplaceEnabled()) {
                $menu->addManageItem('CorePluginsAdmin_Marketplace',
                    $this->urlForAction('marketplace', array('activated' => '', 'mode' => 'admin')),
                    $order = 12);
            }
        }
    }

    public function configureUserMenu(MenuUser $menu)
    {
        $isAnonymous = Piwik::isUserIsAnonymous();

        if ($isAnonymous) {
            return;
        }

        if (CorePluginsAdmin::isMarketplaceEnabled()) {
            $menu->addPlatformItem('CorePluginsAdmin_Marketplace',
                                   $this->urlForAction('marketplace', array('activated' => '', 'mode' => 'user')),
                                   $order = 5);
        }


        if ($this->pluginsSettings->hasUserPluginsSettingsForCurrentUser()) {
            $menu->addPersonalItem('CoreAdminHome_PluginSettings',
                $this->urlForAction('userPluginSettings'),
                $order = 15);
        }
    }
}
