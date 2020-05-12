<?php
namespace phpbu\App\Util;

use PHPUnit\Framework\TestCase;

/**
 * Array utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class ArrTest extends TestCase
{
    /**
     * Test isSetAndNotEmptyString
     */
    public function testIsSetAndNotEmptyString()
    {
        $arr = ['foo' => 'bar', 'baz' => '', 'fiz' => 0];

        $this->assertTrue(Arr::isSetAndNotEmptyString($arr, 'foo'), 'foo should be set and not the empty string');
        $this->assertFalse(Arr::isSetAndNotEmptyString($arr, 'baz'), 'baz should be set but the empty string');
        $this->assertTrue(Arr::isSetAndNotEmptyString($arr, 'fiz'), 'fiz should be set and not the empty string');
    }

    /**
     * Test getValue
     */
    public function testGetValue()
    {
        $arr = ['foo' => 'bar', 'baz' => '', 'fiz' => 0];

        $this->assertEquals('bar', Arr::getValue($arr, 'foo'), 'foo should be bar');
        $this->assertNull(Arr::getValue($arr, 'buz'), 'buz should be null');
        $this->assertEquals(0, Arr::getValue($arr, 'fiz'), 'fiz should be 0');
    }

    /**
     * Test getValue
     */
    public function testGetValueDefault()
    {
        $arr = ['foo' => 'bar', 'baz' => '', 'fiz' => 0];

        $this->assertEquals('bar', Arr::getValue($arr, 'foo', 'default'), 'foo should be bar');
        $this->assertEquals('default', Arr::getValue($arr, 'buz', 'default'), 'default should be step in');
        $this->assertEquals(0, Arr::getValue($arr, 'fiz', 'default'), 'fiz should be 0');
    }
}
