<?php

namespace phpbu\App\Backup\Sync;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\StorageObject;
use phpbu\App\Backup\Target;
use phpbu\App\BaseMockery;
use phpbu\App\Result;
use PHPUnit\Framework\TestCase;

/**
 * Google Drive file test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     David Dattée <david.dattee@meetwashing.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 */
class GoogleCloudStorageTest extends TestCase
{
    use BaseMockery;

    /**
     * Tests GoogleCloudStorage::setUp
     */
    public function testSetUpOk()
    {
        $sync = new GoogleCloudStorage();
        $sync->setup([
            'secret' => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'bucket' => 'fake_bucket',
            'path'   => 'test/path',
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests GoogleCloudStorage::simulate
     */
    public function testSimulate()
    {
        $sync = new GoogleCloudStorage();
        $sync->setup([
            'secret' => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'bucket' => 'fake_bucket',
            'path'   => 'test/path',
        ]);

        $resultStub = $this->createMock(Result::class);
        $resultStub->expects($this->once())->method('debug');

        $targetStub = $this->createMock(Target::class);

        $sync->simulate($targetStub, $resultStub);
    }

    /**
     * Tests GoogleCloudStorage::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock(PHPBU_TEST_FILES . '/misc/backup.txt');
        $result = $this->createMock(Result::class);
        $result->expects($this->once())->method('debug');

        $client        = $this->createPartialMock(StorageClient::class, ['bucket']);
        $bucket        = $this->createMock(Bucket::class);
        $requestObject = $this->createMock(StorageObject::class);

        $bucket
            ->expects($this->once())
            ->method('upload')
            ->with(
                $this->isType('resource'),
                [
                    'name'     => 'test/path/backup.txt',
                    'metadata' => [
                        'crc32c' => 'P+XTKQ==',
                    ],
                ],
            )
            ->willReturn($requestObject);

        $client
            ->expects($this->once())
            ->method('bucket')
            ->willReturn($bucket);

        $sync = $this->createPartialMock(GoogleCloudStorage::class, ['createGoogleCloudClient']);
        $sync
            ->expects($this->once())
            ->method('createGoogleCloudClient')
            ->willReturn($client);

        $sync->setup([
            'secret' => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'bucket' => 'fake_bucket',
            'path'   => 'test/path',
        ]);
        $sync->sync($target, $result);
    }

    /**
     * Tests GoogleCloudStorage::sync
     */
    public function testSyncWithCleanup()
    {
        $target = $this->createTargetMock(PHPBU_TEST_FILES . '/misc/backup.txt');
        $result = $this->createMock(Result::class);
        $result->expects($this->exactly(2))->method('debug');

        $client        = $this->createPartialMock(StorageClient::class, ['bucket']);
        $bucket        = $this->createMock(Bucket::class);
        $requestObject = $this->createMock(StorageObject::class);
        $collector     = $this->createMock(\phpbu\App\Backup\Collector\GoogleCloudStorage::class);

        $bucket
            ->expects($this->once())
            ->method('upload')
            ->with(
                $this->isType('resource'),
                [
                    'name'     => 'test/path/backup.txt',
                    'metadata' => [
                        'crc32c' => 'P+XTKQ==',
                    ],
                ],
            )
            ->willReturn($requestObject);

        $client
            ->expects($this->once())
            ->method('bucket')
            ->willReturn($bucket);

        $sync = $this->createPartialMock(GoogleCloudStorage::class, ['createGoogleCloudClient', 'createCollector']);
        $sync
            ->expects($this->once())
            ->method('createGoogleCloudClient')
            ->willReturn($client);
        $sync
            ->expects($this->exactly(1))
            ->method('createCollector')
            ->willReturn($collector);

        $sync->setup([
            'secret'         => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'bucket'         => 'fake_bucket',
            'path'           => 'test/path',
            'cleanup.type'   => 'quantity',
            'cleanup.amount' => 99,
        ]);
        $sync->sync($target, $result);
    }

    /**
     * Tests GoogleCloudStorage::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');

        $target = $this->createTargetMock(PHPBU_TEST_FILES . '/misc/backup.txt');
        $result = $this->createMock(Result::class);

        $sync = $this->createPartialMock(GoogleCloudStorage::class, ['createGoogleCloudClient']);
        $sync
            ->expects($this->once())
            ->method('createGoogleCloudClient')
            ->willThrowException(new \Exception());

        $sync->setup([
            'secret' => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'bucket' => 'fake_bucket',
            'path'   => 'test/path',
        ]);
        $sync->sync($target, $result);
    }

    /**
     * Tests GoogleCloudStorage::setUp
     */
    public function testSetUpNoSecret()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');

        $sync = $this->createPartialMock(GoogleCloudStorage::class, []);
        $sync->setup(['secret' => '']);
    }

    /**
     * Tests GoogleCloudStorage::setUp
     */
    public function testSetUpNoSecretFile()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sync = $this->createPartialMock(GoogleCloudStorage::class, []);
        $sync->setup(['secret' => 'foo']);
    }
}
