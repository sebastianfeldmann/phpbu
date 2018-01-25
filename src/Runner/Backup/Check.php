<?php
namespace phpbu\App\Runner\Backup;

use phpbu\App\Backup\Check as CheckExe;
use phpbu\App\Backup\Check\Simulator;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Check Runner class.
 *
 * @package    phpbu
 * @subpackage App
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Check extends Abstraction
{
    /**
     * Executes or simulate backup check.
     *
     * @param  \phpbu\App\Backup\Check     $check
     * @param  \phpbu\App\Backup\Target    $target
     * @param  mixed                       $value
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @return bool
     */
    public function run(CheckExe $check, Target $target, $value, Collector $collector, Result $result) : bool
    {
        return $this->isSimulation()
            ? $this->simulate($check, $target, $value, $collector, $result)
            : $this->runCheck($check, $target, $value, $collector, $result);
    }

    /**
     * Execute the backup check.
     *
     * @param  \phpbu\App\Backup\Check     $check
     * @param  \phpbu\App\Backup\Target    $target
     * @param  mixed                       $value
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @return bool
     */
    protected function runCheck(CheckExe $check, Target $target, $value, Collector $collector, Result $result) : bool
    {
        return $check->pass($target, $value, $collector, $result);
    }

    /**
     * Simulate the backup check.
     *
     * @param  \phpbu\App\Backup\Check     $check
     * @param  \phpbu\App\Backup\Target    $target
     * @param  mixed                       $value
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @return bool
     */
    protected function simulate(CheckExe $check, Target $target, $value, Collector $collector, Result $result) : bool
    {
        return ($check instanceof Simulator) ? $check->simulate($target, $value, $collector, $result) : true;
    }
}
