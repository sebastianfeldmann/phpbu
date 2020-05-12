<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Target\Compression;
use SebastianFeldmann\Cli\Command\Result as CommandResult;
use SebastianFeldmann\Cli\Command\Runner\Result as RunnerResult;
use PHPUnit\Framework\TestCase;

/**
 * Compressor test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class DirectoryTest extends TestCase
{
    /**
     * Tests Directory:__construct
     */
    public function testNoPathToCompress()
    {
        $this->expectException('phpbu\App\Exception');
        $compressor = new Directory('');
    }

    /**
     * Tests Directory:isPathValid
     */
    public function testIsPathValid()
    {
        $compressor = new Directory('foo/bar/baz');

        $this->assertFalse($compressor->isPathValid('foo/bar/baz'));
        $this->assertTrue($compressor->isPathValid(__DIR__));
    }

    /**
     * Tests Directory::canCompress
     */
    public function testCanCompressUncompressedTarget()
    {
        $this->expectException('phpbu\App\Exception');
        $target = $this->createMock(\phpbu\App\Backup\Target::class);

        $target->method('shouldBeCompressed')
               ->willReturn(false);

        $compressor = new Directory('/foo/bar');
        $compressor->canCompress($target);
    }

    /**
     * Tests Directory:getArchiveFile
     */
    public function testGetArchiveFile()
    {
        $cmp    = Compression\Factory::create('bzip2');
        $target = $this->createMock(\phpbu\App\Backup\Target::class);

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompression')
               ->willReturn($cmp);

        $compressor = new Directory(__DIR__);
        $this->assertEquals('foo.txt.bz2', $compressor->getArchiveFile($target));
    }

    /**
     * Tests Directory:compress
     */
    public function testCompress()
    {
        $cmp           = Compression\Factory::create('bzip2');
        $commandResult = new CommandResult('foo', 0);
        $runnerResult  = new RunnerResult($commandResult);

        $result = $this->createMock(\phpbu\App\Result::class);

        $runner = $this->createMock(\SebastianFeldmann\Cli\Command\Runner::class);
        $runner->method('run')->willReturn($runnerResult);


        $target = $this->createMock(\phpbu\App\Backup\Target::class);

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompression')
               ->willReturn($cmp);

        $compressor = new Directory(__DIR__, PHPBU_TEST_FILES . '/bin', $runner);
        $this->assertEquals('foo.txt.bz2', $compressor->compress($target, $result));
    }


    /**
     * Tests Directory:compress
     */
    public function testCompressFails()
    {
        $this->expectException('phpbu\App\Exception');
        $cmp           = Compression\Factory::create('bzip2');
        $commandResult = new CommandResult('foo', 1);
        $runnerResult  = new RunnerResult($commandResult);

        $result = $this->createMock(\phpbu\App\Result::class);

        $runner = $this->createMock(\SebastianFeldmann\Cli\Command\Runner::class);
        $runner->method('run')->willReturn($runnerResult);

        $target = $this->createMock(\phpbu\App\Backup\Target::class);

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompression')
               ->willReturn($cmp);

        $compressor = new Directory(__DIR__, PHPBU_TEST_FILES . '/bin', $runner);
        $compressor->compress($target, $result);
    }

    /**
     * Tests Directory:compress
     */
    public function testCompressInvalidPath()
    {
        $this->expectException('phpbu\App\Exception');
        $cmp           = Compression\Factory::create('bzip2');
        $commandResult = new CommandResult('foo', 1);
        $runnerResult  = new RunnerResult($commandResult);

        $result = $this->createMock(\phpbu\App\Result::class);

        $runner = $this->createMock(\SebastianFeldmann\Cli\Command\Runner::class);
        $runner->method('run')->willReturn($runnerResult);

        $target = $this->createMock(\phpbu\App\Backup\Target::class);

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompression')
               ->willReturn($cmp);

        $compressor = new Directory('foo/bar/baz', PHPBU_TEST_FILES . '/bin', $runner);
        $compressor->compress($target, $result);
    }
}
