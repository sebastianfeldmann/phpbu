<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Cleanup Abstraction.
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
abstract class Abstraction
{
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
        foreach ($this->getFilesToDelete($target, $collector) as $file) {
            if (!$file->isWritable()) {
                throw new Exception(sprintf('can\'t delete file: %s', $file->getPathname()));
            }
            $result->debug(sprintf('delete %s', $file->getPathname()));
            $file->unlink();
        }
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
        foreach ($this->getFilesToDelete($target, $collector) as $file) {
            $result->debug(sprintf('delete %s', $file->getPathname()));
        }
    }

    /**
     * Return list of files to delete.
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File[]
     * @throws \phpbu\App\Exception
     */
    abstract protected function getFilesToDelete(Target $target, Collector $collector);
}
