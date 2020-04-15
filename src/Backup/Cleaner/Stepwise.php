<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Cleaner\Stepwise\Range;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;

/**
 * Cleanup backup directory.
 * Keep less and less backups over time
 *
 *         | for x days  | for x days        | for x weeks           | for x month                 | for x years
 *         | keep all    | keep one per day  | keep one per week     | keep one per month          | keep one per year
 * --------+-------------+-------------------+-----------------------+-----------------------------+------------------
 * backups | ............| . . . . . . . . . | .       .       .     | .                         . |
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Stepwise extends Abstraction implements Simulator
{
    /**
     * Current timestamp
     *
     * @var int
     */
    protected $timestamp;

    /**
     * Amount of days to keep all backups
     *
     * @var int
     */
    protected $daysToKeepAll;

    /**
     * Amount of days to keep at least one backup per day
     *
     * @var int
     */
    protected $daysToKeepDaily;

    /**
     * Amount of weeks to keep at least one backup per week
     *
     * @var int
     */
    protected $weeksToKeepWeekly;

    /**
     * Amount of month to keep at least one backup per month
     *
     * @var int
     */
    protected $monthToKeepMonthly;

    /**
     * Amount of years to keep at least one backup per year
     *
     * @var int
     */
    protected $yearsToKeepYearly;

    /**
     * List of ranges defined by the configured settings
     *
     * @var \phpbu\App\Backup\Cleaner\Stepwise\Range[]
     */
    protected $ranges;

    /**
     * Stepwise constructor.
     *
     * @param int $time
     */
    public function __construct(int $time = 0)
    {
        $this->timestamp = $time === 0 ? time() : $time;
    }

    /**
     * Setup the Cleaner
     *
     * @see    \phpbu\App\Backup\Cleanup::setup()
     * @param  array $options
     */
    public function setup(array $options)
    {
        $this->daysToKeepAll      = Arr::getValue($options, 'daysToKeepAll', 0);
        $this->daysToKeepDaily    = Arr::getValue($options, 'daysToKeepDaily', 0);
        $this->weeksToKeepWeekly  = Arr::getValue($options, 'weeksToKeepWeekly', 0);
        $this->monthToKeepMonthly = Arr::getValue($options, 'monthToKeepMonthly', 0);
        $this->yearsToKeepYearly  = Arr::getValue($options, 'yearsToKeepYearly', 0);

        $this->setupRanges();
    }

    /**
     * Setup the date ranges
     */
    protected function setupRanges()
    {
        // keep all backups for x days as specified by 'keep all'
        $start = $this->timestamp;
        $end   = mktime(0, 0, 0, date('m', $start), (int) date('d', $start) - $this->daysToKeepAll, date('Y', $start));
        $all   = new Range($start, $end, new Stepwise\Keeper\All());

        // define the range that keeps backups per day
        $end   = mktime(0, 0, 0, date('m', $end), (int) date('d', $end) - $this->daysToKeepDaily, date('Y', $end));
        $daily = new Range($all->getEnd(), $end, new Stepwise\Keeper\OnePerGroup('Ymd'));

        // define the range that keeps backups per week
        $month  = date('m', $end);
        $day    = (int) date('d', $end) - (7 * $this->weeksToKeepWeekly);
        $year   = date('Y', $end);
        $end    = mktime(0, 0, 0, $month, $day, $year);
        $weekly = new Range($daily->getEnd(), $end, new Stepwise\Keeper\OnePerGroup('YW'));

        // define the range that keeps backups per month
        $end     = mktime(0, 0, 0, (int) date('m', $end) - $this->monthToKeepMonthly, date('d', $end), date('Y', $end));
        $monthly = new Range($weekly->getEnd(), $end, new Stepwise\Keeper\OnePerGroup('Ym'));

        // define the range that keeps backups per year
        $end    = mktime(0, 0, 0, date('m', $end), date('d', $end), (int) date('Y', $end) - $this->yearsToKeepYearly);
        $yearly = new Range($monthly->getEnd(), $end, new Stepwise\Keeper\OnePerGroup('Y'));

        // delete all backups older then configured year range
        $delete = new Range($end, 0, new Stepwise\Keeper\None());

        $this->ranges = [$all, $daily, $weekly, $monthly, $yearly, $delete];
    }

    /**
     * Return list of files to delete
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File[]
     * @throws \phpbu\App\Exception
     */
    protected function getFilesToDelete(Target $target, Collector $collector)
    {
        $files  = $collector->getBackupFiles();
        $delete = [];

        // for each backup ...
        foreach ($files as $file) {
            // ... find the right date range ...
            $range = $this->getRangeForFile($file);
            // ... and check if this backup should be kept or deleted
            if (!$range->keep($file)) {
                $delete[] = $file;
            }
        }
        return $delete;
    }

    /**
     * Get matching range for given file
     *
     * @param  \phpbu\App\Backup\File $file
     * @return \phpbu\App\Backup\Cleaner\Stepwise\Range
     * @throws \phpbu\App\Backup\Cleaner\Exception
     */
    protected function getRangeForFile(File $file) : Range
    {
        foreach ($this->ranges as $range) {
            if ($file->getMTime() > $range->getEnd()) {
                return $range;
            }
        }
        throw new Exception('no range for file');
    }
}
