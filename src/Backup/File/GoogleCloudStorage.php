<?php

namespace phpbu\App\Backup\File;

use Google\Cloud\Storage\StorageObject;
use phpbu\App\Exception;

/**
 * Google Drive file class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     David Dattee <david.dattee@meetwashing.fr>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 */
class GoogleCloudStorage extends Remote
{
    /**
     * Google Storage Object
     *
     * @var StorageObject
     */
    private StorageObject $object;

    /**
     * Constructor.
     *
     * @param StorageObject $googleStorageObject
     */
    public function __construct(StorageObject $googleStorageObject)
    {
        $this->object       = $googleStorageObject;
        $this->filename     = $this->object->name();
        $this->pathname     = $this->object->name();
        $this->size         = (int)$this->object->info()['size'];
        $this->lastModified = strtotime($this->object->info()['updated']);
    }

    /**
     * Deletes the file from Google Cloud Storage.
     *
     * @throws Exception
     */
    public function unlink()
    {
        try {
            $this->object->delete();
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
