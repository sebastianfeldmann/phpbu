<?php
namespace phpbu\App\Backup\Sync;

use Google_Client as GClient;
use Google_Http_MediaFileUpload as GStream;
use Google_Service_Drive as GDrive;
use Google_Service_Drive as GDriveService;
use Google_Service_Drive_DriveFile as GFile;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Configuration;
use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use Psr\Http\Message\RequestInterface;

/**
 * Google Drive
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.1.1
 */
class GoogleDrive implements Simulator
{
    use Cleanable;

    /**
     * Google drive service.
     *
     * @var \Google_Service_Drive
     */
    private $service;

    /**
     * Google json secret file.
     *
     * @var string
     */
    private $secret;

    /**
     * Google json credentials file.
     *
     * @var string
     */
    private $access;

    /**
     * Google drive parent folder id.
     *
     * @var string
     */
    private $parent;

    /**
     * Upload chunk size.
     *
     * @var int
     */
    private $chunkSize = 1 * 1024 * 1024;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Google_Client')) {
            throw new Exception('google api client not loaded: use composer to install "google/apiclient"');
        }
        if (!Util\Arr::isSetAndNotEmptyString($config, 'secret')) {
            throw new Exception('google secret json file is mandatory');
        }
        if (!Util\Arr::isSetAndNotEmptyString($config, 'access')) {
            throw new Exception('google credentials json file is mandatory');
        }
        $this->setupAuthFiles($config);
        $this->parent = Util\Arr::getValue($config, 'parentId');

        $this->setUpCleanable($config);
    }

    /**
     * Make sure both google authentication files exist and determine absolute path to them.
     *
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    private function setupAuthFiles(array $config)
    {
        $secret = Util\Path::toAbsolutePath($config['secret'], Configuration::getWorkingDirectory());
        if (!file_exists($secret)) {
            throw new Exception(sprintf('google secret json file not found at %s', $secret));
        }
        $access = Util\Path::toAbsolutePath($config['access'], Configuration::getWorkingDirectory());
        if (!file_exists($access)) {
            throw new Exception(sprintf('google credentials json file not found at %s', $access));
        }
        $this->secret = $secret;
        $this->access = $access;
    }

    /**
     * Execute the Sync
     *
     * @see    \phpbu\App\Backup\Sync::sync()
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        try {
            $service = $this->createDriveService();
            $client  = $service->getClient();
            $client->setDefer(true);

            $status    = false;
            $apiResult = false;
            $apiFile   = $this->createFile($target);
            $request   = $service->files->create($apiFile);
            $stream    = $this->createUploadStream($client, $request, $target);
            $handle    = fopen($target->getPathname(), "rb");
            while (!$status && !feof($handle)) {
                $chunk  = fread($handle, $this->chunkSize);
                $status = $stream->nextChunk($chunk);
            }
            fclose($handle);
            $client->setDefer(false);

            /** @var \Google_Service_Drive_DriveFile $apiResult */
            if ($status != false) {
                $apiResult = $status;
            }
            $result->debug(sprintf('upload: done: %s', $apiResult->getId()));
            $this->cleanup($target, $result);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }
    }

    /**
     * Simulate the sync execution.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug('sync backup to google drive' . PHP_EOL);

        $this->isSimulation = true;
        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Setup google api client and google drive service.
     *
     * @throws \Google_Exception
     */
    protected function createDriveService() : GDriveService
    {
        if (!$this->service) {
            $client = new GClient();
            $client->setApplicationName('phpbu');
            $client->setScopes(GDrive::DRIVE);
            $client->setAuthConfig($this->secret);
            $client->setAccessType('offline');
            $client->setAccessToken($this->getAccessToken());

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $this->updateAccessToken($client->getAccessToken());
            }
            $this->service = new GDriveService($client);
        }
        return $this->service;
    }

    /**
     * Create google api file.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \Google_Service_Drive_DriveFile
     */
    protected function createFile(Target $target) : GFile
    {
        $file = new GFile();
        $file->setName($target->getFilename());
        $file->setParents([$this->parent]);

        return $file;
    }

    /**
     * Create google api file deferred upload.
     *
     * @param  \Google_Client                     $client
     * @param  \Psr\Http\Message\RequestInterface $request
     * @param  \phpbu\App\Backup\Target           $target
     * @return \Google_Http_MediaFileUpload
     * @throws \phpbu\App\Exception
     */
    protected function createUploadStream(GClient $client, RequestInterface $request, Target $target) : GStream
    {
        $media = new GStream(
            $client,
            $request,
            'application/octet-stream',
            null,
            true,
            $this->chunkSize
        );
        $media->setFileSize($target->getSize());

        return $media;
    }

    /**
     * Return google credentials.
     *
     * @return array
     */
    private function getAccessToken() : array
    {
        return json_decode(file_get_contents($this->access), true);
    }

    /**
     * Update the access token in the google credentials file.
     *
     * @param  array $accessToken
     * @return void
     */
    private function updateAccessToken(array $accessToken)
    {
        file_put_contents($this->access, json_encode($accessToken));
    }

    /**
     * Creates collector for remote cleanup.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     * @throws \Google_Exception
     */
    protected function createCollector(Target $target): Collector
    {
        return new Collector\GoogleDrive($target, new Path($this->parent), $this->createDriveService());
    }
}
