<?php
namespace phpbu\App;

use phpbu\App\Exception;
use phpbu\App\Listener;
use phpbu\Backup\Check;
use phpbu\Backup\Cleaner;
use phpbu\Backup\Source;
use phpbu\Backup\Sync;
use phpbu\Log\Logger;

/**
 * Source Factory
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
            'json' => '\\phpbu\\Log\\Json',
            'mail' => '\\phpbu\\Log\\Mail',
        ),
        'source'  => array(
            'mysqldump' => '\\phpbu\\Backup\\Source\\Mysqldump',
            'tar'       => '\\phpbu\\Backup\\Source\\Tar',
        ),
        'check'   => array(
            'sizemin'                 => '\\phpbu\\Backup\\Check\\SizeMin',
            'sizediffpreviouspercent' => '\\phpbu\\Backup\\Check\\SizeDiffPreviousPercent',
            'sizediffavgpercent'      => '\\phpbu\\Backup\\Check\\SizeDiffAvgPercent',
        ),
        'sync'    => array(
            'amazons3' => '\\phpbu\\Backup\\Sync\\AmazonS3',
            'copycom'  => '\\phpbu\\Backup\\Sync\\Copycom',
            'dropbox'  => '\\phpbu\\Backup\\Sync\\Dropbox',
            'ftp'      => '\\phpbu\\Backup\\Sync\\Ftp',
            'rsync'    => '\\phpbu\\Backup\\Sync\\Rsync',
            'sftp'     => '\\phpbu\\Backup\\Sync\\Sftp',
        ),
        'cleaner' => array(
            'capacity'  => '\\phpbu\\Backup\\Cleaner\\Capacity',
            'outdated'  => '\\phpbu\\Backup\\Cleaner\\Outdated',
            'quantity'  => '\\phpbu\\Backup\\Cleaner\\Quantity',
        ),
    );

    /**
     * Backup Factory.
     * Creates 'Source', 'Check', 'Sync' and 'Cleaner' Objects.
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
     * @return \phpbu\Backup\Source
     */
    public static function createLogger($alias, $conf = array())
    {
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
     * @return \phpbu\Backup\Source
     */
    public static function createSource($alias, $conf = array())
    {
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
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\Backup\Check
     */
    public static function createCheck($alias)
    {
        $check = self::create('check', $alias);
        if (!($check instanceof Check)) {
            throw new Exception(sprintf('Check \'%s\' has to implement the \'Check\' interface', $alias));
        }
        return $check;
    }

    /**
     * Sync Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws \phpbu\App\Exception
     * @return \phpbu\Backup\Sync
     */
    public static function createSync($alias, $conf = array())
    {
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
     * @return \phpbu\backup\Cleaner
     */
    public static function createCleaner($alias, $conf = array())
    {
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
     * @param  string $type        Type to create 'source', 'check', 'sync' or 'cleaner'
     * @param  string $alias       Name the class is registered at
     * @param  string $fqcn        Full Qualified Class Name
     * @param  string $force       Overwrite already registered class
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
     * Throws an excepton if type is invalid.
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
