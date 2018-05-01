<?php
namespace phpbu\App\Backup\Sync;

/**
 * DropboxTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class DropboxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Dropbox::setUp
     */
    public function testSetUpOk()
    {
        $dropbox = new Dropbox();
        $dropbox->setup([
            'token' => 'this-is-no-token',
            'path'  => '/'
        ]);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Dropbox::setup
     */
    public function testSlasherizePath()
    {
        $msg = 'sync backup to dropbox' . PHP_EOL
             . '  token:    ********' . PHP_EOL
             . '  location: /foo/' . PHP_EOL;

        $dropbox = new Dropbox();
        $dropbox->setup([
            'token' => 'this-is-no-token',
            'path'  => 'foo'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
                   ->method('debug')
                   ->with($this->equalTo($msg));

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $dropbox->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Dropbox::simulate
     */
    public function testSimulate()
    {
        $dropbox = new Dropbox();
        $dropbox->setup([
            'token' => 'this-is-no-token',
            'path'  => '/'
        ]);

        $resultStub = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->once())
            ->method('debug');

        $targetStub = $this->createMock(\phpbu\App\Backup\Target::class);

        $dropbox->simulate($targetStub, $resultStub);
    }

    /**
     * Tests Dropbox::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoToken()
    {
        $dropbox = new Dropbox();
        $dropbox->setup(['path' => '/']);
    }

    /**
     * Tests Dropbox::setUp
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSetUpNoPath()
    {
        $dropbox = new Dropbox();
        $dropbox->setup(['token' => 'this-is-no-token']);
    }
}
