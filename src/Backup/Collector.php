<?php
namespace phpbu\App\Backup;

use phpbu\App\Util;

/**
 * Collector class.
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
abstract class Collector
{
    /**
     * Absolute path to the directory where to store the backup.
     *
     * @var string
     */
    protected $path;

    /**
     * Path to the backup with potential date placeholders like %d.
     *
     * @var string
     */
    protected $pathRaw;

    /**
     * Part of the path without placeholders
     *
     * @var string
     */
    protected $pathNotChanging;

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
     * Setting up
     *
     * @param \phpbu\App\Backup\Target $target
     */
    public function setUp(Target $target)
    {
        $this->target    = $target;
        $this->fileRegex = Util\Path::datePlaceholdersToRegex($target->getFilenameRaw());
        $this->files     = [];
    }

    /**
     * Return true if target full path matches file and path regex.
     *
     * @param string $targetPath Full path to the remote file to check
     * @return bool
     */
    protected function isFileMatch(string $targetPath): bool
    {
        $rawPath = Util\Path::withoutLeadingSlash($this->pathRaw);
        $pathRegex = Util\Path::datePlaceholdersToRegex($rawPath);
        $pathRegex .= $pathRegex ? '/' : '';
        $fileRegex = Util\Path::datePlaceholdersToRegex($this->target->getFilenameRaw());
        return preg_match('#' . $pathRegex . $fileRegex . '$#i', $targetPath);
    }

    /**
     * Returns true if filename matches the target regex
     *
     * @param string $filename
     * @return bool
     */
    protected function isFilenameMatch(string $filename): bool
    {
        return preg_match('#' . $this->fileRegex . '#i', $filename);
    }

    /**
     * Directory setter.
     *
     * @param  string $path
     * @param  int    $time
     */
    protected function setPath($path, $time = null)
    {
        // remove trailing slashes
        $path                  = rtrim($path, DIRECTORY_SEPARATOR);
        $this->pathRaw         = $path;
        $this->pathNotChanging = $path;

        if (Util\Path::isContainingPlaceholder($path)) {
            $this->detectPathNotChanging($path);
            // replace potential date placeholder
            $path = Util\Path::replaceDatePlaceholders($path, $time);
        }

        $this->path = $path;
    }

    /**
     * Find path elements that can't change because of placeholder usage.
     *
     * @param string $path
     */
    protected function detectPathNotChanging(string $path)
    {
        $partsNotChanging     = [];
        $foundChangingElement = false;

        foreach (Util\Path::getDirectoryListFromAbsolutePath($path) as $depth => $dir) {
            // already found placeholder or found one right now
            // path isn't static anymore so don't add directory to path not changing
            if ($foundChangingElement || Util\Path::isContainingPlaceholder($dir)) {
                $foundChangingElement = true;
                continue;
            }
            // do not add the / element leading slash will be re-added later
            if ($dir !== '/') {
                $partsNotChanging[] = $dir;
            }
        }
        $this->pathNotChanging = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $partsNotChanging);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    abstract public function getBackupFiles() : array;
}
