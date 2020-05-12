<?php
namespace phpbu\App\Util;

use PHPUnit\Framework\TestCase;

/**
 * Math utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class MathTest extends TestCase
{
    /**
     * @dataProvider providerPercentValues
     *
     * @param integer $whole
     * @param integer $part
     * @param integer $expected
     */
    public function testGetDiffInPercent($whole, $part, $expected)
    {
        $diff = Math::getDiffInPercent($whole, $part);
        $this->assertEquals(
            $expected,
            $diff,
            sprintf('diff in percent (%d,%d) should be %d', $whole, $part, $expected)
        );
    }

    /**
     * Data provider date testGetDiffInPercent.
     *
     * @return return array
     */
    public function providerPercentValues()
    {
        return [
            [100, 90, 10],
            [100, 80, 20],
            [100, 50, 50],
            [80, 100, 20],
            [60, 100, 40],
        ];
    }
}
