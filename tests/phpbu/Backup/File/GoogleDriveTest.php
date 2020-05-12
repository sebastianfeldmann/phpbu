<?php
namespace phpbu\App\Backup\File;

use PHPUnit\Framework\TestCase;

/**
 * Google Drive file test.
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class GoogleDriveTest extends TestCase
{
    /**
     * Test GoogleDrive::unlink
     */
    public function testUnlink()
    {
        $file = $this->createMock(\Google_Service_Drive_DriveFile::class);
        $file->expects($this->once())->method('getName')->willReturn('dump.tar.gz');
        $file->expects($this->exactly(2))->method('getId')->willReturn('A12345');
        $file->expects($this->once())->method('getSize')->willReturn(102102);
        $file->expects($this->once())->method('getCreatedTime')->willReturn('2018-05-08 14:14:54.0 +00:00');

        $resource = $this->createMock(\Google_Service_Drive_Resource_Files::class);
        $resource->expects($this->once())
                 ->method('delete');

        $service        = $this->createMock(\Google_Service_Drive::class);
        $service->files = $resource;

        $file = new GoogleDrive($service, $file);
        $this->assertEquals('dump.tar.gz', $file->getFilename());
        $this->assertEquals('A12345', $file->getPathname());
        $this->assertEquals(102102, $file->getSize());
        $this->assertEquals(1525788894, $file->getMTime());

        $file->unlink();
    }

    /**
     * Tests GoogleDrive::unlink
     */
    public function testUnlinkFailure()
    {
        $this->expectException('phpbu\App\Exception');
        $file = $this->createMock(\Google_Service_Drive_DriveFile::class);
        $file->method('getName')->willReturn('dump.tar.gz');
        $file->method('getId')->willReturn('A12345');
        $file->method('getSize')->willReturn(102102);
        $file->method('getCreatedTime')->willReturn('2018-05-08 14:14:54.0 +00:00');

        $resource = $this->createMock(\Google_Service_Drive_Resource_Files::class);
        $resource->expects($this->once())
                 ->method('delete')
                 ->will($this->throwException(new \Exception));

        $service        = $this->createMock(\Google_Service_Drive::class);
        $service->files = $resource;

        $file = new GoogleDrive($service, $file);
        $file->unlink();
    }
}
