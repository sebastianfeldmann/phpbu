<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Backup\Source\Exception;

/**
 * Source Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Source::run
     */
    public function testBackupSuccessful()
    {
        $status = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Status')
                       ->disableOriginalConstructor()
                       ->getMock();
        $status->expects($this->once())
               ->method('handledCompression')
               ->willReturn(true);

        $source = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source')
                       ->disableOriginalConstructor()
                       ->getMock();
        $source->expects($this->once())
               ->method('backup')
               ->willReturn($status);

        $target = $this->getTargetMock(true, true);
        $result = $this->getResultMock();
        $runner = new Source();
        $runner->setSimulation(false);
        $runner->run($source, $target, $result);
    }

    /**
     * Tests Source::run
     */
    public function testSimulateWithFileToCompress()
    {
        $status = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Status')
                       ->disableOriginalConstructor()
                       ->getMock();
        $status->expects($this->once())
               ->method('handledCompression')
               ->willReturn(false);
        $status->expects($this->once())
               ->method('getDataPath')
               ->willReturn(realpath(__DIR__ . '/../../_files/misc/backup.txt'));

        $source = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Mysqldump')
                       ->disableOriginalConstructor()
                       ->getMock();
        $source->expects($this->once())
               ->method('simulate')
               ->willReturn($status);

        $target = $this->getTargetMock(true, false);
        $result = $this->getResultMock();
        $runner = new Source();
        $runner->setSimulation(true);
        $runner->run($source, $target, $result);
    }

    /**
     * Tests Source::run
     */
    public function testSimulateWithDirectoryToCompress()
    {
        $targetPath = realpath(__DIR__ . '/../../_files/misc');
        $status     = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Status')
                           ->disableOriginalConstructor()
                           ->getMock();
        $status->expects($this->once())
               ->method('handledCompression')
               ->willReturn(false);
        $status->expects($this->once())
               ->method('getDataPath')
               ->willReturn($targetPath);
        $status->expects($this->once())
               ->method('isDirectory')
               ->willReturn(true);

        $source = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Mysqldump')
                       ->disableOriginalConstructor()
                       ->getMock();
        $source->expects($this->once())
               ->method('simulate')
               ->willReturn($status);

        $target = $this->getTargetMock(true, false, 5);
        $target->method('getPathname')
               ->willReturn('__SOME_CRAZY_FILENAME__');
        $target->method('getPathnamePlain')
               ->willReturn($targetPath . '/backup.txt');

        $result = $this->getResultMock();
        $runner = new Source();
        $runner->setSimulation(true);
        $runner->run($source, $target, $result);
    }

    /**
     * Tests Source::run
     */
    public function testSourceWithFileToCompress()
    {
        $file           = $this->createTempFile();
        $fileCompressed = $file . '.gz';
        $targetPath     = realpath($file);
        $status         = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Status')
                               ->disableOriginalConstructor()
                               ->getMock();
        $status->expects($this->once())
               ->method('handledCompression')
               ->willReturn(false);
        $status->expects($this->once())
               ->method('getDataPath')
               ->willReturn($targetPath);

        $source = $this->getMockBuilder('\\phpbu\\App\\Backup\\Source\\Mysqldump')
                       ->disableOriginalConstructor()
                       ->getMock();
        $source->expects($this->once())
               ->method('backup')
               ->willReturn($status);

        $target = $this->getTargetMock(true, false, 1, 'gzip', false);
        $target->method('getPathname')
               ->willReturn($fileCompressed);
        $target->method('getPathnamePlain')
               ->willReturn($file);

        $result = $this->getResultMock();
        $runner = new Source();
        $runner->setSimulation(false);
        $runner->run($source, $target, $result);

        if (file_exists($file)) {
            unlink($file);
        }
        if (file_exists($fileCompressed)) {
            unlink($fileCompressed);
        }
    }

    /**
     * Create Target mock.
     *
     * @param  bool   $compress
     * @param  bool   $handledCompression
     * @param  int    $runs
     * @param  string $cmd
     * @param  bool   $simulate
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock($compress, $handledCompression, $runs = 1, $cmd = 'zip', $simulate = true)
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();

        if ($compress) {
            $target->method('shouldBeCompressed')
                   ->willReturn(true);

            if (!$handledCompression) {
                $compressor = $this->getMockBuilder('\\phpbu\\App\\Backup\\Compressor')
                                   ->disableOriginalConstructor()
                                   ->getMock();
                $compressor->method('getCommand')
                           ->willReturn($cmd);


                $target->expects($this->exactly($runs))
                       ->method('getCompressor')
                       ->willReturn($compressor);
            }
        }

        return $target;
    }

    /**
     * Create Result mock.
     *
     * @param  int $expectedDebugCalls
     * @return \phpbu\App\Result
     */
    protected function getResultMock($expectedDebugCalls = 0)
    {
        $result = $this->getMockBuilder('\\phpbu\\App\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();
        if ($expectedDebugCalls > 0) {
            $result->expects($this->exactly($expectedDebugCalls))
                   ->method('debug');
        }
        return $result;
    }

    /**
     * Creates a temp file and returns the path to the created file.
     *
     * @return string
     */
    protected function createTempFile()
    {
        $path = sys_get_temp_dir() . '/' . sha1(rand(999, 9999999)) . '.txt';
        file_put_contents($path, '<html>foo bar baz</html>');
        return $path;
    }
}
