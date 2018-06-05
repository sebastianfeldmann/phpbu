<?php
namespace phpbu\App\Backup\File;

use phpbu\App\Exception;
use SebastianFeldmann\Ftp\Client;
use SebastianFeldmann\Ftp\File;

/**
 * Ftp file class.
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
class Ftp extends Remote
{
    /**
     * @var resource
     */
    private $ftpClient;

    /**
     * Ftp constructor.
     *
     * @param \SebastianFeldmann\Ftp\Client $ftpClient
     * @param \SebastianFeldmann\Ftp\File   $ftpFile
     * @param string                        $path
     */
    public function __construct(Client $ftpClient, File $ftpFile, string $path)
    {
        $this->ftpClient    = $ftpClient;
        $this->filename     = $ftpFile->getFilename();
        $this->pathname     = (!empty($path) ? rtrim($path, '/') . '/' : '') . $ftpFile->getFilename();
        $this->size         = $ftpFile->getSize();
        $this->lastModified = $ftpFile->getLastModifyDate()->getTimestamp();
    }

    /**
     * Deletes the file.
     *
     * @throws \phpbu\App\Exception
     */
    public function unlink()
    {
        try {
            $this->ftpClient->chHome();
            $this->ftpClient->delete($this->pathname);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
