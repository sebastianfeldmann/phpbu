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
}
