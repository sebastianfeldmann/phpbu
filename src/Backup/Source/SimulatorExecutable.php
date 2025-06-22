<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Cli;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Executable Simulator class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
abstract class SimulatorExecutable extends Cli
{
    /**
     * Simulate the backup execution.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @return \phpbu\App\Backup\Source\Status
     */
    public function simulate(Target $target, Result $result) : Status
    {
        $result->debug('backup data:' . PHP_EOL . $this->getExecutable($target)->getCommandPrintable());

        return $this->createStatus($target);
    }

    /**
     * Create backup status.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Source\Status
     */
    abstract protected function createStatus(Target $target) : Status;
}
