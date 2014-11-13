<?php
namespace phpbu\Backup\Source;

use phpbu\App\Exception;
use phpbu\Backup\Source;
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
        // type     => fqcn
        'mysql'     => '\\phpbu\\Backup\\Source\\Mysqldump',
        'directory' => '\\phpbu\\Backup\\Source\\Tar',
    );

    /**
     * Source Factory.
     *
     * @param  string $type
     * @param  array  $conf
     * @throws phpbu\App\Exception
     * @return Source
     */
    public static function create($type, $conf = array())
    {
        if (!isset(self::$classMap)) {
            throw new Exception(sprintf('uknown source: %s', $type));
        }
        $class  = self::$classMap[$type];
        $source = new $class();
        if (!($source instanceof Source)) {
            throw new Exception(sprintf('source type \'%s\' has to implement the \'Source\' interface', $type));
        }
        $source->setup($conf);
        return $source;
    }

    /**
     * Extend the source factory.
     *
     * @param  string $type  Name the class is registered at
     * @param  string $fqcn  Full Qualified Class Name
     * @param  string $force Overwrite already registered class
     * @throws phpbu\App\Exception
     */
    public static function registerSource($type, $fqcn, $force = false)
    {
        if (!$force && isset(self::$classMap[$type])) {
            throw new Exception('source is already registered use force parameter to overwrite');
        }
        self::$classMap[$type] = $fqcn;
    }
}
