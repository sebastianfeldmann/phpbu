<?php
namespace phpbu\App\Util;

use RuntimeException;

/**
 * String utility class.
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Str
{
    /**
     * Converts a given value to boolean.
     *
     * @param  string $value
     * @param  bool   $default
     * @return bool
     */
    public static function toBoolean($value, $default)
    {
        if (is_bool($value)) {
            return $value;
        }
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
     * @return int
     */
    public static function toBytes($value)
    {
        if (!preg_match('#^[0-9]*[BKMGT]$#i', $value)) {
            throw new RuntimeException('Invalid size value');
        }
        $units  = ['B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5];
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
        $units  = ['S' => 1, 'I' => 60, 'H' => 3600, 'D' => 86400, 'W' => 604800, 'M' => 2678400, 'Y' => 31536000];
        $unit   = strtoupper(substr($offset, -1));
        $number = intval(substr($offset, 0, -1));

        return $number * $units[$unit];
    }

    /**
     * Pads all given strings to given length.
     *
     * @param  array   $strings
     * @param  integer $length
     * @param  string  $pad
     * @param  integer $mode
     * @return array
     */
    public static function padAll(array $strings, $length, $pad = ' ', $mode = STR_PAD_LEFT)
    {
        $result = [];
        foreach ($strings as $key => $s) {
            $result[$key] = str_pad($s, $length, $pad, $mode);
        }
        return $result;
    }

    /**
     * Explodes string to array but empty string results in empty array not array with empty string in it.
     *
     * @param  string  $separated
     * @param  string  $separator
     * @param  boolean $trim
     * @return array
     */
    public static function toList($separated, $separator = ',', $trim = true)
    {
        $list = empty($separated) ? [] : explode($separator, $separated);
        if ($trim) {
            $list = array_map('trim', $list);
        }
        return $list;
    }

    /**
     * Appends a plural "s" or "'s".
     *
     * @param  string $subject
     * @param  int    $amount
     * @return string
     */
    public static function appendPluralS($subject, $amount)
    {
        return $subject . ($amount == 1 ? '' : (substr($subject, -1) == 's' ? '\'s' : 's'));
    }
}
