<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util;
use ObjectStorage_Http_Client;
use ObjectStorage;

/**
 * SoftLayer  ObjectStorage Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Petr Cervenka <petr@nanosolutions.io>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.6
 */
class SoftLayer implements Simulator
{
    /**
     * SoftLayer user
     *
     * @var  string
     */
    protected $user;

    /**
     * SoftLayer secret
     *
     * @var  string
     */
    protected $secret;

    /**
     * SoftLayer container
     *
     * @var string
     */
    protected $container;

    /**
     * SoftLayer host
     *
     * @var string
     */
    protected $host;

    /**
     * SoftLayer remote path
     *
     * @var string
     */
    protected $path;

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\ObjectStorage')) {
            throw new Exception('SoftLayer SDK not loaded: use composer to install "softlayer/objectstorage"');
        }
        // check for mandatory options
        $this->validateConfig($config, ['user', 'secret', 'container', 'host', 'path']);

        $this->user      = $config['user'];
        $this->secret    = $config['secret'];
        $this->container = $config['container'];
        $this->host      = $config['host'];
        $this->path      = Util\Path::withLeadingSlash(
            Util\Path::withTrailingSlash(Util\Path::replaceDatePlaceholders($config['path']))
        );
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
     * @param  \phpbu\App\backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $sourcePath = $target->getPathname();
        $targetPath = $this->path . $target->getFilename();

        $options       = ['adapter' => ObjectStorage_Http_Client::SOCKET, 'timeout' => 20];
        $objectStorage = new ObjectStorage($this->host, $this->user, $this->secret, $options);

        $result->debug('softlayer source: ' . $sourcePath);
        $result->debug('softlayer target: ' . $targetPath);

        try {
            /** @var \ObjectStorage_Container $container */
            $container = $objectStorage->with($this->container . $targetPath)
                                       ->setLocalFile($sourcePath)
                                       ->setMeta('description', 'PHPBU Backup: ' . date('r', time()))
                                       ->setHeader('Content-Type', $target->getMimeType());
            $container->create();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }

        $result->debug('upload: done');
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
            'sync backup to SoftLayer' . PHP_EOL
            . '  host:      ' . $this->host . PHP_EOL
            . '  user:      ' . $this->user . PHP_EOL
            . '  secret:     ********' . PHP_EOL
            . '  container: ' . $this->container . PHP_EOL
            . '  location:  ' . $this->path
        );
    }
}
