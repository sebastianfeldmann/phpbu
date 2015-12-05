<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\CliTest;
use phpbu\App\Backup\Compressor;

/**
 * RedisTest
 *
 * @package    phpbu
 * @subpackage tests
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class RedisTest extends CliTest
{
    /**
     * redis
     *
     * @var \phpbu\App\Backup\Source\Redis
     */
    protected $redis;

    /**
     * Setup redis
     */
    public function setUp()
    {
        $this->redis = new Redis();
    }

    /**
     * Clear redis
     */
    public function tearDown()
    {
        $this->redis = null;
    }

    /**
     * Tests Redis::setUp
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testSetupDataPathMissing()
    {
        $this->redis->setup([]);
    }

    /**
     * Tests Redis::getExecutable
     */
    public function testDefault()
    {
        $filePath = realpath(__DIR__ . '/../../../_files');
        $binPath  = $filePath . '/bin';
        $rdbPath  = $filePath . '/misc/dump.rdb';
        $target   = $this->getTargetMock('/tmp/backup.redis');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/backup.redis');

        $this->redis->setup(['pathToRedisData' => $rdbPath, 'pathToRedisCli' => $binPath]);
        $exec = $this->redis->getExecutable($target);

        $this->assertEquals($binPath . '/redis-cli BGSAVE 2> /dev/null', $exec->getCommandLine());
    }

    /**
     * Tests Redis::backup
     */
    public function testBackupOk()
    {
        $filePath = realpath(__DIR__ . '/../../../_files');
        $binPath  = $filePath . '/bin';
        $rdbPath  = $filePath . '/misc/dump.rdb';
        $target   = $this->getTargetMock('/tmp/dump.rdb');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/dump.rdb');

        $this->redis->setup(['pathToRedisData' => $rdbPath, 'pathToRedisCli' => $binPath]);

        $cliResult1 = $this->getCliResultMock(0, 'redis', ['(integer) 100000000']);
        $cliResult2 = $this->getCliResultMock(0, 'redis', ['(integer) 100000000']);
        $cliResult3 = $this->getCliResultMock(0, 'redis', ['(integer) 100000002']);

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $redis = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\RedisCli')
                      ->disableOriginalConstructor()
                      ->getMock();
        $redis->expects($this->any())
              ->method('run')
              ->will($this->onConsecutiveCalls($cliResult1, $cliResult2, $cliResult3));

        $this->redis->setExecutable($redis);

        $status = $this->redis->backup($target, $appResult);

        $this->assertEquals('/tmp/dump.rdb', $status->getDataPath());
        $this->assertEquals(false, $status->handledCompression());

        // make sure the dump is copied to the target directory
        $fileCopied = file_exists('/tmp/dump.rdb');
        $this->assertTrue($fileCopied);
        if ($fileCopied) {
            unlink('/tmp/dump.rdb');
        }
    }

    /**
     * Tests Redis::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupTimeoutFail()
    {
        $filePath = realpath(__DIR__ . '/../../../_files');
        $binPath  = $filePath . '/bin';
        $rdbPath  = $filePath . '/misc/dump.rdb';
        $target   = $this->getTargetMock('/tmp/dump.rdb');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/dump.rdb');

        $this->redis->setup(['pathToRedisData' => $rdbPath, 'pathToRedisCli' => $binPath, 'timeout' => 2]);

        $cliResult = $this->getCliResultMock(0, 'redis', ['(integer) 100000000']);

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $redis = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\RedisCli')
                      ->disableOriginalConstructor()
                      ->getMock();
        $redis->expects($this->any())
              ->method('run')
              ->willReturn($cliResult);

        $this->redis->setExecutable($redis);
        $this->redis->backup($target, $appResult);
    }

    /**
     * Tests Redis::backup
     *
     * @expectedException \phpbu\App\Exception
     */
    public function testBackupSaveFail()
    {
        $filePath = realpath(__DIR__ . '/../../../_files');
        $binPath  = $filePath . '/bin';
        $rdbPath  = $filePath . '/misc/dump.rdb';
        $target   = $this->getTargetMock('/tmp/dump.rdb');
        $target->method('shouldBeCompressed')->willReturn(false);
        $target->method('getPathname')->willReturn('/tmp/dump.rdb');

        $this->redis->setup(['pathToRedisData' => $rdbPath, 'pathToRedisCli' => $binPath]);

        $cliResult1 = $this->getCliResultMock(0, 'redis', ['(integer) 100000000']);
        $cliResult2 = $this->getCliResultMock(1, 'redis');

        $appResult = $this->getAppResultMock();
        $appResult->expects($this->once())->method('debug');

        $redis = $this->getMockBuilder('\\phpbu\\App\\Cli\\Executable\\RedisCli')
                      ->disableOriginalConstructor()
                      ->getMock();
        $redis->expects($this->exactly(2))
              ->method('run')
              ->will($this->onConsecutiveCalls($cliResult1, $cliResult2));

        $this->redis->setExecutable($redis);
        $this->redis->backup($target, $appResult);
    }
}
