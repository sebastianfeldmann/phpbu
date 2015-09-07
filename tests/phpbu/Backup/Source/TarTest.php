<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;
use phpbu\App\Backup\Compressor;

/**
 * TarTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class TarTest extends CliTest
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
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $target = $this->getTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $this->tar->setup(array('path' => __DIR__, 'pathToTar' => $path));
        $exec = $this->tar->getExecutable($target);

        $this->assertEquals($path . '/tar -cf \'/tmp/backup.tar\' -C \'' . __DIR__ . '\' \'.\' 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Tar::getExec
     */
    public function testCompressedTarget()
    {
        $path       = realpath(__DIR__ . '/../../../_files/bin');
        $compressor = $this->getCompressorMock('gzip', 'gz');
        $target     = $this->getTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompressor')->willReturn($compressor);
        $target->method('getPathname')->willReturn('/tmp/backup.tar.gz');

        $this->tar->setup(array('path' => __DIR__, 'pathToTar' => $path));
        $exec = $this->tar->getExecutable($target);

        $this->assertEquals($path . '/tar -zcf \'/tmp/backup.tar.gz\' -C \'' . __DIR__ . '\' \'.\' 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Tar::getExec
     */
    public function testInvalidCompressor()
    {
        $path       = realpath(__DIR__ . '/../../../_files/bin');
        $compressor = $this->getCompressorMock('zip', 'zip');
        $target     = $this->getTargetMock('/tmp/backup.tar', '/tmp/backup.tar.zip');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompressor')->willReturn($compressor);
        $target->method('getPathname')->willReturn('/tmp/backup.tar.zip');
        $target->method('getPathnamePlain')->willReturn('/tmp/backup.tar');

        $this->tar->setup(array('path' => __DIR__, 'pathToTar' => $path));
        $exec = $this->tar->getExecutable($target);

        $this->assertEquals($path . '/tar -cf \'/tmp/backup.tar\' -C \'' . __DIR__ . '\' \'.\' 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupOk()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'tar');
        $appResult = $this->getAppResultMock();
        $tar       = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Tar')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $tar->expects($this->once())->method('run')->willReturn($cliResult);
        $tar->expects($this->once())->method('handlesCompression')->willReturn(true);

        $this->tar->setup(array('path' => __DIR__, 'pathToTar' => $path));
        $this->tar->setExecutable($tar);

        $status = $this->tar->backup($target, $appResult);

        $this->assertTrue($status->handledCompression());
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupOkUnsupportedCompression()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'tar');
        $appResult = $this->getAppResultMock();
        $tar       = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Tar')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $tar->expects($this->once())->method('run')->willReturn($cliResult);
        $tar->expects($this->once())->method('handlesCompression')->willReturn(false);

        $this->tar->setup(array('path' => __DIR__, 'pathToTar' => $path));
        $this->tar->setExecutable($tar);

        $status = $this->tar->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Tar::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'tar');
        $appResult = $this->getAppResultMock();
        $tar       = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Tar')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $tar->expects($this->once())->method('run')->willReturn($cliResult);
        $tar->expects($this->once())->method('handlesCompression')->willReturn(true);

        $this->tar->setup(array('path' => __DIR__, 'pathToTar' => $path));
        $this->tar->setExecutable($tar);

        $this->tar->backup($target, $appResult);
    }
}
