<?php
namespace phpbu\App\Backup\Sync;

use Kunnu\Dropbox\DropboxApp as DropboxConfig;
use Kunnu\Dropbox\Dropbox as DropboxApi;
use Kunnu\Dropbox\DropboxFile;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Sync as SyncInterface;
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
class Dropbox extends SyncInterface
{
    use Clearable;

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
     * @var Path
     */
    protected $path;

    /**
     * Dropbox api client
     *
     * @var DropboxApi
     */
    protected $client;

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
        $this->validateConfig($config, ['token', 'path']);

        $this->token = $config['token'];
        // make sure the path contains leading and trailing slashes
        $this->path  = new Path(Util\Path::withLeadingSlash($config['path']), $this->time);

        $this->setUpClearable($config);
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
        $this->time  = time();
        $sourcePath  = $target->getPathname();
        $dropboxPath = Util\Path::withTrailingSlash($this->path->getPath()) . $target->getFilename();
        if (!$this->client) {
            $this->connect();
        }
        try {
            $file = new DropboxFile($sourcePath);
            $meta = $this->client->upload($file, $dropboxPath, ['autorename' => true]);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }
        // run remote cleanup
        $this->cleanup($target, $result);
        $result->debug('upload: done  (' . $meta->getSize() . ')');
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

        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Creates collector for Dropbox
     *
     * @param \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target): Collector
    {
        return new \phpbu\App\Backup\Collector\Dropbox($target, $this->client, $this->path->getPathRaw(), $this->time);
    }

    /**
     * Create Dropbox api client
     */
    protected function connect()
    {
        $config       = new DropboxConfig("id", "secret", $this->token);
        $this->client = new DropboxApi($config);
    }
}
