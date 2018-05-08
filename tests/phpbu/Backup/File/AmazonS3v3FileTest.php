<?php
namespace phpbu\App\Backup\File;

/**
 * AmazonS3v3FileTest
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
class AmazonS3v3FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $amazonS3 = $this->getMockBuilder(\Aws\S3\S3Client::class)
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
        $this->assertAttributeEquals('dump.tar.gz', 'filename', $file);
        $this->assertAttributeEquals('dump.tar.gz', 'pathname', $file);
        $this->assertAttributeEquals(102102, 'size', $file);
        $this->assertAttributeEquals(1525788894, 'lastModified', $file);
        $this->assertAttributeEquals('test', 'bucket', $file);

        $file->unlink();
        $this->assertTrue(true, 'no exception should occur');
    }
}
