<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * Google Drive sync test.
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
    use BaseMockery;

    /**
     * Tests GoogleDrive::setUp
     */
    public function testSetUpOk()
    {
        $sync = new GoogleDrive();
        $sync->setup([
            'secret'   => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'access'   => PHPBU_TEST_FILES . '/misc/google_credentials.json',
            'parentId' => 'A12345'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests GoogleDrive::simulate
     */
    public function testSimulate()
    {
        $sync = new GoogleDrive();
        $sync->setup([
            'secret'   => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'access'   => PHPBU_TEST_FILES . '/misc/google_credentials.json',
            'parentId' => 'A12345'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $sync->simulate($targetStub, $resultStub);
    }

    /**
     * Tests GoogleDrive::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock(PHPBU_TEST_FILES . '/misc/backup.txt');
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->once())->method('debug');

        $client  = $this->createMock(\Google_Client::class);
        $request = $this->createMock(\Psr\Http\Message\RequestInterface::class);

        $resource = $this->createMock(\Google_Service_Drive_Resource_Files::class);
        $resource->expects($this->once())
                 ->method('create')
                 ->willReturn($request);

        $service        = $this->createMock(\Google_Service_Drive::class);
        $service->files = $resource;
        $service->method('getClient')->willReturn($client);

        $status = $this->createMock(\Google_Service_Drive_DriveFile::class);
        $status->expects($this->once())->method('getId')->willReturn('A12345');

        $stream = $this->createMock(\Google_Http_MediaFileUpload::class);
        $stream->expects($this->once())->method('nextChunk')->willReturn($status);

        $sync = $this->createPartialMock(GoogleDrive::class, ['createDriveService', 'createUploadStream']);
        $sync->expects($this->once())->method('createDriveService')->willReturn($service);
        $sync->expects($this->once())->method('createUploadStream')->willReturn($stream);

        $sync->setup([
            'secret'   => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'access'   => PHPBU_TEST_FILES . '/misc/google_credentials.json',
            'parentId' => 'A12345'
        ]);

        $sync->sync($target, $result);
    }

    /**
     * Tests GoogleDrive::sync
     */
    public function testSyncWithCleanup()
    {
        $target = $this->createTargetMock(PHPBU_TEST_FILES . '/misc/backup.txt');
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(2))->method('debug');

        $client  = $this->createMock(\Google_Client::class);
        $request = $this->createMock(\Psr\Http\Message\RequestInterface::class);

        $fileList = $this->createMock(\Google_Service_Drive_FileList::class);
        $fileList->expects($this->once())
                 ->method('getFiles')
                 ->willReturn([]);

        $resource = $this->createMock(\Google_Service_Drive_Resource_Files::class);
        $resource->expects($this->once())
                 ->method('create')
                 ->willReturn($request);
        $resource->expects($this->once())
                 ->method('listFiles')
                 ->willReturn($fileList);

        $service        = $this->createMock(\Google_Service_Drive::class);
        $service->files = $resource;
        $service->method('getClient')->willReturn($client);

        $status = $this->createMock(\Google_Service_Drive_DriveFile::class);
        $status->expects($this->once())->method('getId')->willReturn('A12345');

        $stream = $this->createMock(\Google_Http_MediaFileUpload::class);
        $stream->expects($this->once())->method('nextChunk')->willReturn($status);

        $sync = $this->createPartialMock(GoogleDrive::class, ['createDriveService', 'createUploadStream']);
        $sync->expects($this->exactly(2))->method('createDriveService')->willReturn($service);
        $sync->expects($this->once())->method('createUploadStream')->willReturn($stream);

        $sync->setup([
            'secret'         => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'access'         => PHPBU_TEST_FILES . '/misc/google_credentials.json',
            'parentId'       => 'A12345',
            'cleanup.type'   => 'quantity',
            'cleanup.amount' => 99
        ]);

        $sync->sync($target, $result);
    }

    /**
     * Tests GoogleDrive::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');
        $target  = $this->createTargetMock(PHPBU_TEST_FILES . '/misc/backup.txt');
        $result  = $this->createMock(\phpbu\App\Result::class);
        $service = $this->createMock(\Google_Service_Drive::class);
        $service->expects($this->once())->method('getClient')->will($this->throwException(new \Exception));

        $sync = $this->createPartialMock(GoogleDrive::class, ['createDriveService', 'createUploadStream']);
        $sync->expects($this->once())->method('createDriveService')->willReturn($service);

        $sync->setup([
            'secret'         => PHPBU_TEST_FILES . '/misc/google_secret.json',
            'access'         => PHPBU_TEST_FILES . '/misc/google_credentials.json',
        ]);

        $sync->sync($target, $result);
    }

    /**
     * Tests GoogleDrive::setUp
     */
    public function testSetUpNoSecret()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sync = new GoogleDrive();
        $sync->setup(['access' => '']);
    }

    /**
     * Tests GoogleDrive::setUp
     */
    public function testSetUpNoAccess()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sync = new GoogleDrive();
        $sync->setup(['secret' => 'foo']);
    }

    /**
     * Tests GoogleDrive::setUp
     */
    public function testSetUpNoSecretFile()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sync = new GoogleDrive();
        $sync->setup(['secret' => 'foo', 'access' => PHPBU_TEST_FILES . '/misc/google_credentials.json']);
    }

    /**
     * Tests GoogleDrive::setUp
     */
    public function testSetUpNoAccessFile()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $sync = new GoogleDrive();
        $sync->setup(['secret' => PHPBU_TEST_FILES . '/misc/google_secret.json', 'access' => 'foo']);
    }
}
