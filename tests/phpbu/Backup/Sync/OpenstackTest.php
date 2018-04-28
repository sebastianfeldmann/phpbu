<?php
namespace phpbu\Backup\Sync;

use phpbu\App\Backup\Sync\Openstack;

/**
 * OpenStackTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 5.1
 */
class OpenstackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Openstack::setUp
     */
    public function testSetUpOk()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Openstack::simulate
     */
    public function testSimulate()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
            ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $openstack->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Openstack::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoAuthUrl()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests Openstack::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoRegion()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests Openstack::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoUsername()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests Openstack::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPassword()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests Openstack::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoContainerName()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
        ]);
    }

    /**
     * Tests Openstack::getUploadPath
     */
    public function testGetUploadPathFixingSlashes()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
            'path'           => '/dir',
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('dir/foo.zip', $openstack->getUploadPath($targetStub));
    }

    /**
     * Tests Openstack::getUploadPath
     */
    public function testGetUploadWithEmptyPath()
    {
        $openstack = new Openstack();
        $openstack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('foo.zip', $openstack->getUploadPath($targetStub));
    }
}
