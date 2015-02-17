<?php
namespace phpbu\Backup\Sync;

use Aws\S3\S3Client;
use phpbu\App\Result;
use phpbu\Backup\Sync;
use phpbu\Backup\Target;
use phpbu\Util\String;

/**
 * Amazon S3 Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpbu.de/
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
     * @see    \phpbu\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\Backup\Sync\Exception
     */
    public function setup(array $config)
    {
        if (!class_exists('\\Aws\\S3\\S3Client')) {
            throw new Exception('Amazon SDK not loaded: use composer "aws/aws-sdk-php": "2.7.*" to install');
        }
        if (!isset($config['key']) || '' == $config['key']) {
            throw new Exception('AWS key is mandatory');
        }
        if (!isset($config['secret']) || '' == $config['secret']) {
            throw new Exception('AWS secret is mandatory');
        }
        if (!isset($config['bucket']) || '' == $config['bucket']) {
            throw new Exception('AWS S3 bucket name is mandatory');
        }
        if (!isset($config['region']) || '' == $config['region']) {
            throw new Exception('AWS S3 region is mandatory');
        }
        if (!isset($config['path']) || '' == $config['path']) {
            throw new Exception('AWS S3 path / object-key is mandatory');
        }
        $this->key    = $config['key'];
        $this->secret = $config['secret'];
        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->path   = String::withTrailingSlash($config['path']);
        $this->acl    = isset($config['acl']) ? $config['acl'] : 'private';
    }

    /**
     * Execute the sync
     *
     * @see    \phpbu\Backup\Sync::sync()
     * @param  \phpbu\backup\Target $target
     * @param  \phpbu\App\Result    $result
     * @throws \phpbu\Backup\Sync\Exception
     */
    public function sync(Target $target, Result $result)
    {
        $sourcePath = $target->getPathnameCompressed();
        $targetPath = $this->path . $target->getFilenameCompressed();

        $s3 = S3Client::factory(array(
            'signature' => 'v4',
            'region'    => $this->region,
            'credentials' => array(
                'key'    => $this->key,
                'secret' => $this->secret,
            )
        ));

        try {
            $fh = fopen($sourcePath, 'r');
            $s3->upload($this->bucket, $targetPath, $fh, $this->acl);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }

        $result->debug('upload: done');
    }
}
