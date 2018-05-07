<?php
namespace phpbu\App\Util;

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
class StrTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var integer
     */
    static protected $time;

    /**
     * Tests Str::isContainingPlaceholders
     */
    public function testIsContainingPlaceholders()
    {
        $string = 'my.name-%Y%m%d.suf';
        $bool   = Str::isContainingPlaceholder($string);
        $this->assertTrue($bool, 'should contain placeholder');

        $string = 'my.name.suf';
        $bool   = Str::isContainingPlaceholder($string);
        $this->assertFalse($bool, 'should not contain placeholder');
    }

    /**
     * Tests multiple date replacements in one string.
     */
    public function testReplaceMultipleDatePlaceholder()
    {
        $string   = 'my.name-%Y%m%d.suf';
        $replaced = Str::replaceDatePlaceholders($string);
        $expected = 'my.name-' . date('Y') . date('m') . date('d') . '.suf';

        $this->assertEquals($expected, $replaced, 'all date placeholder should be replaced');
    }

    /**
     * @dataProvider providerDatePlaceholder
     * @param        $placeholder
     * @param        $expected
     */
    public function testReplaceDatePlaceholder($placeholder, $expected)
    {
        $string   = 'my-%' . $placeholder . '.zip';
        $expected = 'my-' . $expected . '.zip';
        $time     = self::getTime();
        $replaced = Str::replaceDatePlaceholders($string, $time);
        $this->assertEquals($expected, $replaced, sprintf('date placeholder %s should be replaced', $placeholder));
    }

    /**
     * Data provider date placeholder
     *
     * @return array
     */
    public function providerDatePlaceholder()
    {
        $time = self::getTime();
        return [
            ['Y', date('Y', $time)],
            ['y', date('y', $time)],
            ['d', date('d', $time)],
            ['m', date('m', $time)],
            ['H', date('H', $time)],
            ['i', date('i', $time)],
            ['s', date('s', $time)],
            ['w', date('w', $time)],
            ['W', date('W', $time)],
        ];
    }

    /**
     * Tests Str::replaceTargetPlaceholders
     */
    public function testReplaceTargetPlaceholder()
    {
        $target   = '/foo/bar.txt';
        $replaced = Str::replaceTargetPlaceholders('1-%TARGET_DIR% 2-%TARGET_FILE%', $target);
        $expected = '1-/foo 2-/foo/bar.txt';

        $this->assertEquals($expected, $replaced, 'all target placeholder should be replaced');
    }

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
        $this->assertEquals(2, count($list), 'list should contain 2 elements');
    }

    /**
     * Tests Str::toList
     */
    public function testToListEmpty()
    {
        $list = Str::toList('');
        $this->assertEquals(0, count($list), 'list should be empty');
    }

    /**
     * Tests Str::toList
     */
    public function testToListEmptyTrim()
    {
        $list = Str::toList('foo , bar , baz  ');
        $this->assertEquals(3, count($list), 'list should be empty');
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
        $this->assertEquals(3, count($list), 'list should be empty');
        $this->assertEquals('foo  ', $list[0], 'should still contain spaces');
        $this->assertEquals(' bar ', $list[1], 'should still contain spaces');
        $this->assertEquals('  baz', $list[2], 'should still contain spaces');
    }

    /**
     * Test has trailing slash.
     */
    public function testHasTrailingSlash()
    {
        $this->assertEquals(false, Str::hasTrailingSlash('foo'));
        $this->assertEquals(false, Str::hasTrailingSlash('/foo/bar'));
        $this->assertEquals(true, Str::hasTrailingSlash('baz/'));
    }

    /**
     * Test with trailing slash.
     */
    public function testWithTrailingSlash()
    {
        $this->assertEquals('foo/', Str::withTrailingSlash('foo'), 'should be foo/');
        $this->assertEquals('foo/bar/', Str::withTrailingSlash('foo/bar'), 'should be foo/bar/');
        $this->assertEquals('baz/', Str::withTrailingSlash('baz/'), 'should be baz/');
    }

    /**
     * Test without trailing slash.
     */
    public function testWithoutTrailingSlash()
    {
        $this->assertEquals('foo', Str::withoutTrailingSlash('foo/'), 'should be foo');
        $this->assertEquals('foo/bar', Str::withoutTrailingSlash('foo/bar/'), 'should be foo/bar');
        $this->assertEquals('baz', Str::withoutTrailingSlash('baz'), 'should be baz');
        $this->assertEquals('/', Str::withoutTrailingSlash('/'), '/ should be stay /');
    }

    /**
     * Test has leading slash.
     */
    public function testHasLeadingSlash()
    {
        $this->assertEquals(false, Str::hasLeadingSlash('foo'));
        $this->assertEquals(false, Str::hasLeadingSlash('foo/bar/'));
        $this->assertEquals(true,  Str::hasLeadingSlash('/baz'));
    }

    /**
     * Test with trailing slash.
     */
    public function testWithLeadingSlash()
    {
        $this->assertEquals('/foo', Str::withLeadingSlash('foo'), 'should be /foo');
        $this->assertEquals('/foo/bar', Str::withLeadingSlash('foo/bar'), 'should be /foo/bar');
        $this->assertEquals('/baz', Str::withLeadingSlash('/baz'), 'should be baz/');
    }

    /**
     * Test without trailing slash.
     */
    public function testWithoutLeadingSlash()
    {
        $this->assertEquals('foo', Str::withoutLeadingSlash('/foo'), 'should be foo');
        $this->assertEquals('foo/bar', Str::withoutLeadingSlash('/foo/bar'), 'should be foo/bar');
        $this->assertEquals('baz', Str::withoutLeadingSlash('baz'), 'should be baz');
        $this->assertEquals('', Str::withoutLeadingSlash('/'), 'slash should be removed');
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
