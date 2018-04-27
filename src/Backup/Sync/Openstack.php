<?php
namespace phpbu\App\Backup\Sync;

use GuzzleHttp\Psr7\Stream;
use OpenStack\ObjectStore\v1\Service as ObjectStoreService;
use GuzzleHttp\Client;
use OpenStack\Common\Transport\HandlerStack;
use OpenStack\Common\Transport\Utils;
use OpenStack\Identity\v2\Service;
use phpbu\App\Backup\Target;
use phpbu\App\Result;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;

/**
 * OpenStack Swift Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1
 */
class Openstack implements Simulator
{
    /**
     * OpenStack identify url
     *
     * @var string
     */
    protected $authUrl;

    /**
     * OpenStack region
     *
     * @var string
     */
    protected $region;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * Object Store container name
     *
     * @var string
     */
    protected $containerName;

    /**
     * OpenStack service name
     *
     * @var string
     */
    protected $serviceName;

    /**
     * Max stream upload size, files over this size have to be uploaded as Dynamic Large Objects
     *
     * @var int
     */
    protected $maxStreamUploadSize = 5368709120;

    /**
     * Path where to copy the backup.
     *
     * @var string
     */
    protected $path = '';

    /**
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\OpenStack\\OpenStack')) {
            throw new Exception('OpeStack SDK not loaded: use composer to install "php-opencloud/openstack"');
        }

        // check for mandatory options
        $this->validateConfig($config);

        $this->authUrl       = $config['auth_url'];
        $this->region        = $config['region'];
        $this->username      = $config['username'];
        $this->password      = $config['password'];
        $this->containerName = $config['container_name'];
        $this->serviceName   = Arr::getValue($config, 'service_name', 'swift');
        if (Arr::getValue($config, 'path')) {
            $this->path = Str::withTrailingSlash(Str::replaceDatePlaceholders($config['path']));
            $this->path = substr($this->path, 0, 1) == '/' ? substr($this->path, 1) : $this->path;
        }
    }

    /**
     * Make sure all mandatory keys are present in given config.
     *
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function validateConfig(array $config)
    {
        foreach (['auth_url', 'region', 'username', 'password', 'container_name'] as $option) {
            if (!Arr::isSetAndNotEmptyString($config, $option)) {
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
        $httpClient = new Client([
            'base_uri' => Utils::normalizeUrl($this->authUrl),
            'handler'  => HandlerStack::create(),
        ]);

        $options = [
            'authUrl'         => $this->authUrl,
            'region'          => $this->region,
            'username'        => $this->username,
            'password'        => $this->password,
            'identityService' => Service::factory($httpClient),
        ];

        $openStack = new \OpenStack\OpenStack($options);
        $objectStoreService = $openStack->objectStoreV1(['catalogName' => $this->serviceName]);
        $container = $this->getContainer($objectStoreService, $result);

        try {
            if ($target->getSize() > $this->maxStreamUploadSize) {
                // use Dynamic Large Objects
                $uploadOptions = [
                    'name'   => $this->getUploadPath($target),
                    'stream' => new Stream(fopen($target->getPathname(), 'r')),
                ];
                $container->createLargeObject($uploadOptions);
            } else {
                // create an object
                $uploadOptions = [
                    'name' => $this->getUploadPath($target),
                    'content' => file_get_contents($target->getPathname()),
                ];
                $container->createObject($uploadOptions);
            }
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
            'sync backup to OpenStack' . PHP_EOL
            . '  region:   ' . $this->region . PHP_EOL
            . '  key:      ' . $this->username . PHP_EOL
            . '  password:    ********' . PHP_EOL
            . '  container: ' . $this->containerName
        );
    }

    /**
     * @param ObjectStoreService $service
     * @return \OpenStack\ObjectStore\v1\Models\Container
     * @throws \OpenStack\Common\Error\BadResponseError
     */
    protected function getContainer(ObjectStoreService $service, Result $result)
    {
        if (!$service->containerExists($this->containerName)) {
            $result->debug('create container');
            return $service->createContainer(['name' => $this->containerName]);
        }
        return $service->getContainer($this->containerName);
    }

    /**
     * Get the upload path
     *
     * @param \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getUploadPath(Target $target)
    {
        return $this->path . $target->getFilename();
    }
}
