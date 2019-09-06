<?php
namespace phpbu\App\Backup\Collector;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_FileList;
use Google_Service_Drive_Resource_Files;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use PHPUnit\Framework\TestCase;

/**
 * Google Drive collector test.
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
     * Tests GoogleDrive::getBackupFiles
     */
    public function testCollector()
    {
        $path      = '/collector/static-dir/';
        $filename  = 'foo-%Y-%m-%d-%H_%i.txt';
        $target    = new Target($path, $filename, strtotime('2014-12-07 04:30:57'));

        $remotePath        = 'X12345';
        $googleFileList    = [
            [
                'name'          => $target->getFilename(),
                'id'            => 'A12345',
                'size'          => 100,
                'last_modified' => '2018-05-08 14:14:54.0 +00:00',
            ],
            [
                'name'          => 'foo-2000-12-01-12_00.txt',
                'id'            => 'B12345',
                'size'          => 100,
                'last_modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
            [
                'name'          => 'not-matching-2000-12-01-12_00.txt',
                'id'            => 'C12345',
                'size'          => 100,
                'last_modified' => '2000-12-01 12:00:00.0 +00:00',
            ],
        ];

        $googleFileList = array_map(
            function ($item) {
                return $this->createGoogleFileStub($item);
            },
            $googleFileList
        );

        $fileList = $this->createMock(Google_Service_Drive_FileList::class);
        $fileList->expects($this->once())
                 ->method('getFiles')
                 ->willReturn($googleFileList);

        $resource = $this->createMock(Google_Service_Drive_Resource_Files::class);
        $resource->expects($this->once())
                 ->method('listFiles')
                 ->willReturn($fileList);

        $service        = $this->createMock(Google_Service_Drive::class);
        $service->files = $resource;


        $time       = time();
        $pathObject = new Path($remotePath, $time);
        $collector  = new GoogleDrive($target, $pathObject, $service);
        $files      = $collector->getBackupFiles();

        $this->assertCount(2, $files);
        $this->assertArrayHasKey('975672000-foo-2000-12-01-12_00.txt-1', $files);
        $this->assertEquals(
            'foo-2000-12-01-12_00.txt',
            $files['975672000-foo-2000-12-01-12_00.txt-1']->getFilename()
        );
    }

    /**
     * Creates Google file class mock.
     *
     * @param  array $data
     * @return \Google_Service_Drive_DriveFile
     */
    private function createGoogleFileStub(array $data)
    {
        $googleFile = $this->createMock(Google_Service_Drive_DriveFile::class);
        $googleFile->method('getName')->willReturn($data['name']);
        $googleFile->method('getId')->willReturn($data['id']);
        $googleFile->method('getSize')->willReturn($data['size']);
        $googleFile->method('getCreatedTime')->willReturn($data['last_modified']);
        return $googleFile;
    }
}
