<?php
namespace phpbu\App\Backup\Collector;

use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Target;

/**
 * Sftp class.
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
class Ftp extends Collector
{
    /**
     * FTP connection stream
     *
     * @var resource
     */
    private $ftpConnection;

    /**
     * Ftp constructor.
     *
     * @param \phpbu\App\Backup\Target     $target
     * @param resource                     $ftpConnection
     */
    public function __construct(Target $target, $ftpConnection)
    {
        $this->ftpConnection = $ftpConnection;
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles() : array
    {
        $files = ftp_nlist($this->ftpConnection, '.');
        foreach ($files as $filename) {
            if ($filename == $this->target->getFilename()) {
                continue;
            }
            if ($this->isFilenameMatch($filename)) {
                $this->files[] = new \phpbu\App\Backup\File\Ftp($this->ftpConnection, $filename);
            }
        }
        return $this->files;
    }
}
