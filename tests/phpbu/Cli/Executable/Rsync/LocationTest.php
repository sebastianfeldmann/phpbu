<?php
namespace phpbu\App\Cli\Executable\Rsync;

use PHPUnit\Framework\TestCase;

/**
 * Rsync Location Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.1.4
 */
class LocationTest extends TestCase
{
    /**
     * Tests Rsync::toString
     */
    public function testHostAndPath()
    {
        $location = new Location();
        $location->setHost('example.com');
        $location->setPath('/tmp');
        $this->assertEquals('example.com:/tmp', $location->toString(), 'should be \'host:path\'');
    }

    /**
     * Tests Rsync::toString
     */
    public function testHostAndUser()
    {
        $location = new Location();
        $location->setHost('example.com');
        $location->setPath('/tmp');
        $location->setUser('user.name');
        $this->assertEquals('user.name@example.com:/tmp', $location->toString(), 'should have \'user@host:path\'');
    }

    /**
     * Tests Rsync::toString
     */
    public function testHostStringEmptyOnUserOnly()
    {
        $location = new Location();
        $location->setPath('/tmp');
        $location->setUser('user.name');
        $this->assertEquals('/tmp', $location->toString(), 'should only contain the path');
    }
}
