<?php
namespace phpbu\App\Backup\Sync;

use Aws\S3\S3Client;
use phpbu\App\Result;
use phpbu\App\Backup\Sync;
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
        if (!Arr::isSetAndNotEmptyString($config, 'key')) {
            throw new Exception('AWS key is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'secret')) {
            throw new Exception('AWS secret is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'bucket')) {
            throw new Exception('AWS S3 bucket name is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'region')) {
            throw new Exception('AWS S3 region is mandatory');
        }
        if (!Arr::isSetAndNotEmptyString($config, 'path')) {
            throw new Exception('AWS S3 path / object-key is mandatory');
        }
        $this->key    = $config['key'];
        $this->secret = $config['secret'];
        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->path   = Str::withTrailingSlash(Str::replaceDatePlaceholders($config['path']));
        $this->acl    = Arr::getValue($config, 'acl', 'private');
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
}
