<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Backup\Check\Exception;

/**
 * Check Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class CheckTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Check::run
     */
    public function testCheckSuccessful()
    {
        $check = $this->createMock(\phpbu\App\Backup\Check::class);
        $check->expects($this->once())
              ->method('pass')
              ->willReturn(true);

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Check();
        $runner->setSimulation(false);

        $pass = $runner->run($check, $target, '10M', $collector, $result);
        $this->assertTrue($pass, 'check should not fail');
    }

    /**
     * Tests Check::run
     */
    public function testCheckFailed()
    {
        $check = $this->createMock(\phpbu\App\Backup\Check::class);
        $check->expects($this->once())
              ->method('pass')
              ->willReturn(false);

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Check();
        $runner->setSimulation(false);

        $pass = $runner->run($check, $target, '10M', $collector, $result);
        $this->assertFalse($pass, 'check should fail');
    }

    /**
     * Tests Check::run
     */
    public function testCheckSimulationNoSimulator()
    {
        $check = $this->createMock(\phpbu\App\Backup\Check::class);

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Check();
        $runner->setSimulation(true);

        $pass = $runner->run($check, $target, '10M', $collector, $result);
        $this->assertTrue($pass, 'check should succeed');
    }

    /**
     * Tests Check::run
     */
    public function testCheckSimulation()
    {
        $check = $this->createMock(\phpbu\App\Backup\Check\Simulator::class);

        $check->expects($this->once())
              ->method('simulate')
              ->willReturn(true);

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $runner    = new Check();
        $runner->setSimulation(true);

        $pass = $runner->run($check, $target, '10M', $collector, $result);
        $this->assertTrue($pass, 'check should succeed');
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
