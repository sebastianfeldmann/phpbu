<?php
namespace phpbu\App\Adapter;

use PHPUnit\Framework\TestCase;

/**
 * Adapter Util test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.0.7
 */
class UtilTest extends TestCase
{
    /**
     * Tests Util::getAdapterReplacements
     */
    public function testGetAdapterValuesSingle()
    {
        $values = Util::getAdapterReplacements('adapter:foo:bar');

        $this->assertCount(1, $values);
        $this->assertEquals('adapter:foo:bar', $values[0]['search']);
        $this->assertEquals('foo', $values[0]['adapter']);
        $this->assertEquals('bar', $values[0]['path']);
    }

    /**
     * Tests Util::getAdapterReplacements
     */
    public function testGetAdapterValuesSinglePrefixed()
    {
        $values = Util::getAdapterReplacements(':AdaPteR:foo:bar');

        $this->assertCount(1, $values);
        $this->assertEquals(':AdaPteR:foo:bar', $values[0]['search']);
        $this->assertEquals('foo', $values[0]['adapter']);
        $this->assertEquals('bar', $values[0]['path']);
    }

    /**
     * Tests Util::getAdapterReplacements
     */
    public function testGetAdapterValuesSinglePostfixed()
    {
        $values = Util::getAdapterReplacements('adapter:foo:bar:');

        $this->assertCount(1, $values);
        $this->assertEquals('adapter:foo:bar:', $values[0]['search']);
        $this->assertEquals('foo', $values[0]['adapter']);
        $this->assertEquals('bar', $values[0]['path']);
    }

    /**
     * Tests Util::getAdapterReplacements
     */
    public function testGetAdapterValuesSinglePreAndPostfixed()
    {
        $values = Util::getAdapterReplacements(':adapter:foo:bar:');

        $this->assertCount(1, $values);
        $this->assertEquals(':adapter:foo:bar:', $values[0]['search']);
        $this->assertEquals('foo', $values[0]['adapter']);
        $this->assertEquals('bar', $values[0]['path']);
    }

    /**
     * Tests Util::getAdapterReplacements
     */
    public function testGetAdapterValuesMultiple()
    {
        $values = Util::getAdapterReplacements('adapter:foo:bar:/some/path/:adapter:fiz:baz');

        $this->assertCount(2, $values);
        $this->assertEquals('adapter:foo:bar:', $values[0]['search']);
        $this->assertEquals('foo', $values[0]['adapter']);
        $this->assertEquals('bar', $values[0]['path']);
        $this->assertEquals(':adapter:fiz:baz', $values[1]['search']);
        $this->assertEquals('fiz', $values[1]['adapter']);
        $this->assertEquals('baz', $values[1]['path']);
    }


    /**
     * Tests Util::getAdapterReplacements
     */
    public function testGetAdapterValuesMultipleInBetween()
    {
        $values = Util::getAdapterReplacements('/some/:adapter:foo:bar:/path/:adapter:fiz:baz:/end/');

        $this->assertCount(2, $values);
        $this->assertEquals(':adapter:foo:bar:', $values[0]['search']);
        $this->assertEquals('foo', $values[0]['adapter']);
        $this->assertEquals('bar', $values[0]['path']);
        $this->assertEquals(':adapter:fiz:baz:', $values[1]['search']);
        $this->assertEquals('fiz', $values[1]['adapter']);
        $this->assertEquals('baz', $values[1]['path']);
    }
}
