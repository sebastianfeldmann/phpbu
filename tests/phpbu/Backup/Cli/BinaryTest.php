<?php
namespace phpbu\App\Backup\Cli;

use phpbu\App\Backup\Target;

/**
 * CliTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.3.0
 */
class BinaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Binary::execute
     */
    public function testExecuteCompressedOk()
    {
        $compressor = $this->getCompressorMockForCmd(0, 'gzip', 'gz');
        $target     = $this->getTargetMock();
        $target->method('getPathnamePlain')->willReturn('/tmp/foo');
        $target->method('getCompressor')->willReturn($compressor);
        $target->method('fileExists')->willReturn(false);
        $target->method('unlink')->willReturn(true);
        $result = $this->getResultMock(0, 'tar');
        $exec   = $this->getExecMock();
        $exec->expects($this->once())->method('execute')->willReturn($result);

        $binaryTester = new BinaryStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $binaryTester->testExecute($exec, $target, false);

        $this->assertEquals(0, $res->getCode());
        $this->assertEquals('tar', $res->getCmd());
    }

    /**
     * Tests Binary::execute
     */
    public function testExecuteCompressedFail()
    {
        $compressor = $this->getCompressorMockForCmd(0, 'gzip', 'gz');
        $target     = $this->getTargetMock();
        $target->method('getPathnamePlain')->willReturn(tempnam(sys_get_temp_dir(), 'phpbu'));
        $target->method('getCompressor')->willReturn($compressor);
        $result = $this->getResultMock(1, 'tar', array('error'));
        $exec   = $this->getExecMock();
        $exec->method('execute')->willReturn($result);

        $binaryTester = new BinaryStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $binaryTester->testExecute($exec, $target, false);

        $this->assertEquals(1, $res->getCode());
        $this->assertEquals('tar', $res->getCmd());
        $this->assertEquals(1, count($res->getOutput()));
    }

    /**
     * Tests Binary::execute
     */
    public function testExecuteCompressedOutputOk()
    {
        $compressor = $this->getCompressorMockForCmd(0, 'gzip', 'gz');
        $target     = $this->getTargetMock();
        $target->method('getPathnamePlain')->willReturn('/tmp/foo');
        $target->expects($this->once())->method('getCompressor')->willReturn($compressor);
        $result = $this->getResultMock(0, 'mysqldump');
        $exec   = $this->getExecMock();
        $exec->expects($this->once())->method('execute')->willReturn($result);

        $binaryTester = new BinaryStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $binaryTester->testExecute($exec, $target, true);

        $this->assertEquals(0, $res->getCode());
        $this->assertEquals('mysqldump' . PHP_EOL . 'gzip', $res->getCmd());
        $this->assertEquals(0, count($res->getOutput()));
    }

    /**
     * Tests Binary::execute
     */
    public function testExecuteCompressedOutputCompressorFail()
    {
        $compressor = $this->getCompressorMockForCmd(1, 'gzip', 'gz', array('error'));
        $target     = $this->getTargetMock();
        $target->method('getPathnamePlain')->willReturn(tempnam(sys_get_temp_dir(), 'phpbu'));
        $target->expects($this->once())->method('getCompressor')->willReturn($compressor);
        $result = $this->getResultMock(0, 'mysqldump');
        $exec   = $this->getExecMock();
        $exec->method('execute')->willReturn($result);

        $binaryTester = new BinaryStub();

        /** @var \phpbu\App\Backup\Cli\Result $res */
        $res = $binaryTester->testExecute($exec, $target, true);

        $this->assertEquals(1, $res->getCode());
        $this->assertEquals('mysqldump' . PHP_EOL . 'gzip', $res->getCmd());
        $this->assertEquals(1, count($res->getOutput()));
    }

    /**
     * Tests Binary::getCommandLocations
     */
    public function testCommandLocationsDefault()
    {
        $list = Binary::getCommandLocations('tar');
        $this->assertEquals(0, count($list));

        $list = Binary::getCommandLocations('mysqldump');
        $this->assertEquals(2, count($list));
    }

    /**
     * Tests Binary::getCommandLocations
     */
    public function testAddCommandLocations()
    {
        Binary::addCommandLocation('mongodump', '/foo/mongodump');
        $list = Binary::getCommandLocations('mongodump');

        $this->assertEquals(1, count($list));
        $this->assertEquals('/foo/mongodump', $list[0]);
    }

    /**
     * Tests Binary::detectCommand
     */
    public function testDetectCommand()
    {
        $bin  = new BinaryStub();
        $cd   = $bin->testDetectCommand('cd');

        $this->assertTrue(strpos($cd, 'cd') !== false);
        $this->assertTrue(is_executable($cd));
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
     * @param  string  $suffix
     * @param  array   $output
     * @return \phpbu\App\Backup\Compressor
     */
    protected function getCompressorMockForCmd($code, $cmd, $suffix, array $output = array())
    {
        $exec   = $this->getExecMock();
        $exec->method('execute')->willReturn($this->getResultMock($code, $cmd, $output));

        $compressorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Compressor')
                               ->disableOriginalConstructor()
                               ->getMock();
        $compressorStub->method('getCommand')->willReturn($cmd);
        $compressorStub->method('getExec')->willReturn($exec);
        $compressorStub->method('getSuffix')->willReturn($suffix);


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

/**
 * Class BinaryStub
 */
class BinaryStub extends Binary
{
    /**
     * @param  \phpbu\App\Backup\Cli\Exec $exec
     * @param  \phpbu\App\Backup\Target   $target
     * @param  boolean                    $compressOutput
     * @return Result
     */
    public function testExecute(Exec $exec, Target $target, $compressOutput)
    {
        $compressor = $compressOutput ? $target->getCompressor() : null;
        return $this->execute($exec, $target->getPathnamePlain(), $compressor);
    }

    /**
     * @param  string $cmd
     * @return string
     */
    public function testDetectCommand($cmd)
    {
        return $this->detectCommand($cmd);
    }
}
