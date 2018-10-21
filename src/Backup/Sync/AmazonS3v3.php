<?php
namespace phpbu\App\Backup\Sync;

use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Result;

/**
 * Amazon S3 Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 3.0.0
 */
class AmazonS3v3 extends AmazonS3
{
    use Cleanable;

    /**
     * Amazon S3 client.
     *
     * @var S3Client;
     */
    protected $client;

    /**
     * Configure the sync.
     *
     * @see    \phpbu\App\Backup\Sync::setup()
     * @param  array $config
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    public function setup(array $config)
    {
        parent::setup($config);

        $this->setUpCleanable($config);
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
        $this->client = $this->createClient();

        if (!$this->client->doesBucketExist($this->bucket)) {
            $result->debug('create s3 bucket');
            $this->createBucket($this->client);
        }

        try {
            $this->upload($target, $this->client);
            $result->debug('upload: done');

            // run remote cleanup
            $this->cleanup($target, $result);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }
    }

    /**
     * Create the AWS client.
     *
     * @return \Aws\S3\S3Client
     */
    protected function createClient() : S3Client
    {
        return new S3Client([
            'region'      => $this->region,
            'version'     => '2006-03-01',
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ]
        ]);
    }

    /**
     * Create a multi part s3 file uploader.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \Aws\S3\S3Client         $s3
     * @return \Aws\S3\MultipartUploader
     */
    protected function createUploader(Target $target, S3Client $s3) : MultipartUploader
    {
        return new MultipartUploader($s3, $target->getPathname(), [
            'bucket' => $this->bucket,
            'key'    => $this->getUploadPath($target),
        ]);
    }

    /**
     * Creates collector for Amazon S3
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return \phpbu\App\Backup\Collector
     */
    protected function createCollector(Target $target) : Collector
    {
        $path = new Path($this->pathRaw, $this->time);
        return new Collector\AmazonS3v3($target, $path, $this->client, $this->bucket);
    }

    /**
     * Simulate the sync execution.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Result        $result
     */
    public function simulate(Target $target, Result $result)
    {
        parent::simulate($target, $result);

        $this->simulateRemoteCleanup($target, $result);
    }

    /**
     * Create a s3 bucket.
     *
     * @param \Aws\S3\S3Client $s3
     */
    private function createBucket(S3Client $s3)
    {
        $s3->createBucket([
            'ACL'                       => $this->acl,
            'Bucket'                    => $this->bucket,
            'CreateBucketConfiguration' => [
                'LocationConstraint' => $this->region,
            ]
        ]);
    }

    /**
     * Upload backup to Amazon S3 bucket.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \Aws\S3\S3Client         $s3
     * @throws \phpbu\App\Backup\Sync\Exception
     * @throws \phpbu\App\Exception
     */
    private function upload(Target $target, S3Client $s3)
    {
        if ($this->useMultiPartUpload($target)) {
            $this->uploadMultiPart($target, $s3);
        } else {
            $this->uploadStream($target, $s3);
        }
    }

    /**
     * Upload via stream wrapper.
     *
     * @param  \phpbu\App\Backup\Target $target
     * @param  \Aws\S3\S3Client         $s3
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    private function uploadStream(Target $target, S3Client $s3)
    {
        $s3->registerStreamWrapper();
        $source = $this->getFileHandle($target->getPathname(), 'r');
        $stream = $this->getFileHandle('s3://' . $this->bucket . '/' . $this->getUploadPath($target), 'w');
        while (!feof($source)) {
            fwrite($stream, fread($source, 4096));
        }
        fclose($stream);
    }

    /**
     * Upload via multi part.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \Aws\S3\S3Client         $s3
     * @throws \Aws\Exception\MultipartUploadException
     */
    private function uploadMultiPart(Target $target, S3Client $s3)
    {
        $uploader = $this->createUploader($target, $s3);
        $uploader->upload();
    }

    /**
     * Open stream and validate it.
     *
     * @param  string $path
     * @param  string $mode
     * @return resource
     * @throws \phpbu\App\Backup\Sync\Exception
     */
    private function getFileHandle($path, $mode)
    {
        $handle = fopen($path, $mode);
        if (!is_resource($handle)) {
            throw new Exception('fopen failed: could not open stream ' . $path);
        }
        return $handle;
    }

    /**
     * Get the s3 upload path
     *
     * @param  \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getUploadPath(Target $target)
    {
        return (!empty($this->path) ? $this->path . '/' : '') . $target->getFilename();
    }
}
