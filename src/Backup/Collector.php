<?php
namespace phpbu\App\Backup;

use DirectoryIterator;
use SplFileInfo;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;

/**
 * Collector
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Collector
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
     * Constructor
     *
     * @param Target $target
     */
    public function __construct(Target $target)
    {
        $this->target = $target;
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles() : array
    {
        if (null === $this->files) {
            // create regex to match only created backup files
            $this->fileRegex = Str::datePlaceholdersToRegex($this->target->getFilenameRaw());
            $this->files     = [];
            // collect all matching backup files
            $this->collect($this->target->getPathThatIsNotChanging(), 0);
        }
        return $this->files;
    }

    /**
     * Recursive backup collecting.
     *
     * @param string $path
     * @param int    $depth
     */
    protected function collect(string $path, int $depth)
    {
        $dirIterator = new DirectoryIterator($path);
        // collect all matching sub directories and get all the backup files
        if ($depth < $this->target->countChangingPathElements()) {
            foreach ($dirIterator as $file) {
                if ($file->isDot()) {
                    continue;
                }
                if ($this->isValidDirectory($file, $depth)) {
                    $this->collect($file->getPathname(), $depth + 1);
                }
            }
        } else {
            /** @var \phpbu\App\Backup\File $file */
            $this->collectFiles($dirIterator);
        }
    }

    /**
     * Collect backup files in directory.
     *
     * @param \DirectoryIterator $dirIterator
     */
    protected function collectFiles(DirectoryIterator $dirIterator)
    {
        foreach ($dirIterator as $i => $file) {
            if ($file->isDir()) {
                continue;
            }
            // skip currently created backup
            if ($file->getPathname() == $this->target->getPathname()) {
                continue;
            }
            if (preg_match('#' . $this->fileRegex . '#i', $file->getFilename())) {
                $index               = date('YmdHis', $file->getMTime()) . '-' . $i . '-' . $file->getPathname();
                $this->files[$index] = new File($file->getFileInfo());
            }
        }
    }

    /**
     * Check if the iterated file is part of a valid target path.
     *
     * @param  \SplFileInfo $file
     * @param  int          $depth
     * @return bool
     */
    protected function isValidDirectory(SplFileInfo $file, int $depth)
    {
        return $file->isDir() && $this->isMatchingDirectory($file->getBasename(), $depth);
    }

    /**
     * Does a directory match the respective target path.
     *
     * @param  string $dir
     * @param  int    $depth
     * @return bool
     */
    protected function isMatchingDirectory(string $dir, int $depth)
    {
        $dirTarget = Arr::getValue($this->target->getChangingPathElements(), $depth);
        $dirRegex  = Str::datePlaceholdersToRegex($dirTarget);
        return preg_match('#' . $dirRegex . '#i', $dir);
    }
}
