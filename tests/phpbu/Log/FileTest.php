<?php
namespace phpbu\App\Log;

use PHPUnit\Framework\TestCase;

/**
 * Printer Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.2.1
 */
class FileTest extends TestCase
{
    /**
     * Tests Printer::setAutoFlush
     */
    public function testOutFail()
    {
        $this->expectException('InvalidArgumentException');
        $file = new File();
        $file->setOut(null);
    }

    /**
     * Tests Printer::write
     */
    public function testWriteToStdErr()
    {
        $file = new File();
        $file->setOut('php://stdErr');
        ob_start();
        $file->write('');
        $output = ob_get_clean();

        $this->assertEquals('', $output);
        $file->close();
    }

    /**
     * Tests Printer::write
     */
    public function testCreateByResourceToStdErr()
    {
        $file = new File();
        $file->setOut(fopen('php://stdErr', 'wt'));
        ob_start();
        $file->write('');
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    /**
     * Tests Printer::write
     */
    public function testCreateDirAndFileoStdErr()
    {
        $log  = sys_get_temp_dir() . '/logger/file.log';
        $file = new File();
        $file->setOut($log);
        $file->write('foo');
        $file->close();

        $this->assertFileExists($log);

        unlink($log);
        rmdir(dirname($log));
    }
}
