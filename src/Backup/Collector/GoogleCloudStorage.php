<?php

namespace phpbu\App\Backup\Collector;

use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageObject;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;

/**
 * GoogleCloud class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     David Dattee <david.dattee@meetwashing.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class GoogleCloudStorage extends Remote implements Collector
{
    /**
     * Bucket to read from.
     *
     * @var Bucket
     */
    private Bucket $bucket;

    /**
     * @param Target $target
     * @param Bucket $bucket
     * @param Path $path
     */
    public function __construct(Target $target, Bucket $bucket, Path $path)
    {
        $this->setUp($target, $path);
        $this->bucket = $bucket;
    }

    /**
     * Collect all created backups.
     *
     * @return void
     */
    protected function collectBackups()
    {
        /** @var StorageObject $object */
        foreach ($this->bucket->objects() as $object) {
            if ($this->isFileMatch($this->path->getPath() . '/' . $object->name())) {
                $file = new File\GoogleCloudStorage($object);

                $this->files[$this->getFileIndex($file)] = $file;
            }
        }
    }
}
