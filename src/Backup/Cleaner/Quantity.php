<?php
namespace phpbu\Backup\Cleaner;

use phpbu\App\Result;
use phpbu\Backup\Cleaner;
use phpbu\Backup\Collector;
use phpbu\Backup\Target;

/**
 * Cleanup backup directory.
 *
 * Removes oldest backup till the given quantity isn't exceeded anymore.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
     * @see    \phpbu\Backup\Cleanup::setup()
     * @param  array $options
     * @throws \phpbu\Backup\Cleaner\Exception
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
     * @see    \phpbu\Backup\Cleanup::cleanup()
     * @param  \phpbu\Backup\Target    $target
     * @param  \phpbu\Backup\Collector $collector
     * @param  \phpbu\App\Result       $result
     * @throws \phpbu\Backup\Cleaner\Exception
     */
    public function cleanup(Target $target, Collector $collector, Result $result)
    {
        $files = $collector->getBackupFiles();

        // backups exceed capacity?
        if (count($files) > $this->amount) {
            // oldest backups first
            ksort($files);

            // add one for current backup
            while (count($files) + 1 > $this->amount) {
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
}
