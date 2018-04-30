<?php
namespace phpbu\App\Backup\Cleaner;

/**
 * CapacityTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CapacityTest extends TestCase
{
    /**
     * Tests Quantity::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpNoSize()
    {
        $cleaner = new Capacity();
        $cleaner->setup(['foo' => 'bar']);
    }

    /**
     * Tests Quantity::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Capacity();
        $cleaner->setup(['size' => '10']);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteOldestFile()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);
        $targetStub->method('getSize')->willReturn(100);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '400B']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testSimulateDeleteOldestFile()
    {
        $fileList      = $this->getFileMockList(
            [
                // should be deleted but not called because of simulation
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $resultStub->expects($this->exactly(2))
                   ->method('debug');
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);
        $targetStub->method('getSize')->willReturn(100);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '400B']);

        $cleaner->simulate($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testCleanupFileNotWritable()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => false, 'writable' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')->willReturn($fileList);
        $targetStub->method('getSize')->willReturn(100);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '400B']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteNoFile()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->expects($this->once())->method('getBackupFiles')->willReturn($fileList);
        $targetStub->expects($this->once())->method('getSize')->willReturn(100);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '1M']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteTarget()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => true],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->method('getBackupFiles')
                      ->willReturn($fileList);
        $targetStub->method('getSize')
                   ->willReturn(100);
        $targetStub->expects($this->once())
                   ->method('toFile')
                   ->willReturn($this->getFileMock(100, true, 0, true));

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '0B', 'deleteTarget' => 'true']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
