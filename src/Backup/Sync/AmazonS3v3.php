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
        $s3 = new S3Client([
            'region'  => $this->region,
            'version' => '2006-03-01',
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret,
            ]
        ]);

        $s3->registerStreamWrapper();
        $source = $this->getFileHandle($target->getPathname(), 'r');
        $stream = $this->getFileHandle($this->getUploadPath($target), 'w');

        try {
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
