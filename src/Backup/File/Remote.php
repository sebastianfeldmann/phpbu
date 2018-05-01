<?php

namespace phpbu\App\Backup\File;

use phpbu\App\Backup\File;
use phpbu\App\Backup\Sync;

class Remote implements File
{
    /**
     * Sync class
     *
     * @var Sync
     */
    protected $sync;

    /**
     * File size
     *
     * @var int
     */
    protected $size;

    /**
     * Filename
     *
     * @var string
     */
    protected $filename;

    /**
     * Full path with filename
     *
     * @var string
     */
    protected $pathname;

    /**
     * File's last modified unix timestamp
     *
     * @var int
     */
    protected $lastModified;

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
        return $this->filename;
    }

    /**
     * Return the full path and filename.
     *
     * @return string
     */
    public function getPathname(): string
    {
        return $this->pathname;
    }

    /**
     * Return last modified date as unix timestamp.
     *
     * @return integer
     */
    public function getMTime(): int
    {
        return $this->lastModified;
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
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        throw new \phpbu\App\Exception("Method must be overrided in proper file class");
    }
}