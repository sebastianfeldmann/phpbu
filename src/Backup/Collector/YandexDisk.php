<?php

namespace phpbu\App\Backup\Collector;

use Arhitector\Yandex\Disk;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;

/**
 * YandexDisk class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Alexander Palchikov AxelPAL <axelpal@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 */
class YandexDisk extends Remote implements Collector
{
    /**
     * @var Disk
     */
    protected $disk;

    /**
     * OpenStack constructor.
     *
     * @param Target $target
     * @param Path $path
     * @param Disk $disk
     */
    public function __construct(Target $target, Path $path, Disk $disk)
    {
        $this->setUp($target, $path);
        $this->disk = $disk;
    }

    /**
     * Collect all created backups.
     */
    protected function collectBackups()
    {
        $items = $this->disk->getResource($this->path->getPathThatIsNotChanging());
        foreach ($items->toArray()['items'] as $item) {
            if (($item instanceof Disk\Resource\Closed) && $this->isFileMatch($item->getPath())) {
                $file = new File\YandexDisk($this->disk, $item);
                $index = $this->getFileIndex($file);
                $this->files[$index] = $file;
            }
        }
    }
}
