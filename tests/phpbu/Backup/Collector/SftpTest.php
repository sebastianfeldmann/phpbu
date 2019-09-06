<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\TestCase;

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
class SftpTest extends TestCase
{
    /**
     * Test SFTP collector
     */
    public function testStaticDir()
    {
        $path           = '/collector/static-dir/';
        $remotePath     = '/backups';
        $filename       = 'foo-%Y-%m-%d-%H_%i.txt';
        $target         = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $pathUtil       = new Path($remotePath);
        $secLib         = $this->createMock(\phpseclib\Net\SFTP::class);
        $secLibFileList = [
            // directory
            '.' => [
                'type'     => 2,
                'filename' => '.',
            ],
            // directory
            '..' => [
                'type'     => 2,
                'filename' => '..',
            ],
            // directory
            'some_dir' => [
                'type'     => 2,
                'filename' => 'some_dir',
            ],
            // current backup
            $target->getFilename() => [
                'type'     => 1,
                'size'     => 100,
                'mtime'    => 1525788994,
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

        $collector = new Sftp($target, $pathUtil, $secLib);
        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('1525788894-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['1525788894-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    /**
     * Tests Sftp::getBackupFiles
     */
    public function testDynamicDir()
    {
        $path       = '/collector/static-dir/';
        $remotePath = '/backups/%Y';
        $filename   = 'foo-%Y-%m-%d-%H_%i.txt';
        $target     = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $pathUtil   = new Path($remotePath);

        // return result with one matching directory
        $listDir    = [
            // directory
            '.' => [
                'type'     => 2,
                'filename' => '.',
            ],
            // directory
            '..' => [
                'type'     => 2,
                'filename' => '..',
            ],
            // directory not matching
            'some_dir' => [
                'type'     => 2,
                'filename' => 'some_dir',
            ],
            // directory matching
            '2000' => [
                'type'     => 2,
                'filename' => '2000',
            ],
        ];
        // return content of matching directory
        $listFiles = [
            // directory
            '.' => [
                'type'     => 2,
                'filename' => '.',
            ],
            // directory
            '..' => [
                'type'     => 2,
                'filename' => '..',
            ],
            // current backup
            $target->getFilename() => [
                'type'     => 1,
                'size'     => 100,
                'mtime'    => 1525788994,
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

        $secLib = $this->createMock(\phpseclib\Net\SFTP::class);
        $secLib->expects($this->exactly(2))
               ->method('_list')
               ->will($this->onConsecutiveCalls($listDir, $listFiles));

        $collector = new Sftp($target, $pathUtil, $secLib);
        $files     = $collector->getBackupFiles();
        $this->assertCount(2, $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['1525788894-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    /**
     * Tests Sftp::getBackupFiles
     */
    public function testSftpFail()
    {
        $path           = '/collector/static-dir/';
        $remotePath     = '/backups';
        $filename       = 'foo-%Y-%m-%d-%H_%i.txt';
        $target         = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $pathUtil       = new Path($remotePath);
        $secLib         = $this->createMock(\phpseclib\Net\SFTP::class);
        $result         = null;

        $secLib->expects($this->once())
            ->method('_list')
            ->with($remotePath)
            ->willReturn($result);

        $collector = new Sftp($target, $pathUtil, $secLib);

        $files = $collector->getBackupFiles();
        $this->assertCount(0, $files);
    }
}
