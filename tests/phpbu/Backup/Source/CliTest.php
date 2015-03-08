<?php
namespace phpbu\App\Backup\Source;

/**
 * CliTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class CliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Cli::execute
     */
    public function testExecuteCompressedOk()
    {
        $compressor = $this->getCompressorMockForCmd(0, 'gzip');
        $target     = $this->getTargetMock();
        $target->method('getPathname')->willReturn('/tmp/foo');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompressor')->willReturn($compressor);
        $target->method('fileExists')->willReturn(false);
        $target->method('unlink')->willReturn(true);
        $result = $this->getResultMock(0, 'tar');
        $exec   = $this->getExecMock();
        $exec->expects($this->once())->method('execute')->willReturn($result);

        $cliTester = new CliStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $cliTester->testExecute($exec, $target, false);

        $this->assertEquals(0, $res->getCode());
        $this->assertEquals('tar', $res->getCmd());
    }

    /**
     * Tests Cli::execute
     */
    public function testExecuteCompressedFail()
    {
        $compressor = $this->getCompressorMockForCmd(0, 'gzip');
        $target     = $this->getTargetMock();
        $target->method('getPathname')->willReturn('/tmp/foo');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompressor')->willReturn($compressor);
        $target->expects($this->once())->method('fileExists')->willReturn(true);
        $target->expects($this->once())->method('unlink')->willReturn(true);
        $result = $this->getResultMock(1, 'tar', array('error'));
        $exec   = $this->getExecMock();
        $exec->method('execute')->willReturn($result);

        $cliTester = new CliStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $cliTester->testExecute($exec, $target, false);

        $this->assertEquals(1, $res->getCode());
        $this->assertEquals('tar', $res->getCmd());
        $this->assertEquals(1, count($res->getOutput()));
    }

    /**
     * Tests Cli::execute
     */
    public function testExecuteCompressedOutputOk()
    {
        $compressor = $this->getCompressorMockForCmd(0, 'gzip');
        $target     = $this->getTargetMock();
        $target->method('getPathname')->willReturn('/tmp/foo');
        $target->expects($this->once())->method('shouldBeCompressed')->willReturn(true);
        $target->expects($this->once())->method('getCompressor')->willReturn($compressor);
        $target->method('fileExists')->willReturn(true);
        $target->method('unlink')->willReturn(true);
        $result = $this->getResultMock(0, 'mysqldump');
        $exec   = $this->getExecMock();
        $exec->expects($this->once())->method('execute')->willReturn($result);

        $cliTester = new CliStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $cliTester->testExecute($exec, $target, true);

        $this->assertEquals(0, $res->getCode());
        $this->assertEquals('mysqldump' . PHP_EOL . 'gzip', $res->getCmd());
        $this->assertEquals(0, count($res->getOutput()));
    }

    /**
     * Tests Cli::execute
     */
    public function testExecuteCompressedOutputCompressorFail()
    {
        $compressor = $this->getCompressorMockForCmd(1, 'gzip', array('error'));
        $target     = $this->getTargetMock();
        $target->method('getPathname')->willReturn('/tmp/foo');
        $target->expects($this->once())->method('shouldBeCompressed')->willReturn(true);
        $target->expects($this->once())->method('getCompressor')->willReturn($compressor);
        $target->expects($this->once())->method('fileExists')->willReturn(true);
        $target->expects($this->once())->method('unlink')->willReturn(true);
        $result = $this->getResultMock(0, 'mysqldump');
        $exec   = $this->getExecMock();
        $exec->method('execute')->willReturn($result);

        $cliTester = new CliStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $cliTester->testExecute($exec, $target, true);

        $this->assertEquals(1, $res->getCode());
        $this->assertEquals('mysqldump' . PHP_EOL . 'gzip', $res->getCmd());
        $this->assertEquals(1, count($res->getOutput()));
    }

    /**
     * Create Target Mock.
     *
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock()
    {
        $targetStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                           ->disableOriginalConstructor()
                           ->getMock();
        $targetStub->method('getSize')->willReturn(1000);

        return $targetStub;
    }

    /**
     * Create Compressor Mock.
     *
     * @param  integer $code
     * @param  string  $cmd
     * @param  array   $output
     * @return \phpbu\App\Backup\Compressor
     */
    protected function getCompressorMockForCmd($code, $cmd, array $output = array())
    {
        $exec   = $this->getExecMock();
        $exec->method('execute')->willReturn($this->getResultMock($code, $cmd, $output));

        $compressorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Compressor')
                               ->disableOriginalConstructor()
                               ->getMock();
        $compressorStub->method('getCommand')->willReturn($cmd);
        $compressorStub->method('getExec')->willReturn($exec);


        return $compressorStub;
    }

    /**
     * Create Target Mock.
     *
     * @return \phpbu\App\Backup\Cli\Exec
     */
    protected function getExecMock()
    {
        $execStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Exec')
                         ->disableOriginalConstructor()
                         ->getMock();

        return $execStub;
    }

    /**
     * Create Result Mock.
     *
     * @param  integer $code
     * @param  string  $cmd
     * @param  array   $output
     * @return \phpbu\App\Backup\Cli\Result
     */
    protected function getResultMock($code, $cmd, $output = array())
    {
        $resultStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cli\\Result')
                           ->disableOriginalConstructor()
                           ->getMock();

        $resultStub->method('getCode')->willReturn($code);
        $resultStub->method('getCmd')->willReturn($cmd);
        $resultStub->method('getOutput')->willReturn($output);

        return $resultStub;
    }
}

class CliStub extends Cli
{
    public function testExecute($exec, $target, $compressOutput)
    {
        return $this->execute($exec, $target, $compressOutput);
    }
}
