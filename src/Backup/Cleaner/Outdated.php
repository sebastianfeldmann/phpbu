<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Cleaner;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util\String;
use RuntimeException;

/**
 * Cleanup backup directory.
 *
 * Removes all files older then a given offset.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
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
     * Setup the Cleaner.
     *
     * @see    \phpbu\App\Backup\Cleanup::setup()
     * @param  array $options
     * @throws \phpbu\App\Backup\Cleaner\Exception
     */
    public function setup(array $options)
    {
        if (!isset($options['older'])) {
            throw new Exception('option \'older\' is missing');
        }
        try {
            $seconds = String::toTime($options['older']);
        } catch (RuntimeException $e) {
            throw new Exception($e->getMessage());
        }
        $this->offsetRaw     = $options['older'];
        $this->offsetSeconds = $seconds;
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
        $minTime = time() - $this->offsetSeconds;
        $files   = $collector->getBackupFiles();

        /** @var \phpbu\App\Backup\File $file */
        foreach ($files as $file) {
            // last mod date < min date? delete!
            if ($file->getMTime() < $minTime) {
                if (!$file->isWritable()) {
                    throw new Exception(sprintf('can\'t delete file: %s', $file->getPathname()));
                }
                $result->debug(sprintf('delete %s', $file->getPathname()));
                $file->unlink();
            }
        }
    }
}
