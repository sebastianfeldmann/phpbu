<?php
namespace phpbu\App\Backup\File;

use Kunnu\Dropbox\Dropbox as DropboxApi;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Models\FileMetadata;

class Dropbox extends Remote
{
    /**
     * @var DropboxApi
     */
    protected $client;

    /**
     * Dropbox constructor.
     *
     * @param DropboxApi   $client
     * @param FileMetadata $dropboxFile
     */
    public function __construct(DropboxApi $client, FileMetadata $dropboxFile)
    {
        $this->client = $client;
        $this->filename = $dropboxFile->getName();
        $this->pathname = $dropboxFile->getPathDisplay();
        $this->size = $dropboxFile->getSize();
        $this->lastModified = strtotime($dropboxFile->getClientModified());
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->client->delete($this->pathname);
        } catch (DropboxClientException $e) {
            throw new \phpbu\App\Exception($e->getMessage());
        }
    }
}
