<?php
namespace phpbu\App\Backup\Collector;

use Kunnu\Dropbox\Dropbox as DropboxApi;
use Kunnu\Dropbox\Models\FolderMetadata;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;
use phpbu\App\Util;

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
class Dropbox extends Remote implements Collector
{
    /**
     * @var DropboxApi
     */
    protected $client;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Path   $path
     * @param \Kunnu\Dropbox\Dropbox   $client
     */
    public function __construct(Target $target, Path $path, DropboxApi $client)
    {
        $this->setUp($target, $path);
        $this->client = $client;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        $items = $this->client->listFolder(
            Util\Path::withTrailingSlash($this->path->getPathThatIsNotChanging()),
            [
                'limit'     => 100,
                'recursive' => true,
            ]
        );
        /** @var \Kunnu\Dropbox\Models\FileMetadata $item */
        foreach ($items->getItems() as $item) {
            // skip directories
            if ($item instanceof FolderMetadata) {
                continue;
            }
            if ($this->isFileMatch($item->getPathDisplay())) {
                $file                = new File\Dropbox($this->client, $item);
                $index               = $this->getFileIndex($file);
                $this->files[$index] = $file;
            }
        }
    }
}
