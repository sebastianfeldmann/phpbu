<?php
namespace phpbu\App\Backup\File;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

class Ftp extends Remote
{
    /**
     * @var Filesystem
     */
    private $flySystem;

    /**
     * Ftp constructor.
     *
     * @param Filesystem $flySystem
     * @param array      $metadata
     */
    public function __construct(Filesystem $flySystem, array $metadata)
    {
        $this->flySystem = $flySystem;
        $this->filename     = $metadata['basename'];
        $this->size         = $metadata['size'];
        $this->pathname     = $metadata['path'];
        $this->lastModified = $this->flySystem->getTimestamp($this->pathname);
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->flySystem->delete($this->pathname);
        } catch (FileNotFoundException $e) {
            throw new \phpbu\App\Exception($e->getMessage());
        }
    }
}