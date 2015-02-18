<?php
namespace phpbu\Backup;

use phpbu\App\Result;

/**
 * Check
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Check
{
    /**
     * Checks the created backup.
     *
     * @param  \phpbu\Backup\Target $target
     * @param  string $value
     * @param  \phpbu\Backup\Collector
     * @param  \phpbu\App\Result
     * @return boolean
     */
    public function pass(Target $target, $value, Collector $collector, Result $result);
}
