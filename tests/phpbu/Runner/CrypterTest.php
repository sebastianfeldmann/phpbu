<?php
namespace phpbu\App\Runner;

use phpbu\App\Configuration;
use phpbu\App\Backup\Crypter\Exception;

/**
 * Crypter Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class CrypterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Crypter::run
     */
    public function testCryptSuccessful()
    {
        $crypter = $this->getMockBuilder('\\phpbu\\App\\Backup\\Crypter')
                        ->disableOriginalConstructor()
                        ->getMock();
        $crypter->expects($this->once())
                ->method('crypt');

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $runner    = new Crypter();
        $runner->setSimulation(false);
        $runner->run($crypter, $target, $result);
    }

    /**
     * Tests Crypter::run
     *
     * @expectedException \phpbu\App\Backup\Crypter\Exception
     */
    public function testCryptFailing()
    {
        $crypter = $this->getMockBuilder('\\phpbu\\App\\Backup\\Crypter')
                        ->disableOriginalConstructor()
                        ->getMock();
        $crypter->expects($this->once())
                ->method('crypt')
                ->will($this->throwException(new Exception));

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $runner    = new Crypter();
        $runner->setSimulation(false);
        $runner->run($crypter, $target, $result);
    }

    /**
     * Tests Crypter::run
     */
    public function testCryptSimulation()
    {
        $crypter = $this->getMockBuilder('\\phpbu\\App\\Backup\\Crypter\\Simulator')
                        ->disableOriginalConstructor()
                        ->getMock();
        $crypter->expects($this->once())
                ->method('simulate');

        $target    = $this->getTargetMock();
        $result    = $this->getResultMock();
        $runner    = new Crypter();
        $runner->setSimulation(true);
        $runner->run($crypter, $target, $result);
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
