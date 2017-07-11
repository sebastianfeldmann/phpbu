<?php
namespace phpbu\App;

use phpbu\App\Backup\Check;
use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Sync;
use phpbu\App\Log\Logger;
use phpbu\App\Runner\Task;

/**
 * Factory
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Factory
{
    /**
     * Map of available sources, checks, syncs and cleanups.
     *
     * @var array
     */
    private static $classMap = [
        // type
        //   alias => fqcn
        'adapter'  => [
            'array'  => '\\phpbu\\App\\Adapter\\PHPArray',
            'dotenv' => '\\phpbu\\App\\Adapter\\Dotenv',
            'env'    => '\\phpbu\\App\\Adapter\\Env',
        ],
        'runner' => [
            'bootstrap' => '\\phpbu\\App\\Runner\\Bootstrap',
            'check'     => '\\phpbu\\App\\Runner\\Check',
            'cleaner'   => '\\phpbu\\App\\Runner\\Cleaner',
            'crypter'   => '\\phpbu\\App\\Runner\\Crypter',
            'source'    => '\\phpbu\\App\\Runner\\Source',
            'sync'      => '\\phpbu\\App\\Runner\\Sync',
        ],
        'logger'  => [
            'json'    => '\\phpbu\\App\\Log\\Json',
            'mail'    => '\\phpbu\\App\\Log\\Mail',
            'webhook' => '\\phpbu\\App\\Log\\Webhook',
        ],
        'source'  => [
            'arangodump'  => '\\phpbu\\App\\Backup\\Source\\Arangodump',
            'elasticdump' => '\\phpbu\\App\\Backup\\Source\\Elasticdump',
            'mongodump'   => '\\phpbu\\App\\Backup\\Source\\Mongodump',
            'mysqldump'   => '\\phpbu\\App\\Backup\\Source\\Mysqldump',
            'pgdump'      => '\\phpbu\\App\\Backup\\Source\\Pgdump',
            'redis'       => '\\phpbu\\App\\Backup\\Source\\Redis',
            'rsync'       => '\\phpbu\\App\\Backup\\Source\\Rsync',
            'tar'         => '\\phpbu\\App\\Backup\\Source\\Tar',
            'xtrabackup'  => '\\phpbu\\App\\Backup\\Source\\XtraBackup',
        ],
        'check'   => [
            'sizemin'                 => '\\phpbu\\App\\Backup\\Check\\SizeMin',
            'sizediffpreviouspercent' => '\\phpbu\\App\\Backup\\Check\\SizeDiffPreviousPercent',
            'sizediffavgpercent'      => '\\phpbu\\App\\Backup\\Check\\SizeDiffAvgPercent',
        ],
        'crypter'   => [
            'mcrypt'  => '\\phpbu\\App\\Backup\\Crypter\\Mcrypt',
            'openssl' => '\\phpbu\\App\\Backup\\Crypter\\OpenSSL',
        ],
        'sync'    => [
            'amazons3'    => '\\phpbu\\App\\Backup\\Sync\\AmazonS3v3',
            'amazons3-v3' => '\\phpbu\\App\\Backup\\Sync\\AmazonS3v3',
            'amazons3-v2' => '\\phpbu\\App\\Backup\\Sync\\AmazonS3v2',
            'dropbox'     => '\\phpbu\\App\\Backup\\Sync\\Dropbox',
            'ftp'         => '\\phpbu\\App\\Backup\\Sync\\Ftp',
            'googledrive' => '\\phpbu\\App\\Backup\\Sync\\GoogleDrive',
            'rsync'       => '\\phpbu\\App\\Backup\\Sync\\Rsync',
            'sftp'        => '\\phpbu\\App\\Backup\\Sync\\Sftp',
            'softlayer'   => '\\phpbu\\App\\Backup\\Sync\\SoftLayer',
        ],
        'cleaner' => [
            'capacity'  => '\\phpbu\\App\\Backup\\Cleaner\\Capacity',
            'outdated'  => '\\phpbu\\App\\Backup\\Cleaner\\Outdated',
            'stepwise'  => '\\phpbu\\App\\Backup\\Cleaner\\Stepwise',
            'quantity'  => '\\phpbu\\App\\Backup\\Cleaner\\Quantity',
        ],
    ];

    /**
     * Backup Factory.
     * Creates 'Source', 'Check', 'Crypter', 'Sync' and 'Cleaner' Objects.
     *
     * @param  string $type
     * @param  string $alias
     * @throws \phpbu\App\Exception
     * @return mixed
     */
    protected function create($type, $alias)
    {
        self::checkType($type);
        $alias = strtolower($alias);

        if (!isset(self::$classMap[$type][$alias])) {
            throw new Exception(sprintf('unknown %s: %s', $type, $alias));
        }
        $class = self::$classMap[$type][$alias];
        return new $class();
    }

    /**
     * Runner Factory.
     *
     * @param  string $alias
     * @param  bool   $isSimulation
     * @return mixed
     * @throws \phpbu\App\Exception
     */
    public function createRunner($alias, $isSimulation)
    {
        $runner = $this->create('runner', $alias);
        if (!($runner instanceof Task)) {
            throw new Exception(sprintf('Runner \'%s\' has to implement the \'Task\' interface', $alias));
        }
        $runner->setSimulation($isSimulation);
        return $runner;
    }

    /**
     * Adapter Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Adapter
     */
    public function createAdapter($alias, $conf = [])
    {
        /** @var \phpbu\App\Adapter $adapter */
        $adapter = $this->create('adapter', $alias);
        if (!($adapter instanceof Adapter)) {
            throw new Exception(sprintf('adapter \'%s\' has to implement the \'Adapter\' interfaces', $alias));
        }
        $adapter->setup($conf);
        return $adapter;
    }

    /**
     * Logger Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Log\Logger
     */
    public function createLogger($alias, $conf = [])
    {
        /** @var \phpbu\App\Log\Logger $logger */
        $logger = $this->create('logger', $alias);
        if (!($logger instanceof Logger)) {
            throw new Exception(sprintf('logger \'%s\' has to implement the \'Logger\' interfaces', $alias));
        }
        if (!($logger instanceof Listener)) {
            throw new Exception(sprintf('logger \'%s\' has to implement the \'Listener\' interface', $alias));
        }
        $logger->setup($conf);
        return $logger;
    }

    /**
     * Source Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Backup\Source
     */
    public function createSource($alias, $conf = [])
    {
        /** @var \phpbu\App\Backup\Source $source */
        $source = $this->create('source', $alias);
        if (!($source instanceof Source)) {
            throw new Exception(sprintf('source \'%s\' has to implement the \'Source\' interface', $alias));
        }
        $source->setup($conf);
        return $source;
    }

    /**
     * Check Factory.
     *
     * @param  string $alias
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Backup\Check
     */
    public function createCheck($alias)
    {
        /** @var \phpbu\App\Backup\Check $check */
        $check = $this->create('check', $alias);
        if (!($check instanceof Check)) {
            throw new Exception(sprintf('Check \'%s\' has to implement the \'Check\' interface', $alias));
        }
        return $check;
    }

    /**
     * Crypter Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Backup\Crypter
     */
    public function createCrypter($alias, $conf = [])
    {
        /** @var \phpbu\App\Backup\Crypter $crypter */
        $crypter = $this->create('crypter', $alias);
        if (!($crypter instanceof Crypter)) {
            throw new Exception(sprintf('Crypter \'%s\' has to implement the \'Crypter\' interface', $alias));
        }
        $crypter->setup($conf);
        return $crypter;
    }

    /**
     * Sync Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Backup\Sync
     */
    public function createSync($alias, $conf = [])
    {
        /** @var \phpbu\App\Backup\Sync $sync */
        $sync = $this->create('sync', $alias);
        if (!($sync instanceof Sync)) {
            throw new Exception(sprintf('sync \'%s\' has to implement the \'Sync\' interface', $alias));
        }
        $sync->setup($conf);
        return $sync;
    }

    /**
     * Cleaner Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Backup\Cleaner
     */
    public function createCleaner($alias, $conf = [])
    {
        /** @var \phpbu\App\Backup\Cleaner $cleaner */
        $cleaner = $this->create('cleaner', $alias);
        if (!($cleaner instanceof Cleaner)) {
            throw new Exception(sprintf('cleaner \'%s\' has to implement the \'Cleaner\' interface', $alias));
        }
        $cleaner->setup($conf);
        return $cleaner;
    }

    /**
     * Extend the backup factory.
     *
     * @param  string  $type        Type to create 'adapter', 'source', 'check', 'sync' or 'cleaner'
     * @param  string  $alias       Name the class is registered at
     * @param  string  $fqcn        Full Qualified Class Name
     * @param  boolean $force       Overwrite already registered class
     * @throws \phpbu\App\Exception
     */
    public static function register($type, $alias, $fqcn, $force = false)
    {
        self::checkType($type);
        $alias = strtolower($alias);

        if (!$force && isset(self::$classMap[$type][$alias])) {
            throw new Exception(sprintf('%s is already registered use force parameter to overwrite', $type));
        }
        self::$classMap[$type][$alias] = $fqcn;
    }

    /**
     * Throws an exception if type is invalid.
     *
     * @param  string $type
     * @throws \phpbu\App\Exception
     */
    private static function checkType($type)
    {
        if (!isset(self::$classMap[$type])) {
            throw new Exception(
                'invalid type, please use only \'' . implode('\', \'', array_keys(self::$classMap)) . '\''
            );
        }
    }
}
