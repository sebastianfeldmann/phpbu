<?php
namespace phpbu\App\Backup\File;

use PHPUnit\Framework\TestCase;

/**
 * SftpTest
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
     * Test creating file and handle removing
     */
    public function testCreateFileWithCorrectProperties()
    {
        $phpSecLibStub = $this->createMock(\phpseclib\Net\SFTP::class);
        $phpSecLibStub->expects($this->once())
                      ->method('delete')
                      ->with('/backups/dump.tar.gz');

        $remotePath = '/backups';
        $fileInfo   = [
            'filename' => 'dump.tar.gz',
            'size'     => 102102,
            'mtime'    => 1525788894,
        ];

        $file = new Sftp($phpSecLibStub, $fileInfo, $remotePath);
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('/backups/dump.tar.gz', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());
        $this->assertTrue($file->isWritable());

        $file->unlink();
        $this->assertTrue(true, 'no exception should occur');
    }
}
