<?php
namespace phpbu\App\Util;

use PHPUnit\Framework\TestCase;

/**
 * String utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class StrTest extends TestCase
{
    /**
     * @var integer
     */
    static protected $time;

    /**
     * Test toBoolean with matching values.
     */
    public function testToBooleanMatch()
    {
        $this->assertTrue(Str::toBoolean('true', false));
        $this->assertTrue(Str::toBoolean(true, false));
        $this->assertTrue(Str::toBoolean('tRuE', false));
        $this->assertTrue(Str::toBoolean('TRUE', false));
        $this->assertFalse(Str::toBoolean(false, true));
        $this->assertFalse(Str::toBoolean('false', true));
        $this->assertFalse(Str::toBoolean('fAlSe', true));
        $this->assertFalse(Str::toBoolean('FALSE', true));
    }

    /**
     * Test toBoolean with non matching values to check the default.
     */
    public function testToBooleanDefault()
    {
        $this->assertTrue(Str::toBoolean('FOO', true));
        $this->assertFalse(Str::toBoolean('BAR', false));
        $this->assertTrue(Str::toBoolean('', true));
        $this->assertTrue(Str::toBoolean(null, true));
    }

    /**
     * Test byte values
     */
    public function testToByteUpperCase()
    {
        $this->assertEquals(500, Str::toBytes('500B'), '500B should match 500 bytes');
        $this->assertEquals(1024, Str::toBytes('1K'), '1K should match 1.024 bytes');
        $this->assertEquals(1048576, Str::toBytes('1M'), '1M should match 1.048.576 bytes');
        $this->assertEquals(2097152, Str::toBytes('2M'), '2M should match 2.097.152 bytes');
        $this->assertEquals(1099511627776, Str::toBytes('1T'), '1T should match 1.099.511.627.776 bytes');
    }

    /**
     * Test byte values lower case
     */
    public function testToByteLowerCase()
    {
        $this->assertEquals(1024, Str::toBytes('1k'), '1k should match 1.024 bytes');
        $this->assertEquals(1048576, Str::toBytes('1m'), '1m should match 1.048.576 bytes');
        $this->assertEquals(2097152, Str::toBytes('2m'), '2m should match 2.097.152 bytes');
        $this->assertEquals(1099511627776, Str::toBytes('1t'), '1t should match 1.099.511.627.776 bytes');
    }

    /**
     * Test byte values
     */
    public function testToTimeUpperCase()
    {
        $this->assertEquals(3600, Str::toTime('60I'), '60I should match 3600 seconds');
        $this->assertEquals(604800, Str::toTime('1W'), '1W should match 604.800 seconds');
        $this->assertEquals(2678400, Str::toTime('1M'), '1M should match 2.678.400 seconds');
        $this->assertEquals(31536000, Str::toTime('1Y'), '1Y should match 31.536.000 seconds');
        $this->assertEquals(172800, Str::toTime('2D'), '2D should match 172.800 seconds');
    }

    /**
     * Test byte values lower case
     */
    public function testToTimeLowerCase()
    {
        $this->assertEquals(3600, Str::toTime('60i'), '60I should match 3600 seconds');
        $this->assertEquals(604800, Str::toTime('1w'), '1W should match 604.800 seconds');
        $this->assertEquals(2678400, Str::toTime('1m'), '1M should match 2.678.400 seconds');
        $this->assertEquals(31536000, Str::toTime('1y'), '1Y should match 31.536.000 seconds');
        $this->assertEquals(172800, Str::toTime('2d'), '2D should match 172.800 seconds');
    }

    /**
     * Tests Str::padAll
     */
    public function testPadAll()
    {
        $padded = Str::padAll(['foo' => 'bar', 'fiz' => 'baz'], 8);
        $this->assertEquals(8, strlen($padded['foo']), 'bar should be padded to a length of 8');
        $this->assertEquals(8, strlen($padded['fiz']), 'baz should be padded to a length of 8');

        $this->assertEquals('     baz', $padded['fiz'], 'baz should be padded with spaces');
        $this->assertEquals('     bar', $padded['foo'], 'bar should be padded with spaces');
    }

    /**
     * Tests Str::toList
     */
    public function testToListFull()
    {
        $list = Str::toList('foo,bar');
        $this->assertCount(2, $list, 'list should contain 2 elements');
    }

    /**
     * Tests Str::toList
     */
    public function testToListEmpty()
    {
        $list = Str::toList('');
        $this->assertCount(0, $list, 'list should be empty');
    }

    /**
     * Tests Str::toList
     */
    public function testToListEmptyTrim()
    {
        $list = Str::toList('foo , bar , baz  ');
        $this->assertCount(3, $list, 'list should be empty');
        $this->assertEquals('foo', $list[0], 'should not contain spaces');
        $this->assertEquals('bar', $list[1], 'should not contain spaces');
        $this->assertEquals('baz', $list[2], 'should noz contain spaces');
    }

    /**
     * Tests Str::toList
     */
    public function testToListEmptyNoTrim()
    {
        $list = Str::toList('foo  , bar ,  baz', ',', false);
        $this->assertCount(3, $list, 'list should be empty');
        $this->assertEquals('foo  ', $list[0], 'should still contain spaces');
        $this->assertEquals(' bar ', $list[1], 'should still contain spaces');
        $this->assertEquals('  baz', $list[2], 'should still contain spaces');
    }

    /**
     * Tests Str::appendPluralS
     */
    public function testAppendPluralS()
    {
        $a = Str::appendPluralS('backup', 2);
        $b = Str::appendPluralS('backup', 0);
        $c = Str::appendPluralS('backup', 1);
        $d = Str::appendPluralS('class', 2);

        $this->assertEquals('backups', $a);
        $this->assertEquals('backups', $b);
        $this->assertEquals('backup', $c);
        $this->assertEquals('class\'s', $d);
    }
}
