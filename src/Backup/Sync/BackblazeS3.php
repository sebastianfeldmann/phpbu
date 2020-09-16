<?php

namespace phpbu\App\Backup\Sync;

use Aws\S3\S3Client;

/**
 * Backblaze Sync
 *
 * Docs example
 * https://help.backblaze.com/hc/en-us/articles/360046980814-Using-the-AWS-SDK-for-PHP-with-Backblaze-B2-Cloud-Storage
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Vladimir Konchakovsky <vk@etradeua.com>
 * @copyright  Vladimir Konchakovsky <vk@etradeua.com>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class BackblazeS3 extends AmazonS3v3
{
    /**
     * Create the Backblaze AWS client.
     *
     * @return \Aws\S3\S3Client
     */
    protected function createClient() : S3Client
    {
        $endpoint = $this->createEndpoint();

        return new S3Client([
            'endpoint'    => $endpoint,
            'region'      => $this->region,
            'version'     => 'latest',
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ]
        ]);
    }

    /**
     * Generate Backblaze enpoint
     *
     * @return string
     */
    private function createEndpoint()
    {
        return strtr('https://s3.{region}.backblazeb2.com', '{region}', $this->region);
    }
}
