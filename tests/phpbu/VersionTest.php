<?php
namespace phpbu\App;

/**
 * Version test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class VersionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Version::createSource
     */
    public function testId()
    {
        $version = Version::id();
        $this->assertTrue(strpos($version, '5.1') !== false, 'version should match');

        $cachedVersion = Version::id();
        $this->assertTrue(strpos($cachedVersion, '5.1') !== false, 'version should match');
    }

    /**
     * Tests Version::createSource
     */
    public function testGetVersion()
    {
        $version = Version::getVersionString();

        $this->assertEquals('phpbu 5.1', substr($version, 0, 9), 'version should match');
    }

    /**
     * Tests Version::getVersionNumber
     */
    public function testVersionNumberDev()
    {
        $version = new Version('5.1', dirname(dirname(dirname(__DIR__))));

        $this->assertEquals('5.1-dev', $number  = $version->getVersionNumber());
    }

    /**
     * Tests Version::getVersionNumber
     */
    public function testVersionNumberRelease()
    {
        $version = new Version('5.1.0', dirname(dirname(dirname(__DIR__))));

        $this->assertEquals('5.1.0', $number  = $version->getVersionNumber());
    }
}
