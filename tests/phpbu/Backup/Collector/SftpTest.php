<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Target;
use phpbu\App\Util\Str;

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

        $collector = new \phpbu\App\Backup\Collector\Sftp($target, $secLib, $remotePath);
        $this->assertAttributeEquals($secLib, 'sftp', $collector);
        $this->assertAttributeEquals($remotePath, 'path', $collector);
        $this->assertAttributeEquals($target, 'target', $collector);
        $this->assertAttributeEquals(Str::datePlaceholdersToRegex($target->getFilenameRaw()), 'fileRegex', $collector);
        $this->assertAttributeEquals([], 'files', $collector);

        $files = $collector->getBackupFiles();
        $this->assertCount(1, $files);
        $this->assertEquals('foo-2000-12-01-12_00.txt', $files[0]->getFilename());
    }
}
