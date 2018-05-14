<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

/**
 * SFTP Collector test
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
class SftpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test SFTP collector
     */
    public function testCollector()
    {
        $path           = '/collector/static-dir/';
        $filename       = 'foo-%Y-%m-%d-%H_%i.txt';
        $target         = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $secLib         = $this->createMock(\phpseclib\Net\SFTP::class);
        $time           = time();
        $remotePath     = '/backups';
        $secLibFileList = [
            '.' => [
                'type'     => 2, // directory
                'filename' => '.',
            ],
            '..' => [
                'type'     => 2, // directory
                'filename' => '..',
            ],
            'some_dir' => [
                'type'     => 2, // directory
                'filename' => 'some_dir',
            ],
            $target->getFilename() => [ // current backup
                'type'     => 1,
                'filename' => $target->getFilename(),
            ],
            'foo-2000-12-01-12_00.txt' => [
                'type'     => 1,
                'size'     => 100,
                'mtime'    => 1525788894,
                'filename' => 'foo-2000-12-01-12_00.txt',
            ],
            'not-matching-2000-12-01-12_00.txt' => [
                'type'     => 1,
                'size'     => 100,
                'mtime'    => 1525788894,
                'filename' => 'not-matching-2000-12-01-12_00.txt',
            ],
        ];

        $secLib->expects($this->once())
               ->method('_list')
               ->with($remotePath)
               ->willReturn($secLibFileList);

        $path = new Path($remotePath, $time);
        $collector = new Sftp($target, $secLib, $path);
        $this->assertAttributeEquals($secLib, 'sftp', $collector);
        $this->assertAttributeEquals($path, 'path', $collector);
        $this->assertAttributeEquals($target, 'target', $collector);
        $this->assertAttributeEquals(Util\Path::datePlaceholdersToRegex($target->getFilenameRaw()), 'fileRegex', $collector);
        $this->assertAttributeEquals([], 'files', $collector);

        $files = $collector->getBackupFiles();
        $this->assertCount(1, $files);
        $this->assertArrayHasKey(1525788894, $files);
        $this->assertEquals('foo-2000-12-01-12_00.txt', $files[1525788894]->getFilename());
    }
}
