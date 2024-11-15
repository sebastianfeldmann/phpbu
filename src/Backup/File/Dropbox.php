<?php
namespace phpbu\App\Backup\File;

use Kunnu\Dropbox as DropboxApi;
use phpbu\App\Exception;

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
     * Dropbox api client.
     *
     * @var \Kunnu\Dropbox\Dropbox
     */
    protected $client;

    /**
     * Dropbox constructor.
     *
     * @param \Kunnu\Dropbox\Dropbox             $client
     * @param \Kunnu\Dropbox\Models\FileMetadata $dropboxFile
     */
    public function __construct(DropboxApi\Dropbox $client, DropboxApi\Models\FileMetadata $dropboxFile)
    {
        $this->client       = $client;
        $this->filename     = $dropboxFile->getName();
        $this->pathname     = $dropboxFile->getPathDisplay();
        $this->size         = $dropboxFile->getSize();
        $this->lastModified = strtotime($dropboxFile->getClientModified());
    }

    /**
     * Deletes the file on Dropbox.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->client->delete($this->pathname);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
