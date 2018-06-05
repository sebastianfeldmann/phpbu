<?php
namespace phpbu\App\Backup\File;

use Kunnu\Dropbox\Dropbox as DropboxApi;
use Kunnu\Dropbox\Models\FileMetadata;

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
class Dropbox extends Remote
{
    /**
     * @var DropboxApi
     */
    protected $client;

    /**
     * Dropbox constructor.
     *
     * @param DropboxApi   $client
     * @param FileMetadata $dropboxFile
     */
    public function __construct(DropboxApi $client, FileMetadata $dropboxFile)
    {
        $this->client       = $client;
        $this->filename     = $dropboxFile->getName();
        $this->pathname     = $dropboxFile->getPathDisplay();
        $this->size         = $dropboxFile->getSize();
        $this->lastModified = strtotime($dropboxFile->getClientModified());
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->client->delete($this->pathname);
        } catch (\Exception $e) {
            throw new \phpbu\App\Exception($e->getMessage());
        }
    }
}
