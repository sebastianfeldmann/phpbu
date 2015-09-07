<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Cleanup backup directory.
 *
 * Removes oldest backup till the given quantity isn't exceeded anymore.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Quantity implements Cleaner
{
    /**
     * Amount of backups to keep
     *
     * @var integer
     */
    protected $amount;

    /**
     * Setup the Cleaner.
     *
     * @see    \phpbu\App\Backup\Cleanup::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Cleaner\Exception
     */
    public function setup(array $options)
    {
        if (!isset($options['amount'])) {
            throw new Exception('option \'amount\' is missing');
        }
        if (!is_numeric($options['amount'])) {
            throw new Exception(sprintf('invalid value for \'amount\': %s', $options['amount']));
        }
        if ($options['amount'] < 1) {
            throw new Exception(sprintf('value for \'amount\' must be greater 0, %s given', $options['amount']));
        }
        $this->amount = intval($options['amount']);
    }

    /**
     * Cleanup your backup directory.
     *
     * @see    \phpbu\App\Backup\Cleanup::cleanup()
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @throws \phpbu\App\Backup\Cleaner\Exception
     */
    public function cleanup(Target $target, Collector $collector, Result $result)
    {
        $files = $collector->getBackupFiles();

        if ($this->isCapacityExceeded($files)) {
            // oldest backups first
            ksort($files);

            while ($this->isCapacityExceeded($files)) {
                $file = array_shift($files);
                $result->debug(sprintf('delete %s', $file->getPathname()));
                if (!$file->isWritable()) {
                    throw new Exception(sprintf('can\'t delete file: %s', $file->getPathname()));
                }
                $result->debug(sprintf('delete %s', $file->getPathname()));
                $file->unlink();
            }
        }
    }

    /**
     * Returns true when the capacity is exceeded.
     *
     * @return boolean
     */
    private function isCapacityExceeded(array $files)
    {
        $totalFiles                  = count($files);
        $totalFilesPlusCurrentBackup = $totalFiles + 1;

        return $totalFiles > 0
            && $totalFilesPlusCurrentBackup > $this->amount;
    }
}
