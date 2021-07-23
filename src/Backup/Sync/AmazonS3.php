<?php
namespace phpbu\App\Backup\Sync;

use phpbu\App\Result;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

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
     * Unix timestamp of generating path from placeholder.
     *
     * @var int
     */
    protected $time;

    /**
     * AWS remote raw path / object key
     *
     * @var string
     */
    protected $pathRaw;

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
     * Set a custom S3 endpoint
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Set path style endpoint
     *
     * @var boolean
     */
    protected $usePathStyle = false;

    /**
     * Use specific S3 signature version
     *
     * @var string
     */
    protected $signatureVersion;

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
     * Configure the sync
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Aws\\S3\\S3Client')) {
            throw new Exception('Amazon SDK not loaded: use composer to install "aws/aws-sdk-php"');
        }

        // check for mandatory options
        $this->validateConfig($config, ['key', 'secret', 'bucket', 'region']);

        $path                   = Util\Arr::getValue($config, 'path', '');
        $pathCleaned            = Util\Path::withoutTrailingSlash(Util\Path::withoutLeadingSlash($path));
        $this->time             = time();
        $this->key              = $config['key'];
        $this->secret           = $config['secret'];
        $this->bucket           = $config['bucket'];
        $this->bucketTTL        = Util\Arr::getValue($config, 'bucketTTL');
        $this->region           = $config['region'];
        $this->path             = Util\Path::replaceDatePlaceholders($pathCleaned, $this->time);
        $this->pathRaw          = $pathCleaned;
        $this->acl              = Util\Arr::getValue($config, 'acl', 'private');
        $this->multiPartUpload  = Util\Str::toBoolean(Util\Arr::getValue($config, 'useMultiPartUpload'), false);
        $this->usePathStyle     = Util\Str::toBoolean(Util\Arr::getValue($config, 'usePathStyleEndpoint'), false);
        $this->endpoint         = Util\Arr::getValue($config, 'endpoint');
        $this->signatureVersion = Util\Arr::getValue($config, 'signatureVersion');
    }

    /**
     * Make sure all mandatory keys are present in given config
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
     * Simulate the sync execution
     *
     * @param Target $target
     * @param Result $result
     */
    public function simulate(Target $target, Result $result)
    {
        $result->debug(
            'sync backup to Amazon S3' . PHP_EOL
            . '  region:   ' . $this->region . PHP_EOL
            . '  key:      ' . $this->key . PHP_EOL
            . '  secret:    ********' . PHP_EOL
            . '  location: ' . $this->bucket . PHP_EOL
        );
    }

    /**
     * Should multi part upload be used
     *
     * @param Target $target
     * @return bool
     * @throws \phpbu\App\Exception
     */
    protected function useMultiPartUpload(Target $target)
    {
        return (
            // files uploaded with multi part upload have to be at least 5MB
            $target->getSize() > $this->minMultiPartUploadSize
            && (
                // files bigger 5GB have to be uploaded via multi part
                $target->getSize() > $this->maxStreamUploadSize
                ||
                // multipart upload is triggered manually
                $this->multiPartUpload
            )
        );
    }
}
