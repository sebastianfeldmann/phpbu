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
class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Version::createSource
     */
    public function testId()
    {
        $version = Version::id();
        $this->assertTrue(strpos($version, '3.1') !== false, 'version should match');

        $cachedVersion = Version::id();
        $this->assertTrue(strpos($cachedVersion, '3.1') !== false, 'version should match');
    }

    /**
     * Tests Version::createSource
     */
    public function testGetVersion()
    {
        $version = Version::getVersionString();

        $this->assertEquals('phpbu 3.1', substr($version, 0, 9), 'version should match');
    }
}
