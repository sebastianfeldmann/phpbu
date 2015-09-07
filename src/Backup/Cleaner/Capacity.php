<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Cleaner;
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
class Capacity implements Cleaner
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
     * @var boolean
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
        $this->deleteTarget  = isset($options['deleteTarget'])
                             ? Str::toBoolean($options['deleteTarget'], false)
                             : false;
        $this->capacityRaw   = $options['size'];
        $this->capacityBytes = $bytes;
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
        $size  = $target->getSize();

        /** @var \phpbu\App\Backup\File $file */
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        // backups exceed capacity?
        if ($size > $this->capacityBytes) {
            // oldest backups first
            ksort($files);

            while ($size > $this->capacityBytes && count($files) > 0) {
                $file  = array_shift($files);
                $size -= $file->getSize();
                if (!$file->isWritable()) {
                    throw new Exception(sprintf('can\'t delete file: %s', $file->getPathname()));
                }
                $result->debug(sprintf('delete %s', $file->getPathname()));
                $file->unlink();
            }

            // deleted all old backups but still exceeding the space limit
            // delete the currently created backup as well
            if ($this->deleteTarget && $size > $this->capacityBytes) {
                $target->unlink();
            }
        }
    }
}
