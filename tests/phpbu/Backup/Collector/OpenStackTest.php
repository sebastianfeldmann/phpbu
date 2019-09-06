<?php
namespace phpbu\App\Backup\Collector;

use DateTimeImmutable;
use OpenStack\ObjectStore\v1\Models\Container;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * OpenStack Collector test
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
class OpenStackTest extends TestCase
{
    /**
     * Test OpenStack collector
     */
    public function testCollector()
    {
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));

        $openStackContainerStub = $this->createMock(Container::class);

        $remotePath     = '/backups/';
        $openStackFiles = [
            [
                'content_type'  => 'application/directory',
                'pathname'      => $remotePath . 'test_dir',
                'size'          => 0,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'content_type'  => 'text/plain',
                'pathname'      => $remotePath . $target->getFilename(),
                'size'          => 100,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'content_type'  => 'text/plain',
                'pathname'      => $remotePath . 'foo-2000-12-01-12_00.txt',
                'size'          => 100,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'content_type'  => 'text/plain',
                'pathname'      => $remotePath . 'not-matching-2000-12-01-12_00.txt',
                'size'          => 100,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
        ];
        $openStackFiles = array_map(
            function ($item) {
                return $this->createOpenStackFileStub($item);
            },
            $openStackFiles
        );

        $openStackContainerStub->expects($this->once())
                               ->method('listObjects')
                               ->with(['prefix' => $remotePath])
                               ->willReturn($this->getOpenStackFilesGenerator($openStackFiles));

        $path      = new Path($remotePath, strtotime('2018-05-08 14:14:54.0 +00:00'));
        $collector = new OpenStack($target, $path, $openStackContainerStub);
        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('1525788894-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['1525788894-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    /**
     * Creates OpenStack file stub
     *
     * @param array $data
     * @return \stdClass
     * @throws \Exception
     */
    private function createOpenStackFileStub(array $data)
    {
        $file                = new stdClass();
        $file->contentType   = $data['content_type'];
        $file->name          = $data['pathname'];
        $file->contentLength = $data['size'];
        $file->lastModified  = new DateTimeImmutable($data['last_modified']);
        return $file;
    }

    /**
     * Returns generator from array of files
     *
     * @param $files
     * @return \Generator
     */
    private function getOpenStackFilesGenerator($files)
    {
        foreach ($files as $file) {
            yield $file;
        }
    }
}
