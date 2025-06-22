<?php

namespace phpbu\App\Backup\File;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use InvalidArgumentException;
use phpbu\App\Exception;

/**
 * YandexDisk class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Alexander Palchikov AxelPAL <axelpal@gmail.com>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 */
class YandexDisk extends Remote
{
    /**
     * YandexDisk api client.
     *
     * @var Disk
     */
    protected $disk;

    /**
     * YandexDisk constructor.
     *
     * @param Disk $disk
     * @param Closed $yandexFile
     */
    public function __construct(Disk $disk, Closed $yandexFile)
    {
        $this->disk = $disk;
        $this->filename = $yandexFile->get('name');
        $this->pathname = $yandexFile->get('path');
        $this->size = $yandexFile->get('size');
        $this->lastModified = strtotime($yandexFile->get('modified'));
    }

    /**
     * Deletes the file on YandexDisk.
     *
     * @throws Exception
     */
    public function unlink()
    {
        try {
            $item = $this->disk->getResource($this->pathname);
            $item->delete($this->pathname);
        } catch (InvalidArgumentException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
