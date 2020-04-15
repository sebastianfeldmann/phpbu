<?php
namespace phpbu\App\Backup\Cleaner;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Cleaner Abstraction
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
abstract class Abstraction
{
    /**
     * Backup Result to handle events and IO
     *
     * @var \phpbu\App\Result
     */
    protected $result;

    /**
     * Cleanup your backup directory
     *
     * @see    \phpbu\App\Backup\Cleanup::cleanup()
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @throws \phpbu\App\Exception
     */
    public function cleanup(Target $target, Collector $collector, Result $result)
    {
        $this->result = $result;
        foreach ($this->getFilesToDelete($target, $collector) as $file) {
            if (!$file->isWritable()) {
                throw new Exception(sprintf('can\'t delete file: %s', $file->getPathname()));
            }
            $result->debug(sprintf('delete %s', $file->getPathname()));
            $file->unlink();
        }
    }

    /**
     * Simulate the cleanup execution
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @param  \phpbu\App\Result           $result
     * @throws \phpbu\App\Exception
     */
    public function simulate(Target $target, Collector $collector, Result $result)
    {
        foreach ($this->getFilesToDelete($target, $collector) as $file) {
            $result->debug(sprintf('delete %s', $file->getPathname()));
        }
    }

    /**
     * Return list of files to delete
     *
     * @param  \phpbu\App\Backup\Target    $target
     * @param  \phpbu\App\Backup\Collector $collector
     * @return \phpbu\App\Backup\File[]
     * @throws \phpbu\App\Exception
     */
    abstract protected function getFilesToDelete(Target $target, Collector $collector);
}
