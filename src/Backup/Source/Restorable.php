<?php
namespace phpbu\App\Backup\Source;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Source;
use phpbu\App\Backup\Target;

/**
 * Restorable interface.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.0
 */
interface Restorable extends Source
{
    /**
     * Restore the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     * @return \phpbu\App\Backup\Source\Status
     */
    public function restore(Target $target, Plan $plan): Status;
}
