<?php
namespace phpbu\Backup;

/**
 * Target test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class TargetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test detecting date placeholder in path
     */
    public function testHasChangingPath()
    {
        $path     = '/tmp/%Y/%m';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertTrue($target->hasChangingPath(), 'path should be recognized as changing');
        $this->assertEquals(2, $target->countChangingPathElements(), '2 changing path elements should be found');
    }

    /**
     * Test recognizing that there are no date placeholder in path
     */
    public function testHasNoChangingPath()
    {
        $path     = '/tmp/foo/bar';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertFalse($target->hasChangingPath(), 'path should be recognized as not changing');
        $this->assertEquals(0, $target->countChangingPathElements(), 'no changing path elements should be found');
    }

    /**
     * Test detecting date placeholder in filename
     */
    public function testHasChangingFilename()
    {
        $path     = '/tmp/foo/bar';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertTrue($target->hasChangingFilename(), 'filename should be recognized as changing');
    }

    /**
     * Test recognizing that there are no date placeholder in filename
     */
    public function testHasNoChangingFilename()
    {
        $path     = '/tmp/foo/bar';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);

        $this->assertFalse($target->hasChangingFilename(), 'filename should be recognized as not changing');
    }

    /**
     * Test date placeholder replacement in filename
     */
    public function testGetFilename()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('2014-test-01.txt', $target->getFilename(), 'filename should as expected');
    }

    /**
     * Test date placeholder replacement in path
     */
    public function testGetPath()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('/tmp/12/01', $target->getPath(), 'path should as expected');
    }
}
