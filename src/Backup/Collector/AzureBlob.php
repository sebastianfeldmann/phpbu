<?php
namespace phpbu\App\Backup\Collector;

use AzureOss\Storage\Blob\BlobContainerClient;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File\AzureBlob as BlobFile;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

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
class AzureBlob extends Remote implements Collector
{
    /**
     * @var \AzureOss\Storage\Blob\BlobContainerClient
     */
    protected $client;

    /**
     * AzureBlob constructor.
     *
     * @param \phpbu\App\Backup\Target                   $target
     * @param \phpbu\App\Backup\Path                     $path
     * @param \AzureOss\Storage\Blob\BlobContainerClient $client
     */
    public function __construct(Target $target, Path $path, BlobContainerClient $client)
    {
        $this->setUp($target, $path);
        $this->client = $client;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        $prefix = $this->getPrefix($this->path->getPathThatIsNotChanging());

        foreach ($this->listBlobs($prefix) as $blob) {
            if ($this->isFileMatch($blob->name)) {
                $file                = new BlobFile($this->client, $blob);
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
            }
        }
    }

    /**
     * List all blobs in the container matching the given prefix.
     *
     * Pagination is handled transparently by the Azure Blob SDK.
     *
     * @param  string $prefix
     * @return iterable<\AzureOss\Storage\Blob\Models\Blob>
     */
    protected function listBlobs(string $prefix): iterable
    {
        return $this->client->getBlobs($prefix);
    }

    /**
     * Return prefix for querying remote files and folders
     *
     * @param string $path
     * @return string
     */
    protected function getPrefix($path): string
    {
        $prefix = Util\Path::withoutLeadingSlash($path);
        $prefix = $prefix ? Util\Path::withTrailingSlash($prefix) : '';
        return $prefix;
    }
}
