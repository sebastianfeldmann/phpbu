<?php
namespace phpbu\Util;

/**
 * String utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
    static protected $time;

    /**
     * Test multiple date replacements in one string.
     */
    public function testReplaceMultipleDatePlaceholder()
    {
        $string   = 'my.name-%Y%m%d.suf';
        $replaced = String::replaceDatePlaceholders($string);
        $expected = 'my.name-' . date('Y') . date('m') . date('d') . '.suf';

        $this->assertEquals($expected, $replaced, 'all date placeholder should be replaced');
    }

    /**
     * @dataProvider providerDatePlaceholder
     */
    public function testReplaceDatePlaceholder($placeholder, $expected)
    {
        $string   = 'my-%' . $placeholder . '.zip';
        $expected = 'my-' . $expected . '.zip';
        $time     = self::getTime();
        $replaced = String::replaceDatePlaceholders($string, $time);
        $this->assertEquals($expected, $replaced, sprintf('date placeholder %s should be replaced', $placeholder));
    }

    /**
     * Data provider date placeholder
     *
     * @return return array
     */
    public function providerDatePlaceholder()
    {
        $time = self::getTime();
        return array(
            array('Y', date('Y', $time)),
            array('y', date('y', $time)),
            array('d', date('d', $time)),
            array('m', date('m', $time)),
            array('H', date('H', $time)),
            array('i', date('i', $time)),
            array('s', date('s', $time)),
            array('w', date('w', $time)),
            array('W', date('W', $time)),
        );
    }

    /**
     * Test toBoolean with matching values.
     */
    public function testToBooleanMatch()
    {
        $this->assertTrue(String::toBoolean('true', false));
        $this->assertTrue(String::toBoolean('tRuE', false));
        $this->assertTrue(String::toBoolean('TRUE', false));
        $this->assertFalse(String::toBoolean('false', true));
        $this->assertFalse(String::toBoolean('fAlSe', true));
        $this->assertFalse(String::toBoolean('FALSE', true));
    }

    /**
     * Test toBoolean with non matching values to check the default.
     */
    public function testToBooleanDefault()
    {
        $this->assertTrue(String::toBoolean('FOO', true));
        $this->assertFalse(String::toBoolean('BAR', false));
        $this->assertTrue(String::toBoolean('', true));
        $this->assertTrue(String::toBoolean(null, true));
    }

    /**
     * Test byte values
     */
    public function testToByteUpperCase()
    {
        $this->assertEquals(500, String::toBytes('500B'), '500B should match 500 bytes');
        $this->assertEquals(1024, String::toBytes('1K'), '1K should match 1.024 bytes');
        $this->assertEquals(1048576, String::toBytes('1M'), '1M should match 1.048.576 bytes');
        $this->assertEquals(2097152, String::toBytes('2M'), '2M should match 2.097.152 bytes');
        $this->assertEquals(1099511627776, String::toBytes('1T'), '1T should match 1.099.511.627.776 bytes');
    }

    /**
     * Test byte values lower case
     */
    public function testToByteLowerCase()
    {
        $this->assertEquals(1024, String::toBytes('1k'), '1k should match 1.024 bytes');
        $this->assertEquals(1048576, String::toBytes('1m'), '1m should match 1.048.576 bytes');
        $this->assertEquals(2097152, String::toBytes('2m'), '2m should match 2.097.152 bytes');
        $this->assertEquals(1099511627776, String::toBytes('1t'), '1t should match 1.099.511.627.776 bytes');
    }

    /**
     * Test byte values
     */
    public function testToTimeUpperCase()
    {
        $this->assertEquals(3600, String::toTime('60I'), '60I should match 3600 seconds');
        $this->assertEquals(604800, String::toTime('1W'), '1W should match 604.800 seconds');
        $this->assertEquals(2678400, String::toTime('1M'), '1M should match 2.678.400 seconds');
        $this->assertEquals(31536000, String::toTime('1Y'), '1Y should match 31.536.000 seconds');
        $this->assertEquals(172800, String::toTime('2D'), '2D should match 172.800 seconds');
    }

    /**
     * Test byte values lower case
     */
    public function testToTimeLowerCase()
    {
        $this->assertEquals(3600, String::toTime('60i'), '60I should match 3600 seconds');
        $this->assertEquals(604800, String::toTime('1w'), '1W should match 604.800 seconds');
        $this->assertEquals(2678400, String::toTime('1m'), '1M should match 2.678.400 seconds');
        $this->assertEquals(31536000, String::toTime('1y'), '1Y should match 31.536.000 seconds');
        $this->assertEquals(172800, String::toTime('2d'), '2D should match 172.800 seconds');
    }

    /**
     * Tests String::toList
     */
    public function testToListFull()
    {
        $list = String::toList('foo,bar');        
        $this->assertEquals(2, count($list), 'list should contain 2 elements');
    }

    /**
     * Tests String::toList
     */
    public function testToListEmpty()
    {
        $list = String::toList('');
        $this->assertEquals(0, count($list), 'list should be empty');
    }

    /**
     * Tests String::toList
     */
    public function testToListEmptyTrim()
    {
        $list = String::toList('foo , bar , baz  ');
        $this->assertEquals(3, count($list), 'list should be empty');
        $this->assertEquals('foo', $list[0], 'should not contain spaces');
        $this->assertEquals('bar', $list[1], 'should not contain spaces');
        $this->assertEquals('baz', $list[2], 'should noz contain spaces');
    }

    /**
     * Tests String::toList
     */
    public function testToListEmptyNoTrim()
    {
        $list = String::toList('foo  , bar ,  baz', ',', false);
        $this->assertEquals(3, count($list), 'list should be empty');
        $this->assertEquals('foo  ', $list[0], 'should still contain spaces');
        $this->assertEquals(' bar ', $list[1], 'should still contain spaces');
        $this->assertEquals('  baz', $list[2], 'should still contain spaces');
    }

    /**
     * Test with trailing slash.
     */
    public function testWithTrailingSlash()
    {
        $this->assertEquals('foo/', String::withTrailingSlash('foo'), 'should be foo/');
        $this->assertEquals('foo/bar/', String::withTrailingSlash('foo/bar'), 'should be foo/bar/');
        $this->assertEquals('baz/', String::withTrailingSlash('baz/'), 'should be baz/');
    }

    /**
     * Test without trailing slash.
     */
    public function testWithoutTrailingSlash()
    {
        $this->assertEquals('foo', String::withoutTrailingSlash('foo/'), 'should be foo');
        $this->assertEquals('foo/bar', String::withoutTrailingSlash('foo/bar/'), 'should be foo/bar');
        $this->assertEquals('baz', String::withoutTrailingSlash('baz'), 'should be baz');
        $this->assertEquals('/', String::withoutTrailingSlash('/'), '/ should be stay /');
    }

    /**
     * Return local test time.
     * Not changing in one test run.
     *
     * @return integer
     */
    protected function getTime()
    {
        if (!self::$time) {
            self::$time = time();
        }
        return self::$time;
    }
}
