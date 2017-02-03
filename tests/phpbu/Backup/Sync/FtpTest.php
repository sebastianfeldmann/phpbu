<?php
namespace phpbu\App\Backup\Sync;

/**
 * FtpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Chris Hawes <me@chrishawes.net>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 */
class FtpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Ftp::setUp
     */
    public function testSetUpOk()
    {
        $ftp = new Ftp();
        $ftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Ftp::simulate
     */
    public function testSimulate()
    {
        $ftp = new Ftp();
        $ftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ]);

        $resultStub = $this->getMockBuilder('\\phpbu\\App\\Result')
                           ->getMock();
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                           ->disableOriginalConstructor()
                           ->getMock();

        $ftp->simulate($targetStub, $resultStub);
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
        $ftp->setup([
            'user' => 'user.name',
            'path' => 'foo'
        ]);
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
        $ftp->setup([
            'host' => 'example.com',
            'path' => 'foo'
        ]);
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
        $ftp->setup([
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => 'foo'
        ]);
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
        $ftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'password',
            'path'     => '/foo'
        ]);
    }
}
