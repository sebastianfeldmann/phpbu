<?php
namespace phpbu\App\Runner\Backup;

use phpbu\App\Backup\Sync as SyncExe;
use phpbu\App\Backup\Sync\Simulator;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Sync Runner
 *
 * @package    phpbu
 * @subpackage app
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class Sync extends Abstraction
{
    /**
     * Execute or simulate the sync.
     *
     * @param  \phpbu\App\Backup\Sync    $sync
     * @param  \phpbu\App\Backup\Target  $target
     * @param  \phpbu\App\Result         $result
     * @throws \phpbu\App\Exception
     */
    public function run(SyncExe $sync, Target $target, Result $result)
    {
        $this->isSimulation() ? $this->simulate($sync, $target, $result) : $this->runSync($sync, $target, $result);
    }

    /**
     * Execute the sync.
     *
     * @param  \phpbu\App\Backup\Sync    $sync
     * @param  \phpbu\App\Backup\Target  $target
     * @param  \phpbu\App\Result         $result
     * @throws \phpbu\App\Exception
     */
    protected function runSync(SyncExe $sync, Target $target, Result $result)
    {
        $sync->sync($target, $result);
    }

    /**
     * Simulate the sync process.
     *
     * @param  \phpbu\App\Backup\Sync    $sync
     * @param  \phpbu\App\Backup\Target  $target
     * @param  \phpbu\App\Result         $result
     * @throws \phpbu\App\Exception
     */
    protected function simulate(SyncExe $sync, Target $target, Result $result)
    {
        if ($sync instanceof Simulator) {
            $sync->simulate($target, $result);
        }
    }
}
