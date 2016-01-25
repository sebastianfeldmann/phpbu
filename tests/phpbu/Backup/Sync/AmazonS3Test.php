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
class AmazonS3Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests AmazonS3::setUp
     */
    public function testSetUpOk()
    {
        $amazonS3 = new AmazonS3();
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
     * Tests AmazonS3::simulate
     */
    public function testSimulate()
    {
        $amazonS3 = new AmazonS3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region',
            'path'   => '/'
        ]);

        $resultStub = $this->getMockBuilder('\\phpbu\\App\\Result')
                           ->getMock();
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                           ->disableOriginalConstructor()
                           ->getMock();

        $amazonS3->simulate($targetStub, $resultStub);
    }

    /**
     * Tests AmazonS3::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoKey()
    {
        $amazonS3 = new AmazonS3();
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
        $amazonS3 = new AmazonS3();
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
        $amazonS3 = new AmazonS3();
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
        $amazonS3 = new AmazonS3();
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
        $amazonS3 = new AmazonS3();
        $amazonS3->setup([
            'key'    => 'dummy-key',
            'secret' => 'dummy-secret',
            'bucket' => 'dummy-bucket',
            'region' => 'dummy-region'
        ]);
    }
}
