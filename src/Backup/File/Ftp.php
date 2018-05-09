<?php
namespace phpbu\App\Backup\File;

class Ftp extends Remote
{
    /**
     * @var resource
     */
    private $ftpConnection;

    /**
     * Ftp constructor.
     *
     * @param resource $ftpConnection
     * @param string   $filename
     */
    public function __construct($ftpConnection, string $filename)
    {
        $this->ftpConnection = $ftpConnection;
        $this->filename      = $filename;
        $this->pathname      = $filename;
        $this->size          = ftp_size($ftpConnection, $filename);
        $this->lastModified  = ftp_mdtm($ftpConnection, $filename);
    }

    /**
     * Deletes the file.
     */
    public function unlink()
    {
        ftp_delete($this->ftpConnection, $this->filename);
    }
}