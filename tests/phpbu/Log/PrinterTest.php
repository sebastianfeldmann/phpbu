<?php
namespace phpbu\App\Log;

/**
 * Printer Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.2.1
 */
class PrinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Printer::getAutoFlush
     */
    public function testAutoFlushDefault()
    {
        $printer = new Printer();

        $af = $printer->getAutoFlush();

        $this->assertFalse($af);
    }

    /**
     * Tests Printer::setAutoFlush
     */
    public function testSetAutoFlush()
    {
        $printer = new Printer();
        $printer->setAutoFlush(true);

        $af = $printer->getAutoFlush();

        $this->assertTrue($af);
    }

    /**
     * Tests Printer::setAutoFlush
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetAutoFlushFail()
    {
        $printer = new Printer();
        $printer->setAutoFlush('green');
    }

    /**
     * Tests Printer::write
     */
    public function testWriteToStdOut()
    {
        $printer = new Printer();
        ob_start();
        $printer->write('foo');
        $output = ob_get_clean();

        $this->assertEquals('foo', $output);
    }

    /**
     * Tests Printer::write
     */
    public function testWriteToStdOutWithAutoFlush()
    {
        $printer = new Printer();
        $printer->setAutoFlush(true);
        ob_start();
        $printer->write('foo');
        $output = ob_get_clean();

        $this->assertEquals('foo', $output);
    }
}
