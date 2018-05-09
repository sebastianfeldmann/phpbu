<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
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
     * @var mixed <integer|double>
     */
    protected $capacityBytes;

    /**
     * Delete current backup as well
     *
     * @var bool
     */
    protected $deleteTarget;

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
        $this->deleteTarget = isset($options['deleteTarget'])
            ? Str::toBoolean($options['deleteTarget'], false)
            : false;
        $this->capacityRaw = $options['size'];
        $this->capacityBytes = $bytes;
    }

    /**
     * Simulate the cleanup execution.
     *
     * @param \phpbu\App\Backup\Target    $target
     * @param \phpbu\App\Backup\Collector $collector
     * @param \phpbu\App\Result           $result
     */
    public function simulate(Target $target, Collector $collector, Result $result)
    {
        $target->setSize('20000000');
        $result->debug('assuming backup size 20MB');

        // because there is no target file on disc to read
        // we have to deactivate the target handling
        // so $targetFile->getMTime or $targetFile->getSize will not be called
        if ($this->deleteTarget) {
            $this->deleteTarget = false;
            $result->debug('target will be deleted as well');
            $result->debug('delete ' . $target->getPathname());
        }
        parent::simulate($target, $collector, $result);
    }

    /**
     * Return list of files to delete.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File\Local[]
     * @throws \phpbu\App\Exception
     */
    protected function getFilesToDelete(Target $target, Collector $collector)
    {
        $files = $this->getDeletableBackups($target, $collector);
        $size = $target->getSize();
        $delete = [];

        // sum up the size of all backups
        /** @var \phpbu\App\Backup\File\Local $file */
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
                $size -= $file->getSize();
                $delete[] = $file;
            }
        }

        return $delete;
    }

    /**
     * Return a list of all deletable backups, including the currently created one if configured.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File\Local[]
     */
    protected function getDeletableBackups(Target $target, Collector $collector): array
    {
        $files = $collector->getBackupFiles();
        // should the currently created backup be deleted as well?
        if ($this->deleteTarget) {
            $file = $target->toFile();
            $index = date('YmdHis', $file->getMTime()) . '-' . count($files) . '-' . $file->getPathname();
            $files[$index] = $file;
        }
        return $files;
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
