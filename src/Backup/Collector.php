<?php
namespace phpbu\Backup;

use DirectoryIterator;
use phpbu\Backup\Target;
use phpbu\Util\String;

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
abstract class Collector
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
     * Collection cache
     *
     * @var array
     */
    protected static $files = array();

    /**
     * Constructor
     *
     * @param Target $target
     */
    public function getBackupFiles(Target $target)
    {
        $index = $target->getPathnameCompressed();
        if (!isset(self::$files[$index])) {
            $pathRaw         = $target->getPathRaw();
            $pathNotChanging = '';
            $changingDirs    = array();
            $files           = array();

            if ($target->hasChangingPath()) {
                // path should be absolute so we remove the root slash
                $dirs = explode('/', substr($pathRaw, 1));

                $pathNotChanging = '';
                foreach ($dirs as $d) {
                    if (false !== strpos($d, '%')) {
                        $changingDirs[] = String::datePlaceholdersToRegex($d);
                    } else {
                        $pathNotChanging .= DIRECTORY_SEPARATOR . $d;
                    }
                }
            } else {
                $pathNotChanging = $target->getPath();
            }

            $fileRegex = String::datePlaceholdersToRegex($target->getNameRaw());
            if ($target->shouldBeCompressed()) {
                $fileRegex .= '.' . $target->getCompressor()->getSuffix();
            }

            // collect all matching backup files
            self::collect($files, $fileRegex,$pathNotChanging, 0, count($changingDirs));
            // store collected files for others to use
            self::$files[$index] = $files;
        }
        return self::$files[$index];
    }

    /**
     * Recursive backup collecting
     *
     * @param array   $files
     * @param string  $fileRegex
     * @param string  $path
     * @param integer $depth
     * @param integer $maxDepth
     */
    protected static function collect(&$files, $fileRegex, $path, $depth, $maxDepth)
    {
        $dItter = new DirectoryIterator($path);
        // collect all matching subdirs and get there backup files
        if ($depth < $maxDepth) {
            foreach ($dItter as $i => $file) {
                if ($file->isDot()) {
                    continue;
                }
                if ($file->isDir()) {
                    self::collect($files, $fileRegex, $file->getPathname(), $depth + 1, $maxDepth);
                }
            }
        } else {
            foreach ($dItter as $i => $file) {
                if ($file->isDir()) {
                    continue;
                }
                if (preg_match('#' . $fileRegex . '#i', $file->getFilename())) {
                    $index         = date('YmdHis', $file->getMTime()) . '-' . $i . '-' . $file->getPathname();
                    $files[$index] = $file->getFileInfo();
                }
            }
        }
    }
}
