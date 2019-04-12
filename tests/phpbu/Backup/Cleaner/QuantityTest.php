<?php
namespace phpbu\App\Backup\Cleaner;

/**
 * Quantity Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class QuantityTest extends TestCase
{
    /**
     * Tests Capacity::setUp
     */
    public function testSetUpNoAmount()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Quantity();
        $cleaner->setup(['foo' => 'bar']);
    }

    /**
     * Tests Capacity::setUp
     */
    public function testSetUpInvalidValue()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Quantity();
        $cleaner->setup(['amount' => 'false']);
    }

    /**
     * Tests Capacity::setUp
     */
    public function testSetUpAmountToLow()
    {
        $this->expectException('phpbu\App\Backup\Cleaner\Exception');
        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '0']);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteFiles()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => true],
                ['size' => 100, 'shouldBeDeleted' => false],
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '2']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
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
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '3']);

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

        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '10']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteFilesCountingCurrentBackup()
    {
        $fileList      = $this->getFileMockList(
            [
                ['size' => 100, 'shouldBeDeleted' => false],
            ]
        );
        $resultStub    = $this->createMock(\phpbu\App\Result::class);
        $collectorStub = $this->createMock(\phpbu\App\Backup\Collector\Local::class);
        $targetStub    = $this->createMock(\phpbu\App\Backup\Target::class);

        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '1']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
