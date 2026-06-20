<?php
namespace phpbu\App\Backup\Sync;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use phpbu\App\Backup\Collector\AzureBlob as AzureBlobCollector;
use phpbu\App\Backup\Target;
use phpbu\App\BaseMockery;
use phpbu\App\Result;
use PHPUnit\Framework\TestCase;

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
     * Tests AzureBlob::sync
     */
    public function testSync()
    {
        $target   = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $messages = [];
        $result   = $this->createDebugCollectingResult($messages);

        $azureBlob = $this->createPartialMock(
            AzureBlob::class,
            ['createClient', 'doesContainerExist', 'createContainer', 'uploadBlob', 'getFileHandle']
        );
        $azureBlob->method('createClient')->willReturn($this->createContainerClient());
        $azureBlob->method('doesContainerExist')->willReturn(false);
        $azureBlob->expects($this->once())->method('createContainer');
        $azureBlob->expects($this->once())->method('uploadBlob')
                  ->with($this->anything(), $this->stringStartsWith('backup/'), $this->anything());
        $azureBlob->method('getFileHandle')->willReturn('filehandle');

        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $azureBlob->sync($target, $result);

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

        $azureBlob = $this->createPartialMock(
            AzureBlob::class,
            ['createClient', 'doesContainerExist', 'createContainer', 'uploadBlob', 'getFileHandle']
        );
        $azureBlob->method('createClient')->willReturn($this->createContainerClient());
        $azureBlob->method('doesContainerExist')->willReturn(true);
        $azureBlob->expects($this->never())->method('createContainer');
        $azureBlob->expects($this->once())->method('uploadBlob');
        $azureBlob->method('getFileHandle')->willReturn('filehandle');

        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $azureBlob->sync($target, $result);

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

        $azureBlob = $this->createPartialMock(
            AzureBlob::class,
            ['createClient', 'doesContainerExist', 'createContainer', 'uploadBlob', 'getFileHandle', 'createCollector']
        );
        $azureBlob->method('createClient')->willReturn($this->createContainerClient());
        $azureBlob->method('doesContainerExist')->willReturn(false);
        $azureBlob->expects($this->once())->method('uploadBlob');
        $azureBlob->method('getFileHandle')->willReturn('filehandle');
        $azureBlob->method('createCollector')->willReturn($collector);

        $azureBlob->setup([
            'connection_string' => self::CONNECTION_STRING,
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup',
            'cleanup.type'      => 'quantity',
            'cleanup.amount'    => 99
        ]);

        $azureBlob->sync($target, $result);

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

        $azureBlob = $this->createPartialMock(
            AzureBlob::class,
            ['createClient', 'doesContainerExist', 'createContainer', 'uploadBlob', 'getFileHandle']
        );
        $azureBlob->method('createClient')->willReturn($this->createContainerClient());
        $azureBlob->method('doesContainerExist')->willReturn(false);
        $azureBlob->method('getFileHandle')->willReturn('filehandle');
        $azureBlob->method('uploadBlob')->will($this->throwException(new \Exception()));

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

    /**
     * Build an offline container client (no network calls on construction).
     *
     * @return \AzureOss\Storage\Blob\BlobContainerClient
     */
    private function createContainerClient(): BlobContainerClient
    {
        return BlobServiceClient::fromConnectionString(self::CONNECTION_STRING)
                                ->getContainerClient('dummy-container-name');
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
}
