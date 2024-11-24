<?php
namespace phpbu\App\Backup\Sync;

use Kunnu\Dropbox as DropboxApi;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

/**
 * Dropbox
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.1
 */
class Dropbox implements Simulator
{
    use Cleanable;

    /**
     * API access token
     *
     * Goto https://www.dropbox.com/developers/apps
     * create your app
     *  - Dropbox api app
     *  - files and datastore
     *  - yes
     *  - provide some app name "my-dropbox-app"
     *  - generate access token to authenticate connection to your dropbox
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
     * Dropbox api client
     *
     * @var DropboxApi\Dropbox
     */
    protected $client;

    /**
     * Unix timestamp of generating path from placeholder.
     *
     * @var int
     */
    protected $time;

    /**
     * @var string
     */
    private $appKey;

    /**
     * @var string
     */
    private $appSecret;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Kunnu\\Dropbox\\Dropbox')) {
            throw new Exception('Dropbox sdk not loaded: use composer to install "kunalvarma05/dropbox-php-sdk"');
        }

        // check for mandatory options
        $this->validateConfig($config, ['token', 'path', 'appKey', 'appSecret']);

        $this->time      = time();
        $this->token     = $config['token'];
        $this->appKey    = $config['appKey'];
        $this->appSecret = $config['appSecret'];
        // make sure the path contains a leading slash
        $this->path  = new Path(Util\Path::withLeadingSlash($config['path']), $this->time);

        $this->setUpCleanable($config);
    }

    /**
     * Make sure all mandatory keys are present in given config.
     *
     * @param  array    $config
     * @param  string[] $keys
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
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::sync()
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $sourcePath  = $target->getPathname();
        $dropboxPath = $this->path->getPath() . '/' . $target->getFilename();
        $client      = $this->createClient();

        try {
            $file = new DropboxApi\DropboxFile($sourcePath);
            $meta = $client->upload($file, $dropboxPath, ['autorename' => true]);
            $result->debug('upload: done  (' . $meta->getSize() . ')');

            // run remote cleanup
            $this->cleanup($target, $result);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 0, $e);
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
        $result->debug(
            'sync backup to dropbox' . PHP_EOL
            . '  token:    ********' . PHP_EOL
            . '  location: ' . $this->path->getPath() . PHP_EOL
        );
        $this->isSimulation = true;
        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Creates the Dropbox collector.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target) : Collector
    {
        $collector = new Collector\Dropbox($target, $this->path, $this->createClient());
        $collector->setSimulation($this->isSimulation);

        return $collector;
    }

    /**
     * Create a dropbox api client.
     *
     * @return \Kunnu\Dropbox\Dropbox
     * @throws DropboxClientException
     */
    protected function createClient() : DropboxApi\Dropbox
    {
        if (!$this->client) {
            $app          = new DropboxApi\DropboxApp($this->appKey, $this->appSecret, $this->token);
            $this->client = new DropboxApi\Dropbox($app);
        }
        return $this->client;
    }
}
