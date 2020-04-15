<?php

namespace phpbu\App\Backup\Sync;

use Arhitector\Yandex\Disk;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Path;

/**
 * Yandex.Disk
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Alexander Palchikov AxelPAL <axelpal@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class YandexDisk implements Sync\Simulator
{
    use Cleanable;
    /**
     * API access token
     * Goto https://oauth.yandex.ru/client/new
     * create your app
     *  - Check all Disks permissions
     *  - generate access token:
     * 1) Goto https://oauth.yandex.ru/authorize?response_type=token&client_id=APP_ID
     *    (replace APP_ID with ID giving to you)
     * 2) Then you should get token parameter from GET-parameters of opened page
     *
     * @var  string
     */
    protected $token;

    /**
     * Remote path
     *
     * @var \phpbu\App\Backup\Path
     */
    protected $path;

    /**
     * @var Disk
     */
    protected $disk;

    /**
     * Unix timestamp of generating path from placeholder.
     *
     * @var int
     */
    protected $time;

    /**
     * (non-PHPDoc)
     *
     * @param array $config
     * @throws Exception
     * @throws \phpbu\App\Exception
     * @see    \phpbu\App\Backup\Sync::setup()
     */
    public function setup(array $config): void
    {
        if (!class_exists(Disk::class)) {
            throw new Exception('Yandex.Disk sdk not loaded: use composer to install "arhitector/yandex"');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'token')) {
            throw new Exception('API access token is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'path')) {
            throw new Exception('yandex.disk path is mandatory');
        }
        $this->token = $config['token'];
        $this->path = new \phpbu\App\Backup\Path(Path::withLeadingSlash($config['path']), time());

        $this->setUpCleanable($config);
    }

    /**
     * (non-PHPDoc)
     *
     * @param Target $target
     * @param Result $result
     * @throws Exception
     * @see    \phpbu\App\Backup\Sync::sync()
     */
    public function sync(Target $target, Result $result): void
    {
        $sourcePath = $target->getPathname();
        $yandexDiskPath = $this->path . '/' . $target->getFilename();
        $this->createDisk();

        $size = null;
        if (stream_is_local($sourcePath) && file_exists($sourcePath)) {
            $size = filesize($sourcePath);
        }

        try {
            $this->createFolders();
            $file = $this->createDisk()->getResource($yandexDiskPath);
            $file->upload($sourcePath, true);
            if ($file->has()) {
                $result->debug('upload: done  (' . $size . ')');
            } else {
                $result->debug('upload: error while uploading file');
            }
            $this->cleanup($target, $result);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }
    }

    private function createFolders(): void
    {
        $folderPath = '';
        $folderPaths = explode('/', $this->path->getPath());
        if (!empty($folderPaths)) {
            foreach ($folderPaths as $folderPathPart) {
                if (!empty($folderPathPart)) {
                    $folderPath .= "/$folderPathPart";
                    $file = $this->createDisk()->getResource($folderPath);
                    if (!$file->has()) {
                        $file->create();
                    }
                }
            }
        }
    }

    /**
     * Simulate the sync execution.
     *
     * @param Target $target
     * @param Result $result
     */
    public function simulate(Target $target, Result $result): void
    {
        $result->debug('sync backup to yandex disk' . PHP_EOL);

        $this->isSimulation = true;
        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Creates the YandexDisk collector.
     *
     * @param Target $target
     * @return Collector
     */
    protected function createCollector(Target $target): Collector
    {
        $collector = new Collector\YandexDisk($target, $this->path, $this->createDisk());
        $collector->setSimulation($this->isSimulation);

        return $collector;
    }

    /**
     * Create a YandexDisk api client.
     *
     * @return Disk
     */
    protected function createDisk(): Disk
    {
        if (!$this->disk) {
            $this->disk = new Disk($this->token);
        }
        return $this->disk;
    }
}
