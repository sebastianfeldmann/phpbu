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
class PathTest extends TestCase
{
    /**
     * @var integer
     */
    static protected $time;

    /**
     * Tests Path::isContainingPlaceholders
     */
    public function testIsContainingPlaceholders()
    {
        $string = 'my.name-%Y%m%d.suf';
        $bool   = Path::isContainingPlaceholder($string);
        $this->assertTrue($bool, 'should contain placeholder');

        $string = 'my.name.suf';
        $bool   = Path::isContainingPlaceholder($string);
        $this->assertFalse($bool, 'should not contain placeholder');
    }

    /**
     * Tests multiple date replacements in one string.
     */
    public function testReplaceMultipleDatePlaceholder()
    {
        $string   = 'my.name-%Y%m%d.suf';
        $replaced = Path::replaceDatePlaceholders($string);
        $expected = 'my.name-' . date('Y') . date('m') . date('d') . '.suf';

        $this->assertEquals($expected, $replaced, 'all date placeholder should be replaced');
    }

    /**
     * Tests Path::datePlaceholderToRegex
     */
    public function testDatePlaceholderToRegex()
    {
        $this->assertEquals(
            '[0-9]{4}',
            Path::datePlaceholdersToRegex('%Y')
        );
        $this->assertEquals(
            'backups_[0-9]{4}_[0-9]{2}',
            Path::datePlaceholdersToRegex('backups_%Y_%m')
        );
        $this->assertEquals(
            'backups_[0-9]{4}_[0-9]{2}_[0-9a-z]+',
            Path::datePlaceholdersToRegex('backups_%Y_%m_%T')
        );
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
        $replaced = Path::replaceDatePlaceholders($string, $time);
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
     * Tests Path::replaceTargetPlaceholders
     */
    public function testReplaceTargetPlaceholder()
    {
        $target   = '/foo/bar.txt';
        $replaced = Path::replaceTargetPlaceholders('1-%TARGET_DIR% 2-%TARGET_FILE%', $target);
        $expected = '1-/foo 2-/foo/bar.txt';

        $this->assertEquals($expected, $replaced, 'all target placeholder should be replaced');
    }

    /**
     * Test has trailing slash.
     */
    public function testHasTrailingSlash()
    {
        $this->assertFalse(Path::hasTrailingSlash('foo'));
        $this->assertFalse(Path::hasTrailingSlash('/foo/bar'));
        $this->assertTrue(Path::hasTrailingSlash('baz/'));
    }

    /**
     * Test with trailing slash.
     */
    public function testWithTrailingSlash()
    {
        $this->assertEquals('foo/', Path::withTrailingSlash('foo'), 'should be foo/');
        $this->assertEquals('foo/bar/', Path::withTrailingSlash('foo/bar'), 'should be foo/bar/');
        $this->assertEquals('baz/', Path::withTrailingSlash('baz/'), 'should be baz/');
    }

    /**
     * Test without trailing slash.
     */
    public function testWithoutTrailingSlash()
    {
        $this->assertEquals('foo', Path::withoutTrailingSlash('foo/'), 'should be foo');
        $this->assertEquals('foo/bar', Path::withoutTrailingSlash('foo/bar/'), 'should be foo/bar');
        $this->assertEquals('baz', Path::withoutTrailingSlash('baz'), 'should be baz');
        $this->assertEquals('/', Path::withoutTrailingSlash('/'), '/ should be stay /');
    }

    /**
     * Test has leading slash.
     */
    public function testHasLeadingSlash()
    {
        $this->assertFalse(Path::hasLeadingSlash('foo'));
        $this->assertFalse(Path::hasLeadingSlash('foo/bar/'));
        $this->assertTrue(Path::hasLeadingSlash('/baz'));
    }

    /**
     * Test with trailing slash.
     */
    public function testWithLeadingSlash()
    {
        $this->assertEquals('/foo', Path::withLeadingSlash('foo'), 'should be /foo');
        $this->assertEquals('/foo/bar', Path::withLeadingSlash('foo/bar'), 'should be /foo/bar');
        $this->assertEquals('/baz', Path::withLeadingSlash('/baz'), 'should be baz/');
    }

    /**
     * Test without trailing slash.
     */
    public function testWithoutLeadingSlash()
    {
        $this->assertEquals('foo', Path::withoutLeadingSlash('/foo'), 'should be foo');
        $this->assertEquals('foo/bar', Path::withoutLeadingSlash('/foo/bar'), 'should be foo/bar');
        $this->assertEquals('baz', Path::withoutLeadingSlash('baz'), 'should be baz');
        $this->assertEquals('', Path::withoutLeadingSlash('/'), 'slash should be removed');
    }

    /**
     * Tests Path::isAbsolutePath
     */
    public function testIsAbsolutePathTrue()
    {
        $path = '/foo/bar';
        $res  = Path::isAbsolutePath($path);

        $this->assertTrue($res, 'should be detected as absolute path');
    }

    /**
     * Tests Path::isAbsolutePath
     */
    public function testIsAbsolutePathFalse()
    {
        $path = '../foo/bar';
        $res  = Path::isAbsolutePath($path);

        $this->assertFalse($res, 'should not be detected as absolute path');
    }

    /**
     * Tests Path::isAbsolutePath
     */
    public function testIsAbsolutePathStream()
    {
        $path = 'php://foo/bar';
        $res  = Path::isAbsolutePath($path);

        $this->assertTrue($res, 'should not be detected as absolute path');
    }

    /**
     * Tests Path::isAbsolutePathWindows
     *
     * @dataProvider providerWindowsPaths
     *
     * @param string  $path
     * @param boolean $expected
     */
    public function testIsAbsolutePathWindows($path, $expected)
    {
        $res = Path::isAbsoluteWindowsPath($path);

        $this->assertEquals($expected, $res, 'should be detected as expected');
    }

    /**
     * Tests Path::toAbsolutePath
     */
    public function testToAbsolutePathAlreadyAbsolute()
    {
        $res = Path::toAbsolutePath('/foo/bar', '');

        $this->assertEquals('/foo/bar', $res, 'should be detected as absolute');
    }


    /**
     * Tests Path::withoutLeadingOrTrailingSlash
     */
    public function testWithoutLeadingOrTrailingSlash()
    {
        $this->assertEquals('foo/bar', Path::withoutLeadingOrTrailingSlash('/foo/bar/'));
        $this->assertEquals('foo/bar', Path::withoutLeadingOrTrailingSlash('foo/bar/'));
        $this->assertEquals('foo/bar', Path::withoutLeadingOrTrailingSlash('/foo/bar'));
        $this->assertEquals('foo/bar', Path::withoutLeadingOrTrailingSlash('foo/bar'));
    }

    /**
     * Data provider testIsAbsolutePathWindows.
     *
     * @return array
     */
    public function providerWindowsPaths()
    {
        return [
            ['C:\foo', true],
            ['\\foo\\bar', true],
            ['..\\foo', false],
        ];
    }

    /**
     * Tests Path::toAbsolutePath
     */
    public function testToAbsolutePathWIthIncludePath()
    {
        $filesDir = PHPBU_TEST_FILES . '/conf/xml';
        set_include_path(get_include_path() . PATH_SEPARATOR . $filesDir);
        $res = Path::toAbsolutePath('config-valid.xml', '', true);

        $this->assertEquals($filesDir . '/config-valid.xml', $res);
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
