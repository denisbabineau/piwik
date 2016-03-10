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
class Plugin extends OptionTable
{
    public function __construct($pluginName)
    {
        parent::__construct('Plugin_' . $pluginName . '_Settings');
    }

}
