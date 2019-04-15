<?php
namespace phpbu\App\Util;

use RuntimeException;

/**
 * Time utility class.
 *
 * @package    phpbu
 * @subpackage Util
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.2
 */
class Time
{
    /**
     * Time formatting helper
     *
     * @var array
     */
    private static $times = [
        'hour'   => 3600,
        'minute' => 60,
        'second' => 1
    ];

    /**
     * Returns the time passed since execution start
     *
     * @throws \RuntimeException
     */
    public static function timeSinceExecutionStart() : float
    {
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $startOfRequest = $_SERVER['REQUEST_TIME_FLOAT'];
        } elseif (isset($_SERVER['REQUEST_TIME'])) {
            $startOfRequest = $_SERVER['REQUEST_TIME'];
        } else {
            throw new RuntimeException('Cannot determine time at which the execution started');
        }
        return \microtime(true) - $startOfRequest;
    }

    /**
     * Return string like '1 hour 3 minutes 12 seconds'
     *
     * @param  float $time
     * @return string
     */
    public static function formatTime(float $time) : string
    {
        $time      = $time < 1 ? 1 : round($time);
        $formatted = [];
        foreach (self::$times as $unit => $value) {
            if ($time >= $value) {
                $units = \floor($time / $value);
                $time -= $units * $value;
                $formatted[] = $units . ' ' . ($units == 1 ? $unit : $unit . 's');
            }
        }
        return implode(' ', $formatted);
    }
}
