<?php
namespace phpbu\App\Cli\Executable;

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
class RedisCliTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests RedisCli::getProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testNoCommand()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $redis = new RedisCli($path);
        $redis->getCommandLine();
    }

    /**
     * Tests RedisCli::getProcess
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testInvalidCommand()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $redis = new RedisCli($path);
        $redis->runCommand('foo');
    }

    /**
     * Tests RedisCli::getProcess
     */
    public function testBackup()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $redis = new RedisCli($path);
        $redis->backup();

        $this->assertEquals($path . '/redis-cli BGSAVE', $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::getProcess
     */
    public function testLastBackup()
    {
        $path  = realpath(__DIR__ . '/../../../_files/bin');
        $redis = new RedisCli($path);
        $redis->lastBackupTime();

        $this->assertEquals($path . '/redis-cli LASTSAVE', $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::createProcess
     */
    public function testPassword()
    {
        $expected = 'redis-cli -a \'fooBarBaz\' BGSAVE';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $redis  = new RedisCli($path);
        $redis->backup()->usePassword('fooBarBaz');

        $this->assertEquals($path . '/' . $expected, $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::createProcess
     */
    public function testHost()
    {
        $expected = 'redis-cli -h \'example.com\' BGSAVE';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $redis  = new RedisCli($path);
        $redis->backup()->useHost('example.com');

        $this->assertEquals($path . '/' . $expected, $redis->getCommandLine());
    }

    /**
     * Tests RedisCli::createProcess
     */
    public function testPort()
    {
        $expected = 'redis-cli -p \'1313\' BGSAVE';
        $path     = realpath(__DIR__ . '/../../../_files/bin');
        $redis  = new RedisCli($path);
        $redis->backup()->usePort(1313);

        $this->assertEquals($path . '/' . $expected, $redis->getCommandLine());
    }
}
