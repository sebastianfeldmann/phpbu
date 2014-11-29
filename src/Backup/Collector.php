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
    public static function getBackupFiles(Target $target)
    {
        $index = $target->getPathnameCompressed();
        if (!isset(self::$files[$index])) {
            $pathRaw         = $target->getPathRaw();
            $pathNotChanging = '';
            $changingDirs    = array();
            $files           = array();

            // collect all matching backup files
            self::collect($files, $target, $target->getPathThatIsNotChanging(), 0);
            // store collected files for others to use
            self::$files[$index] = $files;
        }
        return self::$files[$index];
    }

    /**
     * Recursive backup collecting
     *
     * @param array   $files
     * @param Target  $target
     * @param string  $path
     * @param integer $depth
     */
    protected static function collect(array &$files, Target $target, $path, $depth)
    {
        $dItter = new DirectoryIterator($path);
        // collect all matching subdirs and get there backup files
        if ($depth < $target->countChangingPathElements()) {
            foreach ($dItter as $i => $file) {
                if ($file->isDot()) {
                    continue;
                }
                // TODO: match directory against dir-regex $target->getChangingPathElements()[$depth]
                if ($file->isDir()) {
                    self::collect($files, $target, $file->getPathname(), $depth + 1);
                }
            }
        } else {
            // create regex to match only created backup files
            $fileRegex = String::datePlaceholdersToRegex($target->getFilenameRaw());
            if ($target->shouldBeCompressed()) {
                $fileRegex .= '.' . $target->getCompressor()->getSuffix();
            }
            foreach ($dItter as $i => $file) {
                if ($file->isDir()) {
                    continue;
                }
                // skip currently created backup
                if ($file->getPathname() == $target->getPathnameCompressed()) {
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
