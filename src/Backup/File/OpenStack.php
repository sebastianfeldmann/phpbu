<?php
namespace phpbu\App\Backup\File;

use OpenStack\ObjectStore\v1\Models\Container;
use OpenStack\ObjectStore\v1\Models\StorageObject;
use phpbu\App\Exception;

/**
 * OpenStack class.
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
class OpenStack extends Remote
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * OpenStack constructor.
     *
     * @param Container     $container
     * @param StorageObject $object
     */
    public function __construct($container, $object)
    {
        $this->container    = $container;
        $this->filename     = basename($object->name);
        $this->pathname     = $object->name;
        $this->size         = (int) $object->contentLength;
        $this->lastModified = $object->lastModified->getTimestamp();
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->container->getObject($this->getPathname())->delete();
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
