<?php
namespace phpbu\Backup;

use DirectoryIterator;
use phpbu\Backup\Target;

/**
 * Collector
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class Collector
{
    /**
     * Raw Path with potential date placeholders.
     *
     * @var string
     */
    protected $pathRaw;

    /**
     * Path that is not changing over time.
     *
     * @var string
     */
    protected $pathNotChanging;

    /**
     * List of dynamic directories
     *
     * @var array
     */
    protected $changingDirs = array();

    /**
     * Regular expression to match backup files
     *
     * @var string
     */
    protected $fileRegex;

    /**
     * Constructor
     *
     * @param Target $target
     */
    public function __construct(Target $target)
    {
        $this->pathRaw = $target->getPathRaw();
        if ($target->hasChangingPath()) {
            $dirs = explode('/', substr($this->pathRaw, 1));

            $this->pathNotChanging = '';
            foreach ($dirs as $d) {
                if (false !== strpos($d, '%')) {
                    $this->changingDirs[] = $this->createRegex($d);
                } else {
                    $this->pathNotChanging .= DIRECTORY_SEPARATOR . $d;
                }
            }
        } else {
            $this->pathNotChanging = $target->getPath();
        }

        $this->fileRegex = $this->createRegex($target->getNameRaw());
        if ($target->shouldBeCompressed()) {
            $this->fileRegex .= '.' . $target->getCompressor()->getSuffix();
        }
    }

    /**
     * Get all backup files
     *
     * @return array:
     */
    public function getBackupFiles()
    {
        $files = array();
        $this->collect($files, $this->pathNotChanging, 0);
        return $files;
    }

    /**
     * Recursive backup collecting
     *
     * @param array   $files
     * @param string  $path
     * @param integer $depth
     */
    public function collect(&$files, $path, $depth)
    {
        $dItter = new DirectoryIterator($path);
        // collect all matching subdirs and get there backup files
        if ($depth < count($this->changingDirs)) {
            foreach ($dItter as $i => $file) {
                if ($file->isDot()) {
                    continue;
                }
                if ($file->isDir()) {
                    // TODO: match
                    $this->collect($files, $file->getPathname(), $depth + 1);
                }
            }
        } else {
            foreach ($dItter as $i => $file) {
                if ($file->isDir()) {
                    continue;
                }
                if (preg_match('#' . $this->fileRegex . '#i', $file->getFilename())) {
                    $index         = date('YmdHis', $file->getMTime()) . '-' . $i . '-' . $file->getPathname();
                    $files[$index] = $file->getFileInfo();
                }
            }
        }
    }

    /**
     * Create a regex that matches the raw path considering possible date placeholders.
     *
     * @param  string $pathRaw
     * @return string
     */
    protected function createRegex($pathRaw)
    {
        $regex = preg_quote($pathRaw);
        return preg_replace('#%[a-z]#i', '[0-9a-z]+', $regex);
    }
}
