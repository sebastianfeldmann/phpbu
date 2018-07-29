<?php
namespace phpbu\App\Cli;

use phpbu\App\Util\Time;

/**
 * Statistics class.
 *
 * @package    phpbu
 * @subpackage Cli
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.2
 */
final class Statistics
{
    /**
     * Returns a string like 'Time: 1 minute 20 seconds Memory: 3,5 MB'
     */
    public static function resourceUsage() : string
    {
        return \sprintf(
            'Time: %s, Memory: %4.2fMB',
            Time::formatTime(Time::timeSinceExecutionStart()),
            \memory_get_peak_usage(true) / 1048576
        );
    }
}
