<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File\Local;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;
use RuntimeException;

/**
 * Cleanup backup directory.
 *
 * Removes oldest backup till the given capacity isn't exceeded anymore.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Capacity extends Abstraction implements Simulator
{
    /**
     * Original XML value
     *
     * @var string
     */
    protected $capacityRaw;

    /**
     * Capacity in bytes.
     *
     * @var int|float
     */
    protected $capacityBytes;

    /**
     * Setup the the Cleaner.
     *
     * @see    \phpbu\App\Backup\Cleanup::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Cleaner\Exception
     */
    public function setup(array $options)
    {
        if (!isset($options['size'])) {
            throw new Exception('option \'size\' is missing');
        }
        try {
            $bytes = Str::toBytes($options['size']);
        } catch (RuntimeException $e) {
            throw new Exception($e->getMessage());
        }
        $this->capacityRaw   = $options['size'];
        $this->capacityBytes = $bytes;
    }

    /**
     * Return list of files to delete.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File[]
     * @throws \phpbu\App\Exception
     */
    protected function getFilesToDelete(Target $target, Collector $collector)
    {
        $files  = $collector->getBackupFiles();
        $size   = $target->getSize();
        $delete = [];



        // sum up the size of all backups
        /** @var \phpbu\App\Backup\File $file */
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        // check if backups exceed capacity?
        if ($this->isCapacityExceeded($size)) {
            // sort backups by date, oldest first, key 'YYYYMMDDHHIISS-NR-PATH'
            ksort($files);

            while ($this->isCapacityExceeded($size) && count($files) > 0) {
                // get oldest backup from list, move it to delete list
                $file = array_shift($files);
                if ($file === null) {
                    break;
                }
                $size -= $file->getSize();
                $delete[] = $file;
            }
        }

        return $delete;
    }

    /**
     * Is a given size bigger than the configured capacity limit.
     *
     * @param  int|double $currentCapacity
     * @return bool
     */
    protected function isCapacityExceeded($currentCapacity): bool
    {
        return $currentCapacity > $this->capacityBytes;
    }
}
