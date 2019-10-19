<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

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
class Quantity extends Abstraction implements Cleaner
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
     * Return list of files to delete.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File[]
     */
    protected function getFilesToDelete(Target $target, Collector $collector)
    {
        $files  = $collector->getBackupFiles();
        $delete = [];

        if ($this->isCapacityExceeded($files)) {
            // oldest backups first
            ksort($files);

            while ($this->isCapacityExceeded($files)) {
                $file     = array_shift($files);
                if ($file === null) {
                    break;
                }
                $delete[] = $file;
            }
        }

        return $delete;
    }

    /**
     * Returns true when the capacity is exceeded.
     *
     * @param  array $files
     * @return bool
     */
    private function isCapacityExceeded(array $files)
    {
        return count($files) > $this->amount;
    }
}
