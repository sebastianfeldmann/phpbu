<?php
namespace phpbu\App\Backup\Collector;

use Aws\S3\S3Client;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\TestCase;

/**
 * Amazon S3v3 Collector test
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
     * Test Amazon S3 collector
     */
    public function testCollector()
    {
        $time      = time();
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $path      = new Path('', $time, false);
        $amazonS3  = $this->getMockBuilder(S3Client::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['listObjects'])
                         ->getMock();

        $amazonS3Contents = [
            'Contents' => [
                [
                    'Key'          => $target->getFilename(), // Current backup file
                    'Size'         => 100,
                    'LastModified' => '2018-05-08 14:14:54.0 +00:00',
                ],
                [
                    'Key'          => 'foo-2000-12-01-12_00.txt',
                    'Size'         => 100,
                    'LastModified' => '2000-12-01 12:00:00.0 +00:00',
                ],
                [
                    'Key'          => 'not-matching-2000-12-01-12_00.txt',
                    'Size'         => 100,
                    'LastModified' => '2000-12-01 12:00:00.0 +00:00',
                ],
            ],
        ];

        // Firstly mock listObjects without wrong or non existed contents key to
        // make sure it returns empty array of files
        $amazonS3->expects($this->once())
                 ->method('listObjects')
                 ->with(['Bucket' => 'test', 'Prefix' => ''])
                 ->willReturn($amazonS3Contents);

        $collector = new AmazonS3v3($target, $path, $amazonS3, 'test');
        $files     = $collector->getBackupFiles();
        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    public function testNoAWSResult()
    {
        $time      = time();
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $path      = new Path('', $time, false);
        $amazonS3  = $this->getMockBuilder(S3Client::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['listObjects'])
                         ->getMock();
        $amazonS3->expects($this->exactly(3))
                 ->method('listObjects')
                 ->with(['Bucket' => 'test', 'Prefix' => ''])
                 ->will($this->onConsecutiveCalls(null, ['content' => null], ['content' => true]));


        $collector1 = new AmazonS3v3($target, $path, $amazonS3, 'test');
        $this->assertEquals([], $collector1->getBackupFiles());
        $collector2 = new AmazonS3v3($target, $path, $amazonS3, 'test');
        $this->assertEquals([], $collector2->getBackupFiles());
        $collector3 = new AmazonS3v3($target, $path, $amazonS3, 'test');
        $this->assertEquals([], $collector3->getBackupFiles());
    }
}
