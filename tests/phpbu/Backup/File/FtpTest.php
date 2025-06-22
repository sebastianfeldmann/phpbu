<?php
namespace phpbu\App\Backup\File;

use DateTimeImmutable;
use Exception;
use SebastianFeldmann;
use PHPUnit\Framework\TestCase;
use SebastianFeldmann\Ftp\Client;
use SebastianFeldmann\Ftp\File;

/**
 * FtpTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class FtpTest extends TestCase
{
    /**
     * Test creating file and handle removing
     */
    public function testFile()
    {
        $ftpClient = $this->createMock(Client::class);
        $ftpClient->expects($this->once())->method('chHome');
        $ftpClient->expects($this->once())
                  ->method('__call');

        $remotePath = 'backups';
        $ftpFile    = $this->createMock(File::class);
        $ftpFile->expects($this->exactly(2))->method('getFilename')->willReturn('foo.txt');
        $ftpFile->expects($this->once())->method('getSize')->willReturn(102102);
        $ftpFile->expects($this->once())
                ->method('getLastModifyDate')
                ->willReturn(DateTimeImmutable::createFromFormat('YmdHis', '20180508141454'));

        $file = new Ftp($ftpClient, $ftpFile, $remotePath);
        $this->assertEquals('foo.txt', $file->getFilename());
        $this->assertEquals('backups/foo.txt', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertTrue(1525780000 < $file->getMTime());
        $this->assertEquals(true, $file->isWritable());

        $file->unlink();
    }

    /**
     * Tests Ftp::unlink
     */
    public function testDeleteFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $ftpClient = $this->createMock(Client::class);
        $ftpClient->expects($this->once())->method('chHome');
        $ftpClient->expects($this->once())
                  ->method('__call')
                  ->will($this->throwException(new Exception));

        $remotePath = 'backups';
        $ftpFile    = $this->createMock(File::class);
        $ftpFile->expects($this->exactly(2))->method('getFilename')->willReturn('foo.txt');
        $ftpFile->expects($this->once())->method('getSize')->willReturn(102102);
        $ftpFile->expects($this->once())
                ->method('getLastModifyDate')
                ->willReturn(DateTimeImmutable::createFromFormat('YmdHis', '20180508141454'));

        $file = new Ftp($ftpClient, $ftpFile, $remotePath);
        $file->unlink();
    }
}
