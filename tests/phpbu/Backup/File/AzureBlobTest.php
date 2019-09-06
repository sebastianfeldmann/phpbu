<?php
namespace phpbu\App\Backup\File;

use DateTime;
use Exception;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\Blob;
use MicrosoftAzure\Storage\Blob\Models\BlobProperties;
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
    /**
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $azureBlob = $this->getMockBuilder(BlobRestProxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteBlob'])
            ->getMock();

        $azureBlob->expects($this->once())
                 ->method('deleteBlob')
                 ->with('mycontainer', 'dump.tar.gz');

        $blobProps = new BlobProperties();
        $blobProps->setLastModified(new DateTime('2018-05-08 14:14:54.0 +00:00'));
        $blobProps->setContentLength(102102);

        $blob = new Blob();
        $blob->setProperties($blobProps);
        $blob->setName('dump.tar.gz');

        $file = new AzureBlob($azureBlob, 'mycontainer', $blob);
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());

        $file->unlink();
        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests AmazonS3V3::unlink
     */
    public function testAzureBlobDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $azureBlob = $this->getMockBuilder(BlobRestProxy::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteBlob'])
            ->getMock();

        $azureBlob->expects($this->once())
                 ->method('deleteBlob')
                 ->with('mycontainer', 'dump.tar.gz')
                 ->will($this->throwException(new Exception));

        $blobProps = new BlobProperties();
        $blobProps->setLastModified(new DateTime('2018-05-08 14:14:54.0 +00:00'));
        $blobProps->setContentLength(102102);

        $blob = new Blob();
        $blob->setProperties($blobProps);
        $blob->setName('dump.tar.gz');

        $file = new AzureBlob($azureBlob, 'mycontainer', $blob);
        $file->unlink();
    }
}
