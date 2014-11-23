<?php
namespace phpbu\Backup\Cleaner;

use DirectoryIterator;
use phpbu\App\Result;
use phpbu\Backup\Cleaner;
use phpbu\Backup\Target;
use phpbu\Util\String;
use RuntimeException;

/**
 * Cleanup backup directory.
 *
 * Removes oldest backup till the given capacity issn't exceeded anymore.
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
        // TODO: if target directory is dynamic %d or something like that
        $path   = dirname($target);
        $dItter = new DirectoryIterator($path);
        $files  = array();
        $size   = 0;
        // sum filesize of al backups
        foreach ($dItter as $i => $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            $files[date('YmdHis', $fileInfo->getMTime()) . '_' . $i] = $fileInfo->getFileInfo();
            $size                                                   += $fileInfo->getSize();
        }

        // backups exceed capacity?
        if ($size > $this->capacityBytes) {
            // oldest backups first
            ksort($files);

            while ($size > $this->capacityBytes) {
                $fileInfo = array_shift($files);
                $size    -= $fileInfo->getSize();
                $result->debug(sprintf('delete %s', $fileInfo->getPathname()));
                // TODO: check deletable...
                unlink($fileInfo->getPathname());
            }
        }
    }
}
