<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\CliMockery;
use phpbu\App\BaseMockery;
use phpbu\App\Configuration;
use SebastianFeldmann\Cli\Command\Result as CommandResult;
use SebastianFeldmann\Cli\Command\Runner\Result as RunnerResult;
use PHPUnit\Framework\TestCase;

/**
 * McryptTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://www.phpbu.de/
 * @since      Class available since Release 1.1.5
 */
class McryptTest extends TestCase
{
    use BaseMockery;
    use CliMockery;

    /**
     * @var Mcrypt
     */
    protected $mcrypt;

    /**
     * Tests Mcrypt::setUp
     */
    public function testSetUpOk()
    {
        $mcrypt = new Mcrypt();
        $mcrypt->setup(['key' => 'fooBarBaz', 'algorithm' => 'blowfish']);

        $this->assertTrue(true, 'no exception should occur');
    }

    /**
     * Tests Mcrypt::setUp
     */
    public function testSetUpNoKeyOrKeyFile()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $mcrypt = new Mcrypt();
        $mcrypt->setup(['algorithm' => 'blowfish']);
    }

    /**
     * Tests Mcrypt::setUp
     */
    public function testSetUpNoAlgorithm()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $mcrypt = new Mcrypt();
        $mcrypt->setup(['k' => 'fooBarBaz']);
    }

    /**
     * Tests Mcrypt::getExecutable
     */
    public function testKeyAndAlgorithm()
    {
        $target   = $this->createTargetMock('/foo/bar.txt');
        $mcrypt   = new Mcrypt();
        $mcrypt->setup(['pathToMcrypt' => PHPBU_TEST_BIN, 'key' => 'fooBarBaz', 'algorithm' => 'blowfish']);

        $executable = $mcrypt->getExecutable($target);
        $expected = PHPBU_TEST_BIN . '/mcrypt -u -k \'fooBarBaz\' -a \'blowfish\' \'/foo/bar.txt\'';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests Mcrypt::getExecutable
     */
    public function testKeyFile()
    {
        Configuration::setWorkingDirectory('/foo');

        $target   = $this->createTargetMock('/foo/bar.txt');
        $mcrypt   = new Mcrypt();
        $mcrypt->setup(['pathToMcrypt' => PHPBU_TEST_BIN, 'keyFile' => '/foo/my.key', 'algorithm' => 'blowfish']);

        $executable = $mcrypt->getExecutable($target);
        $expected   = PHPBU_TEST_BIN . '/mcrypt -u -f \'/foo/my.key\' -a \'blowfish\' \'/foo/bar.txt\'';

        $this->assertEquals($expected, $executable->getCommand());
    }

    /**
     * Tests Mcrypt::crypt
     */
    public function testCryptOk()
    {
        $commandResult = new CommandResult('foo', 0);
        $runnerResult  = new RunnerResult($commandResult);

        $runner = $this->createMock(\SebastianFeldmann\Cli\Command\Runner::class);
        $runner->method('run')->willReturn($runnerResult);

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->getAppResultMock();

        $appResult->expects($this->once())->method('debug');

        $mcrypt = new Mcrypt($runner);
        $mcrypt->setup(['pathToMcrypt' => PHPBU_TEST_BIN, 'keyFile' => '/foo/my.key', 'algorithm' => 'blowfish']);
        $mcrypt->crypt($target, $appResult);
    }

    /**
     * Tests Mcrypt::crypt
     */
    public function testCryptFail()
    {
        $this->expectException('phpbu\App\Backup\Crypter\Exception');
        $commandResult = new CommandResult('foo', 1);
        $runnerResult  = new RunnerResult($commandResult);

        $runner = $this->createMock(\SebastianFeldmann\Cli\Command\Runner::class);
        $runner->method('run')->willReturn($runnerResult);

        $target    = $this->createTargetMock(__FILE__);
        $appResult = $this->createMock(\phpbu\App\Result::class);

        $appResult->expects($this->once())->method('debug');

        $mcrypt = new Mcrypt($runner);
        $mcrypt->setup(['pathToMcrypt' => PHPBU_TEST_BIN, 'keyFile' => '/foo/my.key', 'algorithm' => 'blowfish']);
        $mcrypt->crypt($target, $appResult);
    }

    /**
     * Tests Mcrypt::getSuffix
     */
    public function testGetSuffix()
    {
        $mcrypt = new Mcrypt();
        $suffix = $mcrypt->getSuffix();
        $this->assertEquals('nc', $suffix);
    }
}
