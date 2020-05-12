<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\BaseMockery;
use PHPUnit\Framework\TestCase;

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
class DropboxTest extends TestCase
{
    use BaseMockery;

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
             . '  location: /foo' . PHP_EOL;

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
     * Tests Dropbox::sync
     */
    public function testSync()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->once())->method('debug');

        $metaMock = $this->createMock(\Kunnu\Dropbox\Models\FileMetadata::class);
        $metaMock->expects($this->once())->method('getSize')->willReturn(12345678);

        $clientMock = $this->createMock(\Kunnu\Dropbox\Dropbox::class);
        $clientMock->expects($this->once())->method('upload')->willReturn($metaMock);

        $dropbox = $this->createPartialMock(Dropbox::class, ['createClient']);
        $dropbox->method('createClient')->willReturn($clientMock);

        $dropbox->setup([
            'token' => 'this-is-no-token',
            'path'  => '/'
        ]);

        $dropbox->sync($target, $result);
    }

    /**
     * Tests Dropbox::sync
     */
    public function testSyncWithCleanup()
    {
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);
        $result->expects($this->exactly(2))->method('debug');

        $metaMock = $this->createMock(\Kunnu\Dropbox\Models\FileMetadata::class);
        $metaMock->expects($this->once())->method('getSize')->willReturn(12345678);

        $metaCollectionMock = $this->createMock(\Kunnu\Dropbox\Models\MetadataCollection::class);
        $metaCollectionMock->expects($this->once())->method('getItems')->willReturn([]);

        $clientMock = $this->createMock(\Kunnu\Dropbox\Dropbox::class);
        $clientMock->expects($this->once())->method('upload')->willReturn($metaMock);
        $clientMock->expects($this->once())->method('listFolder')->willReturn($metaCollectionMock);

        $dropbox = $this->createPartialMock(Dropbox::class, ['createClient']);
        $dropbox->method('createClient')->willReturn($clientMock);

        $dropbox->setup([
            'token'          => 'this-is-no-token',
            'path'           => '/',
            'cleanup.type'   => 'quantity',
            'cleanup.amount' => 99
        ]);

        $dropbox->sync($target, $result);
    }

    /**
     * Tests Dropbox::sync
     */
    public function testSyncFail()
    {
        $this->expectException('phpbu\App\Exception');
        $target = $this->createTargetMock('foo.txt', 'foo.txt.gz');
        $result = $this->createMock(\phpbu\App\Result::class);

        $clientMock = $this->createMock(\Kunnu\Dropbox\Dropbox::class);
        $clientMock->expects($this->once())->method('upload')->will($this->throwException(new \Exception));

        $dropbox = $this->createPartialMock(Dropbox::class, ['createClient']);
        $dropbox->method('createClient')->willReturn($clientMock);

        $dropbox->setup([
            'token' => 'this-is-no-token',
            'path'  => '/'
        ]);

        $dropbox->sync($target, $result);
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
     */
    public function testSetUpNoToken()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $dropbox = new Dropbox();
        $dropbox->setup(['path' => '/']);
    }

    /**
     * Tests Dropbox::setUp
     */
    public function testSetUpNoPath()
    {
        $this->expectException('phpbu\App\Backup\Sync\Exception');
        $dropbox = new Dropbox();
        $dropbox->setup(['token' => 'this-is-no-token']);
    }
}
