<?php
namespace phpbu\Backup\Cleaner;

use phpbu\App\Result;
use phpbu\Backup\Cleaner;
use phpbu\Backup\Collector;
use phpbu\Backup\Target;
use phpbu\Util\String;

/**
 * Cleanup backup directory.
 *
 * Removes oldest backup till the given quantity isn't exceeded anymore.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Quantity implements Cleaner
{
    /**
     * Amount of backups to keep
     *
     * @var string
     */
    protected $amount;

    /**
     * @see \phpbu\Backup\Cleanup::setup()
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
     * @see \phpbu\Backup\Cleanup::cleanup()
     */
    public function cleanup(Target $target, Collector $collector, Result $result)
    {
        $path  = dirname($target);
        $files = $collector->getBackupFiles($target);

        // backups exceed capacity?
        if (count($files) > $this->amount) {
            // oldest backups first
            ksort($files);

            // add one for current backup
            while (count($files) + 1 > $this->amount) {
                $file = array_shift($files);
                $result->debug(sprintf('delete %s', $file->getPathname()));
                if (!$file->isWritable()) {
                    throw new Exception(sprintf('can\'t detele file: %s', $file->getPathname()));
                }
                $result->debug(sprintf('delete %s', $file->getPathname()));
                $file->unlink();
            }
        }
    }
}
