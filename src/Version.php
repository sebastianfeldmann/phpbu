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
 * @internal
 */
final class Version
{
    /**
     * Current version
     *
     * @var string
     */
    private static $version;

    /**
     * Path to application root directory.
     *
     * @var string
     */
    private $path;

    /**
     * Current release version.
     *
     * @var string
     */
    private $release;

    /**
     * Current version number.
     *
     * @var string
     */
    private $number;

    /**
     * @param string $release
     * @param string $path
     */
    public function __construct($release, $path)
    {
        $this->release = $release;
        $this->path    = $path;
    }

    /**
     * Return the full version number.
     *
     * @return string
     */
    public function getVersionNumber()
    {
        if ($this->number === null) {
            if (count(explode('.', $this->release)) == 3) {
                $this->number = $this->release;
            } else {
                $this->number = $this->release . '-dev';
            }
        }
        return $this->number;
    }

    /**
     * Return the current version of PHPUnit.
     *
     * @return string
     */
    public static function id() : string
    {
        if (self::$version === null) {
            $version = new self('5.1.9', dirname(dirname(__DIR__)));
            self::$version = $version->getVersionNumber();
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
