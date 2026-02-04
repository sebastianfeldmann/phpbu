<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Util;
use phpbu\App\Backup\Path;
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
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
abstract class Remote extends Abstraction
{
    /**
     * Path class.
     *
     * @var \phpbu\App\Backup\Path
     */
    protected $path;

    /**
     * Setting up Target and fileRegex.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Path   $path
     */
    protected function setUp(Target $target, Path $path)
    {
        $this->target    = $target;
        $this->path      = $path;
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
}
