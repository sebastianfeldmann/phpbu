<?php
namespace phpbu\App;

use SebastianBergmann;

/**
 * Application Version.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Version
{
    /**
     * Version of the phar file.
     * Is getting set via the phar build process.
     *
     * @var string
     */
    private static $pharVersion;

    /**
     * Current version
     *
     * @var string
     */
    private static $version;

    /**
     * Return the current version of PHPUnit.
     *
     * @return string
     */
    public static function id() : string
    {
        if (self::$pharVersion !== null) {
            return self::$pharVersion;
        }

        if (self::$version === null) {
            $version = new SebastianBergmann\Version('5.1.0', dirname(dirname(__DIR__)));
            self::$version = $version->getVersion();
        }

        return self::$version;
    }

    /**
     * Return the version string.
     *
     * @return string
     */
    public static function getVersionString() : string
    {
        return 'phpbu ' . self::id() . ' by Sebastian Feldmann and contributors.';
    }
}
