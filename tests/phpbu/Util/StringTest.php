<?php
namespace phpbu\Util;

/**
 * String utility test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class StringTest extends \PHPUnit_Framework_TestCase
{
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
        $replaced = String::replaceDatePlaceholders($string);
        $this->assertEquals($expected, $replaced, sprintf('date placeholder %s should be replaced', $placeholder));
    }

    /**
     * Data provider date placeholder
     *
     * @return return array
     */
    public function providerDatePlaceholder()
    {
        return array(
            array('Y', date('Y')),
            array('y', date('y')),
            array('d', date('d')),
            array('m', date('m')),
            array('H', date('H')),
            array('i', date('i')),
            array('s', date('s')),
            array('w', date('w')),
            array('W', date('W')),
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
}
