<?php
namespace phpbu\Backup\Check;

use phpbu\App\Result;
use phpbu\Backup\Check;
use phpbu\Backup\Target;
use phpbu\Util\Math;
use phpbu\Util\String;
use phpbu\Backup\Collector;

/**
 * ComparePercent class.
 * Checks if a backup filesize differs more than a given percent value comparing to the previous backup.
 * If no previous backup exists this check will pass.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeDiffPreviousPercent implements Check
{
    /**
     * @see \phpbu\Backup\Check::pass()
     */
    public function pass(Target $target, $value, Collector $collector, Result $result)
    {
        // throws App\Exception if file doesn't exist
        $backupSize   = $target->getSize();
        $history      = $collector->getBackupFiles();
        $historyCount = count($history);
        $result       = true;

        if ($historyCount > 0) {
            // oldest backups first
            ksort($history);
            /* @var $prevFile \SplFileInfo */
            $prevFile    = array_shift($history);
            $prevSize    = $prevFile->getSize();
            $diffPercent = Math::getDiffInPercent($backupSize, $prevSize);

            $result = $diffPercent < $value;
        }

        return $result;
    }
}
