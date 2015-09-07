<?php
namespace phpbu\App;

use phpbu\App\Backup\Check;
use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Crypter;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Sync;
use phpbu\App\Log\Logger;

/**
 * Source Factory
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
abstract class Factory
{
    /**
     * Map of available sources, checks, syncs and cleanups.
     *
     * @var array
     */
    private static $classMap = array(
        //   type       => fqcn
        'logger'  => array(
            'json' => '\\phpbu\\App\\Log\\Json',
            'mail' => '\\phpbu\\App\\Log\\Mail',
        ),
        'source'  => array(
            'mongodump'   => '\\phpbu\\App\\Backup\\Source\\Mongodump',
            'mysqldump'   => '\\phpbu\\App\\Backup\\Source\\Mysqldump',
            'tar'         => '\\phpbu\\App\\Backup\\Source\\Tar',
            'elasticdump' => '\\phpbu\\App\\Backup\\Source\\Elasticdump',
            'arangodump' => '\\phpbu\\App\\Backup\\Source\\Arangodump',
            'xtrabackup' => '\\phpbu\\App\\Backup\\Source\\XtraBackup',
        ),
        'check'   => array(
            'sizemin'                 => '\\phpbu\\App\\Backup\\Check\\SizeMin',
            'sizediffpreviouspercent' => '\\phpbu\\App\\Backup\\Check\\SizeDiffPreviousPercent',
            'sizediffavgpercent'      => '\\phpbu\\App\\Backup\\Check\\SizeDiffAvgPercent',
        ),
        'crypter'   => array(
            'mcrypt'  => '\\phpbu\\App\\Backup\\Crypter\\Mcrypt',
            'openssl' => '\\phpbu\\App\\Backup\\Crypter\\OpenSSL',
        ),
        'sync'    => array(
            'amazons3'  => '\\phpbu\\App\\Backup\\Sync\\AmazonS3',
            'copycom'   => '\\phpbu\\App\\Backup\\Sync\\Copycom',
            'dropbox'   => '\\phpbu\\App\\Backup\\Sync\\Dropbox',
            'ftp'       => '\\phpbu\\App\\Backup\\Sync\\Ftp',
            'rsync'     => '\\phpbu\\App\\Backup\\Sync\\Rsync',
            'sftp'      => '\\phpbu\\App\\Backup\\Sync\\Sftp',
            'softlayer' => '\\phpbu\\App\\Backup\\Sync\\SoftLayer',
        ),
        'cleaner' => array(
            'capacity'  => '\\phpbu\\App\\Backup\\Cleaner\\Capacity',
            'outdated'  => '\\phpbu\\App\\Backup\\Cleaner\\Outdated',
            'quantity'  => '\\phpbu\\App\\Backup\\Cleaner\\Quantity',
        ),
    );

    /**
     * Backup Factory.
     * Creates 'Source', 'Check', 'Crypter', 'Sync' and 'Cleaner' Objects.
     *
     * @param  string $type
     * @param  string $alias
     * @throws \phpbu\App\Exception
     * @return mixed
     */
    public static function create($type, $alias)
    {
        $type  = strtolower($type);
        $alias = strtolower($alias);
        self::checkType($type);
        if (!isset(self::$classMap[$type][$alias])) {
            throw new Exception(sprintf('unknown %s: %s', $type, $alias));
        }
        $class = self::$classMap[$type][$alias];
        return new $class();
    }

    /**
     * Logger Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\App\Backup\Source
     */
    public static function createLogger($alias, $conf = array())
    {
        /** @var \phpbu\App\Log\Logger $logger */
        $logger = self::create('logger', $alias);
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
    public static function createSource($alias, $conf = array())
    {
        /** @var \phpbu\App\Backup\Source $source */
        $source = self::create('source', $alias);
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
    public static function createCheck($alias)
    {
        /** @var \phpbu\App\Backup\Check $check */
        $check = self::create('check', $alias);
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
    public static function createCrypter($alias, $conf = array())
    {
        /** @var \phpbu\App\Backup\Crypter $crypter */
        $crypter = self::create('crypter', $alias);
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
    public static function createSync($alias, $conf = array())
    {
        /** @var \phpbu\App\Backup\Sync $sync */
        $sync = self::create('sync', $alias);
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
    public static function createCleaner($alias, $conf = array())
    {
        /** @var \phpbu\App\Backup\Cleaner $cleaner */
        $cleaner = self::create('cleaner', $alias);
        if (!($cleaner instanceof Cleaner)) {
            throw new Exception(sprintf('cleaner \'%s\' has to implement the \'Cleaner\' interface', $alias));
        }
        $cleaner->setup($conf);
        return $cleaner;
    }

    /**
     * Extend the backup factory.
     *
     * @param  string  $type        Type to create 'source', 'check', 'sync' or 'cleaner'
     * @param  string  $alias       Name the class is registered at
     * @param  string  $fqcn        Full Qualified Class Name
     * @param  boolean $force       Overwrite already registered class
     * @throws \phpbu\App\Exception
     */
    public static function register($type, $alias, $fqcn, $force = false)
    {
        $type  = strtolower($type);
        $alias = strtolower($alias);
        self::checkType($type);
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
            throw new Exception('invalid type, use \'source\', \'check\', \'sync\', \'cleaner\' or \'logger\'');
        }
    }
}
