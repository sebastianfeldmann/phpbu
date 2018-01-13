<?php
namespace phpbu\App\Configuration\Loader;
use phpbu\App\Configuration\Bootstrapper;

/**
 * Factory class for file based Configuration Loader.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 2.1.2
 */
abstract class Factory
{
    /**
     * Default loader to return if none could be detected.
     *
     * @var string
     */
    const DEFAULT_LOADER = 'Xml';

    /**
     * File type to loader class map.
     *
     * @var array
     */
    private static $extToLoaderMap = [
        'xml'  => 'Xml',
        'json' => 'Json',
    ];

    /**
     * Create a Configuration Loader based on the file to load.
     *
     * @param  string                                $filename
     * @param  \phpbu\App\Configuration\Bootstrapper $bootstrapper
     * @return \phpbu\App\Configuration\Loader
     */
    public static function createLoader(string $filename, Bootstrapper $bootstrapper)
    {
        $ext   = pathinfo($filename, PATHINFO_EXTENSION);
        $type  = isset(self::$extToLoaderMap[$ext]) ? self::$extToLoaderMap[$ext] : self::DEFAULT_LOADER;
        $class = '\\phpbu\\App\\Configuration\\Loader\\' . $type;

        return new $class($filename, $bootstrapper);
    }
}
