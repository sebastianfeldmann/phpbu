<?php
namespace phpbu\Backup\Sync;

/**
 * SftpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.5
 */
class SftpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Sftp::setUp
     */
    public function testSetUpOk()
    {
        $sftp = new Sftp();
        $sftp->setup(array(
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoHost()
    {
        $sftp = new Sftp();
        $sftp->setup(array(
            'user' => 'user.name',
            'path' => 'foo'
        ));
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoUser()
    {
        $sftp = new Sftp();
        $sftp->setup(array(
            'host' => 'example.com',
            'path' => 'foo'
        ));
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoPassword()
    {
        $sftp = new Sftp();
        $sftp->setup(array(
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => 'foo'
        ));
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpPathWithRootSlash()
    {
        $sftp = new Sftp();
        $sftp->setup(array(
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => '/foo'
        ));
    }
}
