<?php
namespace phpbu\App\Util;

/**
 * Array Util
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.1.5
 */
abstract class Arr
{
    /**
     * Check array key for existence and value that is not the empty string
     *
     * @param  array  $arr
     * @param  string $key
     * @return boolean
     */
    public static function isSetAndNotEmptyString(array $arr, string $key)
    {
        return isset($arr[$key]) && '' !== $arr[$key];
    }

    /**
     * Return an array key if it exists, null or given default otherwise
     *
     * @param  array  $arr
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function getValue(array $arr, string $key, $default = null)
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
}
