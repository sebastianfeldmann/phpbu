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
     * Path class.
     *
     * @var Path
     */
    protected $path;

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
    protected $files = [];

    /**
     * Setting up
     *
     * @param \phpbu\App\Backup\Target $target
     */
    public function setUp(Target $target)
    {
        $this->target    = $target;
        $this->fileRegex = Util\Path::datePlaceholdersToRegex($target->getFilenameRaw());
    }

    /**
     * Return true if target full path matches file and path regex.
     *
     * @param  string $targetPath Full path to the remote file to check
     * @return bool
     */
    protected function isFileMatch(string $targetPath) : bool
    {
        $rawPath    = Util\Path::withoutLeadingSlash($this->path->getPathRaw());
        $rawPath    = !empty($rawPath) ? Util\Path::withTrailingSlash($rawPath) : $rawPath;
        $pathRegex  = Util\Path::datePlaceholdersToRegex($rawPath);
        $fileRegex  = Util\Path::datePlaceholdersToRegex($this->target->getFilenameRaw());
        $targetPath = Util\Path::withoutLeadingSlash($targetPath);
        return preg_match('#' . $pathRegex . $fileRegex . '$#i', $targetPath);
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

    /**
     * @return Path
     */
    public function getPath() : Path
    {
        return $this->path;
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    abstract public function getBackupFiles() : array;
}
