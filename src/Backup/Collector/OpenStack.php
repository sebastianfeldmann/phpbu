<?php
namespace phpbu\App\Backup\Collector;

use OpenStack\ObjectStore\v1\Models\Container;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

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
class OpenStack extends Remote implements Collector
{
    /**
     * @var \OpenStack\ObjectStore\v1\Models\Container
     */
    protected $container;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target                   $target
     * @param \phpbu\App\Backup\Path                     $path
     * @param \OpenStack\ObjectStore\v1\Models\Container $container
     */
    public function __construct(Target $target, Path $path, Container $container)
    {
        $this->setUp($target, $path);
        $this->container = $container;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        // get all objects matching our path prefix
        $remotePath = Util\Path::withTrailingSlash($this->path->getPathThatIsNotChanging());
        $objects    = $this->container->listObjects(['prefix' => $remotePath]);
        /** @var StorageObject $object */
        foreach ($objects as $object) {
            // skip directories
            if ($object->contentType == 'application/directory') {
                continue;
            }
            if ($this->isFileMatch($object->name)) {
                $file                = new \phpbu\App\Backup\File\OpenStack($this->container, $object);
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
            }
        }

        return $this->files;
    }
}
