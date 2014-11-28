<?php
namespace phpbu\Backup\Cleaner;

use DirectoryIterator;
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
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
     * @see \phpbu\Backup\Cleanup::setup()
     */
    public function setup(array $options)
    {
        if (!isset($options['size'])) {
            throw new RuntimeException('option \'size\' is missing');
        }
        $bytes = String::toBytes($options['size']);
        if ($bytes < 1) {
            throw new RuntimeException(sprintf('invalid value for \'size\': %s', $options['size']));
        }
        $this->capacityRaw   = $options['size'];
        $this->capacityBytes = $bytes;
    }

    /**
     * @see \phpbu\Backup\Cleanup::cleanup()
     */
    public function cleanup(Target $target, Result $result)
    {
        $files = Collector::getBackupFiles($target);
        $size  = 0;

        foreach ($files as $file) {
            $size += $file->getSize();
        }

        // backups exceed capacity?
        if ($size > $this->capacityBytes) {
            // oldest backups first
            ksort($files);

            while ($size > $this->capacityBytes) {
                $file  = array_shift($files);
                $size -= $file->getSize();
                if (!$file->isWritable()) {
                    throw new RuntimeException(sprintf('can\'t detele file: %s', $file->getPathname()));
                }
                $result->debug(sprintf('delete %s', $file->getPathname()));
                unlink($file->getPathname());
            }
        }
    }
}
