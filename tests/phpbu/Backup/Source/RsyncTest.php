<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * RsyncTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 3.2.0
 */
class RsyncTest extends TestCase
{
    use BaseMockery;
    use CliMockery;
    /**
     * Tests Rsync::setUp
     */
    public function testSetupPathMissing()
    {
        $this->expectException('phpbu\App\Exception');
        $rsync = new Rsync();
        $rsync->setup([]);

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testDefault()
    {
        $target = $this->createTargetMock('/tmp/backup.rsync');

        $rsync = new Rsync();
        $rsync->setup(['path' => __DIR__, 'pathToRsync' => PHPBU_TEST_BIN]);

        $exec = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -av \'' . __DIR__ . '\' \'/tmp/backup.rsync\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testPathAndHost()
    {
        $target = $this->createTargetMock('/tmp/backup.rsync');

        $rsync = new Rsync();
        $rsync->setup(['path' => '/foo/bar', 'host' => 'example.com', 'pathToRsync' => PHPBU_TEST_BIN]);

        $exec = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -av \'example.com:/foo/bar\' \'/tmp/backup.rsync\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testPathHostAndName()
    {
        $target = $this->createTargetMock('/tmp/backup.rsync');

        $rsync = new Rsync();
        $rsync->setup([
            'path'        => '/foo/bar',
            'host'        => 'example.com',
            'user'        => 'username',
            'pathToRsync' => PHPBU_TEST_BIN
        ]);

        $exec = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -av \'username@example.com:/foo/bar\' \'/tmp/backup.rsync\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::getExecutable
     */
    public function testCustomArgs()
    {
        $target = $this->createTargetMock('/tmp/backup.rsync');

        $rsync = new Rsync();
        $rsync->setup([
            'args'        => '-av /foo %TARGET_FILE%',
            'pathToRsync' => PHPBU_TEST_BIN
        ]);

        $exec = $rsync->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/rsync -av /foo /tmp/backup.rsync',
            $exec->getCommand()
        );
    }

    /**
     * Tests Rsync::backup
     */
    public function testBackupOk()
    {
        $target = $this->createTargetMock('/tmp/backup.rsync');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'rsync'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $rsync = new Rsync($runner);
        $rsync->setup([
            'path'        => '/foo/bar',
            'host'        => 'example.com',
            'user'        => 'username',
            'pathToRsync' => PHPBU_TEST_BIN
        ]);

        $status = $rsync->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Rsync::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $target = $this->createTargetMock('/tmp/backup.rsync');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'rsync'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $rsync = new Rsync($runner);
        $rsync->setup([
            'path'        => __DIR__,
            'pathToRsync' => PHPBU_TEST_BIN
        ]);

        $rsync->backup($target, $appResult);
    }
}
