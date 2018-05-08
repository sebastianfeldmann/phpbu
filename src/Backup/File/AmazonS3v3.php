<?php
namespace phpbu\App\Backup\File;

use Aws\S3\S3Client;
use phpbu\App\Exception;

class AmazonS3v3 extends Remote
{
    /**
     * @var S3Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * AmazonS3v3 constructor.
     *
     * @param S3Client $client
     * @param string   $bucket
     * @param array    $object
     */
    public function __construct(S3Client $client, string $bucket, array $object)
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->filename = basename($object['Key']);
        $this->pathname = $object['Key'];
        $this->size = $object['Size'];
        $this->lastModified = strtotime($object['LastModified']);
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $this->pathname,
            ]);
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}