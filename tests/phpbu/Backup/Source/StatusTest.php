<?php
namespace phpbu\App\Backup\Source;

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
class StatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Status::create
     */
    public function testDefaults()
    {
        $status = Status::create();

        $this->assertTrue($status->handledCompression());
    }

    /**
     * Tests Status::getDataPath
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoPathForAlreadyCompressedSources()
    {
        $status = Status::create();
        $status->getDataPath();
    }

    /**
     * Tests Status::getDataPath
     */
    public function testUncompressedDataPath()
    {
        $status = Status::create()->uncompressed()->dataPath('/foo');

        $this->assertFalse($status->handledCompression());
        $this->assertEquals('/foo', $status->getDataPath());
    }
}
