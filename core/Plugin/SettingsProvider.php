<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\CacheId;
use Piwik\Plugin;
use Piwik\Cache as PiwikCache;

/**
 * Base class of all plugin settings providers. Plugins that define their own configuration settings
 * can extend this class to easily make their settings available to Piwik users.
 *
 * Descendants of this class should implement the {@link init()} method and call the
 * {@link addSetting()} method for each of the plugin's settings.
 *
 * For an example, see the {@link Piwik\Plugins\ExampleSettingsPlugin\ExampleSettingsPlugin} plugin.
 *
 * @api
 */
class SettingsProvider
{
    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    public function __construct(Plugin\Manager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return \Piwik\Settings\Plugin\PluginSettings|null
     */
    public function getPluginSettings($pluginName)
    {
        if (!$this->pluginManager->isPluginLoaded($pluginName)) {
            return;
        }

        try {
            if (!$this->pluginManager->isPluginActivated($pluginName)) {
                return;
            }

            $plugin = $this->pluginManager->getLoadedPlugin($pluginName);
        } catch (\Exception $e) {
            // we are not allowed to use possible settings from this plugin, plugin is not active
            return;
        }

        $settings = $plugin->findComponent('Settings', 'Piwik\\Settings\\Settings');

        return $settings;
    }

    /**
     * Returns all available plugin settings, even settings for inactive plugins. A plugin has to specify a file named
     * `Settings.php` containing a class named `Settings` that extends `Piwik\Settings\Settings` in order to be
     * considered as a plugin setting. Otherwise the settings for a plugin won't be available.
     *
     * @return \Piwik\Settings\Settings[]   An array containing array([pluginName] => [setting instance]).
     */
    public function getAllPluginsSettings()
    {
        $cacheId = CacheId::languageAware('AllPluginSettings');
        $cache = PiwikCache::getTransientCache();

        if (!$cache->contains($cacheId)) {
            /** @var \Piwik\Settings\Settings[] $settings */
            $settings = $this->pluginManager->findComponents('Settings', '\\Piwik\\Settings\\Settings');

            $byPluginName = array();

            foreach ($settings as $setting) {
                $byPluginName[$setting->getPluginName()] = $setting;
            }

            $cache->save($cacheId, $byPluginName);
        }

        return $cache->fetch($cacheId);
    }
}
