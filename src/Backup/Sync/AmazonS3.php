<?php
namespace phpbu\App\Backup\Sync;

use Aws\S3\S3Client;
use phpbu\App\Result;
use phpbu\App\Backup\Sync;
use phpbu\App\Backup\Target;
use phpbu\App\Util\Arr;
use phpbu\App\Util\String;

/**
 * Amazon S3 Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.4
 */
class AmazonS3 implements Sync
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
     * (non-PHPDoc)
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Aws\\S3\\S3Client')) {
            throw new Exception('Amazon SDK not loaded: use composer "aws/aws-sdk-php": "2.7.*" to install');
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
        $this->path   = String::withTrailingSlash(String::replaceDatePlaceholders($config['path']));
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
    public function sync(Target $target, Result $result)
    {
        $sourcePath = $target->getPathname();
        $targetPath = $this->path . $target->getFilename();

        $s3 = S3Client::factory(
            array(
                'signature' => 'v4',
                'region'    => $this->region,
                'credentials' => array(
                    'key'    => $this->key,
                    'secret' => $this->secret,
                )
            )
        );

        try {
            $fh = fopen($sourcePath, 'r');
            $s3->upload($this->bucket, $targetPath, $fh, $this->acl);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }

        $result->debug('upload: done');
    }
}
