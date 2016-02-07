<?php
namespace phpbu\App\Backup\Sync;

use Aws\S3\S3Client;
use phpbu\App\Result;
use phpbu\App\Backup\Sync;
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
 * @since      Class available since Release 3.0.0
 */
class AmazonS3v3 extends AmazonS3
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
        $targetPath = $this->getUploadPath($target);

        $s3 = new S3Client([
            'region'  => $this->region,
            'version' => '2006-03-01',
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ]
        ]);

        try {
            $s3->registerStreamWrapper();
            $stream = fopen($targetPath, 'w');
            $source = fopen($sourcePath, 'r');
            while(!feof($source)) {
                fwrite($stream, fread($source, 4096));
            }
            fclose($stream);

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), null, $e);
        }

        $result->debug('upload: done');
    }

    /**
     * Get the s3 upload path
     *
     * @param \phpbu\App\Backup\Target $target
     * @return string
     */
    public function getUploadPath(Target $target)
    {
        return 's3://' . $this->bucket
               . (substr($this->path, 0, 1) == '/' ? '' : '/')
               . $this->path
               . (substr($this->path, -1, 1) == '/' ? '' : '/')
               . $target->getFilename();
    }
}
