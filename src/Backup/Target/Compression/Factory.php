<?php
namespace phpbu\App\Backup\Target\Compression;

use phpbu\App\Exception;

/**
 * Factory
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.2.1
 */
class Factory
{
    /**
     * List of available compressors
     *
     * @var array
     */
    protected static $availableCompressors = [
        'gzip'  => 'Gzip',
        'bzip2' => 'Bzip2',
        'xz'    => 'Xz',
        'zip'   => 'Zip',
        'zstd'  => 'Zstd'
    ];

    /**
     * Create a Compression.
     *
     * @param  string $name
     * @return \phpbu\App\Backup\Target\Compression
     */
    public static function create($name)
    {
        $path = '';
        // check if a path is given for the compression command
        if (basename($name) !== $name) {
            $path = dirname($name);
            $name = basename($name);
        }
        $class = self::getClassName($name);
        return new $class($path);
    }

    /**
     * Return compressions FQCN by name.
     *
     * @param  string $name
     * @return string
     * @throws \phpbu\App\Exception
     */
    public static function getClassName($name)
    {
        if (!isset(self::$availableCompressors[$name])) {
            throw new Exception('Invalid compressor: ' . $name);
        }
        $class = self::$availableCompressors[$name];
        return '\\phpbu\\App\\Backup\\Target\\Compression\\' . $class;
    }
}
