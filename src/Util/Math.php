<?php
namespace phpbu\App\Util;

/**
 * Math Util class
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Math
{
    /**
     * Calculates the difference of two values in percent
     *
     * @param  int $a
     * @param  int $b
     * @return int
     */
    public static function getDiffInPercent(int $a, int $b) : int
    {
        if ($a > $b) {
            $whole = $a;
            $part  = $b;
        } else {
            $whole = $b;
            $part  = $a;
        }
        return intval(100 - ceil(($part / $whole) * 100));
    }
}
