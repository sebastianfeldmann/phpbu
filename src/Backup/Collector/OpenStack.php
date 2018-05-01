<?php

namespace phpbu\App\Backup\Collector;

use OpenStack\ObjectStore\v1\Models\Container;
use phpbu\App\Backup\Sync\Openstack as OpenStackSync;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use phpbu\App\Backup\Target;

class OpenStack extends Collector
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Path where to search for backup files
     *
     * @var string
     */
    protected $path;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param Container                $container
     * @param string                   $path
     */
    public function __construct(Target $target, Container $container, string $path)
    {
        $this->container = $container;
        $this->path = $path;
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
        $objects = $this->container->listObjects(['prefix' => $this->path]);
        /** @var StorageObject $object */
        foreach ($objects as $object) {
            // skip directories
            if ($object->contentType == 'application/directory') {
                continue;
            }
            // skip currently created backup
            if ($object->name == $this->path . $this->target->getFilename()) {
                continue;
            }
            if (preg_match('#' . $this->fileRegex . '#i', basename($object->name))) {
                $this->files[] = new \phpbu\App\Backup\File\OpenStack($this->container, $object);
            }
        }

        return $this->files;
    }
}
