<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use phpbu\App\Configuration;
use SebastianFeldmann\Cli\Command\Result as CommandResult;
use SebastianFeldmann\Cli\Command\Runner\Simple;
use SebastianFeldmann\Cli\Processor\ProcOpen;
use PHPUnit\Framework\TestCase;

/**
 * TarTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class TarTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Tar::setUp
     */
    public function testSetupPathMissing()
    {
        $this->expectException('phpbu\App\Exception');
        $tar = new Tar();
        $tar->setup([]);

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Tar::setUp
     */
    public function testSetupPathDoesNotExist()
    {
        $this->expectException('phpbu\App\Exception');
        $tar = new Tar();
        $tar->setup(['path' => getcwd() . '/foo']);

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testDefault()
    {
        $target = $this->createTargetMock('/tmp/backup.tar');
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
    public function testDereferenceOption()
    {
        $target = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'dereference' => 'true']);

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar -h -cf \'/tmp/backup.tar\' -C \''
            . dirname(__DIR__) . '\' \''
            . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testExcludes()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'exclude' => './foo,./bar']);

        $target = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --exclude=\'./foo\' --exclude=\'./bar\' -cf \'/tmp/backup.tar\' -C \''
            . dirname(__DIR__) . '\' \''
            . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     *
     * We have to create a configuration here to actually calculate a relative path from the configuration location.
     */
    public function testIncremental()
    {
        $incFile = getcwd() . '/foo.snar';
        $conf    = new Configuration();
        $conf->setFilename(getcwd() . '/config.xml');

        $target  = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'incrementalFile' => 'foo.snar']);

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --listed-incremental=\'' . $incFile . '\' '
            . '-cf \'/tmp/backup.tar\' -C \''
            . dirname(__DIR__) . '\' \''
            . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::setUp
     */
    public function testIncrementalInvalidZeroLevelFormat()
    {
        $this->expectException('phpbu\App\Exception');

        $tar = new Tar();
        $tar->setup(
            [
                'pathToTar'        => PHPBU_TEST_BIN,
                'path'             => __DIR__,
                'incrementalFile'  => 'foo.snar',
                'forceLevelZeroOn' => 'foo'
            ]
        );

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testIncrementalForceZeroTrue()
    {
        $incFile = getcwd() . '/foo.snar';
        $time    = time();
        $conf    = new Configuration();
        $conf->setFilename(getcwd() . '/config.xml');

        $target  = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar = new Tar(null, $time);
        $tar->setup(
            [
                'pathToTar'        => PHPBU_TEST_BIN,
                'path'             => __DIR__,
                'incrementalFile'  => 'foo.snar',
                'forceLevelZeroOn' => '%D@Mon|Tue|Wed|Thu|Fri|Sat|Sun'
            ]
        );

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --listed-incremental=\'' . $incFile . '\' --level=\'0\' '
            . '-cf \'/tmp/backup.tar\' -C \''
            . dirname(__DIR__) . '\' \''
            . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testIncrementalForceZeroFalse()
    {
        $incFile = getcwd() . '/foo.snar';
        $time    = time() + (3600 * 48);
        $conf    = new Configuration();
        $conf->setFilename(getcwd() . '/config.xml');

        $target  = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar = new Tar(null, $time);
        $tar->setup(
            [
                'pathToTar'        => PHPBU_TEST_BIN,
                'path'             => __DIR__,
                'incrementalFile'  => 'foo.snar',
                'forceLevelZeroOn' => '%d@' . date('d')
            ]
        );

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --listed-incremental=\'' . $incFile . '\' '
            . '-cf \'/tmp/backup.tar\' -C \''
            . dirname(__DIR__) . '\' \''
            . basename(__DIR__) . '\'',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::getExecutable
     */
    public function testForceLocal()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'forceLocal' => 'true']);

        $target = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --force-local -cf \'/tmp/backup.tar\' -C \''
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

        $target = $this->createTargetMock('/tmp/backup.tar');
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

        $target = $this->createTargetMock('/tmp/backup.tar');
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
     * Tests Tar::getExecutable
     */
    public function testThrottle()
    {
        $target = $this->createTargetMock('/tmp/backup.tar');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.tar');

        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'throttle' => '1m']);

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar -c -C \''
            . dirname(__DIR__) . '\' \''
            . basename(__DIR__) . '\''
            . ' | pv -qL \'1m\' > /tmp/backup.tar',
            $exec->getCommand()
        );
    }

    /**
     * Tests Tar::backup
     */
    public function testInvalidDir()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'tar'));

        $tar = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $appResult = $this->getAppResultMock();
        $target    = $this->createTargetMock('/tmp/backup.tar');
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

        $compression = $this->createCompressionMock('gzip', 'gz');
        $target      = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
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
    public function testUseCompressProgram()
    {
        $tar = new Tar();
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'compressProgram' => 'lbzip2']);

        $compression = $this->createCompressionMock('bzip2', 'bz2');
        $target      = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.bz2');
        $target->method('shouldBeCompressed')->willReturn(true);
        $target->method('getCompression')->willReturn($compression);
        $target->method('getPathname')->willReturn('/tmp/backup.tar.bz2');

        $exec = $tar->getExecutable($target);

        $this->assertEquals(
            PHPBU_TEST_BIN . '/tar --use-compress-program=\'lbzip2\' -cf \'/tmp/backup.tar.bz2\' -C \''
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

        $compression = $this->createCompressionMock('zip', 'zip');
        $target      = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.zip');
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
        $processor = $this->createMock(ProcOpen::class);
        $processor->expects($this->once())
                  ->method('run')
                  ->willReturn(new CommandResult('tar', 0, '', '', '', [0]));

        $runner = new Simple($processor);
        $tar    = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $target = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
        $target->method('getCompression')->willReturn($this->createCompressionMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $status = $tar->backup($target, $appResult);

        $this->assertTrue($status->handledCompression());
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupOkOnFailedRead()
    {
        $processor = $this->createMock(ProcOpen::class);
        $processor->expects($this->once())
                  ->method('run')
                  ->willReturn(new CommandResult('tar', 1, '', '', '', [0, 1]));

        $runner = new Simple($processor);
        $tar    = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__, 'ignoreFailedRead' => 'true']);

        $target = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
        $target->method('getCompression')->willReturn($this->createCompressionMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $status = $tar->backup($target, $appResult);

        $this->assertTrue($status->handledCompression());
    }


    /**
     * Tests Tar::backup
     */
    public function testBackupFailOnFailedRead()
    {
        $this->expectException('RuntimeException');
        $processor = $this->createMock(ProcOpen::class);
        $processor->expects($this->once())
                  ->method('run')
                  ->willReturn(new CommandResult('tar', 1, '', '', '', [0]));

        $runner = new Simple($processor);
        $tar    = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $target = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.gz');
        $target->method('getCompression')->willReturn($this->createCompressionMock('gzip', 'gz'));

        $appResult = $this->getAppResultMock();

        $tar->backup($target, $appResult);
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

        $target = $this->createTargetMock('/tmp/backup.tar', '/tmp/backup.tar.zip');
        $target->method('getCompression')->willReturn($this->createCompressionMock('zip', 'zip'));

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $status = $tar->backup($target, $appResult);

        $this->assertFalse($status->handledCompression());
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupInvalidPath()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $tar    = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __FILE__]);

        $target    = $this->createTargetMock('/tmp/backup.tar');
        $appResult = $this->getAppResultMock();

        $tar->backup($target, $appResult);
    }

    /**
     * Tests Tar::backup
     */
    public function testBackupFail()
    {
        $this->expectException('phpbu\App\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(2, 'tar'));

        $tar = new Tar($runner);
        $tar->setup(['pathToTar' => PHPBU_TEST_BIN, 'path' => __DIR__]);

        $target    = $this->createTargetMock('/tmp/backup.tar');
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $tar->backup($target, $appResult);
    }
}
