<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;
use phpbu\App\Util\Str;

/**
 * Amazon S3 Sync base class
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
abstract class AmazonS3 implements Simulator
{
    /**
     * AWS key
     *
     * @var  string
     */
    protected $key;

    /**
     * AWS secret
     *
     * @var  string
     */
    protected $secret;

    /**
     * AWS S3 bucket
     *
     * @var string
     */
    protected $bucket;

    /**
     * TTL for all items in this bucket.
     *
     * @var int
     */
    protected $bucketTTL;

    /**
     * AWS S3 region
     *
     * @var string
     */
    protected $region;

    /**
     * AWS remote path / object key
     *
     * @var string
     */
    protected $path;

    /**
     * AWS acl
     * 'private' by default
     *
     * @var string
     */
    protected $acl;

    /**
     * Use multi part config
     *
     * @var boolean
     */
    protected $multiPartUpload;

    /**
     * Min multi part upload size
     *
     * @var int
     */
    protected $minMultiPartUploadSize = 5242880;

    /**
     * Max stream upload size
     *
     * @var int
     */
    protected $maxStreamUploadSize = 104857600;

    /**
     * Configure the sync.
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Aws\\S3\\S3Client')) {
            throw new Exception('Amazon SDK not loaded: use composer to install "aws/aws-sdk-php"');
        }

        // check for mandatory options
        $this->validateConfig($config);

        $this->key             = $config['key'];
        $this->secret          = $config['secret'];
        $this->bucket          = $config['bucket'];
        $this->bucketTTL       = Arr::getValue($config, 'bucketTTL');
        $this->region          = $config['region'];
        $this->path            = Str::withTrailingSlash(Str::replaceDatePlaceholders($config['path']));
        $this->acl             = Arr::getValue($config, 'acl', 'private');
        $this->multiPartUpload = Str::toBoolean(Arr::getValue($config, 'useMultiPartUpload'), false);
    }

    /**
     * Make sure all mandatory keys are present in given config.
     *
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    protected function validateConfig(array $config)
    {
        foreach (['key', 'secret', 'bucket', 'region', 'path'] as $option) {
            if (!Arr::isSetAndNotEmptyString($config, $option)) {
                throw new Exception('AWS S3 ' . $option . ' is mandatory');
            }
        }
    }

    /**
     * Execute the sync
     *
     * @see    \phpbu\App\Backup\Sync::sync()
     * @param  \phpbu\App\Backup\Target $target
     * @param  \phpbu\App\Result        $result
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    abstract public function sync(Target $target, Result $result);

    /**
     * Simulate the sync execution.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug(
            'sync backup to Amazon S3' . PHP_EOL
            . '  region:   ' . $this->region . PHP_EOL
            . '  key:      ' . $this->key . PHP_EOL
            . '  secret:    ********' . PHP_EOL
            . '  location: ' . $this->bucket
        );
    }

    /**
     * Should multi part upload be used.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return bool
     */
    protected function useMultiPartUpload(Target $target)
    {
        // files bigger 5GB have to be uploaded via multi part
        // files uploaded with multi part upload has to be at least 5MB
        return (
            $target->getSize() > $this->maxStreamUploadSize || $this->multiPartUpload
        ) && $target->getSize() > $this->minMultiPartUploadSize;
    }
}
