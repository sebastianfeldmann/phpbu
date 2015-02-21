<?php
namespace phpbu\Backup\Cleaner;

use phpbu\App\Result;
use phpbu\Backup\Cleaner;
use phpbu\Backup\Collector;
use phpbu\Backup\Target;
use phpbu\Util\String;
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
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
     * @var integer
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
     * @see    \phpbu\Backup\Cleanup::setup()
     * @param  array $options
     * @throws \phpbu\Backup\Cleaner\Exception
     */
    public function setup(array $options)
    {
        if (!isset($options['size'])) {
            throw new Exception('option \'size\' is missing');
        }
        try {
            $bytes = String::toBytes($options['size']);
        } catch (RuntimeException $e) {
            throw new Exception($e->getMessage());
        }
        if ($bytes < 0) {
            throw new Exception(sprintf('invalid value for \'size\': %s', $options['size']));
        }
        $this->deleteTarget  = isset($options['deleteTarget'])
                             ? String::toBoolean($options['deleteTarget'], false)
                             : false;
        $this->capacityRaw   = $options['size'];
        $this->capacityBytes = $bytes;
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
        $size  = $target->getSize();
        
        /** @var \phpbu\Backup\File $file */
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
