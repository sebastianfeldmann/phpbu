<?php
namespace phpbu\App\Backup;

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
     * Test detecting date placeholder in path.
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
     * Test recognizing that there are no date placeholder in path.
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
     * Test detecting date placeholder in filename.
     */
    public function testHasChangingFilename()
    {
        $path     = '/tmp/foo/bar';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertTrue($target->hasChangingFilename(), 'filename should be recognized as changing');
    }

    /**
     * Test recognizing that there are no date placeholder in filename.
     */
    public function testHasNoChangingFilename()
    {
        $path     = '/tmp/foo/bar';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);

        $this->assertFalse($target->hasChangingFilename(), 'filename should be recognized as not changing');
    }

    /**
     * Test date placeholder replacement in filename.
     */
    public function testGetFilename()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('2014-test-01.txt', $target->getFilename());
    }

    /**
     * Tests Target::getChangingPathElements
     */
    public function testGetChangingPathElements()
    {
        $path     = '/tmp/foo/%Y/%m';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals(array('%Y', '%m'), $target->getChangingPathElements());
    }

    /**
     * Tests Target::getCompressor
     */
    public function testGetCompressor()
    {
        $compressor = $this->getCompressorMockForCmd('zip', 'zip', 'application/zip');

        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompressor($compressor);

        $this->assertEquals($compressor, $target->getCompressor());
    }

    /**
     * Tests Target::getMimeType
     */
    public function testGetMimeTypeDefault()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('text/plain', $target->getMimeType());
    }

    /**
     * Tests Target::getMimeType
     */
    public function testSetMimeType()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setMimeType('application/x-tar');

        $this->assertEquals('application/x-tar', $target->getMimeType());
    }

    /**
     * Tests Target::getMimeType
     */
    public function testGetMimeTypeCompressed()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompressor($this->getCompressorMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('application/zip', $target->getMimeType());
    }

    /**
     * Tests Target::getFilename
     */
    public function testGetFilenameCompressed()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompressor($this->getCompressorMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('2014-test-01.txt.zip', $target->getFilename());
    }

    /**
     * Tests Target::getFilename
     */
    public function testGetFilenamePlain()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompressor($this->getCompressorMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('2014-test-01.txt', $target->getFilenamePlain());
    }

    /**
     * Tests Target::disableCompression
     */
    public function testDisableEnableCompression()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompressor($this->getCompressorMockForCmd('zip', 'zip', 'application/zip'));
        $target->disableCompression();

        $this->assertEquals('2014-test-01.txt', $target->getFilename());

        $target->enableCompression();

        $this->assertEquals('2014-test-01.txt.zip', $target->getFilename());
    }

    /**
     * Tests Target::enableCompression
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testEnableCompressionFail()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->enableCompression();

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Test date placeholder replacement in path.
     */
    public function testGetPath()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('/tmp/12/01', $target->getPath());
    }

    /**
     * Tests Target::setPermission
     */
    public function testSetPermissionEmpty()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setPermissions('');

        $this->assertEquals(0700, $target->getPermissions());
    }

    /**
     * Tests Target::setPermission
     */
    public function testSetPermissionOk()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setPermissions('0644');

        $this->assertEquals(0644, $target->getPermissions());
    }

    /**
     * Tests Target::setPermission
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetPermissionFail()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setPermissions('0999');

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Tests Target::getPathRaw
     */
    public function testGetPathRaw()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('/tmp/%m/%d', $target->getPathRaw());
    }

    /**
     * Tests Target::fileExists
     */
    public function testFileExists()
    {
        $path     = dirname(__FILE__);
        $filename = basename(__FILE__);
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals(true, $target->fileExists());
    }

    /**
     * Tests Target::getSize
     */
    public function testGetSizeOk()
    {
        $path     = dirname(__FILE__);
        $filename = basename(__FILE__);
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals(filesize(__FILE__), $target->getSize());
    }

    /**
     * Tests Target::getSize
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testGetSizeFail()
    {
        $path     = '.';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->getSize();

        $this->assertFalse(true, 'exception should be thrown');
    }

    /**
     * Create Compressor Mock.
     *
     * @param  string $cmd
     * @param  string $suffix
     * @param  string $mimeType
     * @return \phpbu\App\Backup\Compressor
     */
    protected function getCompressorMockForCmd($cmd, $suffix, $mimeType)
    {
        $compressorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Compressor')
                               ->disableOriginalConstructor()
                               ->getMock();
        $compressorStub->method('getCommand')->willReturn($cmd);
        $compressorStub->method('getSuffix')->willReturn($suffix);
        $compressorStub->method('getMimeType')->willReturn($mimeType);

        return $compressorStub;
    }
}
