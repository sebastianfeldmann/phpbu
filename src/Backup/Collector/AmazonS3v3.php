<?php
namespace phpbu\App\Backup\Collector;

use Aws\S3\S3Client;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File\AmazonS3v3 as AwsFile;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

/**
 * AmazonS3v3 class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class AmazonS3v3 extends Remote implements Collector
{
    /**
     * @var \Aws\S3\S3Client
     */
    protected $client;

    /**
     * Amazon S3 bucket name
     *
     * @var string
     */
    protected $bucket;

    /**
     * Amazon S3 constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Path   $path
     * @param \Aws\S3\S3Client         $client
     * @param string                   $bucket
     */
    public function __construct(Target $target, Path $path, S3Client $client, string $bucket)
    {
        $this->setUp($target, $path);
        $this->client = $client;
        $this->bucket = $bucket;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        $result = $this->client->listObjects([
            'Bucket' => $this->bucket,
            'Prefix' => $this->getPrefix($this->path->getPathThatIsNotChanging()),
        ]);

        if (!isset($result['Contents']) || !$result['Contents'] || !is_array($result['Contents'])) {
            return;
        }

        foreach ($result['Contents'] as $object) {
            if ($this->isFileMatch($object['Key'])) {
                $file                = new AwsFile($this->client, $this->bucket, $object);
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
            }
        }
    }

    /**
     * Return prefix for querying remote files and folders
     *
     * @param string $path
     * @return string
     */
    protected function getPrefix($path): string
    {
        $prefix = Util\Path::withoutLeadingSlash($path);
        $prefix = $prefix ? Util\Path::withTrailingSlash($prefix) : '';
        return $prefix;
    }
}
