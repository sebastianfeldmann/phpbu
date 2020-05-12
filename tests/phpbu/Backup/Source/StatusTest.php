<?php
namespace phpbu\App\Backup\Source;

use PHPUnit\Framework\TestCase;

/**
 * Status Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.0.1
 */
class StatusTest extends TestCase
{
    /**
     * Tests Status::create
     */
    public function testDefaults()
    {
        $status = Status::create();

        $this->assertTrue($status->handledCompression());
        $this->assertFalse($status->isDirectory());
    }

    /**
     * Tests Status::getDataPath
     */
    public function testNoPathForAlreadyCompressedSources()
    {
        $this->expectException('phpbu\App\Exception');
        $status = Status::create();
        $status->getDataPath();
    }

    /**
     * Tests Status::getDataPath
     */
    public function testUncompressedFile()
    {
        $status = Status::create()->uncompressedFile('/foo.dump');

        $this->assertFalse($status->handledCompression());
        $this->assertEquals('/foo.dump', $status->getDataPath());
    }

    /**
     * Tests Status::getDataPath
     */
    public function testUncompressedDirectory()
    {
        $status = Status::create()->uncompressedDirectory('/foo');

        $this->assertFalse($status->handledCompression());
        $this->assertEquals('/foo', $status->getDataPath());
        $this->assertTrue($status->isDirectory());
    }
}
