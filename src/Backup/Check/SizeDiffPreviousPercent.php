<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Result;
use phpbu\App\Backup\Check;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Math;

/**
 * SizeDiffPreviousPercent class
 *
 * Checks if a backup filesize differs more than a given percent value as compared to the previous backup.
 * If no previous backup exists this check will pass.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeDiffPreviousPercent implements Check
{
    /**
     * @see    \phpbu\App\Backup\Check::pass()
     * @param  \phpbu\App\Backup\Target    $target
     * @param  string                      $value
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @return boolean
     * @throws \phpbu\App\Exception
     */
    public function pass(Target $target, $value, Collector $collector, Result $result)
    {
        // throws App\Exception if file doesn't exist
        $backupSize   = $target->getSize();
        $history      = $collector->getBackupFiles();
        $historyCount = count($history);
        $pass         = true;

        if ($historyCount > 0) {
            // oldest backups first
            ksort($history);
            /* @var $prevFile \SplFileInfo */
            $prevFile    = array_shift($history);
            $prevSize    = $prevFile->getSize();
            $diffPercent = Math::getDiffInPercent($backupSize, $prevSize);

            $pass = $diffPercent < $value;
        }

        return $pass;
    }
}
