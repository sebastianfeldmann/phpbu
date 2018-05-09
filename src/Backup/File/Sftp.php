<?php
namespace phpbu\App\Backup\File;

/**
 * Sftp file class.
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
class Sftp extends Remote
{
    /**
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * Sftp constructor.
     *
     * @param \phpseclib\Net\SFTP $sftp
     * @param array               $fileInfo
     * @param string              $remotePath
     */
    public function __construct(\phpseclib\Net\SFTP $sftp, array $fileInfo, string $remotePath)
    {
        $this->sftp         = $sftp;
        $this->filename     = $fileInfo['filename'];
        $this->pathname     = $remotePath . '/' . $fileInfo['filename'];
        $this->size         = $fileInfo['size'];
        $this->lastModified = $fileInfo['mtime'];
    }

    /**
     * Deletes the file.
     */
    public function unlink()
    {
        $this->sftp->delete($this->pathname);
    }
}
