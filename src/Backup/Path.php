<?php
namespace phpbu\App\Backup;

use phpbu\App\Util;

/**
 * Trait Path
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
trait Path
{
    /**
     * Absolute path to the directory where to store the backup.
     *
     * @var string
     */
    private $path;

    /**
     * Path to the backup with potential date placeholders like %d.
     *
     * @var string
     */
    private $pathRaw;

    /**
     * Indicates if the path changes over time.
     *
     * @var bool
     */
    private $pathIsChanging = false;

    /**
     * Part of the path without placeholders
     *
     * @var string
     */
    private $pathNotChanging;

    /**
     * List of all path elements.
     *
     * @var string[]
     */
    private $pathElements = [];

    /**
     * Directory setter.
     *
     * @param  string $path
     * @param  int    $time
     */
    public function setPath($path, $time = null)
    {
        // remove trailing slashes
        $path                  = rtrim($path, DIRECTORY_SEPARATOR);
        $this->pathRaw         = $path;
        $this->pathNotChanging = $path;

        if (Util\Path::isContainingPlaceholder($path)) {
            $this->pathIsChanging = true;
            $this->detectPathNotChanging($path);
            // replace potential date placeholder
            $path = Util\Path::replaceDatePlaceholders($path, $time);
        }

        $this->path = $path;
    }

    /**
     * Return path element at given index.
     *
     * @param  int $index
     * @return string
     */
    public function getPathElementAtIndex(int $index) : string
    {
        return $this->pathElements[$index];
    }

    /**
     * Return the full target path depth.
     *
     * @return int
     */
    public function getPathDepth() : int
    {
        return count($this->pathElements);
    }

    /**
     * Find path elements that can't change because of placeholder usage.
     *
     * @param string $path
     */
    private function detectPathNotChanging(string $path)
    {
        $partsNotChanging     = [];
        $foundChangingElement = false;

        foreach (Util\Path::getDirectoryListFromAbsolutePath($path) as $depth => $dir) {
            $this->pathElements[] = $dir;

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
}
