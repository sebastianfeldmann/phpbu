<?php
namespace phpbu\App\Backup\File;

use PHPUnit\Framework\TestCase;

/**
 * DropboxTest
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
class DropboxTest extends TestCase
{
    /**
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $dropboxFileMetadataStub = $this->createMock(\Kunnu\Dropbox\Models\FileMetadata::class);
        $dropboxFileMetadataStub->method('getName')->willReturn('dump.tar.gz');
        $dropboxFileMetadataStub->method('getPathDisplay')->willReturn('backups/dump.tar.gz');
        $dropboxFileMetadataStub->method('getSize')->willReturn(102102);
        $dropboxFileMetadataStub->method('getClientModified')->willReturn('2018-05-08 14:14:54.0 +00:00');

        $dropboxClientStub = $this->createMock(\Kunnu\Dropbox\Dropbox::class);
        $dropboxClientStub->expects($this->once())
                          ->method('delete')
                          ->with('backups/dump.tar.gz');

        $file = new Dropbox($dropboxClientStub, $dropboxFileMetadataStub);
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('backups/dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());

        $file->unlink();
    }

    /**
     * Tests Dropbox::unlink
     */
    public function testDropboxDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $dropboxFileMetadataStub = $this->createMock(\Kunnu\Dropbox\Models\FileMetadata::class);
        $dropboxFileMetadataStub->method('getName')->willReturn('dump.tar.gz');
        $dropboxFileMetadataStub->method('getPathDisplay')->willReturn('backups/dump.tar.gz');
        $dropboxFileMetadataStub->method('getSize')->willReturn(102102);
        $dropboxFileMetadataStub->method('getClientModified')->willReturn('2018-05-08 14:14:54.0 +00:00');

        $dropboxClientStub = $this->createMock(\Kunnu\Dropbox\Dropbox::class);
        $dropboxClientStub->expects($this->once())
                          ->method('delete')
                          ->with('backups/dump.tar.gz')
                          ->will($this->throwException(new \Exception));

        $file = new Dropbox($dropboxClientStub, $dropboxFileMetadataStub);
        $file->unlink();
    }
}
