<?php
namespace phpbu\App\Runner\Backup;

use phpbu\App\Backup\Crypter\Exception;

/**
 * Crypter Runner test
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class CrypterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Crypter::run
     */
    public function testCryptSuccessful()
    {
        $crypter = $this->createMock(\phpbu\App\Backup\Crypter::class);
        $crypter->expects($this->once())
                ->method('crypt');

        $target = $this->getTargetMock();
        $result = $this->getResultMock();
        $runner = new Crypter();
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
        $crypter = $this->createMock(\phpbu\App\Backup\Crypter::class);
        $crypter->expects($this->once())
                ->method('crypt')
                ->will($this->throwException(new Exception));

        $target = $this->getTargetMock();
        $result = $this->getResultMock();
        $runner = new Crypter();
        $runner->setSimulation(false);
        $runner->run($crypter, $target, $result);
    }

    /**
     * Tests Crypter::run
     */
    public function testCryptSimulation()
    {
        $crypter = $this->createMock(\phpbu\App\Backup\Crypter\Simulator::class);
        $crypter->expects($this->once())
                ->method('simulate');

        $target = $this->getTargetMock();
        $result = $this->getResultMock();
        $runner = new Crypter();
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
        $target = $this->createMock(\phpbu\App\Backup\Target::class);
        return $target;
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
