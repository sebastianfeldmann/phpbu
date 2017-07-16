<?php
namespace phpbu\App\Backup\Sync;

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
class AmazonS3v3Test extends \PHPUnit\Framework\TestCase
{
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
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoKey()
    {
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
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoSecret()
    {
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
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoBucket()
    {
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
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoRegion()
    {
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
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $amazonS3 = new AmazonS3v3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region'
        ]);
    }
}
