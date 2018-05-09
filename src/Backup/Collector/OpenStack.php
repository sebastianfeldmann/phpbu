<?php
namespace phpbu\App\Backup\Collector;

use OpenStack\ObjectStore\v1\Models\Container;
use phpbu\App\Backup\Collector;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use phpbu\App\Backup\Target;

/**
 * OpenStack collector class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class OpenStack extends Collector
{
    /**
     * @var \OpenStack\ObjectStore\v1\Models\Container
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
     * @param \phpbu\App\Backup\Target                   $target
     * @param \OpenStack\ObjectStore\v1\Models\Container $container
     * @param string                                     $path
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
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles() : array
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
            if ($this->isFilenameMatch(basename($object->name))) {
                $this->files[] = new \phpbu\App\Backup\File\OpenStack($this->container, $object);
            }
        }

        return $this->files;
    }
}
