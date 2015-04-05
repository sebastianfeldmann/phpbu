<?php
namespace phpbu\App\Backup\Crypter;

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
class McryptTest extends \PHPUnit_Framework_TestCase
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
        $this->mcrypt->setBinary('mcrypt');
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
     *
     * @expectedException \RuntimeException
     */
    public function testSetUpCantFindBinary()
    {
        $mcrypt = new Mcrypt();
        $mcrypt->setup(array('key' => 'fooBarBaz', 'algorithm' => 'blowfish', 'pathToMcrypt' => '/foo/bar/mcrypt'));
    }

    /**
     * Tests Mcrypt::setUp
     */
    public function testSetUpFindBinary()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $mcrypt = new Mcrypt();
        $mcrypt->setup(array('key' => 'fooBarBaz', 'algorithm' => 'blowfish', 'pathToMcrypt' => $path));

        $this->assertTrue(true, 'no exception should be thrown');
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
     * Tests Mcrypt::getExec
     */
    public function testGetExecKeyAndAlgorithm()
    {
        $target = $this->getTargetMock();
        $this->mcrypt->setup(array('key' => 'fooBarBaz', 'algorithm' => 'blowfish'));
        $exec = $this->mcrypt->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' \'/foo/bar.txt\' 2> /dev/null', $call);
    }

    /**
     * Tests Mcrypt::getExec
     */
    public function testGetExecFile()
    {
        Cli::registerBase('configuration', '/foo');
        $target = $this->getTargetMock();
        $this->mcrypt->setup(array('keyFile' => '/foo/my.key', 'algorithm' => 'blowfish'));
        $exec = $this->mcrypt->getExec($target);

        $call = (string) $exec->getExec();

        $this->assertEquals('mcrypt -u -f \'/foo/my.key\' -a \'blowfish\' \'/foo/bar.txt\' 2> /dev/null', $call);
    }

    /**
     * Tests Mcrypt::crypt
     */
    public function testCryptOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('execute')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $this->mcrypt->setExec($exec);
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
        $cliResult = $this->getCliResultMock(1);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $exec->expects($this->once())->method('execute')->willReturn($cliResult);
        $appResult->expects($this->once())->method('debug');

        $this->mcrypt->setExec($exec);
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

    /**
     * Create Cli\Result mock.
     *
     * @param  integer $code
     * @return \phpbu\App\Backup\Cli\Result
     */
    protected function getCliResultMock($code)
    {
        $cliResult = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();

        $cliResult->method('getCmd')->willReturn('mcrypt');
        $cliResult->method('getCode')->willReturn($code);
        $cliResult->method('getOutput')->willReturn(array());
        $cliResult->method('wasSuccessful')->willReturn($code == 0);

        return $cliResult;
    }

    /**
     * Create Target mock.
     *
     * @param  string  $pathname
     * @param  boolean $compressed
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock($pathname = '/foo/bar.txt', $compressed = false)
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();
        $target->method('getPath')->willReturn(dirname($pathname));
        $target->method('getPathname')->willReturn($pathname);
        $target->method('fileExists')->willReturn(false);
        $target->method('shouldBeCompressed')->willReturn($compressed);

        return $target;
    }
}
