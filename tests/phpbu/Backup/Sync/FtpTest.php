<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

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
class FtpTest extends TestCase
{
    use BaseMockery;

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
     * Tests Dropbox::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->once())->method('debug');

        $clientMock = $this->createMock(\SebastianFeldmann\Ftp\Client::class);
        $clientMock->expects($this->once())->method('uploadFile');

        $ftp = $this->createPartialMock(Ftp::class, ['createClient']);
        $ftp->method('createClient')->willReturn($clientMock);

        $ftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ]);

        $ftp->sync($target, $result);
    }

    /**
     * Tests Dropbox::sync
     */
    public function testSyncWithCleanup()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(2))->method('debug');

        $clientMock = $this->createMock(\SebastianFeldmann\Ftp\Client::class);
        $clientMock->expects($this->once())->method('uploadFile');
        $clientMock->expects($this->once())->method('chHome');
        $clientMock->expects($this->once())->method('lsFiles')->willReturn([]);

        $ftp = $this->createPartialMock(Ftp::class, ['createClient']);
        $ftp->method('createClient')->willReturn($clientMock);

        $ftp->setup([
            'host'           => 'example.com',
            'user'           => 'user.name',
            'password'       => 'secret',
            'path'           => 'foo',
            'cleanup.type'   => 'quantity',
            'cleanup.amount' => 99,
        ]);

        $ftp->sync($target, $result);
    }

    /**
     * Tests Dropbox::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);

        $clientMock = $this->createMock(\SebastianFeldmann\Ftp\Client::class);
        $clientMock->expects($this->once())->method('uploadFile')->will($this->throwException(new \Exception));

        $ftp = $this->createPartialMock(Ftp::class, ['createClient']);
        $ftp->method('createClient')->willReturn($clientMock);

        $ftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ]);

        $ftp->sync($target, $result);
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

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $ftp->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Ftp::setUp
     */
    public function testSetUpNoHost()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $this->expectExceptionMessage('option \'host\' is missing');
        $ftp = new Ftp();
        $ftp->setup([
            'user'     => 'user.name',
            'password' => '12345',
            'path'     => 'foo',
        ]);
    }

    /**
     * Tests Ftp::setUp
     */
    public function testSetUpNoUser()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $this->expectExceptionMessage('option \'user\' is missing');
        $ftp = new Ftp();
        $ftp->setup([
            'host'     => 'example.com',
            'password' => 'user',
            'path'     => 'foo',
        ]);
    }

    /**
     * Tests Ftp::setUp
     */
    public function testSetUpNoPassword()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $this->expectExceptionMessage('option \'password\' is missing');
        $ftp = new Ftp();
        $ftp->setup([
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Ftp::setUp
     */
    public function testSetUpPathWithRootSlash()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $this->expectExceptionMessage('absolute path is not allowed');
        $ftp = new Ftp();
        $ftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'password',
            'path'     => '/foo'
        ]);
    }
}
