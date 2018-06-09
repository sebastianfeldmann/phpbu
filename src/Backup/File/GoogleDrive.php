<?php
namespace phpbu\App\Backup\File;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use phpbu\App\Exception;

/**
 * GoogleDrive file class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class GoogleDrive extends Remote
{
    /**
     * Google api client.
     *
     * @var \Google_Client
     */
    private $client;

    /**
     * Goole api file id.
     *
     * @var string
     */
    private $fileId;

    /**
     * Constructor.
     *
     * @param \Google_Client                  $client
     * @param \Google_Service_Drive_DriveFile $googleFile
     */
    public function __construct(Google_Client $client, Google_Service_Drive_DriveFile $googleFile)
    {
        $this->client       = $client;
        $this->filename     = $googleFile->getName();
        $this->pathname     = $googleFile->getId();
        $this->fileId       = $googleFile->getId();
        $this->size         = $googleFile->getSize();
        $this->lastModified = strtotime($googleFile->getCreatedTime());
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $service = new Google_Service_Drive($this->client);
            $service->files->delete($this->fileId);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
