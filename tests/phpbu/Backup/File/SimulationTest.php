<?php
namespace phpbu\App\Backup\File;

use PHPUnit\Framework\TestCase;

/**
 * SimulationTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class SimulationTest extends TestCase
{
    /**
     * Test creating file and handle removing
     */
    public function testFile()
    {
        $time = time();
        $file = new Simulation($time, 100, '/foo', 'bar.txt');

        $this->assertEquals(100, $file->getSize());
        $this->assertEquals($time, $file->getMTime());
        $this->assertEquals('bar.txt', $file->getFilename());
        $this->assertEquals('/foo', $file->getPath());
        $this->assertEquals('/foo/bar.txt', $file->getPathname());
    }

    /**
     * Test creating file and handle removing
     */
    public function testDeleteFile()
    {
        $time = time();
        $file = new Simulation($time, 100, '/foo', 'bar.txt');

        $this->assertTrue($file->isWritable());

        $file->unlink();
        $this->assertTrue(true, 'no exception should be thrown');
    }
}
