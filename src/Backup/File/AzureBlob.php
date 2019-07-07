<?php
namespace phpbu\App\Backup\File;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\Blob;
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
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.2.7
 */
class AzureBlob extends Remote
{
    /**
     * Azure Blob client.
     *
     * @var BlobRestProxy
     */
    private $client;

    /**
     * @var string
     */
    protected $containerName;

    /**
     * AzureBlob constructor.
     *
     * @param BlobRestProxy $client
     * @param string        $containerName
     * @param Blob          $blob
     */
    public function __construct(BlobRestProxy $client, string $containerName, Blob $blob)
    {
        $this->client        = $client;
        $this->containerName = $containerName;
        $this->filename      = basename($blob->getName());
        $this->pathname      = $blob->getName();
        $props               = $blob->getProperties();
        $this->size          = $props->getContentLength();
        $this->lastModified  = $props->getLastModified()->getTimestamp();
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->client->deleteBlob($this->containerName, $this->pathname);
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
