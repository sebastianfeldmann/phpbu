<?php
namespace phpbu\App\Backup\Compressor;

use phpbu\App\Backup\Compressor;
use phpbu\App\Cli\Result;

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
class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Directory:__construct
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoPathToCompress()
    {
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
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCanCompressUncompressedTarget()
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();

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
        $cmp    = Compressor::create('bzip2');
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompressor')
               ->willReturn($cmp);

        $compressor = new Directory(__DIR__);
        $this->assertEquals('foo.txt.bz2', $compressor->getArchiveFile($target));
    }

    /**
     * Tests Directory:compress
     */
    public function testCompress()
    {
        $result     = new Result('foo', 0);
        $cmp        = Compressor::create('bzip2');
        $executable = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable')
                           ->disableOriginalConstructor()
                           ->getMock();
        $executable->method('run')->willReturn($result);

        $result = $this->getMockBuilder('\\phpbu\\App\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompressor')
               ->willReturn($cmp);

        $compressor = new Directory(__DIR__);
        $compressor->setExecutable($executable);
        $this->assertEquals('foo.txt.bz2', $compressor->compress($target, $result));
    }


    /**
     * Tests Directory:compress
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCompressFails()
    {
        $result     = new Result('foo', 1);
        $cmp        = Compressor::create('bzip2');
        $executable = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable')
                           ->disableOriginalConstructor()
                           ->getMock();
        $executable->method('run')->willReturn($result);

        $result = $this->getMockBuilder('\\phpbu\\App\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompressor')
               ->willReturn($cmp);

        $compressor = new Directory(__DIR__);
        $compressor->setExecutable($executable);
        $compressor->compress($target, $result);
    }

    /**
     * Tests Directory:compress
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCompressInvalidPath()
    {
        $result     = new Result('foo', 1);
        $cmp        = Compressor::create('bzip2');
        $executable = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable')
                           ->disableOriginalConstructor()
                           ->getMock();
        $executable->method('run')->willReturn($result);

        $result = $this->getMockBuilder('\\phpbu\\App\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();

        $target->method('shouldBeCompressed')
               ->willReturn(true);
        $target->method('getPathname')
               ->willReturn('foo.txt.bz2');
        $target->method('getPathnamePlain')
               ->willReturn('foo.txt');
        $target->method('getCompressor')
               ->willReturn($cmp);

        $compressor = new Directory('foo/bar/baz');
        $compressor->setExecutable($executable);
        $compressor->compress($target, $result);
    }
}
