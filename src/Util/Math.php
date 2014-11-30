<?php
namespace phpbu\Util;

class Math
{
    /**
     * Calculates the difference of to values in percent
     *
     * @param  integer $a
     * @param  integer $b
     * @return integer
     */
    public static function getDiffInPercent($a, $b)
    {
        if ( $a > $b ) {
            $whole = $a;
            $part  = $b;
        } else {
            $whole = $b;
            $part  = $a;
        }
        return 100 - ceil(($part / $whole) * 100);
    }
}
