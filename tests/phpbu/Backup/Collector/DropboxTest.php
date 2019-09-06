<?php
namespace phpbu\App\Backup\Collector;

use Kunnu\Dropbox\Models\FileMetadata;
use Kunnu\Dropbox\Models\FolderMetadata;
use Kunnu\Dropbox\Models\MetadataCollection;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\TestCase;

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
class DropboxTest extends TestCase
{
    /**
     * Test Dropbox collector.
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
                'name'          => $target->getFilename(),
                'pathname'      => $remotePath . $target->getFilename(),
                'size'          => 100,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'name'          => 'foo-2000-12-01-12_00.txt',
                'pathname'      => $remotePath . 'foo-2000-12-01-12_00.txt',
                'size'          => 100,
                'last_modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
            [
                'name'          => 'not-matching-2000-12-01-12_00.txt',
                'pathname'      => $remotePath . 'not-matching-2000-12-01-12_00.txt',
                'size'          => 100,
                'last_modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
        ];

        $dropboxFileList = array_map(
            function ($item) {
                return $this->createDropboxFileStub($item);
            },
            $dropboxFileList
        );

        // add a folder as well
        $dropboxFileList[] = $this->createMock(FolderMetadata::class);

        $dropboxFileListResult = $this->createMock(MetadataCollection::class);
        $dropboxFileListResult->method('getItems')->willReturn($dropboxFileList);

        $dropboxClientStub->expects($this->once())
                          ->method('listFolder')
                          ->with('backups/', ['recursive' => true])
                          ->willReturn($dropboxFileListResult);

        $time = time();
        $pathObject = new Path($remotePath, $time);
        $collector  = new Dropbox($target, $pathObject, $dropboxClientStub);

        $files = $collector->getBackupFiles();
        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    /**
     * Creates Dropbox file metadata class mock.
     *
     * @param  array $data
     * @return \Kunnu\Dropbox\Models\FileMetadata
     */
    private function createDropboxFileStub(array $data)
    {
        $dropboxFileMetadataStub = $this->createMock(FileMetadata::class);
        $dropboxFileMetadataStub->method('getName')->willReturn($data['name']);
        $dropboxFileMetadataStub->method('getPathDisplay')->willReturn($data['pathname']);
        $dropboxFileMetadataStub->method('getSize')->willReturn($data['size']);
        $dropboxFileMetadataStub->method('getClientModified')->willReturn($data['last_modified']);
        return $dropboxFileMetadataStub;
    }
}
