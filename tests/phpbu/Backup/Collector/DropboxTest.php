<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

/**
 * Dropbox Collector test
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
class DropboxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test Dropbox collector
     */
    public function testCollector()
    {
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));

        $dropboxClientStub = $this->createMock(\Kunnu\Dropbox\Dropbox::class);
        $remotePath        = 'backups/';
        $dropboxFileList   = [
            [
                'name' => $target->getFilename(),
                'pathname' => $remotePath . $target->getFilename(),
                'size' => 100,
                'last_modified' => 1525788894,
            ],
            [
                'name' => 'foo-2000-12-01-12_00.txt',
                'pathname' => $remotePath . 'foo-2000-12-01-12_00.txt',
                'size' => 100,
                'last_modified' => 1525788894,
            ],
            [
                'name' => 'not-matching-2000-12-01-12_00.txt',
                'pathname' => $remotePath . 'not-matching-2000-12-01-12_00.txt',
                'size' => 100,
                'last_modified' => 1525788894,
            ],
        ];

        $dropboxFileList = array_map(
            function ($item) {
                return $this->createDropboxFileStub($item);
            },
            $dropboxFileList
        );

        // add a folder as well
        $dropboxFileList[] = $this->createMock(\Kunnu\Dropbox\Models\FolderMetadata::class);

        $dropboxFileListResult = $this->createMock(\Kunnu\Dropbox\Models\MetadataCollection::class);
        $dropboxFileListResult->method('getItems')->willReturn($dropboxFileList);

        $dropboxClientStub->expects($this->once())
                          ->method('listFolder')
                          ->with('backups/', ['limit' => 100, 'recursive' => true])
                          ->willReturn($dropboxFileListResult);

        $time = time();
        $pathObject = new Path($remotePath, $time, false, true);
        $collector = new Dropbox($target, $dropboxClientStub, $remotePath, $time);
        $this->assertAttributeEquals($dropboxClientStub, 'client', $collector);
        $this->assertAttributeEquals($pathObject, 'path', $collector);
        $this->assertAttributeEquals($target, 'target', $collector);
        $this->assertAttributeEquals(Util\Path::datePlaceholdersToRegex($target->getFilenameRaw()), 'fileRegex', $collector);
        $this->assertAttributeEquals([], 'files', $collector);

        $files = $collector->getBackupFiles();
        $this->assertCount(1, $files);
        $this->assertEquals('foo-2000-12-01-12_00.txt', $files[0]->getFilename());
    }

    /**
     * Creates Dropbox file metadata class mock
     *
     * @param  array $data
     * @return \Kunnu\Dropbox\Models\FileMetadata
     */
    private function createDropboxFileStub(array $data)
    {
        $dropboxFileMetadataStub = $this->createMock(\Kunnu\Dropbox\Models\FileMetadata::class);
        $dropboxFileMetadataStub->method('getName')->willReturn($data['name']);
        $dropboxFileMetadataStub->method('getPathDisplay')->willReturn($data['pathname']);
        $dropboxFileMetadataStub->method('getSize')->willReturn($data['size']);
        $dropboxFileMetadataStub->method('getClientModified')->willReturn($data['last_modified']);
        return $dropboxFileMetadataStub;
    }
}
