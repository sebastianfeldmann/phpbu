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
class SftpTest extends \PHPUnit\Framework\TestCase
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
            'path'     => '/foo'
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
            'path'     => 'foo'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

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

    /**
     * Tests absolute path
     */
    public function testSetUpPathWithAbsolutePath()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => '/foo',
        ]);
        $this->assertEquals(['/', 'foo'], $sftp->getRemoteDirectoryList());
    }
}
