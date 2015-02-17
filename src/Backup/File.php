<?php
namespace phpbu\Backup;

use SplFileInfo;
use phpbu\App\Exception;

/**
 * File
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  2014 Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
 * @since      Class available since Release 1.0.0
 */
class File
{
    /**
     * FileInfo
     *
     * @var \SplFileInfo
     */
    protected $fileInfo;

    /**
     * Constructor
     *
     * @param SplFileInfo $fileInfo
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * FileInfo getter
     *
     * @return SplFileInfo
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * Return the filesize
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->fileInfo->getSize();
    }

    /**
     * Return the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->fileInfo->getFilename();
    }

    /**
     * Return the full path and filename
     *
     * @return string
     */
    public function getPathname()
    {
        return $this->fileInfo->getPathname();
    }

    /**
     * Return the path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->fileInfo->getPath();
    }

    /**
     * Return last modified date as unix timestamp
     *
     * @return integer
     */
    public function getMTime()
    {
        return $this->fileInfo->getMTime();
    }

    /**
     * Returns wether the file is writabe or not
     *
     * @return boolean
     */
    public function isWritable()
    {
        return $this->fileInfo->isWritable();
    }

    /**
     * Deletes the file
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        if (!unlink($this->fileInfo->getPathname())) {
            throw new Exception(sprintf('can\'t delete file: %s', $this->fileInfo->getPathname()));
        }
    }
}
