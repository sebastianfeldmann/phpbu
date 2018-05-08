<?php
namespace phpbu\App\Backup\File;

/**
 * DropboxFileTest
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
class DropboxFileTest extends \PHPUnit\Framework\TestCase
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

        $file = new \phpbu\App\Backup\File\Dropbox($dropboxClientStub, $dropboxFileMetadataStub);
        $this->assertAttributeEquals('dump.tar.gz', 'filename', $file);
        $this->assertAttributeEquals('backups/dump.tar.gz', 'pathname', $file);
        $this->assertAttributeEquals(102102, 'size', $file);
        $this->assertAttributeEquals(1525788894, 'lastModified', $file);

        $file->unlink();
    }
}
