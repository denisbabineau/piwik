<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Settings\Setting;
use Piwik\Settings\Settings;
use Exception;

class SettingsMetadata
{

    /**
     * @param Settings[]  $settingsInstances
     * @param array $settingValues   array('pluginName' => array('settingName' => 'settingValue'))
     * @param \Callable $filterCallback
     * @throws Exception;
     */
    public function setPluginSettings($settingsInstances, $settingValues, $filterCallback)
    {
        try {
            foreach ($settingsInstances as $pluginName => $pluginSetting) {
                foreach ($pluginSetting->getSettingsWritableByCurrentUser() as $setting) {

                    if (!call_user_func($filterCallback, $setting)) {
                        continue;
                    }

                    $value = $this->findSettingValueFromRequest($settingValues, $pluginName, $setting->getName());

                    if (isset($value)) {
                        $setting->setValue($value);
                    }
                }
            }

        } catch (Exception $e) {
            $message = $e->getMessage();

            if (!empty($setting)) {
                throw new Exception($setting->configure()->title . ': ' . $message);
            }
        }

        try {
            foreach ($settingsInstances as $pluginSetting) {
                $pluginSetting->save();
            }
        } catch (Exception $e) {
            throw new Exception(Piwik::translate('CoreAdminHome_PluginSettingsSaveFailed'));
        }
    }

    private function findSettingValueFromRequest($settingValues, $pluginName, $settingName)
    {
        if (!array_key_exists($pluginName, $settingValues)) {
            return;
        }

        $settings = $settingValues[$pluginName];

        if (array_key_exists($settingName, $settings)) {
            $value = $settings[$settingName];

            if (is_string($value)) {
                return Common::unsanitizeInputValue($value);
            }

            return $value;
        }
    }


    /**
     * @param Setting[][] $writableSettings A list of Settings instead by pluginname
     * @return array
     */
    public function formatSettings($writableSettings)
    {
        $metadata = array();
        foreach ($writableSettings as $pluginName => $settings) {
            $plugin = array(
                'pluginName' => $pluginName,
                'settings' => array()
            );
            foreach ($settings as $writableSetting) {
                $plugin['settings'][] = $this->formatMetadata($writableSetting);
            }
            $metadata[] = $plugin;
        }

        return $metadata;
    }

    private function formatMetadata(Setting $setting)
    {
        $config = $setting->configure();

        if (isset($config->inlineHelp) && !is_array($config->inlineHelp)) {
            $config->inlineHelp = array($config->inlineHelp);
        }

        if (is_array($config->inlineHelp)) {
            foreach ($config->inlineHelp as $key => $help) {
                $config->inlineHelp[$key] = Piwik::translate('' . $help);
            }
        }

        if (is_array($config->availableValues)) {
            foreach ($config->availableValues as $key => $value) {
                if (!is_array($value)) {
                    $config->availableValues[$key] = Piwik::translate('' . $value);
                }
            }

            $config->availableValues = (object) $config->availableValues;
        }

        return array(
            'name' => $config->getName(),
            'title' => Piwik::translate($config->title),
            'type' => $config->type,
            'uiControlType' => $config->uiControlType,
            'availableValues' => $config->availableValues,
            'defaultValue' => $config->defaultValue,
            'description' => Piwik::translate($config->description),
            'inlineHelp' => $config->inlineHelp,
            'introduction' => Piwik::translate($config->introduction),
            'uiControlAttributes' => $config->uiControlAttributes,
            'showIf' => $config->showIf,
            'value' => $setting->getValue()
        );
    }

}