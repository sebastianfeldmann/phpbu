<?php
namespace phpbu\App\Backup;

use phpbu\App\Result;

/**
 * Check
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
interface Check
{
    /**
     * Checks the created backup.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  string                      $value
     * @param  \phpbu\App\Backup\Collector
     * @return boolean
     */
    public function pass(Target $target, $value, Collector $collector);
}
