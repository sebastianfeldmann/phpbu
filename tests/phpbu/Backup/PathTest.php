<?php
namespace phpbu\App\Backup;

use PHPUnit\Framework\TestCase;

/**
 * Path test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class PathTest extends TestCase
{
    /**
     * Tests Path::getPath
     */
    public function testGetPath()
    {
        $path = new Path('/foo', strtotime('2018-06-13 14:00:00'));
        $this->assertEquals('/foo', $path->getPath());
    }

    /**
     * Tests Path::getPathDepth
     */
    public function testGetStaticPathDepth()
    {
        $path1 = new Path('/foo', strtotime('2018-06-13 14:00:00'));
        $path2 = new Path('/foo/bar/baz', strtotime('2018-06-13 14:00:00'));
        $path3 = new Path('foo/bar/baz', strtotime('2018-06-13 14:00:00'));

        $this->assertEquals(2, $path1->getPathDepth());
        $this->assertEquals(2, $path1->getPathThatIsNotChangingDepth());
        $this->assertEquals(4, $path2->getPathDepth());
        $this->assertEquals(4, $path2->getPathThatIsNotChangingDepth());
        $this->assertEquals(3, $path3->getPathDepth());
        $this->assertEquals(3, $path3->getPathThatIsNotChangingDepth());
    }

    /**
     * Tests Path::getPathDepth
     */
    public function testGetDynamicPathDepth()
    {
        $path1 = new Path('/foo/%m', strtotime('2018-06-13 14:00:00'));
        $path2 = new Path('/foo/%Y/baz', strtotime('2018-06-13 14:00:00'));
        $path3 = new Path('foo/%Y/%m', strtotime('2018-06-13 14:00:00'));

        $this->assertEquals(3, $path1->getPathDepth());
        $this->assertEquals(2, $path1->getPathThatIsNotChangingDepth());
        $this->assertEquals(4, $path2->getPathDepth());
        $this->assertEquals(2, $path2->getPathThatIsNotChangingDepth());
        $this->assertEquals(3, $path3->getPathDepth());
        $this->assertEquals(1, $path3->getPathThatIsNotChangingDepth());
    }

    /**
     * Tests Path::getPathDepth
     */
    public function testPlaceholderReplacement()
    {
        $path1 = new Path('/foo/%m', strtotime('2018-06-13 14:00:00'));
        $path2 = new Path('/foo/%Y/baz', strtotime('2018-06-13 14:00:00'));
        $path3 = new Path('foo/%Y/%m/%H', strtotime('2018-06-13 14:00:00'));

        $this->assertEquals('/foo/06', $path1->getPath());
        $this->assertEquals('/foo/2018/baz', $path2->getPath());
        $this->assertEquals('foo/2018/06/14', $path3->getPath());
    }

    /**
     * Tests Path::getPathElementAtIndex
     */
    public function testGetPathElementAtIndex()
    {
        $path1 = new Path('/foo/bar/baz/fiz', strtotime('2018-06-13 14:00:00'));
        $path2 = new Path('foo/bar/baz/fiz', strtotime('2018-06-13 14:00:00'));

        $this->assertEquals('/', $path1->getPathElementAtIndex(0));
        $this->assertEquals('foo', $path2->getPathElementAtIndex(0));


    }
}
