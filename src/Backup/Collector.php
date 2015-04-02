<?php
namespace phpbu\App\Backup;

use DirectoryIterator;
use phpbu\App\Util\Str;

/**
 * Collector
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
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
     * Collection cache
     *
     * @var array<\phpbu\App\Backup\File>
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
     * @return array<\phpbu\App\Backup\File>
     */
    public function getBackupFiles()
    {
        if (null === $this->files) {
            $this->files = array();
            // collect all matching backup files
            $this->collect($this->target->getPathThatIsNotChanging(), 0);
        }
        return $this->files;
    }

    /**
     * Recursive backup collecting.
     *
     * @param string  $path
     * @param integer $depth
     */
    protected function collect($path, $depth)
    {
        $dItter = new DirectoryIterator($path);
        // collect all matching subdirs and get all the backup files
        if ($depth < $this->target->countChangingPathElements()) {
            foreach ($dItter as $i => $file) {
                if ($file->isDot()) {
                    continue;
                }
                // TODO: match directory against dir-regex Target::getChangingPathElements
                if ($file->isDir()) {
                    $this->collect($file->getPathname(), $depth + 1);
                }
            }
        } else {
            // create regex to match only created backup files
            $fileRegex = Str::datePlaceholdersToRegex($this->target->getFilenameRaw());
            if ($this->target->shouldBeCompressed()) {
                $fileRegex .= '.' . $this->target->getCompressor()->getSuffix();
            }
            /** @var \SplFileInfo $file */
            foreach ($dItter as $i => $file) {
                if ($file->isDir()) {
                    continue;
                }
                // skip currently created backup
                if ($file->getPathname() == $this->target->getPathname()) {
                    continue;
                }
                if (preg_match('#' . $fileRegex . '#i', $file->getFilename())) {
                    $index = date('YmdHis', $file->getMTime()) . '-' . $i . '-' . $file->getPathname();
                    $this->files[$index] = new File($file->getFileInfo());
                }
            }
        }
    }
}
