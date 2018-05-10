<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Target;
use phpbu\App\Util\Str;

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
class AmazonS3V3Test extends \PHPUnit\Framework\TestCase
{
    /**
     * Test Amazon S3 collector
     */
    public function testCollector()
    {
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));

        $amazonS3 = $this->getMockBuilder(\Aws\S3\S3Client::class)
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
        $amazonS3->expects($this->exactly(4))
            ->method('listObjects')
            ->with([
                'Bucket'    => 'test',
                'Prefix'    => '',
                'Delimiter' => '/',
            ])
            ->will(
                $this->onConsecutiveCalls(null, ['Contents' => false], ['Contents' => true], $amazonS3Contents)
            );

        $collector = new \phpbu\App\Backup\Collector\AmazonS3v3($target, $amazonS3, 'test', '');
        $this->assertAttributeEquals($amazonS3, 'client', $collector);
        $this->assertAttributeEquals('', 'path', $collector);
        $this->assertAttributeEquals('test', 'bucket', $collector);
        $this->assertAttributeEquals($target, 'target', $collector);
        $this->assertAttributeEquals(Str::datePlaceholdersToRegex($target->getFilenameRaw()), 'fileRegex', $collector);
        $this->assertAttributeEquals([], 'files', $collector);

        $this->assertEquals([], $collector->getBackupFiles());
        $this->assertEquals([], $collector->getBackupFiles());
        $this->assertEquals([], $collector->getBackupFiles());
        $files = $collector->getBackupFiles();
        $this->assertCount(1, $files);
        $this->assertEquals('foo-2000-12-01-12_00.txt', $files[0]->getFilename());
    }
}
