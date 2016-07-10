<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;
use phpbu\App\Backup\Compressor;

/**
 * RsyncTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class RsyncTest extends CliTest
{
    /**
     * tar
     *
     * @var \phpbu\App\Backup\Source\Tar
     */
    protected $rsync;

    /**
     * Setup tar
     */
    public function setUp()
    {
        $this->rsync = new Rsync();
    }

    /**
     * Clear tar
     */
    public function tearDown()
    {
        $this->rsync = null;
    }

    /**
     * Tests Rsync::setUp
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupPathMissing()
    {
        $this->rsync->setup([]);

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Rsync::getExec
     */
    public function testDefault()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $target = $this->getTargetMock('/tmp/backup.rsync');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.rsync');

        $this->rsync->setup(['path' => __DIR__, 'pathToRsync' => $path]);
        $exec = $this->rsync->getExecutable($target);

        $this->assertEquals($path . '/rsync -av \'' . __DIR__ . '\' \'/tmp/backup.rsync\'', $exec->getCommandLine());
    }

    /**
     * Tests Rsync::getExec
     */
    public function testPathAndHost()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $target = $this->getTargetMock('/tmp/backup.rsync');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.rsync');

        $this->rsync->setup(['path' => '/foo/bar', 'host' => 'example.com', 'pathToRsync' => $path]);
        $exec = $this->rsync->getExecutable($target);

        $this->assertEquals(
            $path . '/rsync -av \'example.com:/foo/bar\' \'/tmp/backup.rsync\'',
            $exec->getCommandLine()
        );
    }

    /**
     * Tests Rsync::getExec
     */
    public function testPathHostAndName()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $target = $this->getTargetMock('/tmp/backup.rsync');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.rsync');

        $this->rsync->setup([
            'path'        => '/foo/bar',
            'host'        => 'example.com',
            'user'        => 'username',
            'pathToRsync' => $path
        ]);
        $exec = $this->rsync->getExecutable($target);

        $this->assertEquals(
            $path . '/rsync -av \'username@example.com:/foo/bar\' \'/tmp/backup.rsync\'',
            $exec->getCommandLine()
        );
    }

    /**
     * Tests Rsync::getExec
     */
    public function testCustomArgs()
    {
        $path   = realpath(__DIR__ . '/../../../_files/bin');
        $target = $this->getTargetMock('/tmp/backup.rsync');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.rsync');

        $this->rsync->setup([
            'args'        => '-av /foo %TARGET_FILE%',
            'pathToRsync' => $path
        ]);
        $exec = $this->rsync->getExecutable($target);

        $this->assertEquals(
            $path . '/rsync -av /foo /tmp/backup.rsync',
            $exec->getCommandLine()
        );
    }

    /**
     * Tests Rsync::backup
     */
    public function testBackupOk()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(0, 'rsync');
        $appResult = $this->getAppResultMock();
        $rsync     = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Rsync')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $rsync->expects($this->once())->method('run')->willReturn($cliResult);

        $this->rsync->setup([
            'path'        => '/foo/bar',
            'host'        => 'example.com',
            'user'        => 'username',
            'pathToRsync' => $path
        ]);
        $this->rsync->setExecutable($rsync);

        $status = $this->rsync->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Rsync::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $path      = realpath(__DIR__ . '/../../../_files/bin');
        $target    = $this->getTargetMock();
        $cliResult = $this->getCliResultMock(1, 'rsync');
        $appResult = $this->getAppResultMock();
        $rsync     = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\Tar')
                          ->disableOriginalConstructor()
                          ->getMock();

        $appResult->expects($this->once())->method('debug');
        $rsync->expects($this->once())->method('run')->willReturn($cliResult);

        $this->rsync->setup(['path' => __DIR__, 'pathToRsync' => $path]);
        $this->rsync->setExecutable($rsync);

        $this->rsync->backup($target, $appResult);
    }
}
