<?php
namespace phpbu\Backup\Check;

use phpbu\App\Result;
use phpbu\Backup\Check;
use phpbu\Backup\Collector;
use phpbu\Backup\Target;
use phpbu\Util\String;

/**
 * SizeMin
 *
 * Checks if a backup file has a least a given size.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeMin implements Check
{
    /**
     * @see \phpbu\Backup\Check::pass()
     */
    public function pass(Target $target, $value, Collector $collector, Result $result)
    {
        // throws App\Exception if file doesn't exist
        $actualSize = $target->getSize();
        $testSize   = String::toBytes($value);

        return $testSize <= $actualSize;
    }
}
