<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * CapacityTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CapacityTest extends TestCase
{
    /**
     * Tests Quantity::setUp
     */
    public function testSetUpNoSize()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Capacity();
        $cleaner->setup(['foo' => 'bar']);
    }

    /**
     * Tests Quantity::setUp
     */
    public function testSetUpInvalidValue()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
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
        $resultStub    = $this->createMock(Result::class);
        $targetStub    = $this->createMock(Target::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '300B']);
        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testSimulateDeleteOldestFile()
    {
        $fileList   = $this->getFileMockList(
            [
                // should be deleted but not called because of simulation
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub = $this->createMock(Result::class);
        $resultStub->expects($this->once())
                   ->method('debug');


        $targetStub    = $this->createMock(Target::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '300B']);
        $cleaner->simulate($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupFileNotWritable()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => false, 'writable' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(Result::class);
        $targetStub    = $this->createMock(Target::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '300B']);

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
        $resultStub    = $this->createMock(Result::class);
        $targetStub    = $this->createMock(Target::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->expects($this->once())->method('getBackupFiles')->willReturn($fileList);

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
        $resultStub    = $this->createMock(Result::class);
        $targetStub    = $this->createMock(Target::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '0B']);
        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::simulate
     */
    public function testSimulateDeleteTarget()
    {
        $fileList   = $this->getFileMockList(
            [
                // should be deleted but not called because of simulation
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        // instead of the unlink call a debug call should be emitted.
        $resultStub = $this->createMock(Result::class);
        $resultStub->expects($this->exactly(4))
                   ->method('debug');

        $targetStub    = $this->createMock(Target::class);
        $collectorStub = $this->createMock(Local::class);
        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Capacity();
        $cleaner->setup(['size' => '0B']);
        $cleaner->simulate($targetStub, $collectorStub, $resultStub);
    }
}
