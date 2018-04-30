<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Result;
use phpbu\App\Backup\Collector\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Str;

/**
 * SizeMin
 *
 * Checks if a backup file has a least a given size.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeMin implements Simulator
{
    /**
     * Execute the check.
     *
     * @param  \phpbu\App\Backup\Target          $target
     * @param  string                            $value
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @param  \phpbu\App\Result                 $result
     * @return bool
     * @throws \phpbu\App\Exception
     */
    public function pass(Target $target, $value, Local $collector, Result $result) : bool
    {
        // throws App\Exception if file doesn't exist
        $actualSize = $target->getSize();
        $testSize   = Str::toBytes($value);

        return $testSize <= $actualSize;
    }

    /**
     * Simulate check.
     *
     * @param \phpbu\App\Backup\Target          $target
     * @param string                            $value
     * @param \phpbu\App\Backup\Collector\Local $collector
     * @param \phpbu\App\Result                 $result
     * @return bool
     */
    public function simulate(Target $target, $value, Local $collector, Result $result) : bool
    {
        $result->debug('checking size to be at least ' . $value . PHP_EOL);
    }
}
