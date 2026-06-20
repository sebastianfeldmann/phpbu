<?php
namespace phpbu\App\Backup\File;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Models\Blob;
use AzureOss\Storage\Blob\Models\BlobProperties;
use DateTimeImmutable;
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
    /**
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $blob = $this->createBlob('collector/static-dir/dump.tar.gz', '2018-05-08 14:14:54.0 +00:00', 102102);

        $file = $this->getMockBuilder(AzureBlob::class)
            ->setConstructorArgs([$this->createContainerClient(), $blob])
            ->onlyMethods(['deleteBlob'])
            ->getMock();
        $file->expects($this->once())
             ->method('deleteBlob')
             ->with($this->equalTo('collector/static-dir/dump.tar.gz'));

        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('collector/static-dir/dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());

        $file->unlink();
    }

    /**
     * Tests AzureBlob::unlink failure
     */
    public function testAzureBlobDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');

        $blob = $this->createBlob('collector/static-dir/dump.tar.gz', '2018-05-08 14:14:54.0 +00:00', 102102);

        $file = $this->getMockBuilder(AzureBlob::class)
            ->setConstructorArgs([$this->createContainerClient(), $blob])
            ->onlyMethods(['deleteBlob'])
            ->getMock();
        $file->method('deleteBlob')->will($this->throwException(new \Exception()));

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
