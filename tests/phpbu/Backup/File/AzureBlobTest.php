<?php
namespace phpbu\App\Backup\File;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Models\Blob;
use AzureOss\Storage\Blob\Models\BlobProperties;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Test double exposing a controllable blob deletion seam so the real
 * AzureBlob logic runs without hitting the (final) Azure SDK client.
 */
class TestableAzureBlobFile extends AzureBlob
{
    /** @var bool */
    public $deleted = false;

    /** @var bool */
    public $deleteThrows = false;

    /** @var string|null */
    public $deletedPathname = null;

    protected function deleteBlob(): void
    {
        if ($this->deleteThrows) {
            throw new \Exception('delete failed');
        }
        $this->deleted         = true;
        $this->deletedPathname = $this->pathname;
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
    /**
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $blob = $this->createBlob('dump.tar.gz', '2018-05-08 14:14:54.0 +00:00', 102102);
        $file = new TestableAzureBlobFile($this->createContainerClient(), $blob);

        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());

        $file->unlink();
        $this->assertTrue($file->deleted, 'blob should be deleted');
        $this->assertEquals('dump.tar.gz', $file->deletedPathname, 'the correct blob must be targeted for deletion');
    }

    /**
     * Tests AzureBlob::unlink failure
     */
    public function testAzureBlobDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');

        $blob = $this->createBlob('dump.tar.gz', '2018-05-08 14:14:54.0 +00:00', 102102);
        $file = new TestableAzureBlobFile($this->createContainerClient(), $blob);
        $file->deleteThrows = true;

        $file->unlink();
    }

    /**
     * Build an offline container client (no network calls on construction).
     *
     * @return \AzureOss\Storage\Blob\BlobContainerClient
     */
    private function createContainerClient(): BlobContainerClient
    {
        return BlobServiceClient::fromConnectionString(
            'DefaultEndpointsProtocol=https;AccountName=accountname;AccountKey=accountkey;' .
            'EndpointSuffix=core.windows.net'
        )->getContainerClient('mycontainer');
    }

    /**
     * Build a blob model as returned by the Azure Blob SDK.
     *
     * @param  string $name
     * @param  string $lastModified
     * @param  int    $contentLength
     * @return \AzureOss\Storage\Blob\Models\Blob
     */
    private function createBlob(string $name, string $lastModified, int $contentLength): Blob
    {
        $properties = new BlobProperties(
            new DateTimeImmutable($lastModified),
            $contentLength,
            'application/octet-stream',
            null,
            []
        );

        return new Blob($name, $properties);
    }
}
