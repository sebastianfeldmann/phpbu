<?php
namespace phpbu\Backup\Cleaner;

/**
 * Quantity Test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class QuantityTest extends TestCase
{
    /**
     * Tests Capacity::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpNoAmout()
    {
        $cleaner = new Quantity();
        $cleaner->setup(array('foo' => 'bar'));
    }

    /**
     * Tests Capacity::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Quantity();
        $cleaner->setup(array('amount' => 'false'));
    }

    /**
     * Tests Capacity::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpAmountToLow()
    {
        $cleaner = new Quantity();
        $cleaner->setup(array('amount' => '0'));
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteFiles()
    {
        $fileList      = $this->getFileMockList(
            array(
                array('size' => 100, 'shouldBeDeleted' => true),
                array('size' => 100, 'shouldBeDeleted' => true),
                array('size' => 100, 'shouldBeDeleted' => false),
                array('size' => 100, 'shouldBeDeleted' => false),
            )
        );
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(array('amount' => '3'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteNoFile()
    {
        $fileList      = $this->getFileMockList(
            array(
                array('size' => 100, 'shouldBeDeleted' => false),
                array('size' => 100, 'shouldBeDeleted' => false),
                array('size' => 100, 'shouldBeDeleted' => false),
                array('size' => 100, 'shouldBeDeleted' => false),
                array('size' => 100, 'shouldBeDeleted' => false),
            )
        );
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Quantity();
        $cleaner->setup(array('amount' => '10'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
