<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\CliTest;
use phpbu\App\Util\Cli;

/**
 * McryptTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class McryptTest extends CliTest
{
    /**
     * @var Mcrypt
     */
    protected $mcrypt;

    /**
     * Setup mysqldump
     */
    public function setUp()
    {
        $this->mcrypt = new Mcrypt();
    }

    /**
     * Clear mysqldump
     */
    public function tearDown()
    {
        $this->mcrypt = null;
    }

    /**
     * Tests Mcrypt::setUp
     */
    public function testSetUpOk()
    {
        $this->mcrypt->setup(array('key' => 'fooBarBaz', 'algorithm' => 'blowfish'));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Mcrypt::setUp
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testSetUpNoKeyOrKeyFile()
    {
        $this->mcrypt->setup(array('algorithm' => 'blowfish'));
    }

    /**
     * Tests Mcrypt::setUp
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testSetUpNoAlgorithm()
    {
        $this->mcrypt->setup(array('k' => 'fooBarBaz'));
    }

    /**
     * Tests Mcrypt::getExecutable
     */
    public function testKeyAndAlgorithm()
    {
        $expected = 'mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' \'/foo/bar.txt\' 2> /dev/null';
        $target   = $this->getTargetMock('/foo/bar.txt');
        $path     = $this->getBinDir();
        $this->mcrypt->setup(array('pathToMcrypt' => $path, 'key' => 'fooBarBaz', 'algorithm' => 'blowfish'));

        $executable = $this->mcrypt->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Mcrypt::getExecutable
     */
    public function testKeyFile()
    {
        Cli::registerBase('configuration', '/foo');

        $expected = 'mcrypt -u -f \'/foo/my.key\' -a \'blowfish\' \'/foo/bar.txt\' 2> /dev/null';
        $target   = $this->getTargetMock('/foo/bar.txt');
        $path     = $this->getBinDir();
        $this->mcrypt->setup(array('pathToMcrypt' => $path, 'keyFile' => '/foo/my.key', 'algorithm' => 'blowfish'));

        $executable = $this->mcrypt->getExecutable($target);

        $this->assertEquals($path . '/' . $expected, $executable->getCommandLine());
    }

    /**
     * Tests Mcrypt::crypt
     */
    public function testCryptOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'mcrypt');
        $appResult = $this->getAppResultMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Mcrypt')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('run')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $this->mcrypt->setExecutable($exec);
        $this->mcrypt->crypt($target, $appResult);
    }

    /**
     * Tests Mcrypt::crypt
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testCryptFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'mcrypt');
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Mcrypt')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('run')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $this->mcrypt->setExecutable($exec);
        $this->mcrypt->crypt($target, $appResult);
    }

    /**
     * Tests Mcrypt::getSuffix
     */
    public function testGetSuffix()
    {
        $suffix = $this->mcrypt->getSuffix();
        $this->assertEquals('nc', $suffix);
    }
}
