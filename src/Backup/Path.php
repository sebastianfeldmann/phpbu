<?php
namespace phpbu\App\Backup;

use phpbu\App\Util;

/**
 * Path class.
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
class Path
{
    /**
     * Path
     *
     * @var string
     */
    private $path;

    /**
     * Raw path, that could contain placeholders
     *
     * @var string
     */
    private $pathRaw;

    /**
     * Part of path, that is permanent
     *
     * @var string
     */
    private $pathNotChanging;

    /**
     * Indicates if the path changes over time.
     *
     * @var bool
     */
    private $pathIsChanging = false;

    /**
     * List of all path elements.
     *
     * @var string[]
     */
    private $pathElements = [];

    /**
     * Time for replacing placeholders
     *
     * @var int
     */
    private $time;

    /**
     * Whether leading slash is needed or not
     *
     * @var bool
     */
    private $leadingSlash;

    /**
     * Whether trailing slash is needed or not
     *
     * @var bool
     */
    private $trailingSlash;

    /**
     * Path constructor.
     *
     * @param string   $path
     * @param int|null $time
     * @param bool     $leadingSlash
     * @param bool     $trailingSlash
     */
    public function __construct(string $path, $time = null, $leadingSlash = true, $trailingSlash = false)
    {
        $this->leadingSlash  = $leadingSlash;
        $this->trailingSlash = $trailingSlash;
        $this->pathRaw       = $path;
        $this->time          = $time;

        $this->setUp();
    }

    /**
     * Updates path according to provided parameters.
     */
    private function setUp()
    {
        if ($this->leadingSlash) {
            $this->pathRaw = Util\Path::withLeadingSlash($this->pathRaw);
        } else {
            $this->pathRaw = Util\Path::withoutLeadingSlash($this->pathRaw);
        }
        if ($this->trailingSlash) {
            $this->pathRaw = Util\Path::withTrailingSlash($this->pathRaw);
        } else {
            $this->pathRaw = Util\Path::withoutTrailingSlash($this->pathRaw);
        }

        $path = $this->pathRaw;
        if (Util\Path::isContainingPlaceholder($this->pathRaw)) {
            $this->pathIsChanging = true;
            $this->detectPathNotChanging($this->pathRaw);
            // replace potential date placeholder
            $path = Util\Path::replaceDatePlaceholders($this->pathRaw, $this->time);
        } else {
            $this->pathNotChanging = $path;
        }

        $this->path = $path;
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
     * Return the path to the backup file.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * Return the path to the backup file.
     *
     * @return string
     */
    public function getPathRaw() : string
    {
        return $this->pathRaw;
    }

    /**
     * Is dirname configured with any date placeholders.
     *
     * @return bool
     */
    public function hasChangingPath() : bool
    {
        return $this->pathIsChanging;
    }

    /**
     * Return the part of the path that is not changing.
     *
     * @return string
     */
    public function getPathThatIsNotChanging() : string
    {
        return $this->pathNotChanging;
    }

    /**
     * Return path when casted to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->path;
    }
}
