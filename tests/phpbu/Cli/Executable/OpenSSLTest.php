<?php
namespace phpbu\App\Cli\Executable;

use phpbu\App\Exception;
use PHPUnit\Framework\TestCase;

/**
 * OpenSSL ExecutableTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.6
 */
class OpenSLLTest extends TestCase
{
    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testNoAlgorithm()
    {
        $this->expectException(Exception::class);

        $openSSL  = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->encryptFile('/foo/bar.txt')
                ->usePassword('fooBarBaz');

        $openSSL->getCommand();
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testPasswordBase64Encode()
    {
        $expected = 'openssl enc -e -a -aes-256-cbc -pass \'pass:fooBarBaz\' '
            . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' '
            . '&& rm \'/foo/bar.txt\'';
        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->encryptFile('/foo/bar.txt')
                ->encodeBase64(true)
                ->usePassword('fooBarBaz')
                ->useAlgorithm('aes-256-cbc');

        $this->assertEquals('(' . PHPBU_TEST_BIN . '/' . $expected . ')', $openSSL->getCommand());
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testPassword()
    {
        $expected = 'openssl enc -e -aes-256-cbc -pass \'pass:fooBarBaz\' '
                  . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' '
                  . '&& rm \'/foo/bar.txt\'';
        $openSSL  = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->encryptFile('/foo/bar.txt')
                ->usePassword('fooBarBaz')
                ->useAlgorithm('aes-256-cbc');

        $this->assertEquals('(' . PHPBU_TEST_BIN . '/' . $expected . ')', $openSSL->getCommand());
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testDecrypt()
    {
        $expected = 'openssl enc -d -aes-256-cbc -pass \'pass:fooBarBaz\' '
                    . '-in \'/foo/bar.txt.enc\' -out \'/foo/bar.txt\'';
        $openSSL  = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->decryptFile('/foo/bar.txt')
            ->usePassword('fooBarBaz')
            ->useAlgorithm('aes-256-cbc')
            ->deleteSource(false);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $openSSL->getCommand());
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testDoNotDeleteUncrypted()
    {
        $expected = 'openssl enc -e -aes-256-cbc -pass \'pass:fooBarBaz\' '
                  . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\'';
        $openSSL  = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->encryptFile('/foo/bar.txt')
                ->usePassword('fooBarBaz')
                ->useAlgorithm('aes-256-cbc')
                ->deleteSource(false);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $openSSL->getCommand());
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testCert()
    {
        $expected = 'openssl smime -encrypt -aes256 -binary -in \'/foo/bar.txt\' '
                  . '-out \'/foo/bar.txt.enc\' -outform DER \'/foo/my.pem\' '
                  . '&& rm \'/foo/bar.txt\'';
        $openSSL  = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->encryptFile('/foo/bar.txt')
                ->useSSLCert('/foo/my.pem')
                ->useAlgorithm('aes256');

        $this->assertEquals('(' . PHPBU_TEST_BIN . '/' . $expected . ')', $openSSL->getCommand());
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testPasswordAlreadySet()
    {
        $this->expectException(Exception::class);

        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->usePassword('foo')->useSSLCert('/foo/my.pem');
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testCertAlreadySet()
    {
        $this->expectException(Exception::class);

        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->useSSLCert('/foo/my.pub')->usePassword('foo');
    }

    /**
     * Tests OpenSSL::useAlgorithm
     */
    public function testUseInvalidAlgorithm()
    {
        $this->expectException(Exception::class);

        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->usePassword('foo')->useAlgorithm('invalid');
    }

    /**
     * Tests OpenSSL::useAlgorithm
     */
    public function testChooseAlgorithmAfterMode()
    {
        $this->expectException(Exception::class);

        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->useAlgorithm('invalid');
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testNoSource()
    {
        $this->expectException(Exception::class);

        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->getCommand();
    }

    /**
     * Tests OpenSSL::createCommandLine
     */
    public function testNoMode()
    {
        $this->expectException(Exception::class);

        $openSSL = new OpenSSL(PHPBU_TEST_BIN);
        $openSSL->encryptFile('/foo/bar.txt');
        $openSSL->getCommand();
    }
}
