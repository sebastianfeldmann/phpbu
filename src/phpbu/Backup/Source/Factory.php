<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\Backup\Target;

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
     * Map of allowed sources.
     *
     * @var array
     */
    private static $classMap = array(
        // type => fqcn
        'mysql' => '\\phpbu\\Backup\\Source\\Mysqldump',
    );

    /**
     * Source Factory.
     *
     * @param  string $type
     * @param  Target $target
     * @param  array  $conf
     * @throws Exception
     * @return Source
     */
    public static function create($type, Target $target, $conf = array())
    {
        if (!isset(self::$classMap)) {
            throw new \Exception(sprintf('uknown source: %s', $type));
        }
        $class  = self::$classMap[$type];
        return new $class($target, $conf);
    }

    /**
     * Extend the source factory.
     *
     * @param  string $type  Name the class is registered at
     * @param  string $fqcn  Full Qualified Class Name
     * @param  string $force Overwrite already registered class
     * @throws Exception
     */
    public static function registerSource($type, $fqcn, $force = false)
    {
        if (!$force && isset(self::$classMap[$type])) {
            throw new Exception('source already registered use fource parameter to overwrite');
        }
        self::$classMap[$type] = $fqcn;
    }
}
