<?php
namespace phpbu\App\Backup\Collector;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\BlobServiceClient;
use AzureOss\Storage\Blob\Models\Blob;
use AzureOss\Storage\Blob\Models\BlobProperties;
use DateTimeImmutable;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use PHPUnit\Framework\TestCase;

/**
 * Test double exposing a controllable blob listing seam so the real
 * AzureBlob collector logic runs without hitting the (final) Azure SDK client.
 */
class TestableAzureBlobCollector extends AzureBlob
{
    /** @var \AzureOss\Storage\Blob\Models\Blob[] */
    public $blobs = [];

    protected function listBlobs(string $prefix): iterable
    {
        return $this->blobs;
    }
}

/**
 * AzureBlob Collector test
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
     * Test Azure Blob collector
     */
    public function testCollector()
    {
        $time     = time();
        $pathName = 'collector/static-dir/';
        $filename = 'foo-%Y-%m-%d-%H_%i.txt';
        $target   = new Target($pathName, $filename, strtotime('2014-12-07 04:30:57'));
        $path     = new Path($pathName, $time, false);

        $collector = new TestableAzureBlobCollector($target, $path, $this->createContainerClient());
        $collector->blobs = [
            $this->createBlob('collector/static-dir/not-matching-2000-12-01-12_00.txt', '2000-12-01 12:00:00 +00:00'),
            $this->createBlob('collector/static-dir/foo-2000-12-01-12_00.txt', '2000-12-01 12:00:00 +00:00'),
            $this->createBlob($target->getPathname(), '2018-05-08 14:14:54 +00:00'),
        ];

        $files = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-0', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-0']->getFilename()
        );
    }

    /**
     * Test collector with an empty container
     */
    public function testNoBlobResult()
    {
        $time     = time();
        $pathName = '/collector/static-dir/';
        $filename = 'foo-%Y-%m-%d-%H_%i.txt';
        $target   = new Target($pathName, $filename, strtotime('2014-12-07 04:30:57'));
        $path     = new Path('', $time, false);

        $collector = new TestableAzureBlobCollector($target, $path, $this->createContainerClient());
        $collector->blobs = [];

        $this->assertEquals([], $collector->getBackupFiles());
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
     * @return \AzureOss\Storage\Blob\Models\Blob
     */
    private function createBlob(string $name, string $lastModified): Blob
    {
        $properties = new BlobProperties(
            new DateTimeImmutable($lastModified),
            100,
            'application/octet-stream',
            null,
            []
        );

        return new Blob($name, $properties);
    }
}
