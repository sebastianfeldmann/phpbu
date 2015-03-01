<?php
namespace phpbu\Backup\Sync;

/**
 * RsyncTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.5
 */
class RsyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Rsync::setUp
     */
    public function testSetUpOk()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => 'foo',
            'user' => 'dummy-user',
            'host' => 'dummy-host'
        ));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Rsync::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'user' => 'dummy-user',
            'host' => 'dummy-host'
        ));
    }

    /**
     * Tests Rsync::setUp
     */
    public function testSetUpNoPathOkWithRawArgs()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'args' => 'dummy-args'
        ));
        $this->assertTrue(true, 'there should not be an Exception');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringEmptyWithoutSetup()
    {
        $rsync = new Rsync();
        $this->assertEquals('', $rsync->getRsyncHostString(), 'should be empty on init');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringHostOnly()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => '/tmp',
            'host' => 'example.com'
        ));
        $this->assertEquals('example.com:', $rsync->getRsyncHostString(), 'should be \'host:\'');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringHostAndUser()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => '/tmp',
            'user' => 'user.name',
            'host' => 'example.com'
        ));
        $this->assertEquals('user.name@example.com:', $rsync->getRsyncHostString(), 'should have \'user@host:\'');
    }

    /**
     * Tests Rsync::getRsyncHostString
     */
    public function testRsyncHostStringEmptyOnUserOnly()
    {
        $rsync = new Rsync();
        $rsync->setup(array(
            'path' => '/tmp',
            'user' => 'user.name'
        ));
        $this->assertEquals('', $rsync->getRsyncHostString(), 'should still be empty');
    }
}
