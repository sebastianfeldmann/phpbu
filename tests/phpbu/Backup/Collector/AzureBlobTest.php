<?php
namespace phpbu\App\Backup\Collector;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsResult;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\TestCase;

/**
 * AzureBlob Collector test
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
    /**
     * Test Azure Blob collector
     */
    public function testCollector()
    {
        $time      = time();
        $path      = 'collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $path      = new Path($path, $time, false);
        $azureBlob = $this->getMockBuilder(BlobRestProxy::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['listBlobs'])
                         ->getMock();

        $azureBlobContents = ListBlobsResult::create(
            [
                "@attributes" => [
                    "ServiceEndpoint" => "https://accountname.blob.core.windows.net/",
                    "ContainerName" => "mycontainer"
                ],
                "Prefix" => $path->getPath(),
                "MaxResults" => 10,
                "Blobs" => [
                    "Blob" => [
                        [
                            "Name" => 'collector/static-dir/not-matching-2000-12-01-12_00.txt',
                            "Properties" => [
                                "Creation-Time" => "Fri, 01 Dec 2000 12:00:00 GMT",
                                "Last-Modified" => "Fri, 01 Dec 2000 12:00:00 GMT",
                                "Etag" => "0x8D702FF4A6EFED0",
                                "Content-Length" => "100",
                                "Content-Type" => "application/octet-stream",
                                "Content-Encoding" => null,
                                "Content-Language" => null,
                                "Content-MD5" => "4zrnbmTxOxLksK3+1ulT8g==",
                                "Cache-Control" => null,
                                "Content-Disposition" => null,
                                "BlobType" => "BlockBlob",
                                "AccessTier" =>"Hot",
                                "AccessTierInferred" => "true",
                                "LeaseStatus" => "unlocked",
                                "LeaseState" => "available",
                                "ServerEncrypted" => "true"
                            ]
                        ],
                        [
                            "Name" => 'collector/static-dir/foo-2000-12-01-12_00.txt',
                            "Properties" => [
                                "Creation-Time" => "Fri, 01 Dec 2000 12:00:00 GMT",
                                "Last-Modified" => "Fri, 01 Dec 2000 12:00:00 GMT",
                                "Etag" => "0x8D702FF4A6EFED0",
                                "Content-Length" => "100",
                                "Content-Type" => "application/octet-stream",
                                "Content-Encoding" => null,
                                "Content-Language" => null,
                                "Content-MD5" => "4zrnbmTxOxLksK3+1ulT8g==",
                                "Cache-Control" => null,
                                "Content-Disposition" => null,
                                "BlobType" => "BlockBlob",
                                "AccessTier" =>"Hot",
                                "AccessTierInferred" => "true",
                                "LeaseStatus" => "unlocked",
                                "LeaseState" => "available",
                                "ServerEncrypted" => "true"
                            ]
                        ],
                        [
                            "Name" => $target->getPathname(), // Current backup file
                            "Properties" => [
                                "Creation-Time" => "Fri, 08 May 2018 14:14:54 GMT",
                                "Last-Modified" => "Fri, 08 May 2018 14:14:54 GMT",
                                "Etag" => "0x8D702FF4A6EFED0",
                                "Content-Length" => "100",
                                "Content-Type" => "application/octet-stream",
                                "Content-Encoding" => null,
                                "Content-Language" => null,
                                "Content-MD5" => "4zrnbmTxOxLksK3+1ulT8g==",
                                "Cache-Control" => null,
                                "Content-Disposition" => null,
                                "BlobType" => "BlockBlob",
                                "AccessTier" =>"Hot",
                                "AccessTierInferred" => "true",
                                "LeaseStatus" => "unlocked",
                                "LeaseState" => "available",
                                "ServerEncrypted" => "true"
                            ]
                        ]
                    ]
                ],
                "NextMarker" => null
            ]
        );

        // Firstly mock listObjects without wrong or non existed contents key to
        // make sure it returns empty array of files
        $azureBlob->expects($this->once())
                 ->method('listBlobs')
                 ->with($this->equalTo('mycontainer'), $this->isInstanceOf(ListBlobsOptions::class))
                 ->willReturn($azureBlobContents);

        $collector = new AzureBlob($target, $path, $azureBlob, 'mycontainer');
        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-0', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-0']->getFilename()
        );
    }

    public function testNoBlobResult()
    {
        $time      = time();
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $path      = new Path('', $time, false);
        $azureBlob = $this->getMockBuilder(BlobRestProxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['listBlobs'])
            ->getMock();

        $azureBlobContents = ListBlobsResult::create(
            [
                "@attributes" => [
                    "ServiceEndpoint" => "https://accountname.blob.core.windows.net/",
                    "ContainerName" => "mycontainer"
                ],
                "Prefix" => $path,
                "MaxResults" => 10,
                "Blobs" => [
                    "Blob" => []
                ],
                "NextMarker" => null
            ]
        );

        $azureBlob->expects($this->exactly(1))
                 ->method('listBlobs')
                 ->with($this->equalTo('mycontainer'), $this->isInstanceOf(ListBlobsOptions::class))
                 ->willReturn($azureBlobContents);


        $collector1 = new AzureBlob($target, $path, $azureBlob, 'mycontainer');
        $this->assertEquals([], $collector1->getBackupFiles());
    }
}
