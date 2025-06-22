<?php

namespace phpbu\App\Backup\Collector;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use PHPUnit\Framework\TestCase;

/**
 * Google Drive collector test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     David Dattée <david.dattee@meetwashing.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 */
class GoogleCloudStorageTest extends TestCase
{
    /**
     * Tests GoogleDrive::getBackupFiles
     */
    public function testCollector()
    {
        $path     = '/collector/static-dir/';
        $filename = 'foo-%Y-%m-%d-%H_%i.txt';
        $target   = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $bucket   = $this->createMock(Bucket::class);
        $path     = $this->createMock(Path::class);

        $remoteFileList = [
            [
                'name'          => $target->getFilename(),
                'size'          => 100,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'name'          => 'foo-2000-12-01-12_00.txt',
                'size'          => 200,
                'last_modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
            [
                'name'          => 'not-matching-2000-12-01-12_00.txt',
                'size'          => 300,
                'last_modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
        ];

        $remoteFileList = array_map(
            function ($item) {
                return $this->createGoogleStorageObjectFileStub($item);
            },
            $remoteFileList,
        );

        $bucket
            ->expects($this->once())
            ->method('objects')
            ->willReturn($remoteFileList);

        $collector = new GoogleCloudStorage($target, $bucket, $path);
        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-1']->getFilename(),
        );
    }

    /**
     * Creates Google Storage Object file class mock.
     *
     * @param array $data
     * @return StorageObject
     */
    private function createGoogleStorageObjectFileStub(array $data): StorageObject
    {
        $object = $this->createMock(StorageObject::class);
        $object->method('name')->willReturn($data['name']);
        $object->method('info')->willReturn([
            'size'    => $data['size'],
            'updated' => $data['last_modified'],
        ]);

        return $object;
    }
}
