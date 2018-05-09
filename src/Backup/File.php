<?php

namespace phpbu\App\Backup;

/**
 * Interface File.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
interface File
{
    /**
     * Return the filesize.
     *
     * @return integer
     */
    public function getSize(): int;

    /**
     * Return the filename.
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Return the full path and filename.
     *
     * @return string
     */
    public function getPathname(): string;

    /**
     * Return last modified date as unix timestamp.
     *
     * @return integer
     */
    public function getMTime(): int;

    /**
     * Return whether the file is writable or not.
     *
     * @return boolean
     */
    public function isWritable(): bool;

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink();
}
