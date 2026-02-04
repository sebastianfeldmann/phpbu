<?php
namespace phpbu\App\Backup\File;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use phpbu\App\Exception;

/**
 * Google Drive file class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       https://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class GoogleDrive extends Remote
{
    /**
     * Google drive api service.
     *
     * @var Google_Service_Drive
     */
    private $service;

    /**
     * Google api file id.
     *
     * @var string
     */
    private $fileId;

    /**
     * Constructor.
     *
     * @param Google_Service_Drive $service
     * @param Google_Service_Drive_DriveFile $googleFile
     */
    public function __construct(Google_Service_Drive $service, Google_Service_Drive_DriveFile $googleFile)
    {
        $this->service      = $service;
        $this->filename     = $googleFile->getName();
        $this->pathname     = $googleFile->getId();
        $this->fileId       = $googleFile->getId();
        $this->size         = (int) $googleFile->getSize();
        $this->lastModified = strtotime($googleFile->getCreatedTime());
    }

    /**
     * Deletes the file from Google Drive.
     *
     * @throws Exception
     */
    public function unlink()
    {
        try {
            $this->service->files->delete($this->fileId);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
