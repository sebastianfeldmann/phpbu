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
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpNoAmout()
    {
        $cleaner = new Quantity();
        $cleaner->setup(['foo' => 'bar']);
    }

    /**
     * Tests Capacity::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Quantity();
        $cleaner->setup(['amount' => 'false']);
    }

    /**
     * Tests Capacity::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpAmountToLow()
    {
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
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '3']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
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
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

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
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

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
                ['size' => 100, 'shouldBeDeleted' => true],
            ]
        );
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $collectorStub->expects($this->once())
                      ->method('getBackupFiles')
                      ->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(['amount' => '1']);

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
