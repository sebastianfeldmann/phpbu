<?php

namespace phpbu\App\Backup\Sync;

use Aws\S3\S3Client;

/**
 * Wasabi Sync
 *
 * Docs example
 * https://wasabi-support.zendesk.com/hc/en-us/articles/360015106031-What-are-the-service-URLs-for-Wasabi-s-different-storage-regions-
 * https://wasabi-support.zendesk.com/hc/en-us/articles/360000363572-How-do-I-use-AWS-SDK-for-PHP-with-Wasabi-
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Angel Fernandez <@angelfplaza>
 * @copyright  Ángel Fernández - WPHercules <@wphercules>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 */
class WasabiS3 extends AmazonS3v3
{

    /**
     * Create the Wasabi AWS client.
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
     * Generate Wasabi enpoint
     *
     * @return string
     */
    protected function createEndpoint()
    {
        return str_replace('{region}', $this->region, 'https://s3.{region}.wasabisys.com');
    }
}
