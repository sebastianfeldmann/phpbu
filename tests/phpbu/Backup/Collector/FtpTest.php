<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Ftp\Client;
use SebastianFeldmann\Ftp\File;

/**
 * FTP Collector test
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
class FtpTest extends TestCase
{
    /**
     * Tests Ftp::getBackupFiles
     */
    public function testStaticDir()
    {
        $path       = '/collector/static-dir/';
        $remotePath = '/backups';
        $filename   = 'foo-%Y-%m-%d-%H_%i.txt';
        $target     = new Target($path, $filename, strtotime('2018-06-01 11:12:13'));
        $pathUtil   = new Path($remotePath);
        $ftpClient  = $this->createMock(Client::class);
        $fileList   = [
            $target->getFilename() => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20180601111213',
                    'name'   => $target->getFilename(),
                    'unique' => 'foo'
                ]
            ),
            'foo-2018-05-08-14_14.txt' => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20180508141454',
                    'name'   => 'foo-2018-05-08-14_14.txt',
                    'unique' => 'bar'
                ]
            ),
            'not-matching-2000-12-01-12_00.txt' => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20001201011200',
                    'name'   => 'not-matching-2000-12-01-12_00.txt',
                    'unique' => 'baz'
                ]
            )
        ];

        $ftpClient->expects($this->once())
                  ->method('lsFiles')
                  ->with($remotePath)
                  ->willReturn($fileList);

        $collector = new Ftp($target, $pathUtil, $ftpClient);
        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('1525788894-foo-2018-05-08-14_14.txt-1', $files);
        $this->assertEquals(
            'foo-2018-05-08-14_14.txt',
            $files['1525788894-foo-2018-05-08-14_14.txt-1']->getFilename()
        );
    }

    /**
     * Tests Ftp::getBackupFiles
     */
    public function testDynamicDir()
    {
        $path       = '/collector/static-dir/';
        $remotePath = '/backups/%Y';
        $filename   = 'foo-%Y-%m-%d-%H_%i.txt';
        $target     = new Target($path, $filename, strtotime('2018-06-01 11:12:13'));
        $pathUtil   = new Path($remotePath);
        $ftpClient  = $this->createMock(Client::class);

        $dirList    = [
            'some_dir' => new File(
                [
                    'type'   => 'dir',
                    'size'   => 1,
                    'modify' => '20180101102030',
                    'name'   => 'some_dir',
                    'unique' => 'some_dir'
                ]
            ),
            '2018' => new File(
                [
                    'type'   => 'dir',
                    'size'   => 1,
                    'modify' => '20180101112233',
                    'name'   => '2018',
                    'unique' => '2018'
                ]
            )
        ];

        $fileList   = [
            $target->getFilename() => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20180601111213',
                    'name'   => $target->getFilename(),
                    'unique' => 'foo'
                ]
            ),
            'foo-2018-05-08-14_14.txt' => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20180508141454',
                    'name'   => 'foo-2018-05-08-14_14.txt',
                    'unique' => 'bar'
                ]
            ),
            'not-matching-2000-12-01-12_00.txt' => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20001201011200',
                    'name'   => 'not-matching-2000-12-01-12_00.txt',
                    'unique' => 'baz'
                ]
            )
        ];

        $ftpClient->expects($this->once())
                  ->method('lsDirs')
                  ->with('/backups')
                  ->willReturn($dirList);

        $ftpClient->expects($this->once())
                  ->method('lsFiles')
                  ->with('/backups/2018')
                  ->willReturn($fileList);

        $collector = new Ftp($target, $pathUtil, $ftpClient);
        $files     = $collector->getBackupFiles();
        $this->assertCount(2, $files);
        $this->assertEquals(
            'foo-2018-05-08-14_14.txt',
            $files['1525788894-foo-2018-05-08-14_14.txt-1']->getFilename()
        );
    }

    /**
     * Tests Ftp::getBackupFiles
     */
    public function testFtpFail()
    {
        $path       = '/collector/static-dir/';
        $remotePath = '/backups';
        $filename   = 'foo-%Y-%m-%d-%H_%i.txt';
        $target     = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));
        $pathUtil   = new Path($remotePath);
        $ftpClient  = $this->createMock(Client::class);
        $result     = [];

        $ftpClient->expects($this->once())
                  ->method('lsFiles')
                  ->with($remotePath)
                  ->willReturn($result);

        $collector = new Ftp($target, $pathUtil, $ftpClient);
        $files     = $collector->getBackupFiles();
        $this->assertCount(0, $files);
    }

    /**
     * Tests Ftp::getBackupFiles
     */
    public function testFtpSimulate()
    {
        $path       = '/collector/static-dir/';
        $remotePath = '/backups';
        $filename   = 'foo-%Y-%m-%d-%H_%i.txt';
        $target     = new Target($path, $filename, strtotime('2018-06-01 11:12:13'));
        $pathUtil   = new Path($remotePath);
        $ftpClient  = $this->createMock(Client::class);
        $fileList   = [
            'foo-2018-05-08-14_14.txt' => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20180508141454',
                    'name'   => 'foo-2018-05-08-14_14.txt',
                    'unique' => 'bar'
                ]
            ),
            'not-matching-2000-12-01-12_00.txt' => new File(
                [
                    'type'   => 'file',
                    'size'   => 100,
                    'modify' => '20001201011200',
                    'name'   => 'not-matching-2000-12-01-12_00.txt',
                    'unique' => 'baz'
                ]
            )
        ];

        $ftpClient->expects($this->once())
                  ->method('lsFiles')
                  ->with($remotePath)
                  ->willReturn($fileList);

        $collector = new Ftp($target, $pathUtil, $ftpClient);
        $collector->setSimulation(true);

        $files     = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('1525788894-foo-2018-05-08-14_14.txt-0', $files);
        $this->assertEquals(
            'foo-2018-05-08-14_14.txt',
            $files['1525788894-foo-2018-05-08-14_14.txt-0']->getFilename()
        );
    }
}
