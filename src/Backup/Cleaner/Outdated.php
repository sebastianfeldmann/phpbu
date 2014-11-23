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
 * Removes all files older then a given offset.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Outdated implements Cleaner
{
    /**
     * Original XML value
     *
     * @var string
     */
    protected $offsetRaw;

    /**
     * Offset in seconds.
     *
     * @var integer
     */
    protected $offsetSeconds;

    /**
     * @see \phpbu\Backup\Cleanup::setup()
     */
    public function setup(array $options)
    {
        if (!isset($options['older'])) {
            throw new RuntimeException('option \'older\' is missing');
        }
        $seconds = String::toTime($options['older']);
        if ($seconds < 1) {
            throw new RuntimeException(sprintf('invalid value for \'older\': %s', $options['older']));
        }
        $this->offsetRaw     = $options['older'];
        $this->offsetSeconds = $seconds;
    }

    /**
     * @see \phpbu\Backup\Cleanup::cleanup()
     */
    public function cleanup(Target $target, Result $result)
    {
        $path    = dirname($target);
        $dItter  = new DirectoryIterator($path);
        $minTime = time() - $this->offsetSeconds;
        // TODO: if target directory is dynamic %d or something like that
        foreach ($dItter as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }

            // last mod date < min date? delete!
            if ($fileInfo->getMTime() < $minTime) {
                $result->debug(sprintf('delete %s', $fileInfo->getPathname()));
                // TODO: check deletable...
                unlink($fileInfo->getPathname());
            }
        }
    }
}
