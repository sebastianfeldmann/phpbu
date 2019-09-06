<?php
namespace phpbu\App\Backup\File;

use Aws\S3\S3Client;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * AmazonS3v3Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class AmazonS3V3Test extends TestCase
{
    /**
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $amazonS3 = $this->getMockBuilder(S3Client::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['deleteObject'])
                         ->getMock();

        $amazonS3->expects($this->once())
                 ->method('deleteObject')
                 ->with([
                     'Bucket' => 'test',
                     'Key'    => 'dump.tar.gz',
                 ]);

        $metadata = [
            'Key'          => 'dump.tar.gz',
            'Size'         => 102102,
            'LastModified' => '2018-05-08 14:14:54.0 +00:00',
        ];

        $file = new AmazonS3v3($amazonS3, 'test', $metadata);
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
    public function testAWSDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $amazonS3 = $this->getMockBuilder(S3Client::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['deleteObject'])
                         ->getMock();

        $amazonS3->expects($this->once())
                 ->method('deleteObject')
                 ->with([
                    'Bucket' => 'test',
                    'Key'    => 'dump.tar.gz',
                 ])
                 ->will($this->throwException(new Exception));

        $metadata = [
            'Key'          => 'dump.tar.gz',
            'Size'         => 102102,
            'LastModified' => '2018-05-08 14:14:54.0 +00:00',
        ];

        $file = new AmazonS3v3($amazonS3, 'test', $metadata);
        $file->unlink();
    }
}
