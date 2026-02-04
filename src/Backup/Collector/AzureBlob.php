<?php
namespace phpbu\App\Backup\Collector;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
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
     * @var \MicrosoftAzure\Storage\Blob\BlobRestProxy
     */
    protected $client;

    /**
     * Azure Blob Storage Container name
     *
     * @var string
     */
    protected $containerName;

    /**
     * Amazon S3 constructor.
     *
     * @param \phpbu\App\Backup\Target                   $target
     * @param \phpbu\App\Backup\Path                     $path
     * @param \MicrosoftAzure\Storage\Blob\BlobRestProxy $client
     * @param string                                     $containerName
     */
    public function __construct(Target $target, Path $path, BlobRestProxy $client, string $containerName)
    {
        $this->setUp($target, $path);
        $this->client = $client;
        $this->containerName = $containerName;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix($this->getPrefix($this->path->getPathThatIsNotChanging()));
        $listBlobsOptions->setMaxResults(10);

        do {
            $blobList = $this->client->listBlobs($this->containerName, $listBlobsOptions);
            foreach ($blobList->getBlobs() as $blob) {
                if ($this->isFileMatch($blob->getName())) {
                    $file                = new BlobFile($this->client, $this->containerName, $blob);
                    $index               = $this->getFileIndex($file);
                    $this->files[$index] = $file;
                }
            }
            $listBlobsOptions->setContinuationToken($blobList->getContinuationToken());
        } while ($blobList->getContinuationToken());
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
