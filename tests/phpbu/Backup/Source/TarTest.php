<?php
namespace phpbu\App\Backup\Source;
use phpbu\App\Backup\Compressor;

/**
 * TarTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class TarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * tar
     *
     * @var \phpbu\App\Backup\Source\Tar
     */
    protected $tar;

    /**
     * Setup tar
     */
    public function setUp()
    {
        $this->tar = new Tar();
        $this->tar->setBinary('tar');
    }

    /**
     * Clear tar
     */
    public function tearDown()
    {
        $this->tar = null;
    }

    /**
     * Tests Tar::setUp
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupPathMissing()
    {
        $this->tar->setup(array());

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Tar::getExec
     */
    public function testDefault()
    {
        $target = $this->getTargetMock();
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $this->tar->setup(array('path' => 'src'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -cf \'/tmp/backup.tar\' -C \'src\' \'.\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Tar::getExec
     */
    public function testShowStdErr()
    {
        $target = $this->getTargetMock();
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $this->tar->setup(array('path' => 'src', 'showStdErr' => 'true'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -cf \'/tmp/backup.tar\' -C \'src\' \'.\'', $cmd);
    }

    /**
     * Tests Tar::getExec
     */
    public function testCompressorValid()
    {
        $target     = $this->getTargetMock();
        $compressor = Compressor::create('gzip');

        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');
        $target->method('getCompressor')->willReturn($compressor);

        $this->tar->setup(array('path' => 'src'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -zcf \'/tmp/backup.tar\' -C \'src\' \'.\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Tar::getExec
     */
    public function testCompressorInvalid()
    {
        $target     = $this->getTargetMock();
        $compressor = Compressor::create('zip');

        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');
        $target->method('getCompressor')->willReturn($compressor);

        $this->tar->setup(array('path' => 'src'));
        /** @var \phpbu\App\Backup\Cli\Exec $exec */
        $exec = $this->tar->getExec($target);
        $cmd  = (string) $exec->getExec();

        $this->assertEquals('tar -cf \'/tmp/backup.tar\' -C \'src\' \'.\' 2> /dev/null', $cmd);
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupOk()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('execute')->willReturn($cliResult);

        $this->tar->setup(array('path' => 'src'));
        $this->tar->setExec($exec);
        $this->tar->backup($target, $appResult);
    }

    /**
     * Tests Tar::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1);
        $appResult = $this->getMockBuilder('\\phpbu\\App\\Result')
                          ->disableOriginalConstructor()
                          ->getMock();
        $exec      = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $exec->expects($this->once())->method('execute')->willReturn($cliResult);

        $this->tar->setup(array('path' => 'src'));
        $this->tar->setExec($exec);
        $this->tar->backup($target, $appResult);
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

        $cliResult->method('getCmd')->willReturn('tar');
        $cliResult->method('getCode')->willReturn($code);
        $cliResult->method('getOutput')->willReturn(array());
        $cliResult->method('wasSuccessful')->willReturn($code == 0);

        return $cliResult;
    }

    /**
     * Create Target mock.
     *
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock()
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();
        $target->method('getSize')->willReturn(1090);
        $target->method('getPath')->willReturn('.');
        $target->method('fileExists')->willReturn(false);

        return $target;
    }
}
