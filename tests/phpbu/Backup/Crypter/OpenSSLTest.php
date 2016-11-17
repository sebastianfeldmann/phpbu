<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\CliTest;
use phpbu\App\Configuration;
use phpbu\App\Util\Cli;

/**
 * OpenSSLTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.6
 */
class OpenSSLTest extends CliTest
{
    /**
     * @var OpenSSL
     */
    protected $openSSL;

    /**
     * Setup OpenSSL Crypt
     */
    public function setUp()
    {
        $this->openSSL = new OpenSSL();
    }

    /**
     * Clear OpenSSL Crypt
     */
    public function tearDown()
    {
        $this->openSSL = null;
    }

    /**
     * Tests OpenSSL::setUp
     */
    public function testSetUpOk()
    {
        $this->openSSL->setup(array('password' => 'fooBarBaz', 'algorithm' => 'aes-256-cbc'));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests OpenSSL::setUp
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testSetUpNoCertOrPassword()
    {
        $this->openSSL->setup(array('algorithm' => 'aes-256-cbc'));
    }

    /**
     * Tests OpenSSL::setUp
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testSetUpNoAlgorithm()
    {
        $this->openSSL->setup(array('password' => 'fooBarBaz'));
    }

    /**
     * Tests OpenSSL::getExecutable
     */
    public function testPasswordAndAlgorithm()
    {
        $expected = 'openssl enc -e -a -aes-256-cbc -pass \'pass:fooBarBaz\' '
                  . '-in \'/foo/bar.txt\' -out \'/foo/bar.txt.enc\' '
                  . '&& rm \'/foo/bar.txt\'';
        $target   = $this->getTargetMock('/foo/bar.txt');
        $path     = $this->getBinDir();
        $this->openSSL->setup(array('pathToOpenSSL' => $path, 'password' => 'fooBarBaz', 'algorithm' => 'aes-256-cbc'));

        $executable = $this->openSSL->getExecutable($target);

        $this->assertEquals('(' . $path . '/' . $expected . ')', $executable->getCommandLine());
    }

    /**
     * Tests OpenSSL::getExecutable
     */
    public function testCertFile()
    {
        Configuration::setWorkingDirectory('/foo');

        $expected = 'openssl smime -encrypt -aes256 -binary -in \'/foo/bar.txt\' '
                  . '-out \'/foo/bar.txt.enc\' -outform DER \'/foo/my.pem\' '
                  . '&& rm \'/foo/bar.txt\'';
        $target   = $this->getTargetMock('/foo/bar.txt');
        $path     = $this->getBinDir();
        $this->openSSL->setup(array('pathToOpenSSL' => $path, 'certFile' => '/foo/my.pem', 'algorithm' => 'aes256'));

        $executable = $this->openSSL->getExecutable($target);

        $this->assertEquals('(' . $path . '/' . $expected . ')', $executable->getCommandLine());
    }

    /**
     * Tests OpenSSL::crypt
     */
    public function testCryptOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'openssl');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\OpenSSL')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('run')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $this->openSSL->setExecutable($exec);
        $this->openSSL->crypt($target, $appResult);
    }

    /**
     * Tests OpenSSL::crypt
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testCryptFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'openSSL');
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\OpenSSL')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('run')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $this->openSSL->setExecutable($exec);
        $this->openSSL->crypt($target, $appResult);
    }

    /**
     * Tests OpenSSL::getSuffix
     */
    public function testGetSuffix()
    {
        $suffix = $this->openSSL->getSuffix();
        $this->assertEquals('enc', $suffix);
    }
}
