<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

/**
 * AmazonS3Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class AmazonS3v3Test extends TestCase
{
    use BaseMockery;

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpOk()
    {
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testGetUploadPath()
    {
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('foo.zip', $amazonS3->getUploadPath($targetStub));
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testGetUploadPathAddingMissingSlashes()
    {
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => 'fiz'
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('fiz/foo.zip', $amazonS3->getUploadPath($targetStub));
    }

    /**
     * Tests AmazonS3V3::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');

        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(2))->method('debug');

        $clientMock = $this->createAWSS3Mock();
        $clientMock->expects($this->once())->method('doesBucketExist')->willReturn(false);
        $clientMock->expects($this->once())->method('__call');

        $uploaderMock = $this->createAWSS3UploaderMock();
        $uploaderMock->expects($this->once())->method('upload');

        $aws = $this->createPartialMock(AmazonS3v3::class, ['createClient', 'createUploader']);
        $aws->method('createClient')->willReturn($clientMock);
        $aws->method('createUploader')->willReturn($uploaderMock);

        $aws->setup([
            'key'                => 'some-key',
            'secret'             => 'some-secret',
            'bucket'             => 'backup',
            'region'             => 'frankfurt',
            'path'               => 'backup',
            'useMultiPartUpload' => 'true'
        ]);

        $aws->sync($target, $result);
    }

    /**
     * Tests AmazonS3V3::sync
     */
    public function testSyncWithRemoteCleanup()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');

        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(3))->method('debug');

        $clientMock = $this->createAWSS3Mock();
        $clientMock->expects($this->once())->method('doesBucketExist')->willReturn(false);
        $clientMock->expects($this->exactly(2))->method('__call')->will($this->onConsecutiveCalls(null, []));

        $uploaderMock = $this->createAWSS3UploaderMock();
        $uploaderMock->expects($this->once())->method('upload');

        $aws = $this->createPartialMock(AmazonS3v3::class, ['createClient', 'createUploader']);
        $aws->method('createClient')->willReturn($clientMock);
        $aws->method('createUploader')->willReturn($uploaderMock);

        $aws->setup([
            'key'                => 'some-key',
            'secret'             => 'some-secret',
            'bucket'             => 'backup',
            'region'             => 'frankfurt',
            'path'               => 'backup',
            'useMultiPartUpload' => 'true',
            'cleanup.type'       => 'quantity',
            'cleanup.amount'     => 99
        ]);

        $aws->sync($target, $result);
    }

    /**
     * Tests AmazonS3V3::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);

        $clientMock = $this->createAWSS3Mock();
        $clientMock->expects($this->once())->method('doesBucketExist')->willReturn(true);

        $uploaderMock = $this->createAWSS3UploaderMock();
        $uploaderMock->expects($this->once())->method('upload')->will($this->throwException(new \Exception));

        $aws = $this->createPartialMock(AmazonS3v3::class, ['createClient', 'createUploader']);
        $aws->method('createClient')->willReturn($clientMock);
        $aws->method('createUploader')->willReturn($uploaderMock);

        $aws->setup([
            'key'                => 'some-key',
            'secret'             => 'some-secret',
            'bucket'             => 'backup',
            'region'             => 'frankfurt',
            'path'               => 'backup',
            'useMultiPartUpload' => 'true'
        ]);

        $aws->sync($target, $result);
    }

    /**
     * Tests AmazonS3::simulate
     */
    public function testSimulate()
    {
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $amazonS3->simulate($targetStub, $resultStub);
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpNoKey()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpNoSecret()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpNoBucket()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpNoRegion()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'path'   => '/'
        ]);
    }

    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpNoPath()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region'
        ]);
    }

    /**
     * Create an aws s3 client mock
     * @return \Aws\S3\S3Client
     */
    private function createAWSS3Mock()
    {
        /** @var $awsMock \Aws\S3\S3Client */
        $awsMock = $this->createMock(\Aws\S3\S3Client::class);
        return $awsMock;
    }

    private function createAWSS3UploaderMock()
    {
        /** @var $awsMock \Aws\S3\S3Client */
        $awsMock = $this->createMock(\Aws\S3\MultipartUploader::class);
        return $awsMock;
    }
}
