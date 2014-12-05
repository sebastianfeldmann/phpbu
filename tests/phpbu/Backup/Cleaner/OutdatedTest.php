<?php
namespace phpbu\Backup\Cleaner;

require_once __DIR__ . '/TestCase.php';

/**
 * OutdatedTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class OutdatedTest extends TestCase
{
    /**
     * Tests Outdated::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpNoOlder()
    {
        $cleaner = new Outdated();
        $cleaner->setup(array('foo' => 'bar'));
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Outdated();
        $cleaner->setup(array('older' => 'false'));
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException phpbu\Backup\Cleaner\Exception
     */
    public function testSetUpAmountToLow()
    {
        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '0S'));
    }

    /**
     * Tests Outdated::cleanup
     */
    public function testCleanupDeleteFiles()
    {
        $fileList      = $this->getFileMockList(
            array(
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => true,
                    'mTime'           => $this->getMTime('4d'),
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2d'),
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('1d'),
                ),
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

        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '3d'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Outdated::cleanup
     */
    public function testCleanupDeleteNoFile()
    {
        $fileList      = $this->getFileMockList(
            array(
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('4d'),
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('2d'),
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('1d'),
                ),
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

        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '5d'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
