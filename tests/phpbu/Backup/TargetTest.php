<?php
namespace phpbu\App\Backup;

/**
 * Target test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class TargetTest extends \PHPUnit_Framework_TestCase
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

        $this->assertTrue(is_dir($target->getPath()));

        rmdir($target->getPath());
    }

    /**
     * Tests Target::setupPath
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupPathNotWritable()
    {
        $filename = 'foo.txt';
        $path     = sys_get_temp_dir() . '/dirBar';
        mkdir($path, 0100);

        try {
            $target = new Target($path, $filename);
            $target->setupPath();
        } catch (\Exception $e) {
            chmod($target->getPath(), 0755);
            rmdir($target->getPath());
            throw $e;
        }
    }

    /**
     * Tests Target::setupPath
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupPathCantCreateDir()
    {
        $filename = 'foo.txt';
        $path     = sys_get_temp_dir() . '/dirFiz';
        mkdir($path, 0100);
        try {
            $target = new Target($path . '/dirBuz', $filename);
            $target->setupPath();
        } catch (\Exception $e) {
            chmod($path, 0755);
            rmdir($path);
            throw $e;
        }
    }

    /**
     * Tests Target::isCompressed
     */
    public function testNotCompressedWithoutCompressor()
    {
        $path     = '/tmp/%Y/%m';
        $filename = 'foo-%d.txt';
        $target   = new Target($path, $filename);

        $this->assertFalse($target->isCompressed());
    }

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
     * Tests Target::disableEncryption
     */
    public function testDisableEnableEncryption()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCrypter($this->getCrypterMock('nc'));
        $target->disableEncryption();

        $this->assertEquals('2014-test-01.txt', $target->getFilename());

        $target->enableEncryption();

        $this->assertEquals('2014-test-01.txt.nc', $target->getFilename());
    }

    /**
     * Tests Target::enableEncryption
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testEnableEncryptionFail()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->enableEncryption();

        $this->assertFalse(true, 'exception should be thrown');
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

        $crypter = $target->getCrypter($mock);

        $this->assertEquals($mock, $crypter);
    }

    /**
     * Tests Target::disableEncryption
     */
    public function testEnableCompressionAndEncryption()
    {
        $path     = '/tmp/foo/bar';
        $filename = '%Y-test-%d.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-01 04:30:57'));
        $target->setCompressor($this->getCompressorMockForCmd('zip', 'zip', 'application/zip'));
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

        $this->assertEquals('/tmp/12/01', $target->getPath());
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

        $target->setCompressor($this->getCompressorMockForCmd('zip', 'zip', 'application/zip'));

        $this->assertEquals('/tmp/12/01/foo.txt', $target->getPathnamePlain());
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
     * Tests Target::unlink
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testUnlinkNotExists()
    {
        $path     = sys_get_temp_dir() . '/foo';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);
        $target->unlink();
    }

    /**
     * Tests Target::unlink
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testUnlinkNotWritable()
    {
        $path     = sys_get_temp_dir() . '/foo';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);

        // create the file
        mkdir($target->getPath(), 0755);
        file_put_contents($target->getPathname(), 'content');
        chmod($target->getPathname(), 0444);

        try {
            $target->unlink();
        } catch (\Exception $e) {
            chmod($target->getPathname(), 0644);
            unlink($target->getPathname());
            rmdir($target->getPath());
            throw $e;
        }
    }

    /**
     * Tests Target::unlink
     */
    public function testUnlinkSuccess()
    {
        $path     = sys_get_temp_dir() . '/foo';
        $filename = 'foo.txt';
        $target   = new Target($path, $filename);
        $target->setupPath();

        // create the file
        file_put_contents($target->getPathname(), 'content');

        $target->unlink();
        rmdir($target->getPath());

        $this->assertFalse(file_exists($target->getPathname()));
        $this->assertFalse(is_dir($target->getPath()));
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

    /**
     * Create Compressor Mock.
     *
     * @param  string $suffix
     * @return \phpbu\App\Backup\Crypter
     */
    protected function getCrypterMock($suffix)
    {
        $crypterStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Crypter')
                            ->disableOriginalConstructor()
                            ->getMock();
        $crypterStub->method('getSuffix')->willReturn($suffix);

        return $crypterStub;
    }
}
