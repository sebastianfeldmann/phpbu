<?php
namespace phpbu\App\Backup;

use phpbu\App\Util;

/**
 * Path class
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
     * Path without trailing slashes.
     *
     * @var string
     */
    private $path;

    /**
     * Raw path, that could contain placeholders.
     *
     * @var string
     */
    private $pathRaw;

    /**
     * Part of path, that is not changing because of placeholders.
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
     * List of not changing path elements.
     *
     * @var string[]
     */
    private $pathElementsNotChanging = [];

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
    private $isAbsolute;

    /**
     * Path constructor.
     *
     * @param string    $path
     * @param int|null  $time
     */
    public function __construct(string $path, $time = null)
    {
        $this->isAbsolute = Util\Path::hasLeadingSlash($path);
        $this->time       = $time;

        $this->setupPath(Util\Path::withoutTrailingSlash($path));
    }

    /**
     * Setup path
     *
     * @param string $path
     */
    private function setupPath(string $path)
    {
        $this->path                    = $path;
        $this->pathRaw                 = $path;
        $this->pathNotChanging         = $path;
        $this->pathElements            = Util\Path::getDirectoryListFromAbsolutePath($path);
        $this->pathElementsNotChanging = $this->pathElements;

        // if path contains date placeholders determine the path that is not changing
        // and create final path by replacing all placeholders
        if (Util\Path::isContainingPlaceholder($this->pathRaw)) {
            $this->handleChangingPath();
        }
    }

    /**
     * Find path elements that can't change because of placeholder usage.
     *
     * @return void
     */
    private function handleChangingPath()
    {
        $this->pathIsChanging          = true;
        $this->pathElementsNotChanging = [];
        $foundChangingElement          = false;

        // collect path elements that do not change
        foreach ($this->pathElements as $depth => $dir) {
            // already found placeholder or found one right now
            // path isn't static anymore so don't add directory to path not changing
            if ($foundChangingElement || Util\Path::isContainingPlaceholder($dir)) {
                $foundChangingElement = true;
                continue;
            }
            $this->pathElementsNotChanging[] = $dir;
        }
        $pathNotChanging = implode(DIRECTORY_SEPARATOR, $this->pathElementsNotChanging);
        if ($this->isAbsolute) {
            $pathNotChanging = substr($pathNotChanging, 1);
        }
        $this->pathNotChanging = $pathNotChanging;
        $this->path            = Util\Path::replaceDatePlaceholders($this->pathRaw, $this->time);
    }

    /**
     * Return path element at given index.
     *
     * @param  int $index
     * @return string
     */
    public function getPathElementAtIndex(int $index): string
    {
        return $this->pathElements[$index];
    }

    /**
     * Return the full target path depth.
     *
     * @return int
     */
    public function getPathDepth(): int
    {
        return count($this->pathElements);
    }

    /**
     * Return depth of path that is not changing.
     *
     * @return int
     */
    public function getPathThatIsNotChangingDepth(): int
    {
        return count($this->pathElementsNotChanging);
    }

    /**
     * Return the path to the backup file.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Return the path to the backup file.
     *
     * @return string
     */
    public function getPathRaw(): string
    {
        return $this->pathRaw;
    }

    /**
     * Is dirname configured with any date placeholders.
     *
     * @return bool
     */
    public function hasChangingPath(): bool
    {
        return $this->pathIsChanging;
    }

    /**
     * Return the part of the path that is not changing.
     *
     * @return string
     */
    public function getPathThatIsNotChanging(): string
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
