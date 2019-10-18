<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\CliMockery;
use phpbu\App\Backup\Restore\Plan;
use phpbu\App\BaseMockery;
use phpbu\App\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * OpenSSLTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 2.1.6
 */
class OpenSSLTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests OpenSSL::setUp
     */
    public function testSetUpOk()
    {
        $openSSL = new OpenSSL();
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'password' => 'fooBarBaz', 'algorithm' => 'aes-256-cbc']);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests OpenSSL::setUp
     */
    public function testSetUpNoCertOrPassword()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $openSSL = new OpenSSL();
        $openSSL->setup(['algorithm' => 'aes-256-cbc']);
    }

    /**
     * Tests OpenSSL::setUp
     */
    public function testSetUpNoAlgorithm()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $openSSL = new OpenSSL();
        $openSSL->setup(['password' => 'fooBarBaz']);
    }

    /**
     * Tests OpenSSL::getExecutable
     */
    public function testPasswordAndAlgorithm()
    {
        $target  = $this->createTargetMock('/foo/bar.txt');
        $openSSL = new OpenSSL();
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'password' => 'fooBarBaz', 'algorithm' => 'aes-256-cbc']);

        $executable = $openSSL->getExecutable($target);
        $expected   = '(' . PHPBU_TEST_BIN . '/openssl enc -e -a -aes-256-cbc -pass \'pass:fooBarBaz\' '
                    . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' '
                    . '&& rm \'/foo/bar.txt\')';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests OpenSSL::getExecutable
     */
    public function testCertFile()
    {
        Configuration::setWorkingDirectory('/foo');

        $target  = $this->createTargetMock('/foo/bar.txt');
        $openSSL = new OpenSSL();
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'certFile' => '/foo/my.pem', 'algorithm' => 'aes256']);

        $executable = $openSSL->getExecutable($target);
        $expected = '(' . PHPBU_TEST_BIN . '/openssl smime -encrypt -aes256 -binary -in \'/foo/bar.txt\' '
                  . '-out \'/foo/bar.txt.enc\' -outform DER \'/foo/my.pem\' '
                  . '&& rm \'/foo/bar.txt\')';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests OpenSSL::crypt
     */
    public function testCryptOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'openssl'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $openSSL = new OpenSSL($runner);
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'certFile' => '/foo/my.pem', 'algorithm' => 'aes256']);
        $openSSL->crypt($target, $appResult);
    }

    /**
     * Tests OpenSSL::simulate
     */
    public function testSimulate()
    {
        $runner = $this->getRunnerMock();

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $openSSL = new OpenSSL($runner);
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'certFile' => '/foo/my.pem', 'algorithm' => 'aes256']);
        $openSSL->simulate($target, $appResult);
    }

    /**
     * Tests OpenSSL::restore
     */
    public function testRestore()
    {
        $runner = $this->getRunnerMock();

        $target = $this->createTargetMock(__FILE__);
        $plan   = new Plan();

        $openSSL = new OpenSSL($runner);
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'certFile' => '/foo/my.pem', 'algorithm' => 'aes256']);
        $openSSL->restore($target, $plan);

        $this->assertCount(1, $plan->getDecryptionCommands());
    }

    /**
     * Tests OpenSSL::crypt
     */
    public function testCryptFail()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'openssl'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $openSSL = new OpenSSL($runner);
        $openSSL->setup(['pathToOpenSSL' => PHPBU_TEST_BIN, 'password' => 'fooBarBaz', 'algorithm' => 'aes-256-cbc']);
        $openSSL->crypt($target, $appResult);
    }

    /**
     * Tests OpenSSL::getSuffix
     */
    public function testGetSuffix()
    {
        $openSSL = new OpenSSL();
        $suffix  = $openSSL->getSuffix();
        $this->assertEquals('enc', $suffix);
    }
}
