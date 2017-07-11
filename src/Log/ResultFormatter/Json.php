<?php
namespace phpbu\App\Log\ResultFormatter;

use phpbu\App\Log\ResultFormatter;
use phpbu\App\Result;

/**
 * Json ResultFormatter
 *
 * @package    phpbu
 * @subpackage Log
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.0.0
 */
class Json extends Abstraction implements ResultFormatter
{
    /**
     * Create request body from phpbu result data.
     *
     * @param  \phpbu\App\Result $result
     * @return string
     */
    public function format(Result $result): string
    {
        $body            = $this->getSummaryData($result);
        $body['errors']  = $this->formatErrors($result->getErrors());
        $body['backups'] = $this->formatBackups($result->getBackups());

        return json_encode($body);
    }

    /**
     * Format exceptions.
     *
     * @param  array $exceptions
     * @return array
     */
    private function formatErrors(array $exceptions) : array
    {
        $errors = [];
        /* @var $e \Exception */
        foreach ($exceptions as $e) {
            $errors[] = [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ];
        }
        return $errors;
    }

    /**
     * Format backups.
     *
     * @param  array $results
     * @return array
     */
    private function formatBackups(array $results) : array
    {
        $backups = [];
        /* @var $backup \phpbu\App\Result\Backup */
        foreach ($results as $backup) {
            $backups[] = [
                'name'   => $backup->getName(),
                'status' => $backup->allOk() ? 0 : 1,
                'checks' => [
                    'executed' => $backup->checkCount(),
                    'failed'   => $backup->checkCountFailed()
                ],
                'crypt' => [
                    'executed' => $backup->cryptCount(),
                    'skipped'  => $backup->cryptCountSkipped(),
                    'failed'   => $backup->cryptCountFailed()
                ],
                'syncs' => [
                    'executed' => $backup->syncCount(),
                    'skipped'  => $backup->syncCountSkipped(),
                    'failed'   => $backup->syncCountFailed()
                ],
                'cleanup' => [
                    'executed' => $backup->cleanupCount(),
                    'skipped'  => $backup->cleanupCountSkipped(),
                    'failed'   => $backup->cleanupCountFailed()
                ]
            ];
        }

        return $backups;
    }
}
