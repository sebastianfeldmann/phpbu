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
        $check = $this->getMockBuilder('\\phpbu\\App\\Backup\\Check')
                      ->disableOriginalConstructor()
                      ->getMock();
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
        $check = $this->getMockBuilder('\\phpbu\\App\\Backup\\Check')
                      ->disableOriginalConstructor()
                      ->getMock();
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
        $check = $this->getMockBuilder('\\phpbu\\App\\Backup\\Check')
                      ->disableOriginalConstructor()
                      ->getMock();

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
        $check = $this->getMockBuilder('\\phpbu\\App\\Backup\\Check\\Simulator')
                      ->disableOriginalConstructor()
                      ->getMock();

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
