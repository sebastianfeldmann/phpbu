<?php
namespace phpbu\App\Backup\Sync;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsResult;
use MicrosoftAzure\Storage\Blob\Models\ListContainersResult;
use phpbu\App\BaseMockery;
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
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.2.7
 */
class AzureBlobTest extends TestCase
{
    use BaseMockery;

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
     * Tests AzureBlob::setUp
     */
    public function testGetUploadPath()
    {
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name',
            'path'              => '/'
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('foo.zip', $azureBlob->getUploadPath($targetStub));
    }

    /**
     * Tests AzureBlob::setUp
     */
    public function testGetUploadPathAddingMissingSlashes()
    {
        $azureBlob = new AzureBlob();
        $azureBlob->setup([
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name',
            'path'              => 'fiz'
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('fiz/foo.zip', $azureBlob->getUploadPath($targetStub));
    }

    /**
     * Tests AzureBlob::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');

        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(2))->method('debug');

        $listContainers = ListContainersResult::create([
            "@attributes" => [
                "ServiceEndpoint" => "https://accountname.blob.core.windows.net/"
            ],
            "Containers" => [],
            "NextMarker" => null
        ]);

        $clientMock = $this->createAzureBlobMock();
        $clientMock->expects($this->once())->method('listContainers')->willReturn($listContainers);
        $clientMock->expects($this->once())->method('createContainer');

        $azureBlob = $this->createPartialMock(AzureBlob::class, ['createClient', 'getFileHandle']);
        $azureBlob->method('createClient')->willReturn($clientMock);
        $azureBlob->method('getFileHandle')->willReturn('filehandle');

        $azureBlob->setup([
            'connection_string' => 'DefaultEndpointsProtocol=https;AccountName=accountname;AccountKey=accountkey;' .
                                    'EndpointSuffix=core.windows.net',
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup'
        ]);

        $azureBlob->sync($target, $result);
    }

    /**
     * Tests AzureBlob::sync
     */
    public function testSyncWithRemoteCleanup()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');

        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(3))->method('debug');

        $listContainers = ListContainersResult::create([
            "@attributes" => [
                "ServiceEndpoint" => "https://accountname.blob.core.windows.net/"
            ],
            "Containers" => [],
            "NextMarker" => null
        ]);

        $azureBlobContents = ListBlobsResult::create(
            [
                "@attributes" => [
                    "ServiceEndpoint" => "https://accountname.blob.core.windows.net/",
                    "ContainerName" => "mycontainer"
                ],
                "Prefix" => '/',
                "MaxResults" => 10,
                "Blobs" => [
                    "Blob" => []
                ],
                "NextMarker" => null
            ]
        );

        $clientMock = $this->createAzureBlobMock();
        $clientMock->expects($this->once())->method('listContainers')->willReturn($listContainers);
        $clientMock->expects($this->once())->method('createContainer');
        $clientMock->expects($this->once())->method('listBlobs')->willReturn($azureBlobContents);

        $azureBlob = $this->createPartialMock(AzureBlob::class, ['createClient', 'getFileHandle']);
        $azureBlob->method('createClient')->willReturn($clientMock);
        $azureBlob->method('getFileHandle')->willReturn('filehandle');

        $azureBlob->setup([
            'connection_string' => 'DefaultEndpointsProtocol=https;AccountName=accountname;AccountKey=accountkey;' .
                                   'EndpointSuffix=core.windows.net',
            'container_name'    => 'dummy-container-name',
            'path'              => 'backup',
            'cleanup.type'      => 'quantity',
            'cleanup.amount'    => 99
        ]);

        $azureBlob->sync($target, $result);
    }

    /**
     * Tests AmazonS3V3::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');

        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);

        $listContainers = ListContainersResult::create([
            "@attributes" => [
                "ServiceEndpoint" => "https://accountname.blob.core.windows.net/"
            ],
            "Containers" => [],
            "NextMarker" => null
        ]);

        $clientMock = $this->createAzureBlobMock();
        $clientMock->expects($this->once())->method('listContainers')->willReturn($listContainers);
        $clientMock->expects($this->once())->method('createContainer');
        $clientMock->expects($this->once())->method('createBlockBlob')->will($this->throwException(new \Exception));

        $azureBlob = $this->createPartialMock(AzureBlob::class, ['createClient', 'getFileHandle']);
        $azureBlob->method('createClient')->willReturn($clientMock);
        $azureBlob->method('getFileHandle')->willReturn('filehandle');

        $azureBlob->setup([
            'connection_string' => 'DefaultEndpointsProtocol=https;AccountName=accountname;AccountKey=accountkey;' .
                                   'EndpointSuffix=core.windows.net',
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
            'connection_string' => 'dummy-connection-string',
            'container_name'    => 'dummy-container-name',
            'path' => '/'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $azureBlob->simulate($targetStub, $resultStub);
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
     * Create an azure blob client mock
     * @return \MicrosoftAzure\Storage\Blob\BlobRestProxy
     */
    private function createAzureBlobMock()
    {
        /** @var $azureBlobMock \MicrosoftAzure\Storage\Blob\BlobRestProxy */
        $azureBlobMock = $this->createMock(BlobRestProxy::class);
        return $azureBlobMock;
    }
}
