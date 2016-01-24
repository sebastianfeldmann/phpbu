<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Backup\Cleaner\Exception;

/**
 * Cleaner Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class CleanerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Cleaner::run
     */
    public function testCleanupSuccessful()
    {
        $cleaner = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cleaner')
                        ->disableOriginalConstructor()
                        ->getMock();
        $cleaner->expects($this->once())
                ->method('cleanup');

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Cleaner();
        $runner->setSimulation(false);
        $runner->run($cleaner, $target, $collector, $result);
    }

    /**
     * Tests Cleaner::run
     *
     * @expectedException \phpbu\App\Backup\Cleaner\Exception
     */
    public function testCleanupFailing()
    {
        $cleaner = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cleaner')
                        ->disableOriginalConstructor()
                        ->getMock();
        $cleaner->expects($this->once())
                ->method('cleanup')
                ->will($this->throwException(new Exception));

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Cleaner();
        $runner->setSimulation(false);
        $runner->run($cleaner, $target, $collector, $result);
    }

    /**
     * Tests Cleaner::run
     */
    public function testCleanupSimulation()
    {
        $cleaner = $this->getMockBuilder('\\phpbu\\App\\Backup\\Cleaner\\Simulator')
                        ->disableOriginalConstructor()
                        ->getMock();
        $cleaner->expects($this->once())
                ->method('simulate');

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Cleaner();
        $runner->setSimulation(true);
        $runner->run($cleaner, $target, $collector, $result);
    }

    /**
     * Create Target mock.
     *
     * @return \phpbu\App\Backup\Target
     */
    protected function getTargetMock()
    {
        $target = $this->getMockBuilder('\\phpbu\\App\\Backup\\Target')
                       ->disableOriginalConstructor()
                       ->getMock();
        return $target;
    }

    /**
     * Create Collector mock.
     *
     * @return \phpbu\App\Backup\Collector
     */
    protected function getCollectorMock()
    {
        $collector = $this->getMockBuilder('\\phpbu\\App\\Backup\\Collector')
            ->disableOriginalConstructor()
            ->getMock();
        return $collector;
    }

    /**
     * Create Result mock.
     *
     * @return \phpbu\App\Result
     */
    protected function getResultMock()
    {
        $result = $this->getMockBuilder('\\phpbu\\App\\Result')
                       ->disableOriginalConstructor()
                       ->getMock();
        return $result;
    }
}
