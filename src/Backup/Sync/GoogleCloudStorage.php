<?php

namespace phpbu\App\Backup\Sync;

use Google\Cloud\Storage\StorageClient;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Configuration;
use phpbu\App\Result;
use phpbu\App\Util;

/**
 * Google Drive
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     David Dattee <david.dattee@meetwashing.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 */
class GoogleCloudStorage implements Simulator
{
    use Cleanable;

    /**
     * Google Gloud Storage client.
     *
     * @var StorageClient
     */
    private $client;

    /**
     * Google json secret file.
     *
     * @var string
     */
    private $secret;

    /**
     * Bucket to upload to
     *
     * @var string
     */
    private $bucket;

    /**
     * Path to upload to in the bucket
     *
     * @var string
     */
    private $parent;

    /**
     * (non-PHPDoc)
     *
     * @param array $options
     * @throws \phpbu\App\Exception
     * @see    \phpbu\App\Backup\Sync::setup()
     */
    public function setup(array $options)
    {
        if (!class_exists('\\Google\\Cloud\\Storage\\StorageClient')) {
            throw new Exception('google cloud api client not loaded: use composer to install "google/cloud-storage"');
        }
        if (!Util\Arr::isSetAndNotEmptyString($options, 'secret')) {
            throw new Exception('google secret json file is mandatory');
        }
        if (!Util\Arr::isSetAndNotEmptyString($options, 'bucket')) {
            throw new Exception('bucket to upload to is mandatory');
        }
        $this->parent = Util\Arr::getValue($options, 'path', '');

        $this->setupAuthFiles($options);
        $this->setUpCleanable($options);
    }

    /**
     * Make sure google authentication files exist and determine absolute path to them.
     *
     * @param array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    private function setupAuthFiles(array $config)
    {
        $secret = Util\Path::toAbsolutePath($config['secret'], Configuration::getWorkingDirectory());
        if (!file_exists($secret)) {
            throw new Exception(sprintf('google secret json file not found at %s', $secret));
        }

        $this->secret = $secret;
        $this->bucket = $config['bucket'];
    }

    /**
     * Execute the Sync
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result $result
     * @throws \phpbu\App\Backup\Sync\Exception
     * @see    \phpbu\App\Backup\Sync::sync()
     */
    public function sync(Target $target, Result $result)
    {
        try {
            $this->client = $this->createGoogleCloudClient();
            $bucket = $this->client->bucket($this->bucket);
            $hash   = $this->calculateHash($target->getPathname());

            $sentObject = $bucket->upload(
                fopen($target->getPathname(), 'rb'),
                [
                    'name'     => ($this->parent ? $this->parent . '/' : '') . $target->getFilename(),
                    'metadata' => [
                        'crc32c' => $hash,
                    ],
                ],
            );

            $result->debug(sprintf('upload: done: %s', $sentObject->name()));
            $this->cleanup($target, $result);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 0, $e);
        }
    }

    private function calculateHash(string $filePath): string
    {
        return base64_encode(hex2bin(hash_file('crc32c', $filePath)));
    }

    /**
     * Simulate the sync execution.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug('sync backup to google cloud storage' . PHP_EOL);

        $this->isSimulation = true;
        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Setup google api client and google drive service.
     */
    protected function createGoogleCloudClient(): StorageClient
    {
        if (!$this->client) {
            $this->client = new StorageClient(['keyFilePath' => $this->secret]);
        }

        return $this->client;
    }

    /**
     * Creates collector for remote cleanup.
     *
     * @param \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target): Collector
    {
        return new Collector\GoogleCloudStorage(
            $target,
            $this->createGoogleCloudClient()->bucket($this->bucket),
            new Path($this->parent)
        );
    }
}
