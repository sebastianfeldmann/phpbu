<?php

namespace phpbu\App\Backup;

use phpbu\App\Util\Str;

abstract class Collector
{
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
    protected $files;

    /**
     * Setting up
     *
     * @param \phpbu\App\Backup\Target $target
     */
    public function setUp(Target $target)
    {
        $this->target = $target;
        $this->fileRegex = Str::datePlaceholdersToRegex($target->getFilenameRaw());
        $this->files     = [];
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    abstract public function getBackupFiles() : array;
}