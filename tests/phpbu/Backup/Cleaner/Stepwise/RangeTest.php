<?php
namespace phpbu\App\Backup\Cleaner\Stepwise;

use PHPUnit\Framework\TestCase;

/**
 * Range test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class RangeTest extends TestCase
{
    /**
     * Tests Range::getStart
     */
    public function testGetStart()
    {
        $range = new Range(10, 5, new Keeper\All());
        $this->assertEquals(10, $range->getStart());
    }

    /**
     * Tests Range::getEnd
     */
    public function testGetEnd()
    {
        $range = new Range(10, 5, new Keeper\All());
        $this->assertEquals(5, $range->getEnd());
    }

    /**
     * Tests Range::keep
     */
    public function testKeep()
    {
        $fileMock = $this->createMock(\phpbu\App\Backup\File\Local::class);
        $range    = new Range(10, 5, new Keeper\All());
        $this->assertTrue($range->keep($fileMock));
    }
}
