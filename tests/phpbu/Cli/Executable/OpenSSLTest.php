<?php
namespace phpbu\App\Cli\Executable;

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
class OpenSLLTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests OpenSSL::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoAlgorithm()
    {
        $openSSL  = new OpenSSL();
        $openSSL->encryptFile('/foo/bar.txt')
                ->usePassword('fooBarBaz');

        $openSSL->getCommandLine();
    }

    /**
     * Tests OpenSSL::createProcess
     */
    public function testPasswordBase64Encode()
    {
        $expected = 'openssl enc -e -a -aes-256-cbc -pass \'pass:fooBarBaz\' '
            . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' 2> /dev/null '
            . '&& rm \'/foo/bar.txt\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL  = new OpenSSL($path);
        $openSSL->encryptFile('/foo/bar.txt')
                ->encodeBase64(true)
                ->usePassword('fooBarBaz')
                ->useAlgorithm('aes-256-cbc');

        $this->assertEquals('(' . $path . '/' . $expected . ')', $openSSL->getCommandLine());
    }

    /**
     * Tests OpenSSL::createProcess
     */
    public function testPassword()
    {
        $expected = 'openssl enc -e -aes-256-cbc -pass \'pass:fooBarBaz\' '
                  . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' 2> /dev/null '
                  . '&& rm \'/foo/bar.txt\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL  = new OpenSSL($path);
        $openSSL->encryptFile('/foo/bar.txt')
                ->usePassword('fooBarBaz')
                ->useAlgorithm('aes-256-cbc');

        $this->assertEquals('(' . $path . '/' . $expected . ')', $openSSL->getCommandLine());
    }

    /**
     * Tests OpenSSL::createProcess
     */
    public function testDoNotDeleteUncrypted()
    {
        $expected = 'openssl enc -e -aes-256-cbc -pass \'pass:fooBarBaz\' '
                  . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL  = new OpenSSL($path);
        $openSSL->encryptFile('/foo/bar.txt')
                ->usePassword('fooBarBaz')
                ->useAlgorithm('aes-256-cbc')
                ->deleteUncrypted(false);

        $this->assertEquals($path . '/' . $expected, $openSSL->getCommandLine());
    }

    /**
     * Tests OpenSSL::createProcess
     */
    public function testCert()
    {
        $expected = 'openssl smime -encrypt -aes256 -binary -in \'/foo/bar.txt\' '
                  . '-out \'/foo/bar.txt.enc\' -outform DER \'/foo/my.pem\' 2> /dev/null '
                  . '&& rm \'/foo/bar.txt\' 2> /dev/null';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL  = new OpenSSL($path);
        $openSSL->encryptFile('/foo/bar.txt')
                ->useSSLCert('/foo/my.pem')
                ->useAlgorithm('aes256');

        $this->assertEquals('(' . $path . '/' . $expected . ')', $openSSL->getCommandLine());
    }

    /**
     * Tests OpenSSL::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testPasswordAlreadySet()
    {
        $path    = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL = new OpenSSL($path);
        $openSSL->usePassword('foo')->useSSLCert('/foo/my.pem');
    }

    /**
     * Tests OpenSSL::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCertAlreadySet()
    {
        $path    = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL = new OpenSSL($path);
        $openSSL->useSSLCert('/foo/my.pub')->usePassword('foo');
    }

    /**
     * Tests OpenSSL::useAlgorithm
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testUseInvalidAlgorithm()
    {
        $path    = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL = new OpenSSL($path);
        $openSSL->usePassword('foo')->useAlgorithm('invalid');
    }

    /**
     * Tests OpenSSL::useAlgorithm
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testChooseAlgorithmAfterMode()
    {
        $path    = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL = new OpenSSL($path);
        $openSSL->useAlgorithm('invalid');
    }

    /**
     * Tests OpenSSL::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoSource()
    {
        $path    = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL = new OpenSSL($path);
        $openSSL->getCommandLine();
    }

    /**
     * Tests OpenSSL::createProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoMode()
    {
        $path    = realpath(__DIR__ . '/../../../_files/bin');
        $openSSL = new OpenSSL($path);
        $openSSL->encryptFile('/foo/bar.txt');
        $openSSL->getCommandLine();
    }
}
