<?php
namespace phpbu\Util;

use RuntimeException;

/**
 * String utility class.
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
abstract class String
{
    /**
     * Date placeholder replacement.
     * Replaces %{somevalue} with date({somevalue}).
     *
     * @param  string $string
     * @return string
     */
    public static function replaceDatePlaceholders($string)
    {
        if (false !== strpos($string, '%')) {
            $string = preg_replace_callback(
                '#%([a-zA-Z])#',
                function ($match) {
                    return date($match[1]);
                },
                $string
            );
        }
        return $string;
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
     * Returns given size in bytes.
     *   e.g. 1K => 1024
     *
     * @param  string $value
     * @throws RuntimeException
     * @return integer
     */
    public function toBytes($value)
    {
        if (!preg_match('#^[1-9]+[0-9]*[BKMGT]$#i', $value)) {
            throw new RuntimeException('Invalid size value');
        }
        $sizes  = array('B' => 0, 'K' => 1, 'M' => 2, 'G' => 3, 'T' => 4, 'P' => 5);
        $unit   = strtoupper(substr($value, -1));
        $number = substr($value, 0, -1);

        return $number * pow(1024, $sizes[$unit]);
    }
}
