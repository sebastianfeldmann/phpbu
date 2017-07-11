<?php
namespace phpbu\App\Log\ResultFormatter;

use phpbu\App\Result;

/**
 * ResultFormatter base class
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
abstract class Abstraction
{
    /**
     * Returns summary data.
     *
     * @param  \phpbu\App\Result $result
     * @return array
     */
    public function getSummaryData(Result $result) : array
    {
        $start = $result->started();
        $end   = microtime(true);

        return [
            'status'       => $result->allOk() ? 0 : 1,
            'timestamp'    => (int) $start,
            'duration'     => round($end - $start, 4),
            'backupCount'  => count($result->getBackups()),
            'backupFailed' => $result->backupsFailedCount(),
            'errorCount'   => $result->errorCount(),
        ];
    }
}
