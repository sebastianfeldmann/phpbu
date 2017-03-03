<?php
namespace phpbu\App\Backup\Sync;

/**
 * SftpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class SftpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Sftp::setUp
     */
    public function testSetUpOk()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Sftp::simulate
     */
    public function testSimulate()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => '/foo'
        ]);

        $resultStub = $this->getMockBuilder('\\phpbu\\App\\Result')
                           ->getMock();
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                           ->disableOriginalConstructor()
                           ->getMock();

        $sftp->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoHost()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'user' => 'user.name',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoUser()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host' => 'example.com',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPassword()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpPathWithRootSlash()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => '/foo'
        ]);
    }
}
