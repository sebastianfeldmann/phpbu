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
class CheckTest extends \PHPUnit_Framework_TestCase
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
        $config    = new Configuration\Backup\Check('SizeMin', '10M');
        $runner    = new Check();
        $runner->run($check, $config, $target, $collector, $result);

        $this->assertFalse($runner->hasFailed(), 'check should not fail');
    }

    /**
     * Tests Check::run
     */
    public function testCheckFailingByResult()
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
        $config    = new Configuration\Backup\Check('SizeMin', '10M');
        $runner    = new Check();
        $runner->run($check, $config, $target, $collector, $result);

        $this->assertTrue($runner->hasFailed(), 'check should fail');
    }

    /**
     * Tests Check::run
     */
    public function testCheckFailingByException()
    {
        $check = $this->getMockBuilder('\\phpbu\\App\\Backup\\Check')
                      ->disableOriginalConstructor()
                      ->getMock();
        $check->expects($this->once())
              ->method('pass')
              ->will($this->throwException(new Exception('foo')));

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $collector = $this->getCollectorMock();
        $config    = new Configuration\Backup\Check('SizeMin', '10M');
        $runner    = new Check();
        $runner->run($check, $config, $target, $collector, $result);

        $this->assertTrue($runner->hasFailed(), 'check should fail');
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
