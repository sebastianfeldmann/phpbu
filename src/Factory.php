<?php
namespace phpbu\App;

use phpbu\App\Backup\Check;
use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Log\Logger;

/**
 * Factory
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Factory
{
    /**
     * Map of available sources, checks, crypts, syncs and cleanups
     *
     * @var array
     */
    private static $classMap = [
        // type
        //   alias => fqcn
        'adapter'  => [
            'array'     => '\\phpbu\\App\\Adapter\\PHPArray',
            'constants' => '\\phpbu\\App\\Adapter\\PHPConstant',
            'dotenv'    => '\\phpbu\\App\\Adapter\\Dotenv',
            'env'       => '\\phpbu\\App\\Adapter\\Env',
            'wordpress' => '\\phpbu\\App\\Adapter\\PHPWordPress',
        ],
        'logger'  => [
            'json'     => '\\phpbu\\App\\Log\\Json',
            'mail'     => '\\phpbu\\App\\Log\\Mail',
            'webhook'  => '\\phpbu\\App\\Log\\Webhook',
            'telegram' => '\\phpbu\\App\\Log\\Telegram',
            'prometheus' => '\\phpbu\\App\\Log\\Prometheus',
        ],
        'source'  => [
            'arangodump'  => '\\phpbu\\App\\Backup\\Source\\Arangodump',
            'elasticdump' => '\\phpbu\\App\\Backup\\Source\\Elasticdump',
            'mongodump'   => '\\phpbu\\App\\Backup\\Source\\Mongodump',
            'mysqldump'   => '\\phpbu\\App\\Backup\\Source\\Mysqldump',
            'pgdump'      => '\\phpbu\\App\\Backup\\Source\\Pgdump',
            'influxdump'  => '\\phpbu\\App\\Backup\\Source\\Influxdump',
            'ldapdump'    => '\\phpbu\\App\\Backup\\Source\\Ldapdump',
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
            'gpg'     => '\\phpbu\\App\\Backup\\Crypter\\Gpg',
            'mcrypt'  => '\\phpbu\\App\\Backup\\Crypter\\Mcrypt',
            'openssl' => '\\phpbu\\App\\Backup\\Crypter\\OpenSSL',
        ],
        'sync'    => [
            'amazons3'    => '\\phpbu\\App\\Backup\\Sync\\AmazonS3v3',
            'amazons3-v3' => '\\phpbu\\App\\Backup\\Sync\\AmazonS3v3',
            'amazons3-v2' => '\\phpbu\\App\\Backup\\Sync\\AmazonS3v2',
            'backblazes3' => '\\phpbu\\App\\Backup\\Sync\\BackblazeS3',
            'azureblob'   => '\\phpbu\\App\\Backup\\Sync\\AzureBlob',
            'dropbox'     => '\\phpbu\\App\\Backup\\Sync\\Dropbox',
            'ftp'         => '\\phpbu\\App\\Backup\\Sync\\Ftp',
            'googledrive' => '\\phpbu\\App\\Backup\\Sync\\GoogleDrive',
            'rsync'       => '\\phpbu\\App\\Backup\\Sync\\Rsync',
            'sftp'        => '\\phpbu\\App\\Backup\\Sync\\Sftp',
            'softlayer'   => '\\phpbu\\App\\Backup\\Sync\\SoftLayer',
            'openstack'   => '\\phpbu\\App\\Backup\\Sync\\OpenStack',
            'yandex-disk' => '\\phpbu\\App\\Backup\\Sync\\YandexDisk',
        ],
        'cleaner' => [
            'capacity'  => '\\phpbu\\App\\Backup\\Cleaner\\Capacity',
            'outdated'  => '\\phpbu\\App\\Backup\\Cleaner\\Outdated',
            'stepwise'  => '\\phpbu\\App\\Backup\\Cleaner\\Stepwise',
            'quantity'  => '\\phpbu\\App\\Backup\\Cleaner\\Quantity',
        ],
    ];

    /**
     * Backup Factory
     * Creates 'Source', 'Check', 'Crypter', 'Sync' and 'Cleaner' Objects.
     *
     * @param  string $type
     * @param  string $alias
     * @return mixed
     *@throws Exception
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
     * Adapter Factory
     *
     * @param  string $alias
     * @param  array  $conf
     * @return Adapter
     *@throws Exception
     */
    public function createAdapter($alias, $conf = []) : Adapter
    {
        /** @var Adapter $adapter */
        $adapter = $this->create('adapter', $alias);
        if (!($adapter instanceof Adapter)) {
            throw new Exception(sprintf('adapter \'%s\' has to implement the \'Adapter\' interfaces', $alias));
        }
        $adapter->setup($conf);
        return $adapter;
    }

    /**
     * Logger Factory
     *
     * @param  string $alias
     * @param  array  $conf
     * @return Logger
     *@throws Exception
     */
    public function createLogger($alias, $conf = []) : Logger
    {
        /** @var Logger $logger */
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
     * Create a backup target
     *
     * @param Configuration\Backup\Target $conf
     * @return Target
     * @throws Exception
     */
    public function createTarget(Configuration\Backup\Target $conf) : Target
    {
        $target = new Target($conf->dirname, $conf->filename);
        $target->setupPath();
        // add possible compressor
        if (!empty($conf->compression)) {
            $compression = Target\Compression\Factory::create($conf->compression);
            $target->setCompression($compression);
        }
        return $target;
    }

    /**
     * Source Factory
     *
     * @param  string $alias
     * @param  array  $conf
     * @return Source
     *@throws Exception
     */
    public function createSource($alias, $conf = []) : Source
    {
        /** @var Source $source */
        $source = $this->create('source', $alias);
        if (!($source instanceof Source)) {
            throw new Exception(sprintf('source \'%s\' has to implement the \'Source\' interface', $alias));
        }
        $source->setup($conf);
        return $source;
    }

    /**
     * Check Factory
     *
     * @param  string $alias
     * @return Check
     *@throws Exception
     */
    public function createCheck($alias) : Check
    {
        /** @var Check $check */
        $check = $this->create('check', $alias);
        if (!($check instanceof Check)) {
            throw new Exception(sprintf('Check \'%s\' has to implement the \'Check\' interface', $alias));
        }
        return $check;
    }

    /**
     * Crypter Factory
     *
     * @param  string $alias
     * @param  array  $conf
     * @return Crypter
     *@throws Exception
     */
    public function createCrypter($alias, $conf = []) : Crypter
    {
        /** @var Crypter $crypter */
        $crypter = $this->create('crypter', $alias);
        if (!($crypter instanceof Crypter)) {
            throw new Exception(sprintf('Crypter \'%s\' has to implement the \'Crypter\' interface', $alias));
        }
        $crypter->setup($conf);
        return $crypter;
    }

    /**
     * Sync Factory
     *
     * @param  string $alias
     * @param  array  $conf
     * @return Sync
     *@throws Exception
     */
    public function createSync($alias, $conf = []) : Sync
    {
        /** @var Sync $sync */
        $sync = $this->create('sync', $alias);
        if (!($sync instanceof Sync)) {
            throw new Exception(sprintf('sync \'%s\' has to implement the \'Sync\' interface', $alias));
        }
        $sync->setup($conf);
        return $sync;
    }

    /**
     * Cleaner Factory
     *
     * @param  string $alias
     * @param  array  $conf
     * @return Cleaner
     *@throws Exception
     */
    public function createCleaner($alias, $conf = []) : Cleaner
    {
        /** @var Cleaner $cleaner */
        $cleaner = $this->create('cleaner', $alias);
        if (!($cleaner instanceof Cleaner)) {
            throw new Exception(sprintf('cleaner \'%s\' has to implement the \'Cleaner\' interface', $alias));
        }
        $cleaner->setup($conf);
        return $cleaner;
    }

    /**
     * Extend the backup factory
     *
     * @param  string  $type        Type to create 'adapter', 'source', 'check', 'sync' or 'cleaner'
     * @param  string  $alias       Name the class is registered at
     * @param  string  $fqcn        Full Qualified Class Name
     * @param  boolean $force       Overwrite already registered class
     * @throws Exception
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
     * Throws an exception if type is invalid
     *
     * @param  string $type
     * @throws Exception
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
