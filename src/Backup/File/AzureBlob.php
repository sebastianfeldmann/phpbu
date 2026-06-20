<?php
namespace phpbu\App\Backup\File;

use AzureOss\Storage\Blob\BlobContainerClient;
use AzureOss\Storage\Blob\Models\Blob;
use phpbu\App\Exception;

/**
 * AzureBlob class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.2.7
 */
class AzureBlob extends Remote
{
    /**
     * Azure Blob container client.
     *
     * @var BlobContainerClient
     */
    protected $client;

    /**
     * AzureBlob constructor.
     *
     * @param BlobContainerClient $client
     * @param Blob                $blob
     */
    public function __construct(BlobContainerClient $client, Blob $blob)
    {
        $this->client       = $client;
        $this->filename     = basename($blob->name);
        $this->pathname     = $blob->name;
        $this->size         = $blob->properties->contentLength;
        $this->lastModified = $blob->properties->lastModified->getTimestamp();
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->deleteBlob($this->pathname);
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * Delete the blob from the container.
     *
     * @param string $path
     */
    protected function deleteBlob(string $path): void
    {
        $this->client->getBlobClient($path)->delete();
    }
}
