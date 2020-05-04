<?php
namespace phpbu\Backup\Sync;

use phpbu\App\Backup\Sync\Exception;
use phpbu\App\Backup\Sync\OpenStack;
use PHPUnit\Framework\TestCase;

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
class OpenStackTest extends TestCase
{
    /**
     * Tests OpenStack::setUp
     */
    public function testSetUpOk()
    {
        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests OpenStack::simulate
     */
    public function testSimulate()
    {
        $openStack = new OpenStack();
        $openStack->setup([
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

        $openStack->simulate($targetStub, $resultStub);
    }

    /**
     * Tests OpenStack::setUp
     */
    public function testSetUpNoAuthUrl()
    {
        $this->expectException(Exception::class);

        $openStack = new OpenStack();
        $openStack->setup([
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests OpenStack::setUp
     */
    public function testSetUpNoRegion()
    {
        $this->expectException(Exception::class);

        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests OpenStack::setUp
     */
    public function testSetUpNoUsername()
    {
        $this->expectException(Exception::class);

        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests OpenStack::setUp
     */
    public function testSetUpNoPassword()
    {
        $this->expectException(Exception::class);

        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'container_name' => 'container',
        ]);
    }

    /**
     * Tests OpenStack::setUp
     */
    public function testSetUpNoContainerName()
    {
        $this->expectException(Exception::class);

        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
        ]);
    }

    /**
     * Tests OpenStack::getUploadPath
     */
    public function testGetUploadPathFixingSlashes()
    {
        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
            'path'           => '/dir',
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('dir/foo.zip', $openStack->getUploadPath($targetStub));
    }

    /**
     * Tests OpenStack::getUploadPath
     */
    public function testGetUploadWithEmptyPath()
    {
        $openStack = new OpenStack();
        $openStack->setup([
            'auth_url'       => 'some-auth-url',
            'region'         => 'region-name',
            'username'       => 'some-user',
            'password'       => 'secret',
            'container_name' => 'container',
        ]);

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);
        $targetStub->expects($this->once())->method('getFilename')->willReturn('foo.zip');

        $this->assertEquals('foo.zip', $openStack->getUploadPath($targetStub));
    }
}
