<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Backup\Check;
use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

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
interface Simulator extends Check
{
    /**
     * Simulate the check execution.
     *
     * @param  \phpbu\App\Backup\Target          $target
     * @param  string                            $value
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @param  \phpbu\App\Result                 $result
     * @return bool
     */
    public function simulate(Target $target, $value, Local $collector, Result $result) : bool;
}
