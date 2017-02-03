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
     * Tests Tar::setUp
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupPathMissing()
    {
        $tar = new Tar();
        $tar->setup([]);

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testDefault()
    {
        $target = $this->getTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar -cf \'/tmp/backup.tar\' -C \''
              . dirname(__DIR__) . '\' \''
              . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testIgnoreFailedRead()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'ignoreFailedRead' => 'true']);

        $target = $this->getTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --ignore-failed-read -cf \'/tmp/backup.tar\' -C \''
              . dirname(__DIR__) . '\' \''
              . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testRemoveDir()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'removeSourceDir' => 'true']);

        $target = $this->getTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            '(' . PHPBU_TEST_BIN . '/tar -cf \'/tmp/backup.tar\' -C \''
              . dirname(__DIR__) . '\' \'' . basename(__DIR__)
              . '\' && rm -rf \'' . __DIR__ . '\')',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidDir()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'tar'));

        $tar = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $appResult = $this->getAppResultMock();
        $target    = $this->getTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar->backup($target, $appResult);
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testCompressedTarget()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $compression = $this->getCompressionMock('gzip', 'gz');
        $target      = $this->getTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompression')->willReturn($compression);
        $target->method('getPathname')->willReturn('/tmp/backup.tar.gz');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar -zcf \'/tmp/backup.tar.gz\' -C \''
              . dirname(__DIR__) . '\' \''
              . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testInvalidCompression()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $compression = $this->getCompressionMock('zip', 'zip');
        $target      = $this->getTargetMock('/tmp/backup.tar', '/tmp/backup.tar.zip');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompression')->willReturn($compression);
        $target->method('getPathname')->willReturn('/tmp/backup.tar.zip');
        $target->method('getPathnamePlain')->willReturn('/tmp/backup.tar');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar -cf \'/tmp/backup.tar\' -C \''
              . dirname(__DIR__) . '\' \''
              . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'tar'));

        $tar = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $target = $this->getTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
        $target->method('getCompression')->willReturn($this->getCompressionMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $status = $tar->backup($target, $appResult);

        $this->assertTrue($status->handledCompression());
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupOkUnsupportedCompression()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'tar'));

        $tar = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $target = $this->getTargetMock('/tmp/backup.tar', '/tmp/backup.tar.zip');
        $target->method('getCompression')->willReturn($this->getCompressionMock('zip', 'zip'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $status = $tar->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Tar::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupFail()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'tar'));

        $tar = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $target    = $this->getTargetMock('/tmp/backup.tar');
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $tar->backup($target, $appResult);
    }
}
