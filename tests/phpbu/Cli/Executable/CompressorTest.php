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
class CompressorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Compressor::getCommand
     */
    public function testDefault()
    {
        $gzip = new Compressor('gzip', PHPBU_TEST_BIN);
        $gzip->force(true)->compressFile(__FILE__);

        $this->assertEquals(PHPBU_TEST_BIN . '/gzip -f \'' . __FILE__ . '\'', $gzip->getCommand());
    }

    /**
     * Tests Compressor::getCommand
     */
    public function testZipNoForceOption()
    {
        $gzip = new Compressor('zip', PHPBU_TEST_BIN);
        $gzip->force(true)->compressFile(__FILE__);

        $this->assertEquals(PHPBU_TEST_BIN . '/zip \'' . __FILE__ . '\'', $gzip->getCommand());
    }

    /**
     * Tests Compressor::compressFile
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testFailEarlyCompress()
    {
        $gzip = new Compressor('gzip', PHPBU_TEST_BIN);
        $gzip->getCommand();
    }
}
