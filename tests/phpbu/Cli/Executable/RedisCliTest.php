<?php
namespace phpbu\App\Cli\Executable;

use PHPUnit\Framework\TestCase;

/**
 * RedisCli ExecutableTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class RedisCliTest extends TestCase
{
    /**
     * Tests RedisCli::getProcess
     */
    public function testNoCommand()
    {
        $this->expectException('phpbu\App\Exception');
        $redis = new RedisCli(PHPBU_TEST_BIN);
        $redis->getCommandLine();
    }

    /**
     * Tests RedisCli::getProcess
     */
    public function testInvalidCommand()
    {
        $this->expectException('phpbu\App\Exception');
        $redis = new RedisCli(PHPBU_TEST_BIN);
        $redis->runCommand('foo');
    }

    /**
     * Tests RedisCli::getProcess
     */
    public function testBackup()
    {
        $redis = new RedisCli(PHPBU_TEST_BIN);
        $redis->backup();

        $this->assertEquals(PHPBU_TEST_BIN . '/redis-cli BGSAVE', $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::getProcess
     */
    public function testLastBackup()
    {
        $redis = new RedisCli(PHPBU_TEST_BIN);
        $redis->lastBackupTime();

        $this->assertEquals(PHPBU_TEST_BIN . '/redis-cli LASTSAVE', $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::createCommandLine
     */
    public function testPassword()
    {
        $expected = 'redis-cli -a \'fooBarBaz\' BGSAVE';
        $redis  = new RedisCli(PHPBU_TEST_BIN);
        $redis->backup()->usePassword('fooBarBaz');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::createCommandLine
     */
    public function testHost()
    {
        $expected = 'redis-cli -h \'example.com\' BGSAVE';
        $redis  = new RedisCli(PHPBU_TEST_BIN);
        $redis->backup()->useHost('example.com');

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::createCommandLine
     */
    public function testPort()
    {
        $expected = 'redis-cli -p \'1313\' BGSAVE';
        $redis  = new RedisCli(PHPBU_TEST_BIN);
        $redis->backup()->usePort(1313);

        $this->assertEquals(PHPBU_TEST_BIN . '/' . $expected, $redis->getCommandLine());
    }
}
