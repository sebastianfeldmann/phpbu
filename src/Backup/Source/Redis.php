<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Target;
use phpbu\App\Cli\Executable;
use phpbu\App\Exception;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Tar source class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.12
 */
class Redis extends SimulatorExecutable implements Simulator, Restorable
{
    /**
     * Executable to handle redis command.
     *
     * @var \phpbu\App\Cli\Executable\RedisCli
     */
    protected $executable;

    /**
     * Path to executable.
     *
     * @var string
     */
    private $pathToRedisCli;

    /**
     * Time to wait for the dump to finish, 45 seconds by default
     *
     * @var integer
     */
    private $timeout;

    /**
     * Host to backup
     *
     * @var string
     */
    private $host;

    /**
     * Port to connect to
     *
     * @var int
     */
    private $port;

    /**
     * Password for authentication
     *
     * @var string
     */
    private $password;

    /**
     * Path to the redis rdb directory, for Debian it's /var/lib/redis/{PORT}/dump.rdb
     *
     * @var string
     */
    private $pathToRedisData;

    /**
     * Setup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  array $conf
     * @throws \phpbu\App\Exception
     */
    public function setup(array $conf = [])
    {
        $this->pathToRedisCli  = Util\Arr::getValue($conf, 'pathToRedisCli', '');
        $this->pathToRedisData = Util\Arr::getValue($conf, 'pathToRedisData', '');
        $this->timeout         = Util\Arr::getValue($conf, 'timeout', 45);
        $this->host            = Util\Arr::getValue($conf, 'host', '');
        $this->port            = Util\Arr::getValue($conf, 'port', 0);
        $this->password        = Util\Arr::getValue($conf, 'password', '');

        if (empty($this->pathToRedisData)) {
            throw new Exception('pathToRedisData option is mandatory');
        }
    }

    /**
     * Execute the backup.
     *
     * @see    \phpbu\App\Backup\Source
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     * @throws \phpbu\App\Exception
     */
    public function backup(Target $target, Result $result) : Status
    {
        // set uncompressed default MIME type
        $target->setMimeType('application/octet-stream');

        $redisSave = $this->getExecutable($target);
        $redisLast = $this->getRedisLastSave($this->executable);

        $lastBackupTimestamp = $this->getLastBackupTime($redisLast);
        $saveResult          = $this->runCommand($redisSave);
        $result->debug($this->getExecutable($target)->getCommandPrintable());
        if (!$saveResult->isSuccessful()) {
            throw new Exception('redis-cli BGSAVE failed:' . $saveResult->getStdErr());
        }
        // check if the save process is finished
        $this->isDumpCreatedYet($lastBackupTimestamp, $redisLast);
        $this->copyDumpToTarget($target);

        return $this->createStatus($target);
    }

    /**
     * Setup the Executable to run the 'redis-cli' command.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Cli\Executable
     */
    protected function createExecutable(Target $target) : Executable
    {
        $executable = new Executable\RedisCli($this->pathToRedisCli);
        $executable->backup()
                   ->useHost($this->host)
                   ->usePort($this->port)
                   ->usePassword($this->password);
        return $executable;
    }

    /**
     * Creates a RedisLastSave command from a RedisSave command.
     *
     * @param  \phpbu\App\Cli\Executable\RedisCli $redis
     * @return \phpbu\App\Cli\Executable\RedisCli
     */
    public function getRedisLastSave(Executable\RedisCli $redis) : Executable\RedisCli
    {
        $redisLast = clone($redis);
        $redisLast->lastBackupTime();
        return $redisLast;
    }

    /**
     * Return last successful save timestamp.
     *
     * @param  \phpbu\App\Cli\Executable\RedisCli $redis
     * @return int
     * @throws \phpbu\App\Exception
     */
    private function getLastBackupTime(Executable\RedisCli $redis) : int
    {
        $result  = $this->runCommand($redis);
        $output  = $result->getStdOut();
        $matches = [];
        if (!preg_match('#(\(integer\) )?([0-9]+)#i', $output, $matches)) {
            throw new Exception('invalid redis-cli LASTSAVE output');
        }
        return (int) $matches[2];
    }

    /**
     * Check the dump date and return true if BGSAVE is finished.
     *
     * @param  int                                $lastTimestamp
     * @param  \phpbu\App\Cli\Executable\RedisCli $redis
     * @return bool
     * @throws \phpbu\App\Exception
     */
    private function isDumpCreatedYet($lastTimestamp, $redis) : bool
    {
        $i = 0;
        while ($this->getLastBackupTime($redis) <= $lastTimestamp) {
            if ($i > $this->timeout) {
                throw new Exception('redis-cli BGSAVE is taking to long, increase timeout');
            }
            $i++;
            sleep(1);
        }
        return true;
    }

    /**
     * Copy the redis RDB file to its backup location.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     * @throws \phpbu\App\Exception
     */
    private function copyDumpToTarget(Target $target) : string
    {
        if (!file_exists($this->pathToRedisData)) {
            throw new Exception('Redis data not found at: \'' . $this->pathToRedisCli . '\'');
        }
        $targetFile = $target->getPathnamePlain();
        copy($this->pathToRedisData, $targetFile);
        return $targetFile;
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    protected function createStatus(Target $target) : Status
    {
        return Status::create()->uncompressedFile($target->getPathnamePlain());
    }

    /**
     * Restore the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     * @return \phpbu\App\Backup\Source\Status
     */
    public function restore(Target $target, Plan $plan): Status
    {
        $cmd = sprintf('cp %s %s', $target->getPathnamePlain(), $this->pathToRedisData);
        $plan->addRestoreCommand($cmd);

        return Status::create()->uncompressedFile($target->getPathnamePlain());
    }
}
