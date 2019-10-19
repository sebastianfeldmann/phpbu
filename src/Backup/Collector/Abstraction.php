<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\File;
use phpbu\App\Util;
use phpbu\App\Backup\Target;

/**
 * Abstraction class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
abstract class Abstraction
{
    /**
     * Backup target
     *
     * @var \phpbu\App\Backup\Target
     */
    protected $target;

    /**
     * Target filename regex
     *
     * @var string
     */
    protected $fileRegex;

    /**
     * Collection cache
     *
     * @var \phpbu\App\Backup\File[]
     */
    protected $files;

    /**
     * Indicates if current execution is a simulation.
     *
     * @var bool
     */
    protected $isSimulation = false;

    /**
     * Index count for collected backups.
     *
     * @var int
     */
    protected $index = 0;

    /**
     * Indicate that this is a simulation and make sure the collector includes a fake target.
     *
     * @param bool $isSimulation
     */
    public function setSimulation(bool $isSimulation)
    {
        $this->isSimulation = $isSimulation;
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles() : array
    {
        if (null === $this->files) {
            $this->files = [];
            $this->collectBackups();
            // if current run is a simulation
            // add a fake target to the collected files
            if ($this->isSimulation) {
                $file = new File\Simulation(
                    time(),
                    20000000,
                    $this->target->getPath()->getPath(),
                    $this->target->getFilename()
                );
                $this->files[$this->getFileIndex($file)] = $file;
            }
        }
        return $this->files;
    }

    /**
     * Collect all created backups.
     *
     * @return void
     */
    abstract protected function collectBackups();

    /**
     * Return an array index for a given file for key sorting the list later.
     *
     * @param  \phpbu\App\Backup\File $file
     * @return string
     */
    protected function getFileIndex(File $file) : string
    {
        return $file->getMTime() . '-' . $file->getFilename() . '-' . $this->index++;
    }

    /**
     * Returns true if filename matches the target regex
     *
     * @param  string $filename
     * @return bool
     */
    protected function isFilenameMatch(string $filename) : bool
    {
        return preg_match('#' . $this->fileRegex . '#i', $filename);
    }
}
