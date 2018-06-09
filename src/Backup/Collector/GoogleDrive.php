<?php
namespace phpbu\App\Backup\Collector;

use Google_Client;
use Google_Service_Drive;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\File;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;

/**
 * GoogleDrive class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class GoogleDrive extends Remote implements Collector
{
    /**
     * Google api client.
     *
     * @var \Google_Client
     */
    private $client;

    /**
     * Parent folder id.
     *
     * @var string
     */
    private $parent;

    /**
     * Constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param \phpbu\App\Backup\Path   $path
     * @param \Google_Client           $client
     */
    public function __construct(Target $target, Path $path, Google_Client $client)
    {
        $this->setUp($target, $path);
        $this->client = $client;
        $this->parent = $path->getPath();
    }

    /**
     * Collect all created backups.
     *
     * @return void
     */
    protected function collectBackups()
    {
        $service = new Google_Service_Drive($this->client);
        $results = $service->files->listFiles($this->getParams());

        /** @var \Google_Service_Drive_DriveFile $googleFile */
        foreach ($results->getFiles() as $googleFile) {
            if ($this->isFileMatch($this->path->getPath() . '/' . $googleFile->getName())) {
                $file                                    = new File\GoogleDrive($this->client, $googleFile);
                $this->files[$this->getFileIndex($file)] = $file;
            }
        }
    }

    /**
     * Return google api params list to find all backups.
     *
     * @return array
     */
    private function getParams() : array
    {
        return [
            'includeTeamDriveItems' => false,
            'pageSize'              => 1000,
            'fields'                => 'nextPageToken, files(id, name, createdTime, size)',
            'spaces'                => 'drive',
            'q'                     => 'trashed = false AND visibility = \'limited\'' . $this->getParentsFilter(),
        ];
    }

    /**
     * Return parent filter query.
     *
     * @return string
     */
    private function getParentsFilter() : string
    {
        return empty($this->parent) ? '' : ' AND \'' . $this->parent . '\' in parents';
    }
}
