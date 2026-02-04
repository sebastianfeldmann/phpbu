<?php
namespace phpbu\App\Backup\Collector;

use DirectoryIterator;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File\Local as FileLocal;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use SplFileInfo;

/**
 * Local collector class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Local extends Abstraction implements Collector
{
    /**
     * Constructor
     *
     * @param \phpbu\App\Backup\Target $target
     */
    public function __construct(Target $target)
    {
        $this->target = $target;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        $this->fileRegex = Util\Path::datePlaceholdersToRegex($this->target->getFilenameRaw());
        $this->collect($this->target->getPath()->getPathThatIsNotChanging());
    }

    /**
     * Recursive backup collecting.
     *
     * @param string $path
     */
    protected function collect(string $path)
    {
        $dirIterator = new DirectoryIterator($path);
        // collect all matching sub directories and get all the backup files
        $depth = Util\Path::getPathDepth($path);
        if ($depth < $this->target->getPath()->getPathDepth()) {
            foreach ($dirIterator as $file) {
                if ($file->isDot()) {
                    continue;
                }
                if ($this->isValidDirectory($file, $depth)) {
                    $this->collect($file->getPathname());
                }
            }
        } else {
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
        foreach ($dirIterator as $i => $splFile) {
            if ($splFile->isDir()) {
                continue;
            }
            if ($this->isFilenameMatch($splFile->getFilename())) {
                $file                = new FileLocal($splFile->getFileInfo());
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
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
        $dirTarget = $this->target->getPath()->getPathElementAtIndex($depth);
        $dirRegex  = Util\Path::datePlaceholdersToRegex($dirTarget);
        return preg_match('#' . $dirRegex . '#i', $dir);
    }
}
