<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\BaseMockery;
use phpseclib4\Exception\FileSystemException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * SftpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class SftpTest extends TestCase
{
    use BaseMockery;

    /**
     * Test host key and its fingerprint as printed by ssh-keygen -l
     */
    private const HOST_KEY             = 'ssh-ed25519 '
                                       . 'AAAAC3NzaC1lZDI1NTE5AAAAICkL/q8oDfs71KsiB+PR/Rikw3PAHp0wFu47RcQ/DBaO';
    private const HOST_KEY_FINGERPRINT = 'SHA256:G2ZoezdfQkfH8IZer6P9thNTDfYDuFdsL11nFepiSkw';

    /**
     * RSA test key as phpseclib returns it (signature algorithm first) and its fingerprint
     */
    private const RSA_HOST_KEY             = 'rsa-sha2-256 '
                                           . 'AAAAB3NzaC1yc2EAAAADAQABAAABAQCcoZ5rOMZYxterWosmkoZgXcx3xLAwiPzq'
                                           . 'eiHngcCfhfZLaWPbVlGB9D0bpzCYCMatC+rwC6+SpPfTMUQIj8t0uPIR002Dak2f'
                                           . '0QkIOUC9TnPBjTIs5zOtExMzD4IyavyfBLR1mgDDaPmZrQNs1yd8/6p4UQ40AVek'
                                           . '0AKpIICmorJMP3JSop/mDylw5NVHzLdFxf+iFhdrT9yJz0Iw/jXJWeShyFQbG9o9'
                                           . 'CXEwWf/MXB+2bkgXH5/U9xGVfNKSlSFmUYs/1/9kLsmNUTAR4l9k+2gQwLF2tTdD'
                                           . '7WpGaXLUpk1Q0tPISNgrWmxnJUNrI92eWBu8w94O+9FdVVvnUgs3';
    private const RSA_HOST_KEY_FINGERPRINT = 'SHA256:18tUEn0/OSV5RhTK5I9mnwpjhF85KUBvFUHjPWPTMb4';

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
     * Tests Sftp::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(Result::class);
        $result->expects($this->exactly(5))->method('debug');

        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->expects($this->once())->method('realpath')->willReturn('/backup');
        $clientMock->expects($this->once())->method('mkdir')->with('foo');
        $clientMock->expects($this->exactly(3))->method('chdir');
        $clientMock->expects($this->once())->method('put');
        $clientMock->expects($this->exactly(3))
                   ->method('is_dir')
                   ->will($this->onConsecutiveCalls(true, true, false));

        $sftp = $this->createPartialMock(Sftp::class, ['createClient']);
        $sftp->method('createClient')->willReturn($clientMock);

        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => 'foo'
        ]);

        $sftp->sync($target, $result);
    }

    /**
     * Tests Sftp::sync
     */
    public function testSyncWithRemoteCleanup()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(Result::class);
        $result->expects($this->exactly(4))->method('debug');

        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->expects($this->exactly(2))->method('is_dir')->will($this->onConsecutiveCalls(true, true));
        $clientMock->expects($this->exactly(2))->method('chdir');
        $clientMock->expects($this->once())->method('put');
        $clientMock->expects($this->once())->method('rawlist')->willReturn([]);

        $sftp = $this->createPartialMock(Sftp::class, ['createClient']);
        $sftp->method('createClient')->willReturn($clientMock);

        $sftp->setup([
            'host'           => 'example.com',
            'user'           => 'user.name',
            'password'       => 'secret',
            'path'           => '/foo',
            'cleanup.type'   => 'quantity',
            'cleanup.amount' => 99
        ]);

        $sftp->sync($target, $result);
    }

    /**
     * Tests Sftp::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $this->expectExceptionMessage('error uploading file: foo.txt.gz - permission denied');
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(Result::class);
        $result->expects($this->exactly(3))->method('debug');

        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->expects($this->exactly(2))->method('is_dir')->will($this->onConsecutiveCalls(true, true));
        $clientMock->expects($this->exactly(2))->method('chdir');
        $clientMock->expects($this->once())
                   ->method('put')
                   ->willThrowException(new FileSystemException('permission denied'));

        $sftp = $this->createPartialMock(Sftp::class, ['createClient']);
        $sftp->method('createClient')->willReturn($clientMock);

        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'password' => 'secret',
            'path'     => '/foo'
        ]);

        $sftp->sync($target, $result);
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

        $targetStub = $this->createMock(Target::class);

        $sftp->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Sftp::setUp
     */
    public function testSetUpNoHost()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sftp = new Sftp();
        $sftp->setup([
            'user' => 'user.name',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     */
    public function testSetUpNoUser()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sftp = new Sftp();
        $sftp->setup([
            'host' => 'example.com',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     */
    public function testSetUpNoPassword()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sftp = new Sftp();
        $sftp->setup([
            'host' => 'example.com',
            'user' => 'user.name',
            'path' => 'foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     */
    public function testSetUpPathWithRootSlash()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
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
    public function testSetUpWithPrivateKeyThatDoesNotExist()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sftp = new Sftp();
        $sftp->setup([
            'host'     => 'example.com',
            'user'     => 'user.name',
            'key'      => PHPBU_TEST_FILES . '/misc/id_rsa_test_not_exist',
            'password' => '12345',
            'path'     => '/foo'
        ]);
    }

    /**
     * Tests Sftp::setUp
     */
    public function testSetUpInvalidHostKeyFingerprint()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sftp = new Sftp();
        $sftp->setup([
            'host'               => 'example.com',
            'user'               => 'user.name',
            'password'           => 'secret',
            'path'               => 'foo',
            'hostKeyFingerprint' => 'aa:bb:cc'
        ]);
    }

    /**
     * Tests Sftp::setUp rejects a fingerprint with a trailing newline
     */
    public function testSetUpHostKeyFingerprintTrailingNewline()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sftp = new Sftp();
        $sftp->setup([
            'host'               => 'example.com',
            'user'               => 'user.name',
            'password'           => 'secret',
            'path'               => 'foo',
            'hostKeyFingerprint' => self::HOST_KEY_FINGERPRINT . "\n"
        ]);
    }

    /**
     * Tests Sftp::fingerprint and Sftp::keyType for an RSA key as phpseclib returns it
     */
    public function testFingerprintAndTypeOfRsaKey()
    {
        $this->assertEquals(self::RSA_HOST_KEY_FINGERPRINT, Sftp::fingerprint(self::RSA_HOST_KEY));
        $this->assertEquals('ssh-rsa', Sftp::keyType(self::RSA_HOST_KEY));
        $this->assertEquals('ssh-ed25519', Sftp::keyType(self::HOST_KEY));
        $this->assertEquals('unknown', Sftp::keyType('ssh-rsa'));
        $this->assertEquals('unknown', Sftp::keyType('x ' . base64_encode(pack('N', 9) . '<b>x</b>')));
    }

    /**
     * Tests Sftp::setUp with a fingerprint that uses every base64 character class
     */
    public function testSetUpValidHostKeyFingerprint()
    {
        $sftp = new Sftp();
        $sftp->setup([
            'host'               => 'example.com',
            'user'               => 'user.name',
            'password'           => 'secret',
            'path'               => 'foo',
            'hostKeyFingerprint' => 'SHA256:Ab0+/' . str_repeat('x', 38)
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Sftp::fingerprint against the output of ssh-keygen -l for the same key
     */
    public function testFingerprintMatchesOpenSsh()
    {
        $this->assertEquals(self::HOST_KEY_FINGERPRINT, Sftp::fingerprint(self::HOST_KEY));
    }

    /**
     * Tests that a matching host key is checked before the login
     */
    public function testCreateClientVerifiesHostKeyBeforeLogin()
    {
        $calls      = [];
        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->expects($this->once())
                   ->method('getServerPublicHostKey')
                   ->willReturnCallback(function () use (&$calls) {
                       $calls[] = 'hostkey';
                       return self::HOST_KEY;
                   });
        $clientMock->expects($this->once())
                   ->method('login')
                   ->with('user.name', 'secret')
                   ->willReturnCallback(function () use (&$calls) {
                       $calls[] = 'login';
                       return true;
                   });

        $sftp = $this->createSftpWithClient($clientMock, self::HOST_KEY_FINGERPRINT);

        $this->assertSame($clientMock, $this->invokeCreateClient($sftp));
        $this->assertEquals(['hostkey', 'login'], $calls);
    }

    /**
     * Tests that a different host key stops the connection before any credential is sent
     */
    public function testCreateClientHostKeyMismatch()
    {
        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->method('getServerPublicHostKey')->willReturn(self::RSA_HOST_KEY);
        $clientMock->expects($this->never())->method('login');

        $sftp = $this->createSftpWithClient($clientMock, 'SHA256:' . str_repeat('A', 43));

        $level = error_reporting(E_ALL);
        try {
            $this->invokeCreateClient($sftp);
            $this->fail('host key mismatch expected');
        } catch (Exception $e) {
            $this->assertStringContainsString(
                'host key mismatch for example.com: expected SHA256:' . str_repeat('A', 43)
                . ', got ' . self::RSA_HOST_KEY_FINGERPRINT . ' (ssh-rsa key)',
                $e->getMessage()
            );
            $this->assertSame(E_ALL, error_reporting(), 'error reporting must be restored');
        } finally {
            error_reporting($level);
        }
    }

    /**
     * Tests that an unreadable host key stops the connection before any credential is sent
     */
    public function testCreateClientHostKeyUnreadable()
    {
        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->method('getServerPublicHostKey')->willReturn(null);
        $clientMock->expects($this->never())->method('login');

        $sftp = $this->createSftpWithClient($clientMock, self::HOST_KEY_FINGERPRINT);

        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $this->expectExceptionMessage('could not read the host key of example.com');
        $this->invokeCreateClient($sftp);
    }

    /**
     * Tests that without a configured fingerprint the host key is not requested
     */
    public function testCreateClientWithoutFingerprint()
    {
        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->expects($this->never())->method('getServerPublicHostKey');
        $clientMock->expects($this->once())->method('login')->willReturn(true);

        $sftp = $this->createSftpWithClient($clientMock, '');

        $this->assertSame($clientMock, $this->invokeCreateClient($sftp));
    }

    /**
     * Tests that a failed check leaves no client behind for a later call
     */
    public function testCreateClientFailedCheckIsNotCached()
    {
        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->method('getServerPublicHostKey')->willReturn(self::HOST_KEY);
        $clientMock->expects($this->never())->method('login');

        $sftp = $this->createSftpWithClient($clientMock, 'SHA256:' . str_repeat('A', 43));

        for ($i = 0; $i < 2; $i++) {
            try {
                $this->invokeCreateClient($sftp);
                $this->fail('host key mismatch expected');
            } catch (Exception $e) {
                $this->assertStringContainsString('host key mismatch', $e->getMessage());
            }
        }
    }

    /**
     * Tests that a failed login is not cached and restores error reporting
     */
    public function testCreateClientFailedLoginIsNotCached()
    {
        $clientMock = $this->createMock(\phpseclib4\Net\SFTP::class);
        $clientMock->expects($this->exactly(2))->method('login')->willReturn(false);

        $sftp  = $this->createSftpWithClient($clientMock, '');
        $level = error_reporting(E_ALL);
        try {
            for ($i = 0; $i < 2; $i++) {
                try {
                    $this->invokeCreateClient($sftp);
                    $this->fail('authentication failure expected');
                } catch (Exception $e) {
                    $this->assertStringContainsString('authentication failed', $e->getMessage());
                    $this->assertSame(E_ALL, error_reporting(), 'error reporting must be restored');
                }
            }
        } finally {
            error_reporting($level);
        }
    }

    /**
     * Create a Sftp sync whose phpseclib client is the given mock
     *
     * @param  \phpseclib4\Net\SFTP $clientMock
     * @param  string               $fingerprint
     * @return \phpbu\App\Backup\Sync\Sftp
     */
    private function createSftpWithClient($clientMock, string $fingerprint)
    {
        $sftp = $this->createPartialMock(Sftp::class, ['createSftp']);
        $sftp->method('createSftp')->willReturn($clientMock);
        $sftp->setup([
            'host'               => 'example.com',
            'user'               => 'user.name',
            'password'           => 'secret',
            'path'               => 'foo',
            'hostKeyFingerprint' => $fingerprint
        ]);

        return $sftp;
    }

    /**
     * Call the protected Sftp::createClient
     *
     * @param  \phpbu\App\Backup\Sync\Sftp $sftp
     * @return \phpseclib4\Net\SFTP
     */
    private function invokeCreateClient(Sftp $sftp)
    {
        $method = new ReflectionMethod(Sftp::class, 'createClient');

        return $method->invoke($sftp);
    }

    /**
     * Tests absolute path
     */
    public function testSetUpPathWithAbsolutePath()
    {
        $secLibMock = $this->createPHPSecLibSftpMock();
        $target     = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result     = $this->getResultMock(4);

        $sftp = $this->createPartialMock(Sftp::class, ['createClient']);
        $sftp->method('createClient')->willReturn($secLibMock);

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
     * @return \phpseclib4\Net\SFTP
     */
    private function createPHPSecLibSftpMock()
    {
        $secLib = $this->createMock(\phpseclib4\Net\SFTP::class);

        $secLib->expects($this->exactly(2))
               ->method('is_dir')
               ->withConsecutive(['/'], ['foo'])
               ->will($this->onConsecutiveCalls(true, false));
        $secLib->expects($this->exactly(2))->method('chdir');
        $secLib->expects($this->once())->method('mkdir')->with('foo');
        $secLib->expects($this->once())->method('put');

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
