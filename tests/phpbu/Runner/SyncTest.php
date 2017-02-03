<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Backup\Sync\Exception;

/**
 * Sync Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class SyncTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Sync::run
     */
    public function testSyncSuccessful()
    {
        $sync = $this->getMockBuilder('\\phpbu\\App\\Backup\\Sync')
                     ->disableOriginalConstructor()
                     ->getMock();
        $sync->expects($this->once())
             ->method('sync');

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $runner    = new Sync();
        $runner->setSimulation(false);
        $runner->run($sync, $target, $result);
    }

    /**
     * Tests Sync::run
     *
     * @expectedException \phpbu\App\Backup\Sync\Exception
     */
    public function testSyncFailing()
    {
        $sync = $this->getMockBuilder('\\phpbu\\App\\Backup\\Sync')
                     ->disableOriginalConstructor()
                     ->getMock();
        $sync->expects($this->once())
             ->method('sync')
             ->will($this->throwException(new Exception));

        $target = $this->getTargetMock();
        $result = $this->getResultMock();
        $runner = new Sync();
        $runner->setSimulation(false);
        $runner->run($sync, $target, $result);
    }

    /**
     * Tests Sync::run
     */
    public function testSyncSimulation()
    {
        $sync = $this->getMockBuilder('\\phpbu\\App\\Backup\\Sync\\Simulator')
                     ->disableOriginalConstructor()
                     ->getMock();
        $sync->expects($this->once())
             ->method('simulate');

        $target = $this->getTargetMock();
        $result = $this->getResultMock();
        $runner = new Sync();
        $runner->setSimulation(true);
        $runner->run($sync, $target, $result);
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
