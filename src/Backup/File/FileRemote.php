<?php

namespace phpbu\App\Backup\File;

use phpbu\App\Backup\Sync;

class FileRemote implements File
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
     * FileRemote constructor.
     *
     * @param array $attributes
     * @param Sync  $sync
     */
    public function __construct(array $attributes, Sync $sync)
    {
        $this->sync = $sync;

        if (isset($attributes['name'])) {
            $this->filename = $attributes['name'];
        }
        if (isset($attributes['size'])) {
            $this->size = $attributes['size'];
        }
        if (isset($attributes['pathname'])) {
            $this->pathname = $attributes['pathname'];
        }
        if (isset($attributes['last_modified'])) {
            $this->lastModified = $attributes['last_modified'];
        }
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
        $this->sync->unlinkFile($this);
    }
}