<?php
namespace phpbu\App\Backup\Check;

use phpbu\App\Result;
use phpbu\App\Backup\Collector\Local;
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
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class SizeDiffPreviousPercent implements Simulator
{
    /**
     * Execute check.
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
        $result->debug('checking size difference ' . $value . '%' . PHP_EOL);

        // throws App\Exception if file doesn't exist
        $backupSize   = $target->getSize();
        $history      = $collector->getBackupFiles();
        $historyCount = count($history);
        $pass         = true;

        if ($historyCount > 1) {
            // latest backups first
            krsort($history);
            // grab the second backup in the history as it should be the previous one
            $previousBackup = $history[array_keys($history)[1]];
            $prevSize       = $previousBackup->getSize();
            $diffPercent    = Math::getDiffInPercent($backupSize, $prevSize);

            $result->debug('size difference is ' . $diffPercent . '%' . PHP_EOL);
            $pass = $diffPercent < $value;
        }

        return $pass;
    }

    /**
     * Simulate the check execution
     *
     * @param  \phpbu\App\Backup\Target          $target
     * @param  string                            $value
     * @param  \phpbu\App\Backup\Collector\Local $collector
     * @param  \phpbu\App\Result                 $result
     * @return bool
     */
    public function simulate(Target $target, $value, Local $collector, Result $result): bool
    {
        $result->debug('checking size difference ' . $value . '%' . PHP_EOL);
        return true;
    }
}
