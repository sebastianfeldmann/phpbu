<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

class Ftp extends Collector
{
    /**
     * FTP connection stream
     *
     * @var resource
     */
    private $ftpConnection;

    /**
     * Ftp constructor.
     *
     * @param \phpbu\App\Backup\Target     $target
     * @param resource                     $ftpConnection
     */
    public function __construct(Target $target, $ftpConnection)
    {
        $this->ftpConnection = $ftpConnection;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles(): array
    {
        $files = ftp_nlist($this->ftpConnection, '.');
        foreach ($files as $filename) {
            if ($filename == $this->target->getFilename()) {
                continue;
            }
            if ($this->isFilenameMatch($filename)) {
                $this->files[] = new \phpbu\App\Backup\File\Ftp($this->ftpConnection, $filename);
            }
        }
        return $this->files;
    }
}
