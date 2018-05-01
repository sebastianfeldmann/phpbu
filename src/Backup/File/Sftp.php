<?php
namespace phpbu\App\Backup\File;

/**
 * File Sftp class
 *
 * @package phpbu\App\Backup\File
 */
class Sftp extends Remote
{
    /**
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * Sftp constructor.
     *
     * @param \phpseclib\Net\SFTP $sftp
     * @param array               $fileInfo
     */
    public function __construct(\phpseclib\Net\SFTP $sftp, array $fileInfo, string $remotePath)
    {
        $this->sftp         = $sftp;
        $this->filename     = $fileInfo['filename'];
        $this->pathname     = $remotePath . '/' . $fileInfo['filename'];
        $this->size         = $fileInfo['size'];
        $this->lastModified = $fileInfo['mtime'];
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        $this->sftp->delete($this->pathname);
    }
}