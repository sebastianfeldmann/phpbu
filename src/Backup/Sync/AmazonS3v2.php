<?php
namespace phpbu\App\Backup\Sync;

use Aws\S3\S3Client;
use phpbu\App\Result;
use phpbu\App\Backup\Target;

/**
 * Amazon S3 Sync
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 1.1.4
 */
class AmazonS3v2 extends AmazonS3
{
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
        $sourcePath = $target->getPathname();
        $targetPath = $this->path . '/' .  $target->getFilename();

        $s3 = S3Client::factory(
            [
                'signature' => 'v4',
                'region'    => $this->region,
                'credentials' => [
                    'key'    => $this->key,
                    'secret' => $this->secret,
                ]
            ]
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
