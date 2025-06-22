<?php

namespace phpbu\App\Backup\Sync;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use phpbu\App\Backup\Target;
use phpbu\App\BaseMockery;
use phpbu\App\Result;
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
 * @link       https://phpbu.de/
 */
class YandexDiskTest extends TestCase
{
    use BaseMockery;

    /**
     * Tests YandexDisk::setup
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSetUpOk()
    {
        $yandexDisk = new YandexDisk();
        $yandexDisk->setup([
            'token' => 'this-is-no-token',
            'path' => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests YandexDisk::setup
     * @throws ReflectionException
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSlasherizePath()
    {
        $msg = "sync backup to yandex disk\n";

        $yandexDisk = new YandexDisk();
        $yandexDisk->setup([
            'token' => 'this-is-no-token',
            'path' => 'foo'
        ]);

        $resultStub = $this->createResultStub();
        $resultStub->expects($this->once())
            ->method('debug')
            ->with($this->equalTo($msg));

        $targetStub = $this->createTargetStub();

        $yandexDisk->simulate($targetStub, $resultStub);
    }

    /**
     * Tests YandexDisk::sync
     * @throws ReflectionException
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSync()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createResultStub();
        $result->expects($this->once())->method('debug');

        $metaMock = $this->prepareClosedStub();
        $diskMock = $this->prepareDiskStub($metaMock);
        $yandexDisk = $this->prepareYandexDiskStub($diskMock);

        $yandexDisk->setup([
            'token' => 'this-is-no-token',
            'path' => '/'
        ]);

        $yandexDisk->sync($target, $result);
    }

    /**
     * Tests YandexDisk::sync
     * @throws ReflectionException
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSyncWithCleanup()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createResultStub();
        $result->expects($this->exactly(2))->method('debug');

        $metaMock = $this->prepareClosedStub();
        $metaMock->method('toArray')->willReturn(['items' => [$metaMock]]);
        $diskMock = $this->prepareDiskStub($metaMock);
        $yandexDisk = $this->prepareYandexDiskStub($diskMock);

        $yandexDisk->setup([
            'token' => 'this-is-no-token',
            'path' => '/',
            'cleanup.type' => 'quantity',
            'cleanup.amount' => 99
        ]);

        $yandexDisk->sync($target, $result);
    }

    /**
     * Tests YandexDisk::sync
     * @throws ReflectionException
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSyncFail()
    {
        $this->expectException(\phpbu\App\Exception::class);
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createResultStub();

        $metaMock = $this->prepareClosedStub();
        $metaMock->method('upload')->will($this->throwException(new \Exception));
        $diskMock = $this->prepareDiskStub($metaMock);
        $yandexDisk = $this->prepareYandexDiskStub($diskMock);

        $yandexDisk->setup([
            'token' => 'this-is-no-token',
            'path' => '/'
        ]);

        $yandexDisk->sync($target, $result);
    }

    /**
     * Tests YandexDisk::simulate
     * @throws ReflectionException
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSimulate()
    {
        $yandexDisk = new YandexDisk();
        $yandexDisk->setup([
            'token' => 'this-is-no-token',
            'path' => '/'
        ]);

        $resultStub = $this->createResultStub();
        $resultStub->expects($this->once())
            ->method('debug');

        $targetStub = $this->createTargetStub();

        $yandexDisk->simulate($targetStub, $resultStub);
    }

    /**
     * Tests YandexDisk::setUp
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSetUpNoToken()
    {
        $this->expectException(Exception::class);
        $yandexDisk = new YandexDisk();
        $yandexDisk->setup(['path' => '/']);
    }

    /**
     * Tests YandexDisk::setUp
     * @throws Exception
     * @throws \phpbu\App\Exception
     */
    public function testSetUpNoPath()
    {
        $this->expectException(Exception::class);
        $yandexDisk = new YandexDisk();
        $yandexDisk->setup(['token' => 'this-is-no-token']);
    }

    /**
     * @return MockObject|Closed
     * @throws ReflectionException
     */
    private function prepareClosedStub()
    {
        $yandexDiskFileStub = $this->createMock(Closed::class);

        $path = 'backups/dump.tar.gz';
        $valueMap = [
            ['name', null, 'dump.tar.gz'],
            ['path', null, $path],
            ['size', null, 4242],
            ['modified', null, '2018-05-08 14:14:54.0 +00:00']
        ];
        $yandexDiskFileStub
            ->method('get')
            ->willReturnMap($valueMap);
        $yandexDiskFileStub
            ->method('getPath')
            ->willReturn($path);
        $yandexDiskFileStub->method('upload')->willReturn(true);

        return $yandexDiskFileStub;
    }

    /**
     * @param MockObject|Closed $closedStub
     * @return MockObject|Disk
     * @throws ReflectionException
     */
    private function prepareDiskStub($closedStub)
    {
        $diskMock = $this->createMock(Disk::class);
        $valueMap = [
            ['/', 20, 0, $closedStub],
            ['foo.txt.gz', 20, 0, $closedStub],
            ['//foo.txt.gz', 20, 0, $closedStub]
        ];
        $diskMock->method('getResource')->willReturnMap($valueMap);
        return $diskMock;
    }

    /**
     * @param $diskMock
     * @return MockObject|YandexDisk
     * @throws ReflectionException
     */
    private function prepareYandexDiskStub($diskMock)
    {
        $yandexDisk = $this->createPartialMock(YandexDisk::class, ['createDisk']);
        $yandexDisk->method('createDisk')->willReturn($diskMock);
        return $yandexDisk;
    }

    /**
     * @return MockObject|Result
     * @throws ReflectionException
     */
    private function createResultStub()
    {
        return $this->createMock(Result::class);
    }

    /**
     * @return MockObject|Target
     * @throws ReflectionException
     */
    private function createTargetStub()
    {
        return $this->createMock(Target::class);
    }
}
