<?php

namespace phpbu\App\Backup\File;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use InvalidArgumentException;
use phpbu\App\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * YandexDiskTest
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
     * Test creating file and handle removing
     * @throws ReflectionException
     */
    public function testCreateFileWithCorrectProperties()
    {
        $file = new YandexDisk($this->prepareDiskStub(), $this->prepareClosedStub());
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('backups/dump.tar.gz', $file->getPathname());
        $this->assertEquals(4242, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());
    }

    /**
     * Tests YandexDisk::unlink
     * @throws ReflectionException
     * @throws Exception
     */
    public function testYandexDiskDeleteFile()
    {
        $yandexDiskFileStub = $this->prepareClosedStub();
        $yandexDiskFileStub->expects($this->once())
            ->method('delete')
            ->with('backups/dump.tar.gz');

        $yandexDiskStub = $this->prepareDiskStub();
        $yandexDiskStub->expects($this->once())
            ->method('getResource')
            ->with('backups/dump.tar.gz')
            ->willReturn($yandexDiskFileStub);

        $file = new YandexDisk($yandexDiskStub, $yandexDiskFileStub);
        $file->unlink();
    }

    /**
     * Tests YandexDisk::unlink
     * @throws ReflectionException
     * @throws Exception
     */
    public function testYandexDiskDeleteFailure()
    {
        $this->expectException(Exception::class);

        $yandexDiskFileStub = $this->prepareClosedStub();
        $yandexDiskFileStub->expects($this->once())
            ->method('delete')
            ->with('backups/dump.tar.gz')
            ->willThrowException(new InvalidArgumentException);

        $yandexDiskStub = $this->prepareDiskStub();
        $yandexDiskStub->expects($this->once())
            ->method('getResource')
            ->with('backups/dump.tar.gz')
            ->willReturn($yandexDiskFileStub);

        $file = new YandexDisk($yandexDiskStub, $yandexDiskFileStub);
        $file->unlink();
    }

    /**
     * @return MockObject|Closed
     * @throws ReflectionException
     */
    private function prepareClosedStub()
    {
        $yandexDiskFileStub = $this->createMock(Closed::class);

        $valueMap = [
            ['name', null, 'dump.tar.gz'],
            ['path', null, 'backups/dump.tar.gz'],
            ['size', null, 4242],
            ['modified', null, '2018-05-08 14:14:54.0 +00:00']
        ];
        $yandexDiskFileStub
            ->method('get')
            ->willReturnMap($valueMap);

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
