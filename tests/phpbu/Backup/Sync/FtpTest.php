<?php
namespace phpbu\App\Backup\Sync;

/**
 * SftpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Chris Hawes <me@chrishawes.net>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 */
class FtpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Ftp::setUp
     */
    public function testSetUpOk()
    {
        $ftp = new Ftp();
        $ftp->setup(array(
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Ftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     * @expectedExceptionMessage option 'host' is missing
     */
    public function testSetUpNoHost()
    {
        $ftp = new Ftp();
        $ftp->setup(array(
            'user' => 'user.name',
            'path' => 'foo'
        ));
    }

    /**
     * Tests Ftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     * @expectedExceptionMessage option 'user' is missing
     */
    public function testSetUpNoUser()
    {
        $ftp = new Ftp();
        $ftp->setup(array(
            'host' => 'example.com',
            'path' => 'foo'
        ));
    }

    /**
     * Tests Ftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     * @expectedExceptionMessage option 'password' is missing
     */
    public function testSetUpNoPassword()
    {
        $ftp = new Ftp();
        $ftp->setup(array(
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => 'foo'
        ));
    }

    /**
     * Tests Ftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     * @expectedExceptionMessage absolute path is not allowed
     */
    public function testSetUpPathWithRootSlash()
    {
        $ftp = new Ftp();
        $ftp->setup(array(
            'host' => 'example.com',
            'user' => 'user.name',
            'password' => 'password',
            'path' => '/foo'
        ));
    }
}
