<?php
namespace phpbu\Util;

/**
 * Math utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class MathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerPercentValues
     */
    public function testGetDiffInPercent($whole, $part, $expected)
    {
        $diff = Math::getDiffInPercent($whole, $part);
        $this->assertEquals($expected, $diff, sprintf('diff in percent (%d,%d) should be %d', $whole, $part, $expected));
    }

    /**
     * Data provider date placeholder
     *
     * @return return array
     */
    public function providerPercentValues()
    {
        return array(
            array(100, 90, 10),
            array(100, 80, 20),
            array(100, 50, 50),
        );
    }
}
