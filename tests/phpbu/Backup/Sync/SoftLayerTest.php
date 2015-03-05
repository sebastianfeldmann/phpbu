<?php
namespace phpbu\Backup\Sync;

/**
 * SoftLayerTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Petr Cervenka <petr@nanosolutions.io>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class SoftLayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests SoftLayer::setUp
     */
    public function testSetUpOk()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup(array(
            'username'  => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ));

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoUsername()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup(array(
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ));
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoSecret()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup(array(
            'username'  => 'dummy-username',
            'container' => 'dummy-container',
            'host'      => 'dummy-host',
            'path'      => '/'
        ));
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNocontainer()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup(array(
            'username' => 'dummy-username',
            'secret'   => 'dummy-secret',
            'host'     => 'dummy-host',
            'path'     => '/'
        ));
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoHost()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup(array(
            'username'  => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'path'      => '/'
        ));
    }

    /**
     * Tests SoftLayer::setUp
     *
     * @expectedException \phpbu\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $SoftLayer = new SoftLayer();
        $SoftLayer->setup(array(
            'username'  => 'dummy-username',
            'secret'    => 'dummy-secret',
            'container' => 'dummy-container',
            'host'      => 'dummy-host'
        ));
    }
}
