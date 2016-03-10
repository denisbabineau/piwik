<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Storage;

use Piwik\Settings\Storage\Storage;
use Piwik\Settings\Storage\Factory;
use Piwik\SettingsServer;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Tracker
 * @group Handler
 * @group Visit
 * @group Factory
 * @group FactoryTest
 */
class FactoryTest extends IntegrationTestCase
{
    private function getFactory()
    {
        return new Factory();
    }

    public function test_make_shouldCreateDefaultInstance()
    {
        $storage = $this->getFactory()->getPluginStorage('PluginName');
        $this->assertTrue($storage instanceof Storage);
    }

    public function test_make_shouldCreateTrackerInstance_IfInTrackerMode()
    {
        $storage = $this->makeTrackerInstance();

        $this->assertTrue($storage->getBackend() instanceof Storage\Backend\Cache);
    }

    public function test_make_shouldPassThePluginNameToTheStorage()
    {
        $storage = $this->getFactory()->getPluginStorage('PluginName');
        $this->assertEquals('Plugin_PluginName_Settings', $storage->getBackend()->getStorageId());
    }

    public function test_make_shouldPassThePluginNameToTheSettingsStorage()
    {
        $storage = $this->makeTrackerInstance();

        $this->assertEquals('Plugin_PluginName_Settings', $storage->getBackend()->getStorageId());
    }

    private function makeTrackerInstance()
    {
        SettingsServer::setIsTrackerApiRequest();

        $storage = $this->getFactory()->getPluginStorage('PluginName');

        SettingsServer::setIsNotTrackerApiRequest();

        return $storage;
    }
}
