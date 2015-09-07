<?php
namespace phpbu\App\Backup\Cleaner;

/**
 * OutdatedTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class OutdatedTest extends TestCase
{
    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpNoOlder()
    {
        $cleaner = new Outdated();
        $cleaner->setup(array('foo' => 'bar'));
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpInvalidValue()
    {
        $cleaner = new Outdated();
        $cleaner->setup(array('older' => 'false'));
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpAmountToLow()
    {
        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '0S'));
    }

    /**
     * Tests Outdated::setUp
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testSetUpOlderToLow()
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
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
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
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '5d'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }

    /**
     * Tests Outdated::cleanup
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testCleanupNotWritable()
    {
        $fileList      = $this->getFileMockList(
            array(
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('4d'),
                    'writable'        => false,
                ),
                array(
                    'size'            => 100,
                    'shouldBeDeleted' => false,
                    'mTime'           => $this->getMTime('3d'),
                ),
            )
        );
        $resultStub    = $this->getMockBuilder('\\phpbu\\App\\Result')
                              ->getMock();
        $collectorStub = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
                              ->disableOriginalConstructor()
                              ->getMock();
        $targetStub    = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                              ->disableOriginalConstructor()
                              ->getMock();

        $collectorStub->method('getBackupFiles')->willReturn($fileList);

        $cleaner = new Outdated();
        $cleaner->setup(array('older' => '3d'));

        $cleaner->cleanup($targetStub, $collectorStub, $resultStub);
    }
}
