<?php
namespace phpbu\App\Backup\Sync;

/**
 * SoftLayerTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Petr Cervenka <petr@nanosolutions.io>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class SoftLayerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests SoftLayer::setUp
     */
    public function testSetUpOk()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup([
            'user'      => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests SoftLayer::simulate
     */
    public function testSimulate()
    {
        $softLayer = new SoftLayer();
        $softLayer->setup([
            'user'      => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $softLayer->simulate($targetStub, $resultStub);
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoUsername()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup([
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ]);
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoSecret()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup([
            'user'      => 'dummy-username',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ]);
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoContainer()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup([
            'user'   => 'dummy-username',
            'secret' => 'dummy-secret',
            'host'   => 'dummy-host',
            'path'   => '/'
        ]);
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoHost()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup([
            'user'      => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'path'      => '/'
        ]);
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup([
            'user'      => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host'
        ]);
    }
}
