<?php
namespace phpbu\App\Backup\Sync;

use AzureOss\Storage\Blob\BlobContainerClient;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Collector\AzureBlob as AzureBlobCollector;
use phpbu\App\Backup\Target;
use phpbu\App\BaseMockery;
use phpbu\App\Result;
use PHPUnit\Framework\TestCase;

/**
 * Test double exposing controllable seams around the (final) Azure SDK client
 * so the real sync orchestration runs without performing any network calls.
 */
class TestableAzureBlobSync extends AzureBlob
{
    /** @var bool */
    public $containerExists = false;

    /** @var bool */
    public $containerCreated = false;

    /** @var bool */
    public $uploaded = false;

    /** @var string|null */
    public $uploadedPath = null;

    /** @var bool */
    public $uploadThrows = false;

    /** @var \phpbu\App\Backup\Collector|null */
    public $collectorDouble = null;

    public function exposedCreateClient(): BlobContainerClient
    {
        return $this->createClient();
    }

    protected function doesContainerExist(BlobContainerClient $client): bool
    {
        return $this->containerExists;
    }

    protected function createContainer(BlobContainerClient $client)
    {
        $this->containerCreated = true;
    }

    /**
     * Narrow seam: the real upload() body (file handle + getUploadPath) runs;
     * only the final SDK call is replaced, capturing the computed blob path.
     */
    protected function uploadBlob(BlobContainerClient $client, string $path, $source)
    {
        if ($this->uploadThrows) {
            throw new \Exception('upload failed');
        }
        $this->uploaded     = true;
        $this->uploadedPath = $path;
    }

    protected function getFileHandle($path, $mode)
    {
        return 'filehandle';
    }

    protected function createCollector(Target $target): Collector
    {
        return $this->collectorDouble ?: parent::createCollector($target);
    }
}

/**
 * AzureBlobTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.2.7
 */
class AzureBlobTest extends TestCase
{
    use BaseMockery;

    /**
     * A realistic, well-formed connection string the SDK can parse offline.
     */
    const CONNECTION_STRING = 'DefaultEndpointsProtocol=https;AccountName=accountname;AccountKey=accountkey;' .
                              'EndpointSuffix=core.windows.net';

    /**
     * Tests AzureBlob::setUp
     */
    public function testSetUpOk()
    {
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name',
            'path'              => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests AzureBlob::getUploadPath
     */
    public function testGetUploadPath()
    {
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name',
            'path'              => '/'
        ]);

        $targetStub = $this->createMock(Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('foo.zip', $azureBlob->getUploadPath($targetStub));
    }

    /**
     * Tests AzureBlob::getUploadPath
     */
    public function testGetUploadPathAddingMissingSlashes()
    {
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name',
            'path'              => 'fiz'
        ]);

        $targetStub = $this->createMock(Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('fiz/foo.zip', $azureBlob->getUploadPath($targetStub));
    }

    /**
     * Tests AzureBlob::createClient builds a container client from the connection string
     */
    public function testCreateClientBuildsContainerClient()
    {
        $azureBlob = new TestableAzureBlobSync();
        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $client = $azureBlob->exposedCreateClient();

        $this->assertInstanceOf(BlobContainerClient::class, $client);
        $this->assertEquals('dummy-container-name', $client->containerName);
    }

    /**
     * Tests AzureBlob::sync
     */
    public function testSync()
    {
        $target   = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $messages = [];
        $result   = $this->createDebugCollectingResult($messages);

        $azureBlob = new TestableAzureBlobSync();
        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $azureBlob->sync($target, $result);

        $this->assertTrue($azureBlob->containerCreated, 'missing container should be created');
        $this->assertTrue($azureBlob->uploaded, 'backup should be uploaded');
        $this->assertStringStartsWith('backup/', (string) $azureBlob->uploadedPath, 'upload path must include the configured remote path');
        $this->assertContains('create blob container', $messages);
        $this->assertContains('upload: done', $messages);
    }

    /**
     * Tests AzureBlob::sync does not create an already existing container
     */
    public function testSyncContainerAlreadyExists()
    {
        $target   = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $messages = [];
        $result   = $this->createDebugCollectingResult($messages);

        $azureBlob = new TestableAzureBlobSync();
        $azureBlob->containerExists = true;
        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $azureBlob->sync($target, $result);

        $this->assertFalse($azureBlob->containerCreated, 'existing container must not be re-created');
        $this->assertTrue($azureBlob->uploaded, 'backup should be uploaded');
        $this->assertNotContains('create blob container', $messages, 'must not log container creation when it exists');
        $this->assertContains('upload: done', $messages);
    }

    /**
     * Tests AzureBlob::sync with remote cleanup
     */
    public function testSyncWithRemoteCleanup()
    {
        $target   = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $messages = [];
        $result   = $this->createDebugCollectingResult($messages);

        $collector = $this->createMock(AzureBlobCollector::class);
        $collector->method('getBackupFiles')->willReturn([]);

        $azureBlob = new TestableAzureBlobSync();
        $azureBlob->collectorDouble = $collector;
        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup',
            'cleanup.type'      => 'quantity',
            'cleanup.amount'    => 99
        ]);

        $azureBlob->sync($target, $result);

        $this->assertTrue($azureBlob->uploaded, 'backup should be uploaded');
        $this->assertContains('upload: done', $messages);
    }

    /**
     * Tests AzureBlob::sync failure
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');

        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(Result::class);

        $azureBlob = new TestableAzureBlobSync();
        $azureBlob->uploadThrows = true;
        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $azureBlob->sync($target, $result);
    }

    /**
     * Tests AzureBlob::simulate
     */
    public function testSimulate()
    {
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'super-secret-connection-string',
            'container_name'    => 'dummy-container-name',
            'path' => '/'
        ]);

        $messages   = [];
        $resultStub = $this->createDebugCollectingResult($messages);
        $targetStub = $this->createMock(Target::class);

        $azureBlob->simulate($targetStub, $resultStub);

        $this->assertCount(1, $messages);
        // the credential carrying connection string must never be logged
        $this->assertStringContainsString('connectionString: ********', $messages[0]);
        $this->assertStringNotContainsString('super-secret-connection-string', $messages[0]);
        $this->assertStringContainsString('dummy-container-name', $messages[0]);
    }

    /**
     * Build a Result mock that records every debug() message into $messages.
     *
     * @param  array $messages
     * @return \phpbu\App\Result
     */
    private function createDebugCollectingResult(array &$messages): Result
    {
        $result = $this->createMock(Result::class);
        $result->method('debug')->willReturnCallback(function ($message) use (&$messages) {
            $messages[] = $message;
        });
        return $result;
    }

    /**
     * Tests AzureBlob::setUp
     */
    public function testSetUpNoConnectionString()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'container_name' => 'dummy-container-name',
            'path'           => '/'
        ]);
    }

    /**
     * Tests AzureBlob::setUp
     */
    public function testSetUpNoContainerName()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'path'              => '/'
        ]);
    }

    /**
     * Tests AzureBlob::setUp
     */
    public function testSetUpNoPath()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name'
        ]);
    }
}
