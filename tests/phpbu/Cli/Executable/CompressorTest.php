<?php
namespace phpbu\App\Cli\Executable;

/**
 * Compressor Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.0
 */
class CompressorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Compressor::getCommandLine
     */
    public function testDefault()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $gzip = new Compressor('gzip', $path);
        $gzip->force(true)->compressFile(__FILE__);

        $this->assertEquals($path . '/gzip -f \'' . __FILE__ . '\'', $gzip->getCommandLine());
    }

    /**
     * Tests Compressor::getCommandLine
     */
    public function testZipNoForceOption()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $gzip = new Compressor('zip', $path);
        $gzip->force(true)->compressFile(__FILE__);

        $this->assertEquals($path . '/zip \'' . __FILE__ . '\'', $gzip->getCommandLine());
    }

    /**
     * Tests Compressor::compressFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFailEarlyCompress()
    {
        $path = realpath(__DIR__ . '/../../../_files/bin');
        $gzip = new Compressor('gzip', $path);
        $gzip->run();
    }
}
