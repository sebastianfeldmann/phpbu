<?php
namespace phpbu\Backup\Cleaner;

require_once __DIR__ . '/TestCase.php';

/**
 * CapacityTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class CapacityTest extends TestCase
{
    /**
     * Tests Quantity::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpNoSize()
    {
        $cleaner = new Capacity();
        $cleaner->setup(array('foo' => 'bar'));
    }

    /**
     * Tests Quantity::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Capacity();
        $cleaner->setup(array('size' => '10'));
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteOldestFile()
    {
        $fileList      = $this->getFileMockList(
                            array(
                                array('size' => 100, 'shouldBeDeleted' => true),
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
        $targetStub->method('getSize')->willReturn(100);

        $cleaner = new Capacity();
        $cleaner->setup(array('size' => '400B'));

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
        $targetStub->method('getSize')->willReturn(100);

        $cleaner = new Capacity();
        $cleaner->setup(array('size' => '1M'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Capacity::cleanup
     */
    public function testCleanupDeleteTarget()
    {
        $fileList      = $this->getFileMockList(
            array(
                array('size' => 100, 'shouldBeDeleted' => true),
                array('size' => 100, 'shouldBeDeleted' => true),
                array('size' => 100, 'shouldBeDeleted' => true),
                array('size' => 100, 'shouldBeDeleted' => true),
                array('size' => 100, 'shouldBeDeleted' => true),
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

        $collectorStub->method('getBackupFiles')
                      ->willReturn($fileList);
        $targetStub->method('getSize')
                   ->willReturn(100);
        $targetStub->expects($this->once())
                   ->method('unlink');

        $cleaner = new Capacity();
        $cleaner->setup(array('size' => '0B', 'deleteTarget' => 'true'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
