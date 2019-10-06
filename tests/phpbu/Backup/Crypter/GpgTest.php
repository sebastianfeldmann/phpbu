<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\CliMockery;
use phpbu\App\Backup\Restore\Plan;
use phpbu\App\BaseMockery;
use phpbu\App\Configuration;
use PHPUnit\Framework\TestCase;

/**
 * GpgTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 6.0.1
 */
class GpgTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * Tests Gpg::setUp
     */
    public function testSetUpOk()
    {
        $openSSL = new Gpg();
        $openSSL->setup(['pathToPgp' => PHPBU_TEST_BIN, 'user' => 'foo']);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Gpg::setUp
     */
    public function testSetUpNoUser()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $openSSL = new Gpg();
        $openSSL->setup([]);
    }

    /**
     * Tests OpenSSL::crypt
     */
    public function testCryptOk()
    {
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(0, 'openssl'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $openSSL = new Gpg($runner);
        $openSSL->setup(['pathToGpg' => PHPBU_TEST_BIN, 'user' => 'demo']);
        $openSSL->crypt($target, $appResult);
    }

    /**
     * Tests Gpg::simulate
     */
    public function testSimulate()
    {
        $runner = $this->getRunnerMock();

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $openSSL = new Gpg($runner);
        $openSSL->setup(['pathToGpg' => PHPBU_TEST_BIN, 'user' => 'demo']);
        $openSSL->simulate($target, $appResult);
    }

    /**
     * Tests Gpg::restore
     */
    public function testRestore()
    {
        $runner = $this->getRunnerMock();

        $target = $this->createTargetMock(__FILE__);
        $plan   = new Plan();

        $openSSL = new Gpg($runner);
        $openSSL->setup(['pathToGpg' => PHPBU_TEST_BIN, 'user' => 'demo']);
        $openSSL->restore($target, $plan);

        $this->assertCount(1, $plan->getDecryptionCommands());
    }

    /**
     * Tests OpenSSL::crypt
     */
    public function testCryptFail()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $runner = $this->getRunnerMock();
        $runner->expects($this->once())
               ->method('run')
               ->willReturn($this->getRunnerResultMock(1, 'gpg'));

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $openSSL = new Gpg($runner);
        $openSSL->setup(['pathToGpg' => PHPBU_TEST_BIN, 'user' => 'demo']);
        $openSSL->crypt($target, $appResult);
    }

    /**
     * Tests Gpg::getSuffix
     */
    public function testGetSuffix()
    {
        $openSSL = new Gpg();
        $suffix  = $openSSL->getSuffix();
        $this->assertEquals('gpg', $suffix);
    }
}
