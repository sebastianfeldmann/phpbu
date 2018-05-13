<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Result;
use phpseclib;
use phpbu\App\BaseMockery;

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
    use BaseMockery;

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

        $resultStub = $this->createMock(Result::class);
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
     * Tests Sftp::setUp
     */
    public function testSetUpWithPrivateKey()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host'                 => 'example.com',
            'user'                 => 'user.name',
            'private_key'          => '/foo',
            'private_key_password' => '12345',
            'path'                 => '/foo'
        ]);

        $this->assertAttributeEquals('/foo', 'privateKey', $sftp);
        $this->assertAttributeEquals('12345', 'privateKeyPassword', $sftp);
    }

    /**
     * Tests absolute path
     */
    public function testSetUpPathWithAbsolutePath()
    {
        $secLibMock = $this->createPHPSecLibSftpMock();
        $target     = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result     = $this->getResultMock(5);

        $sftp = $this->createPartialMock(Sftp::class, ['login']);
        $sftp->method('login')->willReturn($secLibMock);

        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => '/foo',
        ]);

        $sftp->sync($target, $result);
    }

    /**
     * Create a app result mock
     *
     * @return \phpseclib\Net\SFTP
     */
    private function createPHPSecLibSftpMock()
    {
        $secLib = $this->createMock(phpseclib\Net\SFTP::class);

        $secLib->expects($this->exactly(2))
               ->method('is_dir')
               ->withConsecutive(['/'], ['foo'])
               ->will($this->onConsecutiveCalls(true, false));
        $secLib->method('chdir')->willReturn(true);
        $secLib->expects($this->once())->method('mkdir')->with('foo')->willReturn(true);
        $secLib->method('put')->willReturn(true);

        return $secLib;
    }

    /**
     * Create a app result mock
     *
     * @param  int $expectedDebugCalls
     * @return \phpbu\App\Result
     */
    private function getResultMock(int $expectedDebugCalls)
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->exactly($expectedDebugCalls))->method('debug');

        return $result;
    }
}
