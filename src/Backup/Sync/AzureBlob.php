<?php
namespace phpbu\App\Backup\Sync;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Azure Blob Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.2.7
 */
class AzureBlob implements Simulator
{
    use Cleanable;

    /**
     * Azure Blob client.
     *
     * @var BlobRestProxy;
     */
    private $client;


    /**
     * Azure Blob Connection String
     *
     * @var string
     */
    private $connectionString;

    /**
     * Azure Blob Container Name
     *
     * @var string
     */
    private $containerName;

    /**
     * Azure Blob remote path
     *
     * @var string
     */
    protected $path;

    /**
     * Azure Blob remote raw path
     *
     * @var string
     */
    protected $pathRaw;

    /**
     * Unix timestamp of generating path from placeholder.
     *
     * @var int
     */
    protected $time;

    /**
     * Configure the sync.
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\MicrosoftAzure\\Storage\\Blob\\BlobRestProxy')) {
            throw new Exception('Azure Bob Storage SDK not loaded: use composer to install ' .
                                         '"microsoft/azure-storage-blob"');
        }

        // check for mandatory options
        $this->validateConfig($config, ['connection_string', 'container_name', 'path']);

        $cleanedPath            = Util\Path::withoutTrailingSlash(Util\Path::withoutLeadingSlash($config['path']));
        $this->time             = time();
        $this->connectionString = $config['connection_string'];
        $this->containerName    = $config['container_name'];
        $this->path             = Util\Path::replaceDatePlaceholders($cleanedPath, $this->time);
        $this->pathRaw          = $cleanedPath;
        $this->setUpCleanable($config);
    }

    /**
     * Make sure all mandatory keys are present in given config.
     *
     * @param  array $config
     * @param  array $keys
     * @throws Exception
     */
    protected function validateConfig(array $config, array $keys)
    {
        foreach ($keys as $option) {
            if (!Util\Arr::isSetAndNotEmptyString($config, $option)) {
                throw new Exception($option . ' is mandatory');
            }
        }
    }

    /**
     * Execute the sync.
     *
     * @see    \phpbu\App\Backup\Sync::sync()
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $this->client = $this->createClient();

        if (!$this->doesContainerExist($this->client, $this->containerName)) {
            $result->debug('create blob container');
            $this->createContainer($this->client);
        }

        try {
            $this->upload($target, $this->client);
            $result->debug('upload: done');

            // run remote cleanup
            $this->cleanup($target, $result);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * Create the Azure Blob client.
     *
     * @return \MicrosoftAzure\Storage\Blob\BlobRestProxy
     */
    protected function createClient() : BlobRestProxy
    {
        return BlobRestProxy::createBlobService($this->connectionString);
    }

    /**
     * Creates collector for Azure Blob Storage
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target) : Collector
    {
        $path = new Path($this->pathRaw, $this->time);
        return new Collector\AzureBlob($target, $path, $this->client, $this->containerName);
    }

    /**
     * Simulate the sync execution.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug(
            'sync backup to Azure Blob' . PHP_EOL
            . '  connectionString: ********' . PHP_EOL
            . '  containerName:    ' . $this->containerName . PHP_EOL
        );

        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Check if an Azure Blob Storage Container exists
     *
     * @param \MicrosoftAzure\Storage\Blob\BlobRestProxy $blobRestProxy
     * @param string                                     $containerName
     * @return bool
     */
    private function doesContainerExist(BlobRestProxy $blobRestProxy, string $containerName): bool
    {
        $containers = $blobRestProxy->listContainers()->getContainers();
        foreach ($containers as $container) {
            if ($container->getName() === $containerName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Create an Azure Storage Container
     *
     * @param \MicrosoftAzure\Storage\Blob\BlobRestProxy $blobRestProxy
     */
    private function createContainer(BlobRestProxy $blobRestProxy)
    {
        $blobRestProxy->createContainer($this->containerName);
    }

    /**
     * Upload backup to Azure Blob Storage
     *
     * @param  \phpbu\App\Backup\Target                    $target
     * @param  \MicrosoftAzure\Storage\Blob\BlobRestProxy $blobRestProxy
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    private function upload(Target $target, BlobRestProxy $blobRestProxy)
    {
        $source = $this->getFileHandle($target->getPathname(), 'r');
        $blobRestProxy->createBlockBlob(
            $this->containerName,
            $this->getUploadPath($target),
            $source
        );
    }

    /**
     * Open stream and validate it.
     *
     * @param  string $path
     * @param  string $mode
     * @return resource
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function getFileHandle($path, $mode)
    {
        $handle = fopen($path, $mode);
        if (!is_resource($handle)) {
            throw new Exception('fopen failed: could not open stream ' . $path);
        }
        return $handle;
    }

    /**
     * Get the azure blob upload path
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getUploadPath(Target $target)
    {
        return (!empty($this->path) ? $this->path . '/' : '') . $target->getFilename();
    }
}
