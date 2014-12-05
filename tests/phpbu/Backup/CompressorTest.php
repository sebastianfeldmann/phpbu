<?php
namespace phpbu\Backup;

/**
 * Compressor test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CompressorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test gzip compressor
     */
    public function testGzip()
    {
        $gzip = Compressor::create('gzip');
        $this->assertEquals('gz', $gzip->getSuffix());
        $this->assertEquals('gzip', $gzip->getCommand());
    }

    /**
     * Test bzip2 compressor
     */
    public function testBzip2()
    {
        $gzip = Compressor::create('bzip2');
        $this->assertEquals('bz2', $gzip->getSuffix());
        $this->assertEquals('bzip2', $gzip->getCommand());
    }

    /**
     * Test zip compressor
     */
    public function testZip()
    {
        $gzip = Compressor::create('zip');
        $this->assertEquals('zip', $gzip->getSuffix());
        $this->assertEquals('zip', $gzip->getCommand());
    }

    /**
     * Tets compressor with path to binary
     */
    public function testGzipWithPath()
    {
        $gzip = Compressor::create('/usr/local/bin/gzip');
        $this->assertEquals('gz', $gzip->getSuffix());
        $this->assertEquals('/usr/local/bin/gzip', $gzip->getCommand());
    }
}
