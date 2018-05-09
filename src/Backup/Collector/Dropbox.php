<?php
namespace phpbu\App\Backup\Collector;

use Kunnu\Dropbox\Dropbox as DropboxApi;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

/**
 * Dropbox class.
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
class Dropbox extends Collector
{
    /**
     * @var DropboxApi
     */
    protected $client;

    /**
     * OpenStack remote path
     *
     * @var string
     */
    protected $path;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param DropboxApi               $client
     * @param string                   $path
     */
    public function __construct(Target $target, DropboxApi $client, string $path)
    {
        $this->client = $client;
        $this->path   = $path;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles() : array
    {
        $items = $this->client->listFolder($this->path, ['limit' => 100]);
        foreach ($items->getItems() as $item) {
            // skip directories
            if ($item instanceof \Kunnu\Dropbox\Models\FolderMetadata) {
                continue;
            }
            /** @var \Kunnu\Dropbox\Models\FileMetadata $item */
            // skip currently created backup
            if ($item->getPathDisplay() == $this->path . $this->target->getFilename()) {
                continue;
            }
            if ($this->isFilenameMatch($item->getName())) {
                $this->files[] = new \phpbu\App\Backup\File\Dropbox($this->client, $item);
            }
        }

        return $this->files;
    }
}
