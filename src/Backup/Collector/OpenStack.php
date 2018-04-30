<?php

namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Sync\Openstack as OpenStackSync;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use phpbu\App\Backup\File\FileRemote;
use phpbu\App\Backup\Target;

class OpenStack extends Collector
{
    /**
     * @var OpenStackSync
     */
    protected $sync;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param OpenStackSync            $sync
     */
    public function __construct(Target $target, OpenStackSync $sync)
    {
        $this->sync = $sync;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File\File[]
     */
    public function getBackupFiles(): array
    {
        // get all objects matching our path prefix
        $objects = $this->sync->getContainer()->listObjects(['prefix' => $this->sync->getPath()]);
        /** @var StorageObject $object */
        foreach ($objects as $object) {
            // skip directories
            if ($object->contentType == 'application/directory') {
                continue;
            }
            // skip currently created backup
            if ($object->name == $this->sync->getPath() . $this->target->getFilename()) {
                continue;
            }
            if (preg_match('#' . $this->fileRegex . '#i', basename($object->name))) {
                $attributes = [
                    'name' => basename($object->name),
                    'pathname' => $object->name,
                    'size' => (int)$object->contentLength,
                    'last_modified' => $object->lastModified->getTimestamp(),
                ];
                $this->files[] = new FileRemote($attributes, $this->sync);
            }
        }

        return $this->files;
    }
}