<?php
namespace phpbu\App\Backup\Crypter;

use phpbu\App\Backup\Restore\Plan;
use phpbu\App\Backup\Target;

/**
 * Crypter Restorable interface.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 6.0.0
 */
interface Restorable
{
    /**
     * Decrypt the backup
     *
     * @param  \phpbu\App\Backup\Target       $target
     * @param  \phpbu\App\Backup\Restore\Plan $plan
     */
    public function restore(Target $target, Plan $plan);
}
