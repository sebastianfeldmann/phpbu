<?php
namespace phpbu\App;

/**
 * Version test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Version::createSource
     */
    public function testId()
    {
        $version = Version::id();
        $this->assertTrue(strpos($version, '1.2') !== false, 'version should match');

        $cachedVersion = Version::id();
        $this->assertTrue(strpos($cachedVersion, '1.2') !== false, 'version should match');
    }

    /**
     * Tests Version::createSource
     */
    public function testGetVersion()
    {
        $version = Version::getVersionString();

        $this->assertEquals('phpbu 1.2', substr($version, 0, 9), 'version should match');
    }

    /**
     * Tests Version::createSource
     */
    public function testGetReleaseChannel()
    {
        $channel = Version::getReleaseChannel();

        $this->assertEquals('', $channel, 'default channel should not be alpha or beta');
    }
}
