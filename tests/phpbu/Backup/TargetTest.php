<?php
namespace phpbu\App\Backup;

use Exception;
use phpbu\App\Backup\Target\Compression;
use PHPUnit\Framework\TestCase;

/**
 * Target test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class TargetTest extends TestCase
{
    /**
     * Tests Target::setupPath
     */
    public function testSetupPath()
    {
        $path     = sys_get_temp_dir() . '/dirFoo';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);
        $target->setupPath();

        $this->assertDirectoryExists($target->getPath()->getPath());

        rmdir($target->getPath()->getPath());
    }

    /**
     * Tests Target::setupPath
     */
    public function testToFile()
    {
        $path     = sys_get_temp_dir() . '/dirFoo';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);

        $file = $target->toFile();

        $this->assertEquals($path . '/' . $filename, $file->getPathname());
    }

    /**
     * Tests Target::setupPath
     */
    public function testSetupPathNotWritable()
    {
        $this->expectException('phpbu\App\Exception');
        $filename = 'foo.txt';
        $path     = sys_get_temp_dir() . '/dirBar';
        mkdir($path, 0100);

        try {
            $target = new Target($path, $filename);
            $target->setupPath();
        } catch (Exception $e) {
            if (isset($target)) {
                chmod($target->getPath()->getPath(), 0755);
                rmdir($target->getPath()->getPath());
            }

            throw $e;
        }
    }

    /**
     * Tests Target::setupPath
     */
    public function testSetupPathCantCreateDir()
    {
        $this->expectException('phpbu\App\Exception');
        $filename = 'foo.txt';
        $path     = sys_get_temp_dir() . '/dirFiz';
        mkdir($path, 0100);
        try {
            $target = new Target($path . '/dirBuz', $filename);
            $target->setupPath();
        } catch (Exception $e) {
            chmod($path, 0755);
            rmdir($path);
            throw $e;
        }
    }

    /**
     * Test detecting date placeholder in path.
     */
    public function testHasChangingPath()
    {
        $path     = '/tmp/%Y/%m';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertTrue($target->getPath()->hasChangingPath(), 'path should be recognized as changing');
    }

    /**
     * Test detecting date placeholder in path.
     */
    public function testHasChangingAndFixedMixedPath()
    {
        $path     = '/tmp/%Y/foo/%m';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertTrue($target->getPath()->hasChangingPath(), 'path should be recognized as changing');
    }

    /**
     * Test recognizing that there are no date placeholder in path.
     */
    public function testHasNoChangingPath()
    {
        $path     = '/tmp/foo/bar';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertFalse($target->getPath()->hasChangingPath(), 'path should be recognized as not changing');
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
     * Test date placeholder replacement in filename.
     */
    public function testGetFilenameAfterAppendingSuffix()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->appendFileSuffix('tar');

        $this->assertEquals('2014-test-01.txt.tar', $target->getFilename());
    }

    /**
     * Test date placeholder replacement in filename.
     */
    public function testGetFilenameAfterRemovingSuffix()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->appendFileSuffix('tar');
        $target->appendFileSuffix('enc');
        $target->removeFileSuffix('enc');
        $target->removeFileSuffix('tar');

        $this->assertEquals('2014-test-01.txt', $target->getFilename());
    }

    /**
     * Tests Target::getCompression
     */
    public function testGetCompression()
    {
        $compression = $this->getCompressionMockForCmd('zip', 'zip', 'application/zip');

        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompression($compression);

        $this->assertEquals($compression, $target->getCompression());
    }

    /**
     * Tests Target::disableCompressor
     */
    public function testDisableCompressor()
    {
        $compression = $this->getCompressionMockForCmd('zip', 'zip', 'application/zip');

        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompression($compression);

        $this->assertTrue($target->shouldBeCompressed());

        $target->disableCompression();

        $this->assertFalse($target->shouldBeCompressed());
    }

    /**
     * Tests Target::enableCompressor
     */
    public function testEnableCompressor()
    {
        $compression = $this->getCompressionMockForCmd('zip', 'zip', 'application/zip');

        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompression($compression);

        $this->assertTrue($target->shouldBeCompressed());

        $target->disableCompression();

        $this->assertFalse($target->shouldBeCompressed());

        $target->enableCompression();

        $this->assertTrue($target->shouldBeCompressed());
    }

    /**
     * Tests Target::enableCompressor
     */
    public function testEnableCompressorWithoutCompressor()
    {
        $this->expectException('phpbu\App\Exception');
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $target->enableCompression();
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
        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip', 'application/zip'));

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
        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('2014-test-01.txt.zip', $target->getFilename());
    }

    /**
     * Tests Target::getFilename
     */
    public function testGetFilenameWithAppendedSuffixCompressed()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->appendFileSuffix('tar');
        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('2014-test-01.txt.tar.zip', $target->getFilename());
    }

    /**
     * Tests Target::getFilename
     */
    public function testGetFilenamePlain()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('2014-test-01.txt', $target->getFilenamePlain());
    }

    /**
     * Tests Target::getCrypter
     */
    public function testGetCrypter()
    {
        $mock     = $this->getCrypterMock('nc');
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $target->setCrypter($mock);

        $crypter = $target->getCrypter();

        $this->assertEquals($mock, $crypter);
    }

    /**
     * Tests Target::getCrypter
     */
    public function testDisableCrypter()
    {
        $mock     = $this->getCrypterMock('nc');
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $target->setCrypter($mock);

        $this->assertTrue($target->shouldBeEncrypted());

        $target->disableEncryption();

        $this->assertFalse($target->shouldBeEncrypted());
    }

    /**
     * Tests Target::disableEncryption
     */
    public function testEnableCompressionAndEncryption()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip', 'application/zip'));
        $target->setCrypter($this->getCrypterMock('nc'));

        $this->assertEquals('2014-test-01.txt.zip.nc', $target->getFilename());
    }

    /**
     * Test date placeholder replacement in path.
     */
    public function testGetPath()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('/tmp/12/01', $target->getPath()->getPath());
    }

    /**
     * Tests Target::getPathnamePlain
     */
    public function testGetPathnamePlain()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('/tmp/12/01/foo.txt', $target->getPathnamePlain());

        $target->setCompression($this->getCompressionMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('/tmp/12/01/foo.txt', $target->getPathnamePlain());
    }

    /**
     * Tests Target::getPathRaw
     */
    public function testGetPathRaw()
    {
        $path     = '/tmp/%m/%d';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertEquals('/tmp/%m/%d', $target->getPath()->getPathRaw());
    }

    /**
     * Tests Target::fileExists
     */
    public function testFileExists()
    {
        $path     = dirname(__FILE__);
        $filename = basename(__FILE__);
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));

        $this->assertTrue($target->fileExists());
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
     * Tests Target::setSize
     */
    public function testSetSize()
    {
        $path     = dirname(__FILE__);
        $filename = basename(__FILE__);
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setSize(10000);

        $this->assertEquals(10000, $target->getSize());
    }

    /**
     * Tests Target::getSize
     */
    public function testGetSizeFail()
    {
        $this->expectException('phpbu\App\Exception');
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
     * @return \phpbu\App\Backup\Target\Compression
     */
    protected function getCompressionMockForCmd($cmd, $suffix, $mimeType)
    {
        $compressionStub = $this->createMock(Compression::class);
        $compressionStub->method('getCommand')->willReturn($cmd);
        $compressionStub->method('getSuffix')->willReturn($suffix);
        $compressionStub->method('getMimeType')->willReturn($mimeType);

        return $compressionStub;
    }

    /**
     * Create Compressor Mock.
     *
     * @param  string $suffix
     * @return \phpbu\App\Backup\Crypter
     */
    protected function getCrypterMock($suffix)
    {
        $crypterStub = $this->createMock(Crypter::class);
        $crypterStub->method('getSuffix')->willReturn($suffix);

        return $crypterStub;
    }
}
