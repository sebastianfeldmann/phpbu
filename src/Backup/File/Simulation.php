<?php
namespace phpbu\App\Backup\File;

use phpbu\App\Backup\File;

/**
 * Simulation
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class Simulation implements File
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $file;

    /**
     * Constructor
     *
     * @param int    $time
     * @param int    $size
     * @param string $path
     * @param string $file
     */
    public function __construct(int $time, int $size, string $path, string $file)
    {
        $this->time = $time;
        $this->size = $size;
        $this->path = $path;
        $this->file = $file;
    }

    /**
     * Return the filesize.
     *
     * @return integer
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Return the filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->file;
    }

    /**
     * Return the full path and filename.
     *
     * @return string
     */
    public function getPathname(): string
    {
        return $this->path . '/' . $this->file;
    }

    /**
     * Return the path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return last modified date as unix timestamp.
     *
     * @return integer
     */
    public function getMTime(): int
    {
        return $this->time;
    }

    /**
     * Return whether the file is writable or not.
     *
     * @return boolean
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * Deletes the file.
     */
    public function unlink()
    {
        // do nothing
    }
}
