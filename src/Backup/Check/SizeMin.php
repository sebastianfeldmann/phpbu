<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Result;
use phpbu\App\Backup\Check;
use phpbu\App\Backup\Collector;
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
class SizeMin implements Check
{
    /**
     * @see    \phpbu\App\Backup\Check::pass()
     * @param  \phpbu\App\Backup\Target    $target
     * @param  string                      $value
     * @param  \phpbu\App\Backup\Collector $collector
     * @return boolean
     * @throws \phpbu\App\Exception
     */
    public function pass(Target $target, $value, Collector $collector)
    {
        // throws App\Exception if file doesn't exist
        $actualSize = $target->getSize();
        $testSize   = Str::toBytes($value);

        return $testSize <= $actualSize;
    }
}
