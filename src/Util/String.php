<?php
namespace phpbu\Util;

use RuntimeException;

/**
 * String utility class.
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class String
{
    /**
     * Date placeholder replacement.
     * Replaces %{somevalue} with date({somevalue}).
     *
     * @param  string $string
     * @param  null   $time
     * @return string
     */
    public static function replaceDatePlaceholders($string, $time = null)
    {
        $time = $time === null ? time() : $time;
        return preg_replace_callback(
            '#%([a-zA-Z])#',
            function($match) use ($time) {
                return date($match[1], $time);
            },
            $string
        );
    }

    /**
     * Create a regex that matches the raw path considering possible date placeholders.
     *
     * @param  string $stringWithDatePlaceholders
     * @return string
     */
    public static function datePlaceholdersToRegex($stringWithDatePlaceholders)
    {
        $regex = preg_quote($stringWithDatePlaceholders, '#');
        return preg_replace('#%[a-z]#i', '[0-9a-z]+', $regex);
    }

    /**
     * Converts a given value to boolean.
     *
     * @param  string  $value
     * @param  boolean $default
     * @return boolean
     */
    public static function toBoolean($value, $default)
    {
        if (strtolower($value) == 'false') {
            return false;
        } elseif (strtolower($value) == 'true') {
            return true;
        }
        return $default;
    }

    /**
     * Return given size in bytes.
     * Allowed units:
     *   B => byte
     *   K => kilo byte
     *   M => mega byte
     *   G => giga byte
     *   T => terra byte
     *   P => peta byte
     *
     * e.g.
     * 1K => 1024
     * 2K => 2048
     * ...
     *
     * @param  string $value
     * @throws \RuntimeException
     * @return integer
     */
    public static function toBytes($value)
    {
        if (!preg_match('#^[0-9]*[BKMGT]$#i', $value)) {
            throw new RuntimeException('Invalid size value');
        }
        $units  = array('B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5);
        $unit   = strtoupper(substr($value, -1));
        $number = intval(substr($value, 0, -1));

        return $number * pow(1024, $units[$unit]);
    }

    /**
     * Return time in seconds for a given value.
     * Allowed units:
     *   S => second
     *   I => minute
     *   D => day
     *   W => week
     *   M => month
     *   Y => year
     *
     * e.g.
     *  2I => 120
     * 10D => 864000
     * ...
     *
     * @param  string $offset
     * @throws \RuntimeException
     * @return integer
     */
    public static function toTime($offset)
    {
        if (!preg_match('#^[1-9]+[0-9]*[SIHDWMY]$#i', $offset)) {
            throw new RuntimeException(sprintf('Invalid value for offset: %s', $offset));
        }
        $units  = array('S' => 1, 'I' => 60, 'H' => 3600, 'D' => 86400, 'W' => 604800, 'M' => 2678400, 'Y' => 31536000);
        $unit   = strtoupper(substr($offset, -1));
        $number = intval(substr($offset, 0, -1));

        return $number * $units[$unit];
    }

    /**
     * Adds trailing slash to a string/path if not already there.
     *
     * @param  string $string
     * @return string
     */
    public static function withTrailingSlash($string)
    {
        return $string . (substr($string, -1) !== '/' ? '/' : '');
    }

    /**
     * Removes the trailing slash from a string/path.
     *
     * @param  string $string
     * @return string
     */
    public static function withoutTrailingSlash($string)
    {
        return strlen($string) > 1 && substr($string, -1) === '/' ? substr($string, 0, -1) : $string;
    }
}
