<?php
namespace phpbu\App\Backup\Target;

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
class CompressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Compression::create
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testCreateInvalid()
    {
        Compression::create('/foo/bar');
        $this->assertFalse(true, 'Exception should be thrown');
    }

    /**
     * Test gzip compressor
     */
    public function testGzip()
    {
        $gzip = Compression::create('gzip');
        $this->assertEquals('gz', $gzip->getSuffix());
        $this->assertEquals('gzip', $gzip->getCommand());
        $this->assertEquals('application/x-gzip', $gzip->getMimeType());
    }

    /**
     * Test bzip2 compressor
     */
    public function testBzip2()
    {
        $gzip = Compression::create('bzip2');
        $this->assertEquals('bz2', $gzip->getSuffix());
        $this->assertEquals('bzip2', $gzip->getCommand());
        $this->assertEquals('application/x-bzip2', $gzip->getMimeType());
    }

    /**
     * Test Compression::isPipeable
     */
    public function testIsPipeable()
    {
        $gzip = Compression::create('gzip');
        $this->assertTrue($gzip->isPipeable());

        $bzip = Compression::create('bzip2');
        $this->assertTrue($bzip->isPipeable());

        $zip = Compression::create('zip');
        $this->assertFalse($zip->isPipeable());
    }

    /**
     * Test zip compressor
     */
    public function testZip()
    {
        $gzip = Compression::create('zip');
        $this->assertEquals('zip', $gzip->getSuffix());
        $this->assertEquals('zip', $gzip->getCommand());
        $this->assertEquals('application/zip', $gzip->getMimeType());
    }

    /**
     * Test compressor with path to binary
     */
    public function testGetCommand()
    {
        $gzip = Compression::create('/usr/local/bin/gzip');
        $this->assertEquals('gz', $gzip->getSuffix());
        $this->assertEquals('gzip', $gzip->getCommand());
    }
}
