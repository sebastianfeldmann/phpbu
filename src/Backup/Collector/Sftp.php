<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

class Sftp extends Collector
{
    /**
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * OpenStack remote path
     *
     * @var string
     */
    protected $path;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpseclib\Net\SFTP      $sftp
     * @param string                   $path
     */
    public function __construct(Target $target, \phpseclib\Net\SFTP $sftp, string $path)
    {
        $this->sftp = $sftp;
        $this->path = $path;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles(): array
    {
        $list = $this->sftp->_list($this->path);
        foreach ($list as $filename => $fileInfo) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }
            if ($fileInfo['type'] === 2) {
                continue;
            }
            // skip currently created backup
            if ($fileInfo['filename'] == $this->target->getFilename()) {
                continue;
            }
            if (preg_match('#' . $this->fileRegex . '#i', $fileInfo['filename'])) {
                $this->files[] = new \phpbu\App\Backup\File\Sftp($this->sftp, $fileInfo, $this->path);
            }
        }

        return $this->files;
    }
}