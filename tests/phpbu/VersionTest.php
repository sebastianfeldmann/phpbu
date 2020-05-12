<?php
namespace phpbu\App;

use PHPUnit\Framework\TestCase;

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
class VersionTest extends TestCase
{
    const VERSION = '6.0';

    /**
     * Tests Version::createSource
     */
    public function testId()
    {
        $version = Version::id();
        $this->assertStringContainsString(self::VERSION, $version, 'version should match');

        $cachedVersion = Version::id();
        $this->assertStringContainsString(self::VERSION, $cachedVersion, 'version should match');
    }

    /**
     * Tests Version::createSource
     */
    public function testGetVersion()
    {
        $version = Version::getVersionString();

        $this->assertEquals('phpbu ' . self::VERSION, substr($version, 0, 9), 'version should match');
    }

    /**
     * Tests Version::minor
     */
    public function testMinor()
    {
        $this->assertEquals(self::VERSION, Version::minor());
    }

    /**
     * Tests Version::getVersionNumber
     */
    public function testVersionNumberDev()
    {
        $version = new Version(self::VERSION, dirname(dirname(dirname(__DIR__))));

        $this->assertEquals(self::VERSION . '-dev', $number  = $version->getVersionNumber());
        $this->assertEquals(self::VERSION, Version::minor());
    }

    /**
     * Tests Version::getVersionNumber
     */
    public function testVersionNumberRelease()
    {
        $version = new Version(self::VERSION . '.0', dirname(dirname(dirname(__DIR__))));

        $this->assertEquals(self::VERSION . '.0', $number  = $version->getVersionNumber());
        $this->assertEquals(self::VERSION, Version::minor());
    }
}
