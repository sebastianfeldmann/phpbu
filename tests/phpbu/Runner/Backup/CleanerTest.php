<?php
namespace phpbu\App\Runner\Backup;

use phpbu\App\Backup\Cleaner\Exception;

/**
 * Cleaner Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class CleanerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Cleaner::run
     */
    public function testCleanupSuccessful()
    {
        $cleaner = $this->createMock(\phpbu\App\Backup\Cleaner::class);
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
        $cleaner = $this->createMock(\phpbu\App\Backup\Cleaner::class);
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
        $cleaner = $this->createMock(\phpbu\App\Backup\Cleaner\Simulator::class);
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
        $target = $this->createMock(\phpbu\App\Backup\Target::class);
        return $target;
    }

    /**
     * Create Collector mock.
     *
     * @return \phpbu\App\Backup\Collector
     */
    protected function getCollectorMock()
    {
        $collector = $this->createMock(\phpbu\App\Backup\Collector::class);
        return $collector;
    }

    /**
     * Create Result mock.
     *
     * @return \phpbu\App\Result
     */
    protected function getResultMock()
    {
        $result = $this->createMock(\phpbu\App\Result::class);
        return $result;
    }
}
