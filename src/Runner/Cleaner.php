<?php
namespace phpbu\App\Runner;

use phpbu\App\Backup\Cleaner as CleanerExe;
use phpbu\App\Backup\Cleaner\Simulator;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Cleaner Runner
 *
 * @package    phpbu
 * @subpackage app
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Cleaner extends Abstraction
{
    /**
     * Execute or simulate the cleanup.
     *
     * @param \phpbu\App\Backup\Cleaner   $cleaner
     * @param \phpbu\App\Backup\Target    $target
     * @param \phpbu\App\Backup\Collector $collector
     * @param \phpbu\App\Result           $result
     */
    public function run(CleanerExe $cleaner, Target $target, Collector $collector, Result $result)
    {
        $this->isSimulation()
            ? $this->simulate($cleaner, $target, $collector, $result)
            : $this->clean($cleaner, $target, $collector, $result);
    }

    /**
     * Execute the cleanup.
     *
     * @param \phpbu\App\Backup\Cleaner   $cleaner
     * @param \phpbu\App\Backup\Target    $target
     * @param \phpbu\App\Backup\Collector $collector
     * @param \phpbu\App\Result           $result
     */
    protected function clean(CleanerExe $cleaner, Target $target, Collector $collector, Result $result)
    {
        $cleaner->cleanup($target, $collector, $result);
    }

    /**
     * Simulate the cleanup process.
     *
     * @param \phpbu\App\Backup\Cleaner   $cleaner
     * @param \phpbu\App\Backup\Target    $target
     * @param \phpbu\App\Backup\Collector $collector
     * @param \phpbu\App\Result           $result
     */
    protected function simulate(CleanerExe $cleaner, Target $target, Collector $collector, Result $result)
    {
        if ($cleaner instanceof Simulator) {
            $cleaner->simulate($target, $collector, $result);
        }
    }
}
