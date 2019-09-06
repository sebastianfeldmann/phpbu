<?php

namespace phpbu\App\Backup\Collector;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * YandexDisk Collector test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Alexander Palchikov AxelPAL <axelpal@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 */
class YandexDiskTest extends TestCase
{
    /**
     * Test YandexDisk collector.
     * @throws ReflectionException
     */
    public function testCollector()
    {
        $path = '/collector/static-dir/';
        $filename = 'foo-%Y-%m-%d-%H_%i.txt';
        $target = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));

        $remotePath = 'backups/';
        $yandexDiskStub = $this->prepareDiskStub();
        $yandexFileList = [
            [
                'name' => $target->getFilename(),
                'path' => $remotePath . $target->getFilename(),
                'size' => 100,
                'modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'name' => 'foo-2000-12-01-12_00.txt',
                'path' => $remotePath . 'foo-2000-12-01-12_00.txt',
                'size' => 100,
                'modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
            [
                'name' => 'not-matching-2000-12-01-12_00.txt',
                'path' => $remotePath . 'not-matching-2000-12-01-12_00.txt',
                'size' => 100,
                'modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
        ];

        $yandexFileList = array_map(
            function ($item) {
                return $this->createClosedStubByData($item);
            },
            $yandexFileList
        );

        $yandexDiskStub->method('toArray')->willReturn(['items' => $yandexFileList]);

        $yandexDiskStub
            ->method('getResource')
            ->with(Util\Path::withoutTrailingSlash($remotePath))
            ->willReturn($yandexDiskStub);

        $time = time();
        $pathObject = new Path($remotePath, $time);
        $collector  = new YandexDisk($target, $pathObject, $yandexDiskStub);
        $files      = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    /**
     * @param array $data
     * @return MockObject|Closed
     * @throws ReflectionException
     */
    private function createClosedStubByData(array $data)
    {
        $yandexDiskFileStub = $this->createMock(Closed::class);

        $valueMap = [
            ['name', null, $data['name']],
            ['path', null, $data['path']],
            ['size', null, $data['size']],
            ['modified', null, $data['modified']]
        ];
        $yandexDiskFileStub
            ->method('get')
            ->willReturnMap($valueMap);
        $yandexDiskFileStub
            ->method('getPath')
            ->willReturn($data['path']);

        return $yandexDiskFileStub;
    }

    /**
     * @return MockObject|Disk
     * @throws ReflectionException
     */
    private function prepareDiskStub()
    {
        return $this->createMock(Disk::class);
    }
}
