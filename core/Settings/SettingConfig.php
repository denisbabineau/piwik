<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Settings;

/**
 * Base setting type class.
 *
 * @api
 */
class SettingConfig
{
    const TYPE_INT    = 'integer';
    const TYPE_FLOAT  = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOL   = 'boolean';
    const TYPE_ARRAY  = 'array';

    const UI_CONTROL_RADIO    = 'radio';
    const UI_CONTROL_TEXT     = 'text';
    const UI_CONTROL_TEXTAREA = 'textarea';
    const UI_CONTROL_CHECKBOX = 'checkbox';
    const UI_CONTROL_PASSWORD = 'password';
    const UI_CONTROL_MULTI_SELECT  = 'multiselect';
    const UI_CONTROL_SINGLE_SELECT = 'select';
    const UI_CONTROL_HIDDEN = 'hidden';

    /**
     * Describes the setting's PHP data type. When saved, setting values will always be casted to this
     * type.
     *
     * See {@link Piwik\Plugin\Settings} for a list of supported data types.
     *
     * @var string
     */
    public $type = null;

    /**
     * Describes what HTML element should be used to manipulate the setting through Piwik's UI.
     *
     * See {@link Piwik\Plugin\Settings} for a list of supported control types.
     *
     * @var string
     */
    public $uiControl = null;

    /**
     * Name-value mapping of HTML attributes that will be added HTML form control, eg,
     * `array('size' => 3)`. Attributes will be escaped before outputting.
     *
     * @var array
     */
    public $uiControlAttributes = array();

    /**
     * The list of all available values for this setting. If null, the setting can have any value.
     *
     * If supplied, this field should be an array mapping available values with their prettified
     * display value. Eg, if set to `array('nb_visits' => 'Visits', 'nb_actions' => 'Actions')`,
     * the UI will display **Visits** and **Actions**, and when the user selects one, Piwik will
     * set the setting to **nb_visits** or **nb_actions** respectively.
     *
     * The setting value will be validated if this field is set. If the value is not one of the
     * available values, an error will be triggered.
     *
     * _Note: If a custom validator is supplied (see {@link $validate}), the setting value will
     * not be validated._
     *
     * @var null|array
     */
    public $availableValues = null;

    /**
     * Text that will appear above this setting's section in the _Plugin Settings_ admin page.
     *
     * @var null|string
     */
    public $introduction = null;

    /**
     * Text that will appear directly underneath the setting title in the _Plugin Settings_ admin
     * page. If set, should be a short description of the setting.
     *
     * @var null|string
     */
    public $description = null;

    /**
     * Text that will appear next to the setting's section in the _Plugin Settings_ admin page. If set,
     * it should contain information about the setting that is more specific than a general description,
     * such as the format of the setting value if it has a special format.
     *
     * @var null|string
     */
    public $inlineHelp = null;

    /**
     * A closure that does some custom validation on the setting before the setting is persisted.
     *
     * The closure should take two arguments: the setting value and the {@link Setting} instance being
     * validated. If the value is found to be invalid, the closure should throw an exception with
     * a message that describes the error.
     *
     * **Example**
     *
     *     $setting->validate = function ($value, Setting $setting) {
     *         if ($value > 60) {
     *             throw new \Exception('The time limit is not allowed to be greater than 60 minutes.');
     *         }
     *     }
     *
     * @var null|\Closure
     */
    public $validate = null;

    /**
     * A closure that transforms the setting value. If supplied, this closure will be executed after
     * the setting has been validated.
     *
     * _Note: If a transform is supplied, the setting's {@link $type} has no effect. This means the
     * transformation function will be responsible for casting the setting value to the appropriate
     * data type._
     *
     * **Example**
     *
     *     $setting->transform = function ($value, Setting $setting) {
     *         if ($value > 30) {
     *             $value = 30;
     *         }
     *
     *         return (int) $value;
     *     }
     *
     * @var null|\Closure
     */
    public $transform = null;

    /**
     * This setting's display name, for example, `'Refresh Interval'`.
     *
     * @var string
     */
    public $title = '';

    /**
     * Here you can define conditions so that certain form fields will be only shown when a certain condition
     * is true. This condition is supposed to be evaluated on the client side dynamically. This way you can hide
     * for example some fields depending on another field. For example if SiteSearch is disabled, fields to enter
     * site search keywords is not needed anymore and can be disabled.
     *
     * For example 'sitesearch', or 'sitesearch && !use_sitesearch_default' where 'sitesearch' and 'use_sitesearch_default'
     * are both values of fields.
     *
     * @var string
     */
    public $showIf;

    public function getDefaultType($controlType)
    {
        $defaultTypes = array(
            static::UI_CONTROL_TEXT          => static::TYPE_STRING,
            static::UI_CONTROL_TEXTAREA      => static::TYPE_STRING,
            static::UI_CONTROL_PASSWORD      => static::TYPE_STRING,
            static::UI_CONTROL_CHECKBOX      => static::TYPE_BOOL,
            static::UI_CONTROL_MULTI_SELECT  => static::TYPE_ARRAY,
            static::UI_CONTROL_RADIO         => static::TYPE_STRING,
            static::UI_CONTROL_SINGLE_SELECT => static::TYPE_STRING,
        );

        if (isset($defaultTypes[$controlType])) {
            return $defaultTypes[$controlType];
        }

        return static::TYPE_STRING;
    }

    public function getDefaultUiControl($type)
    {
        $defaultControlTypes = array(
            static::TYPE_INT    => static::UI_CONTROL_TEXT,
            static::TYPE_FLOAT  => static::UI_CONTROL_TEXT,
            static::TYPE_STRING => static::UI_CONTROL_TEXT,
            static::TYPE_BOOL   => static::UI_CONTROL_CHECKBOX,
            static::TYPE_ARRAY  => static::UI_CONTROL_MULTI_SELECT,
        );

        if (isset($defaultControlTypes[$type])) {
            return $defaultControlTypes[$type];
        }

        return static::UI_CONTROL_TEXT;
    }

}
