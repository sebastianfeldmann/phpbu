<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\File\FileRemote;
use phpbu\App\Backup\Sync\Dropbox as DropboxSync;
use phpbu\App\Backup\Target;

class Dropbox extends Collector
{
    /**
     * @var DropboxSync
     */
    protected $sync;

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
     * @param DropboxSync            $sync
     */
    public function __construct(Target $target, DropboxSync $sync)
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
        $items = $this->sync->getClient()->listFolder($this->sync->getPath(), ['limit' => 100]);
        foreach ($items->getItems() as $item) {
            // skip directories
            if ($item instanceof \Kunnu\Dropbox\Models\FolderMetadata) {
                continue;
            }
            /** @var \Kunnu\Dropbox\Models\FileMetadata $item */
            // skip currently created backup
            if ($item->getPathDisplay() == $this->sync->getPath() . $this->target->getFilename()) {
                continue;
            }
            if (preg_match('#' . $this->fileRegex . '#i', $item->getName())) {
                $attributes = [
                    'name' => $item->getName(),
                    'pathname' => $item->getPathDisplay(),
                    'size' => (int)$item->getSize(),
                    'last_modified' => strtotime($item->getClientModified()),
                ];
                $this->files[] = new FileRemote($attributes, $this->sync);
            }
        }

        return $this->files;
    }
}