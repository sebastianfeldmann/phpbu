<?php
namespace phpbu\App\Backup\Collector;

use League\Flysystem\Filesystem;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

class Ftp extends Collector
{
    /**
     * @var Filesystem
     */
    private $flySystem;

    /**
     * @var string
     */
    private $path;

    /**
     * Ftp constructor.
     *
     * @param \phpbu\App\Backup\Target     $target
     * @param \League\Flysystem\Filesystem $flySystem
     */
    public function __construct(Target $target, Filesystem $flySystem)
    {
        $this->flySystem = $flySystem;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles(): array
    {
        $files = $this->flySystem->listContents();
        foreach ($files as $file) {
            if ($file['type'] != 'file') {
                continue;
            }
            // skip currently created backup
            if ($file['basename'] == $this->target->getFilename()) {
                continue;
            }
            if ($this->isFilenameMatch($file['basename'])) {
                $this->files[] = new \phpbu\App\Backup\File\Ftp($this->flySystem, $file);
            }
        }
        return $this->files;
    }
}
