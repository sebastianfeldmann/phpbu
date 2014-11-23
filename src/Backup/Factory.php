<?php
namespace phpbu\Backup;

use phpbu\App\Exception;
use phpbu\Backup\Source;
use phpbu\Backup\Check;
use phpbu\Backup\Sync;
use phpbu\Backup\Cleanup;

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
        //     type     => fqcn
        'source'  => array(
            'mysql'     => '\\phpbu\\Backup\\Source\\Mysqldump',
            'directory' => '\\phpbu\\Backup\\Source\\Tar',
        ),
        'check'   => array(
            'SizeMin' => '\\phpbu\\Backup\\Check\\SizeMin',
        ),
        'sync'    => array(
        ),
        'cleaner' => array(
            'Outdated' => '\\phpbu\\Backup\\Cleaner\\Outdated',
        ),
    );

    /**
     * Backup Factory.
     * Creates 'Source', 'Check', 'Sync' and 'Cleaner' Objects.
     *
     * @param  string $type
     * @param  string $alias
     * @param  array  $conf
     * @throws phpbu\App\Exception
     * @return mixed
     */
    public static function create($type, $alias, $conf = array())
    {
        self::checkType($type);
        if (!isset(self::$classMap[$type][$alias])) {
            throw new Exception(sprintf('unknown $s: %s', $type, $alias));
        }
        $class = self::$classMap[$type][$alias];
        return new $class();
    }

    /**
     * Source Factory.
     *
     * @param  string $alias
     * @param  array  $conf
     * @throws phpbu\App\Exception
     * @return Source
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
     * @throws phpbu\App\Exception
     * @return Check
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
     * @throws phpbu\App\Exception
     * @return Sync
     */
    public static function createSync($alias, $conf = array())
    {
        $sync = self::create('sync', $alias);
        if (!($source instanceof Sync)) {
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
     * @throws phpbu\App\Exception
     * @return Cleaner
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
     * @throws phpbu\App\Exception
     */
    public static function register($type, $alias, $fqcn, $force = false)
    {
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
     * @throws phpbu\App\Exception
     */
    private static function checkType($type)
    {
        if (!isset(self::$classMap[$type])) {
            throw new Exception('invalid type, use \'source\', \'check\', \'sync\' or \'cleaner\'');
        }
    }
}
