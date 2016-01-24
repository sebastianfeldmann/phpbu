<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Collector;
use phpbu\App\Result;
use phpbu\App\Backup\Target;

/**
 * Simulator interface.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
interface Simulator extends Cleaner
{
    /**
     * Simulate the backup execution.
     *
     * @param \phpbu\App\Backup\Target    $target
     * @param \phpbu\App\Backup\Collector $collector
     * @param \phpbu\App\Result           $result
     */
    public function simulate(Target $target, Collector $collector, Result $result);
}
